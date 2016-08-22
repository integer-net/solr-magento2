<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Database;

use IntegerNet\Solr\Implementor\ProductRepository;
use IntegerNet\Solr\Model\Bridge\Attribute;
use IntegerNet\Solr\Model\Bridge\AttributeRepository;
use IntegerNet\Solr\Model\Bridge\Product;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\TestFramework\ObjectManager;

class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testItReturnsProductsWithStoreSpecificValues()
    {
        $storeId = 1;
        $sku = 'product-1';

        /** @var MagentoProduct $productModel */
        $productModel = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $productIds = [ $productModel->getIdBySku($sku) ];

        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $this->assertInstanceOf(\IntegerNet\Solr\Model\Bridge\ProductRepository::class, $productRepository);
        $products = $productRepository->getProductsForIndex($storeId, $productIds);

        $products->rewind();
        $this->assertTrue($products->valid());
        $product = $products->current();
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Product name in store', $product->getAttributeValue($this->getAttribute('name')));
    }
    public static function loadFixture()
    {
        include __DIR__ . '/../_files/products.php';
    }

    /**
     * @param $attributeCode
     * @return Attribute
     * @throws \IntegerNet\Solr\Exception
     */
    private function getAttribute($attributeCode)
    {
        /** @var AttributeRepository $attributeRepository */
        $attributeRepository = $this->objectManager->create(AttributeRepository::class);
        $nameAttribute = $attributeRepository->getAttributeByCode($attributeCode, null);
        return $nameAttribute;
    }
}
