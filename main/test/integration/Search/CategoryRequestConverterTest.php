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


use IntegerNet\Solr\Model\Search\Adapter\CategoryRequestConverter;
use IntegerNet\SolrCategories\Request\CategoryRequest;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface as MagentoRequestInterface;
use Magento\Framework\Search\Request as MagentoRequest;
use Magento\Framework\Search\RequestInterface;
use Magento\Search\Model\QueryFactory;

class CategoryRequestConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryRequestConverter
     */
    private $converter;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->converter = $this->objectManager->create(CategoryRequestConverter::class);
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

        /** @var CategoryRequest $actualSolrRequest */
        $actualSolrRequest = $this->converter->convert($magentoRequest);
        $this->assertInstanceOf(CategoryRequest::class, $actualSolrRequest);
        $actualFq = $actualSolrRequest->getFilterQueryBuilder()->buildFilterQuery($storeId);
        $this->assertFilterQueryParts($expectedFqParts, $actualFq);
    }

    public static function dataConvertRequest()
    {
        return [
            'color_filter' => [
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
                        'color_query' => new MagentoRequest\Query\Filter(
                            'color_query',
                            1,
                            'filter',
                            new MagentoRequest\Filter\Term('color_filter', '24', 'color')
                        )
                    ]
                ),
                'expected_filter_query' => [
                    'store_id:1',
                    'is_visible_in_catalog_i:1',
                    'color_facet:24',
                ],
            ],
            'price_filter_range' => [
                'query_text' => 'bag',
                'magento_request' => self::createMagentoRequest(
                    [
                        'category' => new MagentoRequest\Query\Filter(
                            'category',
                            1,
                            'filter',
                            new MagentoRequest\Filter\Term('category_filter', '20', 'category_ids')
                        ),
                        'price' => new MagentoRequest\Query\Filter(
                            'price',
                            1,
                            'filter',
                            new MagentoRequest\Filter\Range('price_filter', 'price', '10', '19.999')
                        )
                    ],
                    []
                ),
                'expected_filter_query' => [
                    'store_id:1',
                    'is_visible_in_catalog_i:1',
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
            'catalog_view_container',
            'catalogsearch_fulltext',
            new MagentoRequest\Query\BoolExpression('catalog_view_container', '1', $mustMatch, $shouldMatch, []),
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
                    'color_bucket',
                    'color',
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