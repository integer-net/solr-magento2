<?php
namespace IntegerNet\Solr\Model\Source;

use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use IntegerNet\Solr\TestUtil\Traits\AttributeRepositoryMock;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \IntegerNet\Solr\Model\Source\VarcharCategoryAttribute
 * @covers AttributeSearchCriteriaBuilder
 */
class VarcharCategoryAttributeTest extends TestCase
{
    use AttributeRepositoryMock;

    /**
     * @dataProvider dataAttributes
     * @param array $dataAttributes
     * @param array $expectedOptions
     */
    public function testItReturnsFilteredCategoryAttributes(array $dataAttributes, array $expectedOptions)
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
        $attributeRepositoryMock = $this->mockCategoryAttributeRepository($dataAttributes, $searchCriteriaDummy);

        $sourceModel = new VarcharCategoryAttribute(
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
     * @param array $dataAttributes
     * @param SearchCriteria $expectedSearchCriteria
     * @return \PHPUnit_Framework_MockObject_MockObject|CategoryAttributeRepositoryInterface
     */
    protected function mockCategoryAttributeRepository(array $dataAttributes, SearchCriteria $expectedSearchCriteria)
    {
        return $this->mockAttributeRepository($dataAttributes, $expectedSearchCriteria,
            CategoryAttributeRepositoryInterface::class,
            EavAttributeInterface::class);
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
                    EavAttributeInterface::BACKEND_TYPE, ['static', 'varchar'], 'in',
                ],
                [
                    EavAttributeInterface::FRONTEND_INPUT, 'text'
                ],
                [
                    EavAttributeInterface::ATTRIBUTE_CODE,
                    [
                        'url_path',
                        'children_count',
                        'level',
                        'path',
                        'position'
                    ],
                    'nin'
                ]
            );
        return $searchCriteriaBuilderMock;
    }
}