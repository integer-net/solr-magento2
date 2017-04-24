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

use IntegerNet\Solr\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\TestFramework\ObjectManager;

class MultiFilterTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
    }

    public static function tearDownAfterClass()
    {
        //include __DIR__ . '/../_files/filterable_attributes_rollback.php';
        parent::tearDownAfterClass();
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testUnfilteredResult()
    {
        /** @var FulltextIndexer $indexer */
        $indexer = $this->objectManager->create(FulltextIndexer::class);
        $indexer->executeFull();

        $this->dispatch('catalogsearch/result/index?q=product');

        $this->assertContains('Product name in store', $this->getResponse()->getBody());
    }

    public static function loadFixture()
    {
        if (file_exists(__DIR__ . '/../_files/solr_config.php')) {
            include __DIR__ . '/../_files/solr_config.php';
        } else {
            include __DIR__ . '/../_files/solr_config.dist.php';
        }

        include __DIR__ . '/../_files/filterable_attributes.php';
        include __DIR__ . '/../_files/filterable_products.php';


    }
}