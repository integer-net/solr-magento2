<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Bridge;

use IntegerNet\SolrSuggest\Plain\Cache\CacheWriter;
use Magento\TestFramework\ObjectManager;
use IntegerNet\Solr\Model\Bridge\AppFactory;
use PHPUnit\Framework\TestCase;

class AppFactoryTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }
    public function testCacheWriterInstantiation()
    {
        /** @var AppFactory $appFactory */
        $appFactory = $this->objectManager->create(AppFactory::class);
        $this->assertInstanceOf(CacheWriter::class, $appFactory->getCacheWriter());
    }
    public function testWriteCache()
    {
        /** @var AppFactory $appFactory */
        $appFactory = $this->objectManager->create(AppFactory::class);
        $appFactory->getCacheWriter()->write($appFactory->getStoreConfig());
        $this->markTestIncomplete('No assertions');
    }
}
