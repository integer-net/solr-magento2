<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Stub\AttributeStub;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @covers Product
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MagentoProduct
     */
    private $magentoProductStub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AttributeRepository
     */
    private $productAttributeRepositoryMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    private $eventManagerMock;

    protected function setUp()
    {
        $this->magentoProductStub = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productAttributeRepositoryMock = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMagentoAttribute'])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();
    }
    protected function tearDown()
    {
        $this->magentoProductStub = null;
        $this->productAttributeRepositoryMock = null;
        $this->eventManagerMock = null;
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
        $extensionAttributesStub = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getSolrBoost'])
            ->getMockForAbstractClass();

        $this->magentoProductStub->method('getId')->willReturn($productData['id']);
        $this->magentoProductStub->method('getPrice')->willReturn($productData['price']);
        $this->magentoProductStub->method('getStoreId')->willReturn($storeId);
        $this->magentoProductStub->method('getExtensionAttributes')->willReturn($extensionAttributesStub);
        $extensionAttributesStub->method('getSolrBoost')->willReturn($productData['solr_boost']);
        $this->magentoProductStub->method('getCategoryIds')->willReturn($productData['category_ids']);
        $productBridge = $this->makeProductBridge($storeId);

        $this->assertEquals($storeId, $productBridge->getStoreId(), 'store_id');
        $this->assertEquals($productData['id'], $productBridge->getId(), 'id');
        $this->assertEquals($productData['id'] . '_' . $storeId, $productBridge->getSolrId(), 'solr_id');
        $this->assertEquals($productData['solr_boost'], $productBridge->getSolrBoost(), 'solr_boost');
        $this->assertEquals($productData['price'], $productBridge->getPrice(), 'price');
        $this->assertEquals($productData['category_ids'], $productBridge->getCategoryIds(), 'category_ids');

        $this->markTestIncomplete('TODO: Test hasSpecialPrice()');
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

        $productBridge = $this->makeProductBridge($storeId);
        $this->assertEquals($attributeValue, $productBridge->getAttributeValue($attributeStub));

        $this->assertEquals($expectedSearchableValue, $productBridge->getSearchableAttributeValue($attributeStub));
    }

    /**
     * @dataProvider dataIndexable
     * @param $storeAndWebsiteId
     * @param $status
     * @param $visibility
     * @param $websiteIds
     * @param $inStock
     * @param $solrExclude
     * @param $expectedResult
     */
    public function testIndexable($storeAndWebsiteId, $status, $visibility, $websiteIds, $inStock, $solrExclude, $expectedResult)
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(Product::EVENT_CAN_INDEX_PRODUCT, ['product' => $this->magentoProductStub]);

        $storeStub = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();
        $storeStub->method('getWebsiteId')->willReturn($storeAndWebsiteId);

        $stockItemStub = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['getIsInStock'])
            ->getMockForAbstractClass();
        $stockItemStub->method('getIsInStock')->willReturn($inStock);
        $extensionAttributesStub = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getStockItem', 'getSolrExclude'])
            ->getMockForAbstractClass();
        $extensionAttributesStub->method('getStockItem')->willReturn($stockItemStub);
        $extensionAttributesStub->method('getSolrExclude')->willReturn($solrExclude);

        $this->magentoProductStub->method('getStatus')->willReturn($status);
        $this->magentoProductStub->method('getVisibility')->willReturn($visibility);
        $this->magentoProductStub->method('getStore')->willReturn($storeStub);
        $this->magentoProductStub->method('getWebsiteIds')->willReturn($websiteIds);
        $this->magentoProductStub->method('getExtensionAttributes')->willReturn($extensionAttributesStub);

        $productBridge = $this->makeProductBridge($storeAndWebsiteId);
        $this->assertEquals($expectedResult, $productBridge->isIndexable());
    }
    public static function dataIndexable()
    {
        return [
            'indexable' => [
                'store_id' => 1,
                'status' => MagentoProduct\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
                'website_ids' => [1, 2],
                'in_stock' => true,
                'solr_exclude' => false,
                'expected_result' => true,
            ],
            'disabled' => [
                'store_id' => 1,
                'status' => MagentoProduct\Attribute\Source\Status::STATUS_DISABLED,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
                'website_ids' => [1, 2],
                'in_stock' => true,
                'solr_exclude' => false,
                'expected_result' => false,
            ],
            'not visible' => [
                'store_id' => 1,
                'status' => MagentoProduct\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_NOT_VISIBLE,
                'website_ids' => [1, 2],
                'in_stock' => true,
                'solr_exclude' => false,
                'expected_result' => false,
            ],
            'not in website' => [
                'store_id' => 3,
                'status' => MagentoProduct\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
                'website_ids' => [1, 2],
                'in_stock' => true,
                'solr_exclude' => false,
                'expected_result' => false,
            ],
            'not in stock' => [
                'store_id' => 1,
                'status' => MagentoProduct\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
                'website_ids' => [1, 2],
                'in_stock' => false,
                'solr_exclude' => false,
                'expected_result' => false
                ,
            ],
            'solr exclude' => [
                'store_id' => 1,
                'status' => MagentoProduct\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
                'website_ids' => [1, 2],
                'in_stock' => true,
                'solr_exclude' => true,
                'expected_result' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataVisibility
     * @param $storeId
     * @param $expectedVisibleInCatalog
     * @param $expectedVisibleInSearch
     */
    public function testVisibility($storeId, $visibility, $expectedVisibleInCatalog, $expectedVisibleInSearch)
    {
        $this->magentoProductStub->method('getVisibility')->willReturn($visibility);
        $productBridge = $this->makeProductBridge($storeId);
        $this->assertEquals($expectedVisibleInCatalog, $productBridge->isVisibleInCatalog(), 'visible in catalog');
        $this->assertEquals($expectedVisibleInSearch, $productBridge->isVisibleInSearch(), 'visible in search');
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
    public static function dataVisibility()
    {
        return [
            [
                'store_id' => 1,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_BOTH,
                'visible_in_catalog' => true,
                'visible_in_search' => true,
            ],
            [
                'store_id' => 1,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
                'visible_in_catalog' => true,
                'visible_in_search' => false,
            ],
            [
                'store_id' => 1,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_IN_SEARCH,
                'visible_in_catalog' => false,
                'visible_in_search' => true,
            ],
            [
                'store_id' => 1,
                'visibility' => MagentoProduct\Visibility::VISIBILITY_NOT_VISIBLE,
                'visible_in_catalog' => false,
                'visible_in_search' => false,
            ],
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

    /**
     * @param $storeId
     * @return Product
     */
    private function makeProductBridge($storeId)
    {
        return new Product($this->magentoProductStub, $this->productAttributeRepositoryMock, $this->eventManagerMock, $storeId);
    }
}
