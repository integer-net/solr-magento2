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

use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Event\ManagerInterface;

/**
 * @covers ProductIterator
 */
class ProductIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataIterator
     * @param $storeId
     * @param $productIds
     */
    public function testIterator($storeId, $productIds)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductFactory $productFactory */
        $productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $productFactory->expects($this->exactly(count($productIds)))
            ->method('create')
            ->willReturnCallback(function($arguments) {
                return new Product(
                    $arguments[Product::PARAM_MAGENTO_PRODUCT],
                    $this->getAttributeRepositoryStub(),
                    $this->getEventManagerStub(),
                    $arguments[Product::PARAM_STORE_ID]);
            });
        $products = \array_map(function($productId) {
            return $this->getProductStub($productId);
        }, $productIds);

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
        return $this->getMock(ManagerInterface::class);
    }
}