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

class ResultTest extends \Magento\TestFramework\TestCase\AbstractController
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
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoConfigFixture current_store integernet_solr/category/is_indexer_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testNoCategoriesOnSearchResultPage()
    {
        /** @var FulltextIndexer $indexer */
        $indexer = $this->objectManager->create(FulltextIndexer::class);
        $indexer->executeFull();

        $this->dispatch('catalogsearch/result/index?q=abcdefgh');

        $this->assertNotContains('solr-search-result-categories', $this->getResponse()->getBody());
        $this->assertNotContains('Description Category 1', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoConfigFixture current_store integernet_solr/category/is_indexer_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCategoriesOnSearchResultPage()
    {
        /** @var FulltextIndexer $indexer */
        $indexer = $this->objectManager->create(FulltextIndexer::class);
        $indexer->executeFull();

        $this->dispatch('catalogsearch/result/index?q=category');

        $this->assertContains('solr-search-result-categories', $this->getResponse()->getBody());
        $this->assertContains('Description Category 1', $this->getResponse()->getBody());
        $this->assertNotContains('Description Category 3', $this->getResponse()->getBody());
        $this->assertNotContains('Description Other Name 4', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoConfigFixture current_store integernet_solr/category/is_indexer_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testFuzzyCategoriesOnSearchResultPage()
    {
        /** @var FulltextIndexer $indexer */
        $indexer = $this->objectManager->create(FulltextIndexer::class);
        $indexer->executeFull();

        $this->dispatch('catalogsearch/result/index?q=catgeory');

        $this->assertContains('solr-search-result-categories', $this->getResponse()->getBody());
        $this->assertContains('Description Category 1', $this->getResponse()->getBody());
    }

    public static function loadFixture()
    {
        if (file_exists(__DIR__ . '/../_files/solr_config.php')) {
            include __DIR__ . '/../_files/solr_config.php';
        } else {
            include __DIR__ . '/../_files/solr_config.dist.php';
        }

        include __DIR__ . '/../_files/products.php';
        include __DIR__ . '/../_files/categories.php';

    }
}