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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductAttributeRepositoryInterface
     */
    private $productAttributeRepositoryMock;
    use AttributeRepositoryMock;

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
    public function testFilterableInSearchAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
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
    public function testFilterableInCatalogAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
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
    public function testFilterableInCatalogOrSearchAttributes(array $dataAttributes, $useAlphabeticalSearch, $expectedFilters, $expectedSortOrder)
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
    public function testSearchableAttributes(array $dataAttributes, array $expectedFilters, $expectedSortOrder)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters, $expectedSortOrder);
        $attributes = $attributeRepository->getSearchableAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
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
        $attribute = $attributeRepository->getAttributeByCode($storeId, $attributeCode);
        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals($attributeCode, $attribute->getAttributeCode());
        $this->assertEquals($expectedLabel, $attribute->getStoreLabel());
    }

    public function testUnknownAttributeThrowsException()
    {
        $attributeRepository = $this->getAttributeRepository([], [], null);
        $this->productAttributeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException());
        $this->setExpectedException(Exception::class, 'Attribute whatever does not exist');
        $attributeRepository->getAttributeByCode(null, 'whatever');
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
                ]
            ]),
            new FilterGroup([
                FilterGroup::FILTERS => [
                    new Filter([
                        Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                        Filter::KEY_VALUE => '1'
                    ]),
                ]
            ]),
            new FilterGroup([
                FilterGroup::FILTERS => [
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
        $searchCriteriaBuilderMock = $this->mockSearchCriteriaBuilder($expectedFilters, $expectedSortOrder);
        $searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteriaDummy);
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(SearchCriteriaBuilderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $this->productAttributeRepositoryMock = $this->mockProductAttributeRepository($dataAttributes, $searchCriteriaDummy);
        $attributeRepository = new AttributeRepository(
            $this->productAttributeRepositoryMock,
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