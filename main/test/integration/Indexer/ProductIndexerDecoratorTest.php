<?php

namespace IntegerNet\Solr\Indexer;

use IntegerNet\Solr\Model\Indexer\ProductIndexerDecorator;
use PHPUnit\Framework\TestCase;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use Magento\TestFramework\ObjectManager;


class ProductIndexerDecoratorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Indexer
     */
    private $indexer;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        /** @var ProductIndexerFactory $factory */
        $factory = $this->objectManager->create(ProductIndexerFactory::class);
        $this->indexer = $factory->create();
    }

    public function testDecoratedIndexerPassesMethodCalls()
    {
        $this->indexer->deactivateSwapCore();
        $this->markTestIncomplete(
            'For now just testing that there is no error.'.
            'When the deprecated methods have been removed, the test can be removed as well.'
        );
    }

}