<?php
namespace IntegerNet\Solr\Model\Source;

use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use IntegerNet\Solr\TestUtil\Traits\AttributeRepositoryMock;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @covers \IntegerNet\Solr\Model\Source\VarcharProductAttribute
 */
class VarcharProductAttributeTest extends \PHPUnit_Framework_TestCase
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
            ProductAttributeRepositoryInterface::class,
            EavAttributeInterface::class);
    }

        /**
     * @dataProvider dataAttributes
     * @param array $dataAttributes
     */
    public function testItReturnsFilteredProductAttributes(array $dataAttributes, array $expectedOptions)
    {
        $searchCriteriaBuilderMock = $this->mockSearchCriteriaBuilder();
        $searchCriteriaDummy = new SearchCriteria();
        $searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteriaDummy);
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(SearchCriteriaBuilderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);
        $attributeRepositoryMock = $this->mockProductAttributeRepository($dataAttributes, $searchCriteriaDummy);

        $sourceModel = new VarcharProductAttribute(
            $attributeRepositoryMock,
            new AttributeSearchCriteriaBuilder($searchCriteriaBuilderFactoryMock));
        $actualOptions = $sourceModel->toOptionArray();
        $this->assertInternalType('array', $actualOptions);
        $this->assertNotEmpty($actualOptions);
        $actualFirstOption = array_shift($actualOptions);
        $this->assertEquals([
            'value' => '',
            'label' => '',
        ], $actualFirstOption);
        foreach ($expectedOptions as $expectedOption) {
            $this->assertEquals($expectedOption, array_shift($actualOptions));
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
        $expectedOptions = [
            [
                'value' => 'attribute_1',
                'label' => 'Attribute 1 [attribute_1]',
            ],
            [
                'value' => 'attribute_2',
                'label' => 'Attribute 2 [attribute_2]',
            ],
            [
                'value' => 'attribute_3',
                'label' => 'Attribute 3 [attribute_3]',
            ],
        ];
        return [
            [$attributesData, $expectedOptions]
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    protected function mockSearchCriteriaBuilder()
    {
        $searchCriteriaBuilderMock = $this->getSearchCriteriaBuilderMock();
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with(new SortOrder([
                SortOrder::FIELD => 'frontend_label',
                SortOrder::DIRECTION => 'ASC'
            ]));
        $searchCriteriaBuilderMock->expects($this->exactly(3))
            ->method('addFilter')
            ->withConsecutive(
                [
                    EavAttributeInterface::BACKEND_TYPE, ['static', 'varchar'], 'in'
                ],
                [
                    EavAttributeInterface::FRONTEND_INPUT, 'text'
                ],
                [
                    EavAttributeInterface::ATTRIBUTE_CODE,
                    [
                        'url_path',
                        'image_label',
                        'small_image_label',
                        'thumbnail_label',
                        'category_ids',
                        'required_options',
                        'has_options',
                        'created_at',
                        'updated_at',
                    ], 'nin'
                ]
            );
        return $searchCriteriaBuilderMock;
    }
}