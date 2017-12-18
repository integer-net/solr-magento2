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

use IntegerNet\Solr\Controller\AbstractController;
use IntegerNet\Solr\Fixtures\SolrConfig;
use IntegerNet\Solr\Model\Plugin\AdapterFactoryPlugin;
use IntegerNet\Solr\Model\Search\Adapter\SolrAdapter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Search\Adapter\Mysql\Adapter as MysqlAdapter;
use Magento\Search\Model\AdapterFactory;
use Magento\TestFramework\Interception\PluginList;

class AdapterFactoryPluginTest extends AbstractController
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
     * @magentoAppIsolation enabled
     */
    public function testSolrAdapterIsCreatedIfModuleEnabled()
    {
        $this->assertSearchAdapterInstanceOf(SolrAdapter::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testMysqlAdapterIsCreatedIfModuleDisabled()
    {
        SolrConfig::loadAdditional(['integernet_solr/general/is_active' => 0]);
        $this->assertSearchAdapterInstanceOf(MysqlAdapter::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testMysqlAdapterIsCreatedOnCategoryPage()
    {
        $this->simulateCategoryRequest();
        $this->assertSearchAdapterInstanceOf(MysqlAdapter::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testSolrAdapterIsCreatedOnCategoryPageIfSolrIsEnabledForCategories()
    {
        SolrConfig::loadAdditional(['integernet_solr/category/is_active' => 1]);
        $this->simulateCategoryRequest();
        $this->assertSearchAdapterInstanceOf(SolrAdapter::class);
    }


    private function simulateCategoryRequest()
    {
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $request->setModuleName('catalog');
    }

    private function assertSearchAdapterInstanceOf(string $class)
    {
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = $this->_objectManager->create(AdapterFactory::class);
        $adapter = $adapterFactory->create();
        $this->assertInstanceOf($class, $adapter);
    }
}