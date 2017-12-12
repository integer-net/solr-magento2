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
use TddWizard\Fixtures\Catalog\CategoryBuilder;
use TddWizard\Fixtures\Catalog\CategoryFixture;
use TddWizard\Fixtures\Catalog\CategoryFixtureRollback;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Catalog\ProductFixtureRollback;
use Magento\Catalog\Model\Category;

class CategoryRepositoryTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    /**
     * @var ProductFixture
     */
    private $product;

    /**
     * @var CategoryFixture
     */
    private $category;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->product = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        $this->category = new CategoryFixture(
            CategoryBuilder::topLevelCategory()->withProducts(
                [
                    $this->product->getSku()
                ]
            )->build()
        );
    }

    protected function tearDown()
    {
        ProductFixtureRollback::create()->execute($this->product);
        CategoryFixtureRollback::create()->execute($this->category);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testItLoadsCategoryIdsForProduct()
    {
        $storeId = 1;
        $expectedResult = [$this->category->getId()];

        /** @var MagentoProduct $magentoProduct */
        $magentoProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $magentoProduct->load($this->product->getId());

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

}

