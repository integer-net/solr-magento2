<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Helper\CatalogSearch\Data extends \Magento\CatalogSearch\Helper\Data
{
    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        if ($this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            if ($this->_getApp()->getStore()->isCurrentlySecure()) {
                $baseUrl = $this->_configScopeConfigInterface->getValue('web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            } else {
                $baseUrl = $this->_configScopeConfigInterface->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            }
            switch ($this->_configScopeConfigInterface->getValue('integernet_solr/autosuggest/use_php_file_in_home_dir', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                case Integer\Net\Solr\Model\Source\AutosuggestMethod::AUTOSUGGEST_METHOD_PHP:
                    return $baseUrl . 'autosuggest.php?store_id=' . $this->_modelStoreManagerInterface->getStore()->getId();
                case Integer\Net\Solr\Model\Source\AutosuggestMethod::AUTOSUGGEST_METHOD_MAGENTO_DIRECT:
                    return $baseUrl . 'autosuggest-mage.php?store_id=' . $this->_modelStoreManagerInterface->getStore()->getId();
            }
        }

        return parent::getSuggestUrl();
    }
}