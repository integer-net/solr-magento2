<?php
namespace IntegerNet\Solr\Model\Source;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @covers \IntegerNet\Solr\Model\Source\VarcharCategoryAttribute
 */
class VarcharCategoryAttributeTest extends \PHPUnit_Framework_TestCase
{
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
        $attributeRepositoryMock = $this->mockAttributeRepository($dataAttributes, $searchCriteriaDummy);

        $sourceModel = new VarcharCategoryAttribute($attributeRepositoryMock, $searchCriteriaBuilderMock);
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
                'attribute_code' => 'attribute_1',
                'frontend_label' => 'Attribute 1',
            ],
            [
                'attribute_code' => 'attribute_2',
                'frontend_label' => 'Attribute 2',
            ],
            [
                'attribute_code' => 'attribute_3',
                'frontend_label' => 'Attribute 3',
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
    protected function mockAttributeRepository(array $dataAttributes, SearchCriteria $expectedSearchCriteria)
    {
        $attributeRepositoryStub = $this->getMockForAbstractClass(CategoryAttributeRepositoryInterface::class);
        $attributeStubs = [];
        foreach ($dataAttributes as $dataAttribute) {
            $attributeStub = $this->getMockForAbstractClass(EavAttributeInterface::class);
            $attributeStub->method('getDefaultFrontendLabel')
                ->willReturn($dataAttribute['frontend_label']);
            $attributeStub->method('getAttributeCode')
                ->willReturn($dataAttribute['attribute_code']);
            $attributeStubs[] = $attributeStub;
        }
        $attributeSearchResultStub = $this->getMockForAbstractClass(SearchResultsInterface::class);
        $attributeSearchResultStub->method('getItems')
            ->willReturn($attributeStubs);
        $attributeRepositoryStub->method('getList')
            ->with($this->identicalTo($expectedSearchCriteria))
            ->willReturn($attributeSearchResultStub);
        return $attributeRepositoryStub;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    protected function mockSearchCriteriaBuilder()
    {
        $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'addSortOrder', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with('frontend_label', AbstractCollection::SORT_ORDER_ASC);
        $searchCriteriaBuilderMock->expects($this->exactly(3))
            ->method('addFilter')
            ->withConsecutive(
                [
                    new Filter([
                        Filter::KEY_FIELD => 'backend_type',
                        Filter::KEY_CONDITION_TYPE => 'in',
                        Filter::KEY_VALUE => ['static', 'varchar']
                    ])
                ],
                [
                    new Filter([
                        Filter::KEY_FIELD => 'frontend_input',
                        Filter::KEY_VALUE => 'text'
                    ])
                ],
                [
                    new Filter([
                        Filter::KEY_FIELD => 'attribute_code',
                        Filter::KEY_CONDITION_TYPE => 'nin',
                        Filter::KEY_VALUE => [
                            'url_path',
                            'children_count',
                            'level',
                            'path',
                            'position'
                        ]
                    ])
                ]
            );
        return $searchCriteriaBuilderMock;
    }
}