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

use IntegerNet\Solr\Model\Plugin\CollectionProviderPlugin;
use IntegerNet\Solr\Model\Plugin\LayoutPlugin;
use IntegerNet\Solr\Model\Plugin\SearchEnginePlugin;
use IntegerNet\Solr\Model\Search\Adapter\SolrAdapter;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Search\Model\AdapterFactory;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\Search\Adapter\Mysql;

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
        $this->assertSame(LayoutPlugin::class, $pluginInfo['updateLayout']['instance']);
    }

}