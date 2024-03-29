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

use IntegerNet\Solr\Implementor\ProductFactory;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers ProductIterator
 */
class ProductIteratorTest extends TestCase
{
    /**
     * @dataProvider dataIterator
     * @param $storeId
     * @param $productIds
     */
    public function testIterator($storeId, $productIds)
    {
        $productFactory = $this->mockProductFactory($productIds);
        $products = $this->getProductStubs($productIds);

        $iterator = new ProductIterator($productFactory, $products, $storeId);
        /** @var Product[] $productsFromIterator */
        $productsFromIterator = \iterator_to_array($iterator);

        $this->assertEquals(count($products), count($productsFromIterator));
        foreach ($productsFromIterator as $actualProduct) {
            $this->assertInstanceOf(Product::class, $actualProduct, 'Should be instance of product bridge');
            $this->assertEquals(current($products)->getId(), $actualProduct->getId(), 'Product ID');
            $this->assertEquals($storeId, $actualProduct->getStoreId(), 'Store ID');
            next($products);
        }
    }
    public static function dataIterator()
    {
        return [
            [
                'store_id' => null,
                'product_ids' => [11]
            ],
            [
                'store_id' => 1,
                'product_ids' => []
            ],
            [
                'store_id' => 2,
                'product_ids' => [11, 12, 17]
            ],
        ];
    }

    /**
     * @param $productId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductStub($productId)
    {
        $productMock = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $productMock->method('getId')->willReturn($productId);
        return $productMock;
    }

    /**
     * @return AttributeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAttributeRepositoryStub()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AttributeRepository $attributeRepository */
        $attributeRepository = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $attributeRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    private function getEventManagerStub()
    {
        return $this->getMockBuilder(ManagerInterface::class)->getMock();
    }

    /**
     * @param $productIds
     * @return ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockProductFactory($productIds)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductFactory $productFactory */
        $productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $productFactory->expects($this->exactly(count($productIds)))
            ->method('create')
            ->willReturnCallback(function ($arguments) {
                return new Product(
                    $arguments[Product::PARAM_MAGENTO_PRODUCT],
                    $this->getAttributeRepositoryStub(),
                    $this->getEventManagerStub(),
                    $this->getStockRegistryStub(),
                    $this->getScopeConfigStub(),
                    $arguments[Product::PARAM_STORE_ID]);
            });
        return $productFactory;
    }

    /**
     * @param $productIds
     * @return array
     */
    private function getProductStubs($productIds)
    {
        $products = \array_map(function ($productId) {
            return $this->getProductStub($productId);
        }, $productIds);
        return $products;
    }

    /**
     * @return StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStockRegistryStub()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StockRegistryInterface$stockRegistry */
        $stockRegistry = $this->getMockBuilder(StockRegistryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem'])
            ->getMockForAbstractClass();
        $stockRegistry
            ->method('getStockItem')
            ->willReturn($this->getStockItemStub());

        return $stockRegistry;
    }

    /**
     * @return StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStockItemStub()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StockItemInterface $stockItem */
        $stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsInStock'])
            ->getMockForAbstractClass();
        $stockItem
            ->method('getIsInStock')
            ->willReturn(true);

        return $stockItem;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ScopeConfigInterface
     */
    private function getScopeConfigStub()
    {
        return $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();
    }
}