<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Indexer;

use IntegerNet\Solr\Model\Indexer\ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\TestFramework\ObjectManager;

class ProductCollectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->enableFlatProductIndex();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testCollectionUsesEavTables()
    {
        $productCollectionFactory = $this->objectManager->create(ProductCollectionFactory::class);
        /** @var ProductCollection $collection */
        $storeId = 1;
        $collection = $productCollectionFactory->create($storeId);
        $this->assertNotInstanceOf(\Magento\Catalog\Model\ResourceModel\Product\Flat::class, $collection->getEntity());
        $this->assertFalse($collection->isEnabledFlat());
    }

    private function enableFlatProductIndex()
    {
        /** @var  $indexer \Magento\Framework\Indexer\IndexerInterface */
        $indexer = $this->objectManager->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $indexer->load('catalog_product_flat');
        $indexer->reindexAll();
    }
}
