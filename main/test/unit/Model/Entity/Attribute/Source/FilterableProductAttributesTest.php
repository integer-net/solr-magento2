<?php
namespace IntegerNet\Solr\Model\Entity\Attribute\Source;

use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Implementor\Stub\AttributeStub;
use Magento\Store\Model\Store;

/**
 * @covers \IntegerNet\Solr\Model\Source\FilterabeProductAttribute
 */
class FilterableProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataAttributes
     * @param array $attributeStubs
     * @param array $expectedOptions
     */
    public function testItReturnsFilteredProductAttributes(array $attributeStubs, array $expectedOptions)
    {
        $attributeRepositoryMock = $this->mockAttributeRepositoryBridge($attributeStubs);
        $sourceModel = new FilterableProductAttribute($attributeRepositoryMock);

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
        $attributeStubs = [
            AttributeStub::filterable('attribute_1', []),
            AttributeStub::filterable('attribute_2', []),
            AttributeStub::filterable('attribute_3', []),
        ];
        $expectedOptions = [
            [
                'value' => 'attribute_1',
                'label' => 'attribute_1 [attribute_1]',
            ],
            [
                'value' => 'attribute_2',
                'label' => 'attribute_2 [attribute_2]',
            ],
            [
                'value' => 'attribute_3',
                'label' => 'attribute_3 [attribute_3]',
            ],
        ];
        return [
            [$attributeStubs, $expectedOptions]
        ];
    }


    /**
     * @param array $attributeStubs
     * @return \PHPUnit_Framework_MockObject_MockObject|AttributeRepository
     */
    private function mockAttributeRepositoryBridge(array $attributeStubs)
    {
        $attributeRepositoryMock = $this->getMockBuilder(AttributeRepository::class)
            ->getMockForAbstractClass();
        $attributeRepositoryMock->expects($this->once())
            ->method('getFilterableInCatalogAttributes')
            ->with(Store::DEFAULT_STORE_ID)
            ->willReturn($attributeStubs);
        return $attributeRepositoryMock;
    }
}