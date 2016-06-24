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
use Magento\Framework\Event\ManagerInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * @covers ProductIterator
 */
class PagedProductIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataIterator
     * @param $storeId
     * @param $productIds
     * @param int $pageSize
     */
    public function testIterator($storeId, $productIds, $pageSize)
    {
        $expectedPageCount = (int)\ceil(\count($productIds) / $pageSize);
        $productFactory = $this->mockProductFactory($productIds);
        $products = $this->getProductStubs($productIds);
        $collectionFactory = $this->mockCollectionFactory($storeId, $expectedPageCount, $productIds, $products);

        $iterator = new PagedProductIterator($collectionFactory, $productFactory, $productIds, $pageSize, $storeId);
        $iterator->setPageCallback($this->mockCallback($expectedPageCount));
        /** @var Product[] $productsFromIterator */
        $productsFromIterator = \iterator_to_array($iterator);

        $this->assertEquals(\count($products), \count($productsFromIterator));
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
                'product_ids' => [11],
                'page_size' => 10,
            ],
            [
                'store_id' => 1,
                'product_ids' => [],
                'page_size' => 10,
            ],
            [
                'store_id' => 2,
                'product_ids' => [11, 12, 17],
                'page_size' => 2,
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
        return $this->getMock(ManagerInterface::class);
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
     * @param $storeId
     * @param $expectedCalls
     * @param $productIds
     * @param $products
     * @return \PHPUnit_Framework_MockObject_MockObject|CollectionFactory
     */
    private function mockCollectionFactory($storeId, $expectedCalls, $productIds, $products)
    {
        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->exactly($expectedCalls))
            ->method('create')
            ->willReturnCallback(function() use ($storeId, $productIds, $products) {
                return $this->mockCollection($storeId, $productIds, $products);
            });
        return $collectionFactory;
    }

    /**
     * @param $storeId
     * @param $productIds
     * @param $products
     * @return \PHPUnit_Framework_MockObject_MockObject|Collection
     */
    private function mockCollection($storeId, $productIds, $products)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $collectionStub */
        $collectionStub = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'addIdFilter', 'getIterator', 'load', 'getSize'])
            ->getMock();
        $collectionStub->expects($storeId === null ? $this->never() : $this->once())->method('setStoreId')->with($storeId);
        $collectionStub->method('getSize')->willReturn(count($products));
        $collectionStub->expects($this->once())->method('addIdFilter')->with($productIds);
        $collectionStub->method('getIterator')
            ->willReturnCallback(function() use ($collectionStub, $productIds, $products) {
                $offset = ($collectionStub->getCurPage() - 1) * $collectionStub->getPageSize();
                $limit = $collectionStub->getPageSize();
                return new \ArrayIterator(
                    \array_combine(
                        \array_slice($productIds, $offset, $limit),
                        \array_slice($products, $offset, $limit)
                    )
                );
            });
        return $collectionStub;
    }

    /**
     * @param $expectedCallCount
     * @return \PHPUnit_Framework_MockObject_MockObject|callable
     */
    private function mockCallback($expectedCallCount)
    {
        $callbackMock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $callbackMock->expects($this->exactly($expectedCallCount))
            ->method('__invoke');
        return $callbackMock;
    }
}