<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\Autosuggest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoDataFixture loadProductsFixture
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testRenderTemplate()
    {
        $this->loadDefaultLayout();

        /** @var Item $itemBlock */
        $itemBlock = $this->objectManager->create(Item::class);
        $itemBlock->setProduct($this->product('product-1'));
        $this->assertEquals('IntegerNet_Solr::autosuggest/item.phtml', $itemBlock->getTemplate());
        $this->assertRegExp('/Product name in store/', $itemBlock->toHtml());
        $this->assertRegExp('{\$10\.00}', $itemBlock->toHtml());
        $this->assertRegExp('{/placeholder/small_image.jpg}', $itemBlock->toHtml());
    }
    private function product($sku)
    {
        /** @var ProductRepositoryInterface $repo */
        $repo = $this->objectManager->get(ProductRepositoryInterface::class);
        return $repo->get($sku);
    }

    public static function loadProductsFixture()
    {
        include __DIR__ . '/../../_files/products.php';
    }

    private function loadDefaultLayout()
    {
        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        $layout->getUpdate()->load(['default']);
        $layout->generateXml();
        $layout->generateElements();
    }
}