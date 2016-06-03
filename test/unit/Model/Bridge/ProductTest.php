<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Stub\AttributeStub;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\Api\AttributeValue;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;

/**
 * @covers Product
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductInterface
     */
    private $magentoProductStub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AttributeRepository
     */
    private $productAttributeRepositoryMock;

    protected function setUp()
    {
        $this->magentoProductStub = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $this->productAttributeRepositoryMock = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMagentoAttribute'])
            ->getMock();
    }
    protected function tearDown()
    {
        $this->magentoProductStub = null;
        $this->productAttributeRepositoryMock = null;
    }

    public function testCoreAttributes()
    {
        $storeId = 1;
        $productData = [
            'id' => 42,
            'price' => 13.37,
            'solr_boost' => 1.5,
            'category_ids' => [1,2,3]
        ];
        $extensionAttributesStub = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->setMethods(['getSolrBoost'])
            ->getMockForAbstractClass();

        $this->magentoProductStub->method('getId')->willReturn($productData['id']);
        $this->magentoProductStub->method('getPrice')->willReturn($productData['price']);
        $this->magentoProductStub->method('getStoreId')->willReturn($storeId);
        $this->magentoProductStub->method('getExtensionAttributes')->willReturn($extensionAttributesStub);
        $extensionAttributesStub->method('getSolrBoost')->willReturn($productData['solr_boost']);
        $productBridge = new Product($this->magentoProductStub, $this->productAttributeRepositoryMock, $storeId);

        $this->assertEquals($storeId, $productBridge->getStoreId(), 'store_id');
        $this->assertEquals($productData['id'], $productBridge->getId(), 'id');
        $this->assertEquals($productData['id'] . '_' . $storeId, $productBridge->getSolrId(), 'solr_id');
        $this->assertEquals($productData['solr_boost'], $productBridge->getSolrBoost(), 'solr_boost');
        $this->assertEquals($productData['price'], $productBridge->getPrice(), 'price');

        // $this->assertEquals($productData['category_ids'], $productBridge->getCategoryIds(), 'category_ids');
        $this->markTestIncomplete('Missing: getCategoryIds(), getChildren()');
    }

    /**
     * @dataProvider dataCustomAttributes
     * @param $storeId
     * @param $attributeStub
     * @param $attributeValue
     * @param $expectedSearchableValue
     */
    public function testCustomAttributes($storeId, AttributeStub $attributeStub, $attributeValue, $expectedSearchableValue)
    {
        $attributeCode = $attributeStub->getAttributeCode();

        $this->magentoProductStub->method('getCustomAttribute')
            ->with($attributeCode)
            ->willReturn(new AttributeValue([
                AttributeValue::ATTRIBUTE_CODE => $attributeCode,
                AttributeValue::VALUE => $attributeValue]));

        $this->productAttributeRepositoryMock->method('getMagentoAttribute')
            ->willReturn($this->mockMagentoAttribute($expectedSearchableValue));

        $productBridge = new Product($this->magentoProductStub, $this->productAttributeRepositoryMock, $storeId);
        $this->assertEquals($attributeValue, $productBridge->getAttributeValue($attributeStub));

        $this->markTestIncomplete('getFrontend()->getValue() expects DataObject, API Interface is not enough');
        $this->assertEquals($expectedSearchableValue, $productBridge->getSearchableAttributeValue($attributeStub));
    }

    public function testIndexable()
    {
        $this->markTestIncomplete('Check: exclude by event, disabled, visibility, website assignment, out of stock');
    }

    public static function dataCustomAttributes()
    {
        return [
            [
                'store_id' => 1,
                'attribute_stub' => AttributeStub::sortableString('custom_1'),
                'attribute_value' => 'Iron Maiden',
                'expected_searchable_value' => 'Iron Maiden',
            ],
            [
                'store_id' => 1,
                'attribute_stub' => AttributeStub::filterable('custom_2', [666 => 'Number of the Beast', 667 => 'Neighbour of the Beast']),
                'attribute_value' => 666,
                'expected_searchable_value' => 'Number of the Beast',
            ]
        ];
    }

    /**
     * @param $frontendValueForCurrentProduct
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockMagentoAttribute($frontendValueForCurrentProduct)
    {
        // Less mocking would have been nice but I don't see a better way ATM
        $magentoAttributeStub = $this->getMockBuilder(AttributeResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFrontend'])
            ->getMock();
        $attributeFrontendStub = $this->getMockBuilder(AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $attributeFrontendStub->method('getValue')->with($this->magentoProductStub)->willReturn($frontendValueForCurrentProduct);
        $magentoAttributeStub->method('getFrontend')->willReturn($attributeFrontendStub);
        return $magentoAttributeStub;
    }
}
