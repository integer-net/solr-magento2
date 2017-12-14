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

use Magento\TestFramework\ObjectManager;
use Magento\Catalog\Model\Product as MagentoProduct;
use PHPUnit\Framework\TestCase;

class ProductIteratorFactoryTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testGeneratedProductIteratorFactory()
    {
        $storeId = 2;
        $productIds = [11, 12];
        $magentoProducts = [];
        foreach ($productIds as $productId) {
            $magentoProduct = $this->getMockBuilder(MagentoProduct::class)
                ->disableOriginalConstructor()
                ->setMethods(['getId'])
                ->getMock();
            $magentoProduct->method('getId')->willReturn($productId);
            $magentoProducts[] = $magentoProduct;
        }

        $factory = $this->objectManager->create(ProductIteratorFactory::class);
        $this->assertInstanceOf(ProductIteratorFactory::class, $factory, 'Class should exist and be instantiable');

        /** @var ProductIterator $productIterator */
        $productIterator = $factory->create([
            ProductIterator::PARAM_MAGENTO_PRODUCTS => $magentoProducts,
            ProductIterator::PARAM_STORE_ID => $storeId,
        ]);
        /** @var Product[] $productsFromIterator */
        $productsFromIterator = \iterator_to_array($productIterator);
        $this->assertCount(count($productIds), $productsFromIterator);
        foreach (array_combine($productIds, $productsFromIterator) as $expectedProductId => $product) {
            $this->assertEquals($storeId, $product->getStoreId(), 'Store ID should be used from argument');
            $this->assertEquals($expectedProductId, $product->getId(), 'Products should be used from argument');
        }
    }
}