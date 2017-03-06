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
use IntegerNet\Solr\Model\Plugin\SearchEnginePlugin;
use IntegerNet\Solr\Model\Search\Adapter\SolrAdapter;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\AdapterFactory;
use Magento\TestFramework\Interception\PluginList;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\Search\Adapter\Mysql;

class SearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }
    public function testTheSearchEngineluginIsRegistered()
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->create(PluginList::class);

        $pluginInfo = $pluginList->get(ScopeConfigInterface::class, []);
        $this->assertSame(SearchEnginePlugin::class, $pluginInfo['changeSearchEngine']['instance']);
    }

    /**
     * @dataProvider dataSearchEngineConfiguration
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testAdapterFactoryReturnsAdapterBasedOnModuleConfiguration($engine, $isModuleActive, $expectedInstance)
    {
        $storeId = 1;
        $this->setStoreConfig('catalog/search/engine', $engine, $storeId);
        $this->setStoreConfig('integernet_solr/general/is_active', $isModuleActive, $storeId);
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->objectManager->create(AdapterFactory::class);
        $this->assertInstanceOf($expectedInstance, $adapterFactory->create());
    }

    public static function dataSearchEngineConfiguration()
    {
        return [
            'mysql_configured' => [
                'engine' => 'mysql',
                'is_module_active' => false,
                'expected_instance' => Mysql\Adapter::class
            ],
            'solr_configured' => [
                'engine' => 'integernet_solr',
                'is_module_active' => true,
                'expected_instance' => SolrAdapter::class
            ],
            'solr_autoselect' => [
                'engine' => 'mysql',
                'is_module_active' => true,
                'expected_instance' => SolrAdapter::class
            ],
            'mysql_autoselect' => [
                'engine' => 'integernet_solr',
                'is_module_active' => false,
                'expected_instance' => Mysql\Adapter::class
            ],
        ];
    }

    private function setStoreConfig($configPath, $value, $storeCode = null)
    {
        $this->objectManager->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            $configPath,
            $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}