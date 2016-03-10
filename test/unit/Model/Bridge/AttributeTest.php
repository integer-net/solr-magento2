<?php
namespace IntegerNet\Solr\Model\Bridge;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;

/**
 * @covers \IntegerNet\Solr\Model\Bridge\Attribute
 * @covers \IntegerNet\Solr\Model\Bridge\Source
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
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

    public function testGetterDelegation()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AttributeFactory $attributeFactoryStub */
        $attributeFactoryStub = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $attributeCode = 'attribute_1';
        $backendType = 'int';
        $frontendInput = 'select';
        $isSearchable = true;
        $solrBoost = 1.0;
        $storeLabel = 'Attribute 1';
        $usedForSortBy = true;
        $sourceModel = new Boolean($attributeFactoryStub);

        $this->magentoAttributeStub
            ->setAttributeCode($attributeCode)
            ->setBackendType($backendType)
            ->setFrontendInput($frontendInput)
            ->setIsSearchable($isSearchable)
            ->setDefaultFrontendLabel($storeLabel)
            ->setUsedForSortBy($usedForSortBy)
            ->setData('solr_boost', $solrBoost);
        $this->magentoAttributeStub->method('getSource')->willReturn($sourceModel);

        $attributeBridge = new Attribute($this->magentoAttributeStub);

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