<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use IntegerNet\Solr\TestUtil\Traits\AttributeRepositoryMock;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;

class AttributeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use AttributeRepositoryMock;

    /**
     * @return array
     */
    private static function dataAttributes()
    {
        $attributesData = [
            [
                EavAttributeInterface::ATTRIBUTE_CODE => 'attribute_1',
                EavAttributeInterface::FRONTEND_LABEL => 'Attribute 1',
            ],
            [
                EavAttributeInterface::ATTRIBUTE_CODE => 'attribute_2',
                EavAttributeInterface::FRONTEND_LABEL => 'Attribute 2',
            ],
            [
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
     * @param $expectedFilters
     * @param $expectedSortOrder
     * @return SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockSearchCriteriaBuilder($expectedFilters, $expectedSortOrder)
    {
        $searchCriteriaBuilderMock = $this->getSearchCriteriaBuilderMock();
        if ($expectedSortOrder) {
            $searchCriteriaBuilderMock->expects($this->once())
                ->method('addSortOrder')
                ->with($expectedSortOrder);
        } else {
            $searchCriteriaBuilderMock->expects($this->never())
                ->method('addSortOrder');
        }
        $expectedAddFilter = [];
        $expectedAddFilters = [];
        foreach ($expectedFilters as $expectedFilter) {
            if ($expectedFilter instanceof FilterGroup) {
                $expectedAddFilters[] = [ $expectedFilter->getFilters() ];
            } else {
                $expectedAddFilter[] = $expectedFilter;
            }
        }
        $searchCriteriaBuilderMock->expects($this->exactly(count($expectedAddFilter)))
            ->method('addFilter')
            ->withConsecutive(...$expectedAddFilter);
        $searchCriteriaBuilderMock->expects($this->exactly(count($expectedAddFilters)))
            ->method('addFilters')
            ->withConsecutive(...$expectedAddFilters);
        return $searchCriteriaBuilderMock;
    }

    /**
     * @dataProvider dataFilterableInSearchAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testItReturnsFilterableInSearchAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);

        // deprecated getFilterableAttributes() assumes getFilterableInSearchAttributes(), but should not be used
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
    }

    /**
     * @dataProvider dataFilterableInCatalogAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testItReturnsFilterableInCatalogAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
    }

    /**
     * @dataProvider dataFilterableInCatalogOrSearchAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testItReturnsFilterableInCatalogOrSearchAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInCatalogOrSearchAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
    }

    /**
     * @dataProvider dataSearchableAttributes
     * @param array $dataAttributes
     * @param array $expectedFilters
     * @param $expectedSortOrder
     */
    public function testItReturnsSearchableAttributes(array $dataAttributes, array $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getSearchableAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
    }

    /**
     * @dataProvider dataFilterableInSearchAttributes
     * @param array $dataAttributes
     * @param $useAlphabeticalSearch
     * @param $expectedFilters
     * @param $expectedSortOrder
     */
    public function testItUsesStoreId(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
    {
        $storeId = 1;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid store id 1');
        $attributes[0]->getStoreLabel();
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
                ]
            ]),
            new FilterGroup([
                FilterGroup::FILTERS => [
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

    /**
     * @param array $dataAttributes
     * @param array $expectedFilters
     * @param $expectedSortOrder
     * @return AttributeRepository
     */
    protected function getAttributeRepository(array $dataAttributes, array $expectedFilters, $expectedSortOrder)
    {
        $searchCriteriaDummy = new SearchCriteria();
        $searchCriteriaBuilderMock = $this->mockSearchCriteriaBuilder($expectedFilters, $expectedSortOrder);
        $searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteriaDummy);
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(SearchCriteriaBuilderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $productAttributeRepositoryMock = $this->mockProductAttributeRepository($dataAttributes, $searchCriteriaDummy);
        $attributeRepository = new AttributeRepository(
            $productAttributeRepositoryMock,
            new AttributeSearchCriteriaBuilder($searchCriteriaBuilderFactoryMock));
        return $attributeRepository;
    }

    /**
     * @param array $dataAttributes
     * @param $attributes
     */
    protected function assertAttributeCodes(array $dataAttributes, $attributes)
    {
        $this->assertCount(count($dataAttributes), $attributes);
        foreach ($attributes as $actualAttribute) {
            $this->assertInstanceOf(Attribute::class, $actualAttribute);
            $expectedAttributeCode = \array_shift($dataAttributes)[EavAttributeInterface::ATTRIBUTE_CODE];
            $this->assertEquals($expectedAttributeCode, $actualAttribute->getAttributeCode());
        }
    }

}