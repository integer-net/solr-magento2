<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\Solr\Implementor\StoreEmulation;

class Integer\Net\Solr\Model\Bridge\StoreEmulation implements StoreEmulation
{
    protected $_currentStoreId = null;
    protected $_isEmulated = false;
    protected $_initialEnvironmentInfo = null;
    protected $_unsecureBaseConfig = [];

    /**
     * @param int $storeId
     * @throws \Magento\Framework\Exception
     */
    public function start($storeId)
    {
        $this->stop();
        $newLocaleCode = $this->_configScopeConfigInterface->getValue(\Magento\Framework\Model\Locale::XML_PATH_DEFAULT_LOCALE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $this->_currentStoreId = $storeId;
        $this->_initialEnvironmentInfo = $this->_appEmulation->startEnvironmentEmulation($storeId);
        $this->_isEmulated = true;
        Mage::app()->getLocale()->setLocaleCode($newLocaleCode);
        $this->_modelTranslate->setLocale($newLocaleCode)->init(\Magento\Framework\Model\App\Area::AREA_FRONTEND, true);
        $this->_viewDesignInterface->setStore($storeId);
        $this->_viewDesignInterface->setPackageName();
        $themeName = $this->_configScopeConfigInterface->getValue('design/theme/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $this->_viewDesignInterface->setTheme($themeName);

        $this->_unsecureBaseConfig[$storeId] = $this->_configScopeConfigInterface->getValue('web/unsecure', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $store = $this->_modelStoreManagerInterface->getStore($storeId);
        $store->setConfig('web/unsecure/base_skin_url', $this->_configScopeConfigInterface->getValue('web/secure/base_skin_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));
        $store->setConfig('web/unsecure/base_media_url', $this->_configScopeConfigInterface->getValue('web/secure/base_media_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));
        $store->setConfig('web/unsecure/base_js_url', $this->_configScopeConfigInterface->getValue('web/secure/base_js_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));
    }

    public function stop()
    {
        if (isset($this->_unsecureBaseConfig[$this->_currentStoreId])) {
            $store = $this->_modelStoreManagerInterface->getStore($this->_currentStoreId);
            $store->setConfig('web/unsecure/base_skin_url', $this->_unsecureBaseConfig[$this->_currentStoreId]['base_skin_url']);
            $store->setConfig('web/unsecure/base_media_url', $this->_unsecureBaseConfig[$this->_currentStoreId]['base_media_url']);
            $store->setConfig('web/unsecure/base_js_url', $this->_unsecureBaseConfig[$this->_currentStoreId]['base_js_url']);
        }

        if ($this->_isEmulated && $this->_initialEnvironmentInfo) {
            $this->_appEmulation->stopEnvironmentEmulation($this->_initialEnvironmentInfo);
        }
    }

    public function __destruct()
    {
        $this->stop();
    }
}