<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Search;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FulltextSearchCollection;


namespace IntegerNet\Solr\Search;


use IntegerNet\Solr\Model\Search\Adapter\SearchRequestConverter;
use IntegerNet\Solr\Request\Request;
use IntegerNet\Solr\Request\SearchRequest;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface as MagentoRequestInterface;
use Magento\Framework\Search\Request as MagentoRequest;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\Aggregation\Metric;
use Magento\Framework\Search\Request\Aggregation\TermBucket;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\Query\Match;
use Magento\Framework\Search\RequestInterface;
use Magento\Search\Model\QueryFactory;

class SearchRequestConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchRequestConverter
     */
    private $converter;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->converter = $this->objectManager->create(SearchRequestConverter::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider dataConvertRequest
     * @param string $queryText
     * @param RequestInterface $magentoRequest
     * @param string[] $expectedFqParts
     */
    public function testConvertRequest($queryText, RequestInterface $magentoRequest, array $expectedFqParts)
    {
        $storeId = 1;
        $this->setRequestQueryText($queryText);

        /** @var SearchRequest $actualSolrRequest */
        $actualSolrRequest = $this->converter->convert($magentoRequest);
        $this->assertInstanceOf(SearchRequest::class, $actualSolrRequest);
        $actualFq = $actualSolrRequest->getFilterQueryBuilder()->buildFilterQuery($storeId);
        $this->assertFilterQueryParts($expectedFqParts, $actualFq);
    }

    public static function dataConvertRequest()
    {
        $bagStyleFilterMagentoRequest = new MagentoRequest(
            'quick_search_container',
            'catalogsearch_fulltext',
            new BoolExpression(
                'quick_search_container',
                '1',
                [],
                [
                    'search' => new Match(
                        'search',
                        'bag',
                        1,
                        [] // fields to mach query are handled by library
                    ),
                    'style_bags_query' => new Filter(
                        'style_bags_query',
                        1,
                        'filter',
                        new Term('style_bags_filter', '24', 'style_bags')
                    )
                ],
                []
            ),
            0,
            10000,
            [
                'scope' => new Dimension('scope', '1'),
            ],
            [
                new DynamicBucket('price_bucket', 'price', 'auto'),
                new TermBucket('category_bucket', 'category_ids', [new Metric('count')]),
                new TermBucket('style_bags_bucket', 'style_bags', [new Metric('count')]),
            ]
        );

        return [
            'bag_style_filter' => [
                'query_text' => 'bag',
                'magento_request' => $bagStyleFilterMagentoRequest,
                'expected_filter_query' => [
                    'store_id:1',
                    'style_bags_facet:24',
                ],
            ]
        ];
    }

    /**
     * @param $queryText
     */
    private function setRequestQueryText($queryText)
    {
        /** @var MagentoRequestInterface $request */
        $request = $this->objectManager->get(MagentoRequestInterface::class);
        $request->setParams([QueryFactory::QUERY_VAR_NAME => $queryText]);
    }

    /**
     * @param array $expectedFqParts
     * @param $actualFq
     */
    private function assertFilterQueryParts(array $expectedFqParts, $actualFq)
    {
        foreach ($expectedFqParts as $fqPart) {
            $this->assertContains($fqPart, $actualFq);
        }
    }
}