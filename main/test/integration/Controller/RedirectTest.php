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

use IntegerNet\Solr\Fixtures\SolrConfig;

class RedirectTest extends AbstractController
{
    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testCategoryRedirect()
    {
        $this->dispatch('catalogsearch/result/index?q=category+1');

        $this->assertRedirect($this->stringContains('category-1.html'));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testDeactivatedCategoryNoRedirect()
    {
        $this->dispatch('catalogsearch/result/index?q=category+3');

        $this->assertFalse($this->getResponse()->isRedirect());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testProductRedirectBySku()
    {
        $this->dispatch('catalogsearch/result/index?q=product-1');

        $this->assertRedirect($this->stringContains('product-1-store-1.html'));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testProductRedirectByName()
    {
        $this->dispatch('catalogsearch/result/index?q=product+name+in+store');

        $this->assertRedirect($this->stringContains('product-1-store-1.html'));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testDeactivatedProductNoRedirect()
    {
        $this->dispatch('catalogsearch/result/index?q=product+name+in+store+2');

        $this->assertFalse($this->getResponse()->isRedirect());
    }

    public static function setUpBeforeClass()
    {
        SolrConfig::loadFromConfigFile();

        include __DIR__ . '/../_files/categories_basestore.php';
        include __DIR__ . '/../_files/products.php';

    }
}