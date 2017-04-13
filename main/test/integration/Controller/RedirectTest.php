<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Controller;

use IntegerNet\SolrCategories\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\TestFramework\ObjectManager;

class RedirectTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCategoryRedirect()
    {
        $this->dispatch('catalogsearch/result/index?q=category+1');

        $this->assertRedirect($this->stringContains('category-1.html'));
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testDeactivatedCategoryNoRedirect()
    {
        $this->dispatch('catalogsearch/result/index?q=category+3');

        $this->assertFalse($this->getResponse()->isRedirect());
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testProductRedirectBySku()
    {
        $this->dispatch('catalogsearch/result/index?q=product-1');

        $this->assertRedirect($this->stringContains('product-1-store-1.html'));
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testProductRedirectByName()
    {
        $this->dispatch('catalogsearch/result/index?q=product+name+in+store');

        $this->assertRedirect($this->stringContains('product-1-store-1.html'));
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testDeactivatedProductNoRedirect()
    {
        $this->dispatch('catalogsearch/result/index?q=product+name+in+store+2');

        $this->assertFalse($this->getResponse()->isRedirect());
    }

    public static function loadFixture()
    {
        if (file_exists(__DIR__ . '/../_files/solr_config.php')) {
            include __DIR__ . '/../_files/solr_config.php';
        } else {
            include __DIR__ . '/../_files/solr_config.dist.php';
        }

        include __DIR__ . '/../_files/categories_basestore.php';
        include __DIR__ . '/../_files/products.php';

    }
}