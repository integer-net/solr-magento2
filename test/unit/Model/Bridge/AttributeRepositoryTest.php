<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\TestUtil\Traits\AttributeRepositoryMock;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;

class AttributeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use AttributeRepositoryMock;
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
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    protected function mockSearchCriteriaBuilder()
    {
        $searchCriteriaBuilderMock = $this->getSearchCriteriaBuilderMock();
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with('frontend_label', AbstractCollection::SORT_ORDER_ASC);
        $searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [
                    new Filter([
                        Filter::KEY_FIELD => 'is_filterable_in_search',
                        Filter::KEY_VALUE => '1'
                    ])
                ],
                [
                    new Filter([
                        Filter::KEY_FIELD => 'attribute_code',
                        Filter::KEY_CONDITION_TYPE => 'neq',
                        Filter::KEY_VALUE => 'status'
                    ])
                ]
            );
        return $searchCriteriaBuilderMock;
    }

    /**
     * @dataProvider dataAttributes
     * @param array $dataAttributes
     */
    public function testItReturnsFilterableInSearchAttributes(array $dataAttributes)
    {
        $storeId = 0;
        $searchCriteriaDummy = new SearchCriteria();
        $searchCriteriaBuilderMock = $this->mockSearchCriteriaBuilder();
        $searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteriaDummy);
        $productAttributeRepositoryMock = $this->mockProductAttributeRepository($dataAttributes, $searchCriteriaDummy);
        $attributeRepository = new AttributeRepository($productAttributeRepositoryMock, $searchCriteriaBuilderMock);
        //TODO test store id if necessary
        $attributes = $attributeRepository->getFilterableInSearchAttributes($storeId);
        $this->assertCount(count($dataAttributes), $attributes);
        foreach ($attributes as $actualAttribute) {
            $this->assertInstanceOf(Attribute::class, $actualAttribute);
            $expectedAttributeCode = \array_shift($dataAttributes)[EavAttributeInterface::ATTRIBUTE_CODE];
            $this->assertEquals($expectedAttributeCode, $actualAttribute->getAttributeCode());
        }
    }

    public static function dataAttributes()
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
        return [
            [$attributesData]
        ];
    }

}