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
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\Aggregation\Metric;
use Magento\Framework\Search\Request\Aggregation\TermBucket;
use Magento\Framework\Search\Request\Query\Match;
use Magento\Framework\Search\RequestInterface;

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

        /** @var \IntegerNet\Solr\Request\SearchRequest $actualSolrRequest */
        $actualSolrRequest = $this->converter->convert($magentoRequest);
        $this->assertInstanceOf(\IntegerNet\Solr\Request\SearchRequest::class, $actualSolrRequest);
        $actualFq = $actualSolrRequest->getFilterQueryBuilder()->buildFilterQuery($storeId);
        $this->assertFilterQueryParts($expectedFqParts, $actualFq);
    }
    public static function dataConvertRequest()
    {
        $bagStyleFilterMagentoRequest = new \Magento\Framework\Search\Request(
            'quick_search_container',
            'catalogsearch_fulltext',
            new \Magento\Framework\Search\Request\Query\BoolExpression(
                'quick_search_container',
                '1',
                [],
                [
                    'search' => new Match(
                        'search',
                        'bag',
                        1,
                        array(
                            0 =>
                                array(
                                    'field' => 'sku',
                                ),
                            1 =>
                                array(
                                    'field' => '*',
                                ),
                            2 =>
                                array(
                                    'field' => 'name',
                                    'boost' => '5',
                                ),
                            3 =>
                                array(
                                    'field' => 'description',
                                    'boost' => '1',
                                ),
                            4 =>
                                array(
                                    'field' => 'short_description',
                                    'boost' => '1',
                                ),
                            5 =>
                                array(
                                    'field' => 'manufacturer',
                                    'boost' => '1',
                                ),
                            6 =>
                                array(
                                    'field' => 'status',
                                    'boost' => '1',
                                ),
                            7 =>
                                array(
                                    'field' => 'tax_class_id',
                                    'boost' => '1',
                                ),
                        )
                    ),
                    'style_bags_query' => new \Magento\Framework\Search\Request\Query\Filter(
                        'style_bags_query',
                        1,
                        'filter',
                        new \Magento\Framework\Search\Request\Filter\Term('style_bags_filter', '24', 'style_bags')
                    )
                ],
                []
            ),
            0,
            10000,
            [
                'scope' => new \Magento\Framework\Search\Request\Dimension('scope', '1'),
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
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $request->setParams([\Magento\Search\Model\QueryFactory::QUERY_VAR_NAME => $queryText]);
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