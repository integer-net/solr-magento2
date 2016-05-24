<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use IntegerNet\Solr\TestUtil\Traits\AttributeRepositoryMock;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;

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
     * @return SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockSearchCriteriaBuilder($expectedFilters)
    {
        $searchCriteriaBuilderMock = $this->getSearchCriteriaBuilderMock();
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with('frontend_label', AbstractCollection::SORT_ORDER_ASC);
        $searchCriteriaBuilderMock->expects($this->exactly(count($expectedFilters)))
            ->method('addFilter')
            ->withConsecutive(...$expectedFilters);
        return $searchCriteriaBuilderMock;
    }

    /**
     * @dataProvider dataFilterableInSearchAttributes
     * @param array $dataAttributes
     * @param $expectedFilters
     */
    public function testItReturnsFilterableInSearchAttributes(array $dataAttributes, $expectedFilters)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters);
        $attributes = $attributeRepository->getFilterableInSearchAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
    }

    /**
     * @dataProvider dataSearchableAttributes
     * @param array $dataAttributes
     * @param array $expectedFilters
     */
    public function testItReturnsSearchableAttributes(array $dataAttributes, array $expectedFilters)
    {
        $storeId = 0;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters);
        $attributes = $attributeRepository->getSearchableAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
    }

    /**
     * @dataProvider dataFilterableInSearchAttributes
     * @param array $dataAttributes
     * @param $expectedFilters
     */
    public function testItUsesStoreId(array $dataAttributes, $expectedFilters)
    {
        $storeId = 1;
        $attributeRepository = $this->getAttributeRepository($dataAttributes, $expectedFilters);
        $attributes = $attributeRepository->getFilterableInSearchAttributes($storeId);
        $this->assertAttributeCodes($dataAttributes, $attributes);
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid store id 1');
        $attributes[0]->getStoreLabel();
    }

    public static function dataFilterableInSearchAttributes()
    {
        $expectedFilters = [
            [
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::ATTRIBUTE_CODE,
                    Filter::KEY_CONDITION_TYPE => 'neq',
                    Filter::KEY_VALUE => 'status'
                ])
            ],
            [
            new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                Filter::KEY_VALUE => '1'
            ])
            ],
        ];
        return [
            [self::dataAttributes(), $expectedFilters]
        ];
    }
    public static function dataSearchableAttributes()
    {
        $expectedFilters = [
            [
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::ATTRIBUTE_CODE,
                    Filter::KEY_CONDITION_TYPE => 'neq',
                    Filter::KEY_VALUE => 'status'
                ])
            ],
            [
            new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::IS_SEARCHABLE,
                Filter::KEY_VALUE => '1'
            ])
            ],
        ];
        return [
            [self::dataAttributes(), $expectedFilters]
        ];
    }

    /**
     * @param array $dataAttributes
     * @param array $expectedFilters
     * @return AttributeRepository
     */
    protected function getAttributeRepository(array $dataAttributes, array $expectedFilters)
    {
        $searchCriteriaDummy = new SearchCriteria();
        $searchCriteriaBuilderMock = $this->mockSearchCriteriaBuilder($expectedFilters);
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