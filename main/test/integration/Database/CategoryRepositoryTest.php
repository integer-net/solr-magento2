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

use IntegerNet\Solr\Model\Bridge\CategoryRepository;
use IntegerNet\Solr\Model\Bridge\Product;
use IntegerNet\Solr\Model\Bridge\ProductFactory;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
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
    public function testItLoadsCategoryIdsForProduct()
    {
        $storeId = 1;
        $sku = 'product-1';
        $expectedResult = [333];

        /** @var MagentoProduct $magentoProduct */
        $magentoProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $magentoProduct->load($magentoProduct->getIdBySku($sku));

        /** @var ProductFactory $productFactory */
        $productFactory = $this->objectManager->create(ProductFactory::class);
        /** @var Product $product */
        $product = $productFactory->create([
            Product::PARAM_MAGENTO_PRODUCT => $magentoProduct,
            Product::PARAM_STORE_ID => $storeId,
        ]);

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->objectManager->create(CategoryRepository::class);
        $actualResult = $categoryRepository->getCategoryIds($product);

        $this->assertEquals($expectedResult, $actualResult);
    }
    public static function loadFixture()
    {
        include __DIR__ . '/../_files/products.php';
    }
}
