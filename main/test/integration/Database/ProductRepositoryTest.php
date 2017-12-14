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
use IntegerNet\Solr\Indexer\Data\ProductAssociation;
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;
use IntegerNet\Solr\Model\Bridge\Attribute;
use IntegerNet\Solr\Model\Bridge\AttributeRepository;
use IntegerNet\Solr\Model\Bridge\Product;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductRepositoryTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoDataFixture loadProductsFixture
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
        $products = $productRepository->getProductsInChunks($storeId, ProductIdChunks::withAssociationsTogether($productIds, [], 1000));

        $products->rewind();
        $this->assertTrue($products->valid());
        $product = $products->current();
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Product name in store', $product->getAttributeValue($this->getAttribute('name')));
    }
    /**
     * @magentoDataFixture loadConfigurableProductsFixture
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testItReturnsConfigurableProductAssociations()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $actualAssociations = $productRepository->getProductAssociations([1]);
        $this->assertEquals([
            1 => new ProductAssociation(1, [10, 20])
        ], $actualAssociations);
    }
    /**
     * @magentoDataFixture loadGroupedProductsFixture
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testItReturnsGroupedProductAssociations()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->create(ProductRepository::class);
        $actualAssociations = $productRepository->getProductAssociations([22]);
        $this->assertEquals([
            22 => new ProductAssociation(22, [1, 21])
        ], $actualAssociations);
    }
    public static function loadProductsFixture()
    {
        include __DIR__ . '/../_files/products.php';
    }
    public static function loadConfigurableProductsFixture()
    {
        include __DIR__ . '/../_files/product_configurable.php';
    }
    public static function loadGroupedProductsFixture()
    {
        include __DIR__ . '/../_files/product_grouped.php';
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
