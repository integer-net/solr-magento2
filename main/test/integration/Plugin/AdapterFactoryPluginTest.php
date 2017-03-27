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
use IntegerNet\Solr\Model\Search\Adapter\SolrAdapter;
use Magento\Framework\Search\Adapter\Mysql\Adapter as MysqlAdapter;


class AdapterFactoryPluginTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoAppArea frontend
     */
    public function testTheAdapterFactoryPluginIsRegistered()
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->_objectManager->create(PluginList::class);

        $pluginInfo = $pluginList->get(AdapterFactory::class, []);
        $this->assertSame(AdapterFactoryPlugin::class, $pluginInfo['integernet_solr_choose_adapter']['instance']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 0
     */
    public function testMysqlAdapterIsCreated()
    {
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->_objectManager->create(AdapterFactory::class);

        $adapter = $adapterFactory->create();
        $this->assertInstanceOf(MysqlAdapter::class, $adapter);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/category/is_active 0
     */
    public function testSolrAdapterIsCreated()
    {
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->_objectManager->create(AdapterFactory::class);

        $adapter = $adapterFactory->create();
        $this->assertInstanceOf(SolrAdapter::class, $adapter);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/category/is_active 0
     */
    public function testMysqlAdapterIsCreatedOnCategoryPage()
    {
        $this->dispatch('catalog/category/view/id/333');

        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->_objectManager->create(AdapterFactory::class);

        $adapter = $adapterFactory->create();
        $this->assertInstanceOf(MysqlAdapter::class, $adapter);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/category/is_active 1
     */
    public function testSolrAdapterIsCreatedOnCategoryPage()
    {
        $this->dispatch('catalog/category/view/id/333');

        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->_objectManager->create(AdapterFactory::class);

        $adapter = $adapterFactory->create();
        $this->assertInstanceOf(SolrAdapter::class, $adapter);
    }

    public static function loadFixture()
    {
        if (file_exists(__DIR__ . '/../_files/solr_config.php')) {
            include __DIR__ . '/../_files/solr_config.php';
        } else {
            include __DIR__ . '/../_files/solr_config.dist.php';
        }
        include __DIR__ . '/../_files/categories.php';
    }
}