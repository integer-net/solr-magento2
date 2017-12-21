<?php
namespace IntegerNet\Solr\Model\Bridge;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \IntegerNet\Solr\Model\Bridge\Attribute
 * @covers \IntegerNet\Solr\Model\Bridge\Source
 */
class AttributeTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AttributeResource
     */
    protected $magentoAttributeStub;

    protected function setUp()
    {
        $this->magentoAttributeStub = $this->getMockBuilder(AttributeResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSource'])
            ->getMock();
    }

    /**
     * @dataProvider dataAttribute
     * @param $attributeCode
     * @param $backendType
     * @param $frontendInput
     * @param $isSearchable
     * @param $solrBoost
     * @param $storeLabel
     * @param $usedForSortBy
     */
    public function testStoreScope($attributeCode, $backendType, $frontendInput, $isSearchable, $solrBoost, $storeLabel, $usedForSortBy)
    {
        $storeLabelStub = $this->getMockForAbstractClass(AttributeFrontendLabelInterface::class);
        $storeLabelStub->method('getLabel')->willReturn($storeLabel . '-1');
        $storeId = 1;
        $storeLabels = [
            $storeId => $storeLabelStub
        ];
        $this->prepareMagentoAttributeStub($attributeCode, $backendType, $frontendInput, $isSearchable, $storeLabel, $usedForSortBy, $solrBoost, new Boolean($this->getAttributeFactoryStub()), $storeLabels);

        $attributeBridge = new Attribute($this->magentoAttributeStub, $storeId);

        $this->assertAttributeData($attributeCode, $backendType, $frontendInput, $isSearchable, $solrBoost, $storeLabel . '-1', $usedForSortBy, $attributeBridge);

    }

    /**
     * @dataProvider dataFacetTypes
     * @param $frontendInput
     * @param $expectedFacetType
     */
    public function testFacetTypeBasedOnFrontendInput($frontendInput, $expectedFacetType)
    {
        $storeId = 1;
        $this->magentoAttributeStub->setFrontendInput($frontendInput);
        $attributeBridge = new Attribute($this->magentoAttributeStub, $storeId);
        $this->assertEquals($expectedFacetType, $attributeBridge->getFacetType());
    }
    public static function dataFacetTypes()
    {
        return [
            ['select', 'select'],
            ['boolean', 'select'],
            ['multiselect', 'multiselect'],
            ['date', 'text'],
            ['gallery', 'text'],
            ['hidden', 'text'],
            ['image', 'text'],
            ['media_image', 'text'],
            ['multiline', 'text'],
            ['price', 'text'],
            ['text', 'text'],
            ['textarea', 'text'],
            ['weight', 'text'],
        ];
    }

    /**
     * @dataProvider dataAttribute
     * @param $attributeCode
     * @param $backendType
     * @param $frontendInput
     * @param $isSearchable
     * @param $solrBoost
     * @param $storeLabel
     * @param $usedForSortBy
     */
    public function testDefaultLabelOnInvalidStoreScope($attributeCode, $backendType, $frontendInput, $isSearchable, $solrBoost, $storeLabel, $usedForSortBy)
    {
        $storeLabelStub = $this->getMockForAbstractClass(AttributeFrontendLabelInterface::class);
        $storeLabelStub->method('getLabel')->willReturn($storeLabel . '-1');
        $storeId = 1;
        $storeLabels = [
            $storeId => $storeLabelStub
        ];
        $this->prepareMagentoAttributeStub($attributeCode, $backendType, $frontendInput, $isSearchable, $storeLabel, $usedForSortBy, $solrBoost, new Boolean($this->getAttributeFactoryStub()), $storeLabels);

        $invalidStoreId = 999;
        $attributeBridge = new Attribute($this->magentoAttributeStub, $invalidStoreId);

        $this->assertEquals($storeLabel, $attributeBridge->getStoreLabel());
    }

    /**
     * @dataProvider dataAttribute
     * @param $attributeCode
     * @param $backendType
     * @param $frontendInput
     * @param $isSearchable
     * @param $solrBoost
     * @param $storeLabel
     * @param $usedForSortBy
     */
    public function testGetterDelegation($attributeCode, $backendType, $frontendInput, $isSearchable, $solrBoost, $storeLabel, $usedForSortBy)
    {
        $this->prepareMagentoAttributeStub($attributeCode, $backendType, $frontendInput, $isSearchable, $storeLabel, $usedForSortBy, $solrBoost, new Boolean($this->getAttributeFactoryStub()), []);

        $attributeBridge = new Attribute($this->magentoAttributeStub);

        $this->assertAttributeData($attributeCode, $backendType, $frontendInput, $isSearchable, $solrBoost, $storeLabel, $usedForSortBy, $attributeBridge);
    }

    public static function dataAttribute()
    {
        return [
            [
                'attribute_1',
                'int',
                'select',
                true,
                1.0,
                'Attribute 1',
                true
            ]
        ];
    }

    /**
     * @param $attributeCode
     * @param $backendType
     * @param $frontendInput
     * @param $isSearchable
     * @param $defaultStoreLabel
     * @param $usedForSortBy
     * @param $solrBoost
     * @param $sourceModel
     * @param $storeLabels
     */
    protected function prepareMagentoAttributeStub($attributeCode, $backendType, $frontendInput, $isSearchable, $defaultStoreLabel, $usedForSortBy, $solrBoost, $sourceModel, $storeLabels)
    {
        $this->magentoAttributeStub
            ->setAttributeCode($attributeCode)
            ->setBackendType($backendType)
            ->setFrontendInput($frontendInput)
            ->setIsSearchable($isSearchable)
            ->setDefaultFrontendLabel($defaultStoreLabel)
            ->setFrontendLabels($storeLabels)
            ->setUsedForSortBy($usedForSortBy)
            ->setData('solr_boost', $solrBoost);
        $this->magentoAttributeStub->method('getSource')->willReturn($sourceModel);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AttributeFactory
     */
    protected function getAttributeFactoryStub()
    {
        $attributeFactoryStub = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        return $attributeFactoryStub;
    }

    /**
     * @param $attributeCode
     * @param $backendType
     * @param $frontendInput
     * @param $isSearchable
     * @param $solrBoost
     * @param $storeLabel
     * @param $usedForSortBy
     * @param Attribute $attributeBridge
     */
    protected function assertAttributeData($attributeCode, $backendType, $frontendInput, $isSearchable, $solrBoost, $storeLabel, $usedForSortBy, $attributeBridge)
    {
        $this->assertEquals($attributeCode, $attributeBridge->getAttributeCode(), 'attribute code');
        $this->assertEquals($backendType, $attributeBridge->getBackendType(), 'backend type');
        $this->assertEquals($frontendInput, $attributeBridge->getFacetType(), 'facet type');
        $this->assertEquals($isSearchable, $attributeBridge->getIsSearchable(), 'is searchable');
        $this->assertEquals($solrBoost, $attributeBridge->getSolrBoost(), 'solr boost');
        $this->assertEquals($storeLabel, $attributeBridge->getStoreLabel(), 'store label');
        $this->assertEquals($usedForSortBy, $attributeBridge->getUsedForSortBy(), 'used for sort by');
        $this->assertEquals([0 => 'No', 1 => 'Yes'], $attributeBridge->getSource()->getOptionMap(), 'source: option map');
        $this->assertEquals('No', $attributeBridge->getSource()->getOptionText(0), 'source: option text');
    }

}