<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use IntegerNet\Solr\TestUtil\Traits\AttributeRepositoryMock;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

/**
 * @covers AttributeRepository
 * @covers AttributeSearchCriteriaBuilder
 */
class AttributeRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductAttributeRepositoryInterface
     */
    private $productAttributeRepositoryMock;
    use AttributeRepositoryMock;

    protected function tearDown()
    {
        $this->productAttributeRepositoryMock = null;
    }
    /**
     * @return array
     */
    private static function dataAttributes()
    {
        $attributesData = [
            'attribute_1' => [
                EavAttributeInterface::ATTRIBUTE_CODE => 'attribute_1',
                EavAttributeInterface::FRONTEND_LABEL => 'Attribute 1',
            ],
            'attribute_2' => [
                EavAttributeInterface::ATTRIBUTE_CODE => 'attribute_2',
                EavAttributeInterface::FRONTEND_LABEL => 'Attribute 2',
            ],
            'attribute_3' => [
                EavAttributeInterface::ATTRIBUTE_CODE => 'attribute_3',
                EavAttributeInterface::FRONTEND_LABEL => 'Attribute 3',
            ],
        ];
        return $attributesData;
    }

    /**
     * @return SortOrder
     */
    private static function dataOrderByPosition()
    {
        return new SortOrder([
            SortOrder::FIELD => 'position',
            SortOrder::DIRECTION => 'ASC'
        ]);
    }

    /**
     * @return SortOrder
     */
    private static function dataOrderByLabel()
    {
        return new SortOrder([
            SortOrder::FIELD => 'frontend_label',
            SortOrder::DIRECTION => 'ASC'
        ]);
    }

    /**
     * @param array $dataAttributes
     * @param SearchCriteria $expectedSearchCriteria
     * @return ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockProductAttributeRepository(array $dataAttributes, SearchCriteria $expectedSearchCriteria)
    {
        return $this->mockAttributeRepository($dataAttributes, $expectedSearchCriteria,
            ProductAttributeRepositoryInterface::class, AttributeResource::class);
    }

    /**
     * @dataProvider dataFilterableInSearchAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testFilterableInSearchAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->assertMagentoAttributeRegistry($attributes, $attributeRepository);
    }

    /**
     * @dataProvider dataFilterableInCatalogAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testFilterableInCatalogAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->assertMagentoAttributeRegistry($attributes, $attributeRepository);
    }

    /**
     * @dataProvider dataFilterableInCatalogOrSearchAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testFilterableInCatalogOrSearchAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInCatalogOrSearchAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->assertMagentoAttributeRegistry($attributes, $attributeRepository);
    }

    /**
     * @dataProvider dataSearchableAttributes
     * @param array $dataAttributes
     * @param array $expectedFilters
     * @param $expectedSortOrder
     */
    public function testSearchableAttributes(array $dataAttributes, array $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getSearchableAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->assertMagentoAttributeRegistry($attributes, $attributeRepository);
    }

    /**
     * @dataProvider dataSortableAttributes
     * @param array $dataAttributes
     * @param array $expectedFilters
     * @param $expectedSortOrder
     */
    public function testSortableAttributes(array $dataAttributes, array $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getSortableAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->assertMagentoAttributeRegistry($attributes, $attributeRepository);
    }

    /**
     * getAttributeCodesToIndex() should return filterable (in catalog or search) and searchable attributes,
     *
     * @dataProvider dataAttributeCodesToIndex
     * @param array $dataAttributes
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testAttributeCodesToIndex(array $dataAttributes, $expectedFilters, $expectedSortOrder)
    {
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $actualAttributeCodes = $attributeRepository->getAttributeCodesToIndex();
        $expectedAttributeCodes = array_values(array_map(function($data) {
            return $data['attribute_code'];
        }, $dataAttributes));
        $this->assertEquals($expectedAttributeCodes, $actualAttributeCodes);

        // calling a second time should not trigger query to ProductAttributeRepository
        $attributeRepository->getAttributeCodesToIndex();
    }

    /**
     * @dataProvider dataAttributeByCode
     * @param array $dataAttributes
     * @param $storeId
     * @param $attributeCode
     * @param $expectedLabel
     */
    public function testAttributeByCode(array $dataAttributes, $storeId, $attributeCode, $expectedLabel)
    {
        $attributeRepository = $this->getAttributeRepository($dataAttributes, [], null);
        $this->productAttributeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function($code) use ($dataAttributes) {
                return $this->mockAttribute(AttributeResource::class, $dataAttributes[$code]);
            });
        $attribute = $attributeRepository->getAttributeByCode($attributeCode, $storeId);
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals($attributeCode, $attribute->getAttributeCode());
        $this->assertEquals($expectedLabel, $attribute->getStoreLabel());
        $this->assertMagentoAttributeRegistry([$attribute], $attributeRepository);
    }

    public function testUnknownAttributeThrowsException()
    {
        $attributeRepository = $this->getAttributeRepository([], [], null);
        $this->productAttributeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException());
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Attribute whatever does not exist');
        $attributeRepository->getAttributeByCode('whatever', null);
    }

    public static function dataFilterableInSearchAttributes()
    {
        $expectedFilters = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE, 'status', 'neq'
            ],
            [
                EavAttributeInterface::IS_FILTERABLE_IN_SEARCH, '1'
            ],
        ];
        return [
            [self::dataAttributes(), true, $expectedFilters, self::dataOrderByLabel()],
            [self::dataAttributes(), false, $expectedFilters, self::dataOrderByPosition()],
        ];
    }

    public static function dataSearchableAttributes()
    {
        $expectedFilters = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE, 'status', 'neq'
            ],
            [
                EavAttributeInterface::IS_SEARCHABLE, '1'
            ],
        ];
        return [
            [self::dataAttributes(), $expectedFilters, null],
        ];
    }
    public static function dataSortableAttributes()
    {
        $expectedFilters = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE, 'status', 'neq'
            ],
            [
                EavAttributeInterface::USED_FOR_SORT_BY, '1'
            ],
        ];
        return [
            [self::dataAttributes(), $expectedFilters, null],
        ];
    }
    public static function dataFilterableInCatalogAttributes()
    {
        $expectedFilters = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE, 'status', 'neq'
            ],
            [
                EavAttributeInterface::IS_FILTERABLE, '1'
            ],
        ];
        return [
            [self::dataAttributes(), true, $expectedFilters, self::dataOrderByLabel()],
            [self::dataAttributes(), false, $expectedFilters, self::dataOrderByPosition()],
        ];
    }
    public static function dataFilterableInCatalogOrSearchAttributes()
    {
        $expectedFilters = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE, 'status', 'neq'
            ],
            new FilterGroup([
                FilterGroup::FILTERS => [
                    new Filter([
                        Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE,
                        Filter::KEY_VALUE => '1'
                    ]),
                    new Filter([
                        Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                        Filter::KEY_VALUE => '1'
                    ]),
                ]
            ])
        ];
        return [
            [self::dataAttributes(), true, $expectedFilters, self::dataOrderByLabel()],
            [self::dataAttributes(), false, $expectedFilters, self::dataOrderByPosition()],
        ];
    }

    public static function dataAttributeCodesToIndex()
    {
        $expectedFilters = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE, 'status', 'neq'
            ],
            new FilterGroup([
                FilterGroup::FILTERS => [
                    new Filter([
                        Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE,
                        Filter::KEY_VALUE => '1'
                    ]),
                    new Filter([
                        Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                        Filter::KEY_VALUE => '1'
                    ]),
                    new Filter([
                        Filter::KEY_FIELD => EavAttributeInterface::IS_SEARCHABLE,
                        Filter::KEY_VALUE => '1'
                    ]),
                ]
            ]),
        ];
        return [
            [self::dataAttributes(), $expectedFilters, null]
        ];
    }

    public static function dataAttributeByCode()
    {
        return [
            [self::dataAttributes(), null, 'attribute_1', 'Attribute 1']
        ];
    }
    /**
     * @param array $dataAttributes
     * @param array $expectedFilters
     * @param $expectedSortOrder
     * @return AttributeRepository
     */
    protected function getAttributeRepository(array $dataAttributes, array $expectedFilters, $expectedSortOrder)
    {
        $searchCriteriaDummy = new SearchCriteria();
        $searchCriteriaBuilderMock = $this->searchCriteriaBuilderExpects($this->getSearchCriteriaBuilderMock(), $expectedFilters, $expectedSortOrder, $searchCriteriaDummy);
        $this->productAttributeRepositoryMock = $this->mockProductAttributeRepository($dataAttributes, $searchCriteriaDummy);
        $attributeRepository = new AttributeRepository(
            $this->productAttributeRepositoryMock,
            new AttributeSearchCriteriaBuilder($this->mockSearchCriteriaBuilderFactory($searchCriteriaBuilderMock)));
        return $attributeRepository;
    }

    /**
     * @param array $dataAttributes
     * @param $attributes
     */
    private function assertAttributeCodes(array $dataAttributes, $attributes)
    {
        $this->assertCount(count($dataAttributes), $attributes);
        foreach ($attributes as $actualAttribute) {
            $this->assertInstanceOf(Attribute::class, $actualAttribute);
            $expectedAttributeCode = \array_shift($dataAttributes)[EavAttributeInterface::ATTRIBUTE_CODE];
            $this->assertEquals($expectedAttributeCode, $actualAttribute->getAttributeCode());
        }
    }

    /**
     * @param $attributes
     * @param $attributeRepository
     */
    private function assertMagentoAttributeRegistry($attributes, $attributeRepository)
    {
        foreach ($attributes as $actualAttribute) {
            $this->assertNotNull($attributeRepository->getMagentoAttribute($actualAttribute),
                'Magento attribute should be registered in repository');
            $this->assertEquals($actualAttribute->getAttributeCode(),
                $attributeRepository->getMagentoAttribute($actualAttribute)->getAttributeCode(),
                'Registered Magento attribute should have same code');
        }
    }

}