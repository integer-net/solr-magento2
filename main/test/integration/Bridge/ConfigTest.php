<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Bridge;

use IntegerNet\Solr\Implementor\Config as ConfigInterface;
use IntegerNet\Solr\Model\Bridge\Config;
use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use IntegerNet\Solr\Model\Config\AllStoresConfig;
use IntegerNet\Solr\Model\Config\FrontendStoresConfig;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testInstantiation()
    {
        $storeId = 1;
        $config = $this->objectManager->create(ConfigInterface::class, [Config::PARAM_STORE_ID => $storeId]);
        $this->assertInstanceOf(Config::class, $config);
    }

    public function testCurrentStoreConfig()
    {
        $currentStoreConfig = $this->objectManager->create(CurrentStoreConfig::class);
        $this->assertInstanceOf(Config::class, $currentStoreConfig);
    }

    public function testAllStoresConfig()
    {
        $allStoresConfig = $this->objectManager->create(AllStoresConfig::class);
        $this->assertInstanceOf(AllStoresConfig::class, $allStoresConfig);
        $this->assertArrayHasKey(0, $allStoresConfig);
        $this->assertInstanceOf(Config::class, $allStoresConfig[0], 'admin (default) store config should be loaded');
        $this->assertArrayHasKey(1, $allStoresConfig, 'main store config should be loaded');
    }
    public function testFrontendStoresConfig()
    {
        $frontendStoresConfig = $this->objectManager->create(FrontendStoresConfig::class);
        $this->assertInstanceOf(FrontendStoresConfig::class, $frontendStoresConfig);
        $this->assertArrayNotHasKey(0, $frontendStoresConfig, 'admin (default) store config should not be loaded');
        $this->assertArrayHasKey(1, $frontendStoresConfig, 'main store config should be loaded');
    }

}