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

use IntegerNet\Solr\Model\Plugin\LayoutPlugin;
use Magento\Framework\View\Result\Layout;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\ObjectManager;

class LayoutTest extends \PHPUnit_Framework_TestCase
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
    public function testTheLayoutPluginIsRegisteredInFrontend()
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->create(PluginList::class);

        $pluginInfo = $pluginList->get(Layout::class, []);
        $this->assertSame(LayoutPlugin::class, $pluginInfo['integernetSolrUpdateLayout']['instance']);
    }

}