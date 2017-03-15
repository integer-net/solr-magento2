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


}