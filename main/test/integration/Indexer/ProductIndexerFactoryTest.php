<?php

namespace IntegerNet\Solr\Indexer;

use IntegerNet\Solr\Model\Indexer\ProductIndexerDecorator;
use PHPUnit\Framework\TestCase;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use Magento\TestFramework\ObjectManager;


class ProductIndexerFactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testInstantiatesDecoratedIndexer()
    {
        /** @var ProductIndexerFactory $factory */
        $factory = $this->objectManager->create(ProductIndexerFactory::class);
        $indexer = $factory->create();
        $this->assertInstanceOf(ProductIndexerDecorator::class, $indexer);
    }

}