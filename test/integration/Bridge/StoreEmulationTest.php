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

use IntegerNet\Solr\Implementor\StoreEmulation;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

class StoreEmulationTest extends \PHPUnit_Framework_TestCase
{
    /** @var StoreEmulation */
    private $storeEmulation;
    /** @var DesignInterface */
    private $design;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var ScopeConfigInterface */
    private $config;

    protected function setUp()
    {
        $di = Bootstrap::getObjectManager();
        $this->storeManager = $di->get(StoreManagerInterface::class);
        $this->design = $di->get(DesignInterface::class);
        $this->config = $di->get(ScopeConfigInterface::class);
        $this->storeEmulation = $di->create(StoreEmulation::class);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoConfigFixture admin_store web/unsecure/base_url http://admin.example.com/
     * @magentoConfigFixture default_store web/unsecure/base_url http://frontend.example.com/
     * @magentoConfigFixture default_store general/locale/code de_DE
     */
    public function testStoreEmulation()
    {
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);

        $this->assertAppState(Area::AREA_ADMINHTML, 'en_US', 'Magento/backend', 'http://admin.example.com/');

        $this->storeEmulation->runInStore(1, function() {
            $this->assertAppState(Area::AREA_FRONTEND, 'de_DE', 'Magento/luma', 'http://frontend.example.com/');
        });

        $this->assertAppState(Area::AREA_ADMINHTML, 'en_US', 'Magento/backend', 'http://admin.example.com/');
    }

    /**
     * @param $expectedArea
     * @param $expectedLocale
     * @param $expectedTheme
     * @param $expectedBaseUrl
     */
    private function assertAppState($expectedArea, $expectedLocale, $expectedTheme, $expectedBaseUrl)
    {
        $this->assertEquals($expectedArea, $this->design->getArea());
        $this->assertEquals($expectedLocale, $this->design->getLocale());
        $this->assertEquals($expectedTheme, $this->design->getDesignTheme()->getCode());
        $this->assertEquals($expectedBaseUrl, $this->config->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE));
    }
}