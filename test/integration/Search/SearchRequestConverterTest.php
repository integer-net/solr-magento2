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
use IntegerNet\Solr\Request\SearchRequest;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface as MagentoRequestInterface;
use Magento\Framework\Search\Request as MagentoRequest;
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
        return [
            'bag_style_filter' => [
                'query_text' => 'bag',
                'magento_request' => self::createMagentoRequest(
                    [],
                    [
                        'search' => new MagentoRequest\Query\Match(
                            'search',
                            'bag',
                            1,
                            [] // fields to mach query are handled by library
                        ),
                        'style_bags_query' => new MagentoRequest\Query\Filter(
                            'style_bags_query',
                            1,
                            'filter',
                            new MagentoRequest\Filter\Term('style_bags_filter', '24', 'style_bags')
                        )
                    ]
                ),
                'expected_filter_query' => [
                    'store_id:1',
                    'style_bags_facet:24',
                ],
            ],
            'category_filter' => [
                'query_text' => 'bag',
                'magento_request' => self::createMagentoRequest(
                    [
                        'category' => new MagentoRequest\Query\Filter(
                            'category',
                            1,
                            'filter',
                            new MagentoRequest\Filter\Term('category_filter', '20', 'category_ids')
                        )
                    ],
                    [
                        'search' => new MagentoRequest\Query\Match(
                            'search',
                            'bag',
                            1,
                            [] // fields to mach query are handled by library
                        ),
                    ]
                ),
                'expected_filter_query' => [
                    'store_id:1',
                    'category:20',
                ],
            ],
            'price_filter_range' => [
                'query_text' => 'bag',
                'magento_request' => self::createMagentoRequest(
                    [
                        'category' => new MagentoRequest\Query\Filter(
                            'price',
                            1,
                            'filter',
                            new MagentoRequest\Filter\Range('price_filter', 'price', '10', '19.999')
                        )
                    ],
                    [
                        'search' => new MagentoRequest\Query\Match(
                            'search',
                            'bag',
                            1,
                            [] // fields to mach query are handled by library
                        ),
                    ]
                ),
                'expected_filter_query' => [
                    'store_id:1',
                    'price_f:[10.000000 TO 19.999000]',
                ],
            ],
        ];
    }

    /**
     * @param $shouldMatch
     * @return MagentoRequest
     */
    private static function createMagentoRequest($mustMatch, $shouldMatch)
    {
        return new MagentoRequest(
            'quick_search_container',
            'catalogsearch_fulltext',
            new MagentoRequest\Query\BoolExpression('quick_search_container', '1', $mustMatch, $shouldMatch, []),
            0, 10000,
            [
                'scope' => new MagentoRequest\Dimension('scope', '1'),
            ],
            [
                new MagentoRequest\Aggregation\DynamicBucket(
                    'price_bucket',
                    'price',
                    'auto'
                ),
                new MagentoRequest\Aggregation\TermBucket(
                    'category_bucket',
                    'category_ids',
                    [new MagentoRequest\Aggregation\Metric('count')]
                ),
                new MagentoRequest\Aggregation\TermBucket(
                    'style_bags_bucket',
                    'style_bags',
                    [new MagentoRequest\Aggregation\Metric('count')]
                ),
            ]
        );
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