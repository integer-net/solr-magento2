<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Plugin;

use IntegerNet\Solr\Model\Plugin\AdapterFactoryPlugin;
use Magento\Search\Model\AdapterFactory;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\ObjectManager;
use IntegerNet\Solr\Model\Search\Adapter\SolrAdapter;
use Magento\Framework\Search\Adapter\Mysql\Adapter as MysqlAdapter;

class AdapterFactoryPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoAppArea frontend
     */
    public function testTheAdapterFactoryPluginIsRegistered()
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->create(PluginList::class);

        $pluginInfo = $pluginList->get(AdapterFactory::class, []);
        $this->assertSame(AdapterFactoryPlugin::class, $pluginInfo['integernet_solr_choose_adapter']['instance']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store integernet_solr/general/is_active 0
     */
    public function testMysqlAdapterIsCreated()
    {
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->objectManager->create(AdapterFactory::class);
        $this->assertInstanceOf(AdapterFactory::class, $adapterFactory);

        $adapter = $adapterFactory->create();
        $this->assertInstanceOf(MysqlAdapter::class, $adapter);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store integernet_solr/general/is_active 1
     */
    public function testSolrAdapterIsCreated()
    {
        $this->markTestSkipped('Setting configuration doesn\'t work with Magento 2.1.5.');

        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->objectManager->create(AdapterFactory::class);
        $this->assertInstanceOf(AdapterFactory::class, $adapterFactory);

        $adapter = $adapterFactory->create();
        $this->assertInstanceOf(SolrAdapter::class, $adapter);
    }
}