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

class ProductFactoryTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testGeneratedProductFactory()
    {
        $storeId = 2;
        $productId = 42;
        $magentoProduct = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $magentoProduct->method('getId')->willReturn($productId);

        $factory = $this->objectManager->create(ProductFactory::class);
        $this->assertInstanceOf(ProductFactory::class, $factory, 'Class should exist and be instantiable');
        /** @var Product $product */
        $product = $factory->create([
            Product::PARAM_MAGENTO_PRODUCT => $magentoProduct,
            Product::PARAM_STORE_ID => $storeId,
        ]);

        $this->assertEquals($storeId, $product->getStoreId(), 'Store ID should be used from argument');
        $this->assertEquals($productId, $product->getId(), 'Product should be used from argument');
    }
}