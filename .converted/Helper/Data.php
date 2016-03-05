<?php
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Implementor\EventDispatcher;
use IntegerNet\Solr\Implementor\HasUserQuery;
use IntegerNet\SolrSuggest\Implementor\SearchUrl;
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Helper\Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @deprecated use repository directly
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableAttributes($useAlphabeticalSearch = true)
    {
        return $this->_bridgeAttributerepository
            ->getFilterableAttributes($this->_modelStoreManagerInterface->getStore()->getId(), $useAlphabeticalSearch);
    }
    
    /**
     * @deprecated use repository directly
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInSearchAttributes($useAlphabeticalSearch = true)
    {
        return $this->_bridgeAttributerepository
            ->getFilterableInSearchAttributes($this->_modelStoreManagerInterface->getStore()->getId(), $useAlphabeticalSearch);
    }


    /**
     * @deprecated use repository directly
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogAttributes($useAlphabeticalSearch = true)
    {
        return $this->_bridgeAttributerepository
            ->getFilterableInCatalogAttributes($this->_modelStoreManagerInterface->getStore()->getId(), $useAlphabeticalSearch);

    }

    /**
     * @deprecated use repository directly
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogOrSearchAttributes($useAlphabeticalSearch = true)
    {
        return $this->_bridgeAttributerepository
            ->getFilterableInCatalogOrSearchAttributes($this->_modelStoreManagerInterface->getStore()->getId(), $useAlphabeticalSearch);
    }

    /**
     * @deprecated use repository directly
     * @return string[]
     */
    public function getAttributeCodesToIndex()
    {
        return $this->_bridgeAttributerepository->getAttributeCodesToIndex();
    }


    /**
     * @deprecated use IndexField directly
     * @param Attribute $attribute
     * @param bool $forSorting
     * @return string
     */
    public function getFieldName($attribute, $forSorting = false)
    {
        if (! $attribute instanceof Attribute) {
            $attribute = new Integer\Net\Solr\Model\Bridge\Attribute($attribute);
        }
        $indexField = new \IntegerNet\Solr\Indexer\IndexField($attribute, $forSorting);
        return $indexField->getFieldName();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        if (!$this->isLicensed()) {
            return false;
        }
        
        if ($this->isCategoryPage() && !$this->isCategoryDisplayActive()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isSearchPage()
    {
        return $this->_appRequestInterface->getModuleName() == 'catalogsearch'
            && $this->_appRequestInterface->getControllerName() == 'result';
    }

    /**
     * @return bool
     */
    public function isCategoryPage()
    {
        return $this->_appRequestInterface->getModuleName() == 'catalog'
            && $this->_appRequestInterface->getControllerName() == 'category';
    }

    /**
     * @return bool
     */
    public function isCategoryDisplayActive()
    {
        return $this->_configScopeConfigInterface->isSetFlag('integernet_solr/category/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isKeyValid($key)
    {
        if (!$key) {
            return true;
        }
        $key = trim(strtolower($key));
        $key = str_replace(['-', '_', ' '], '', $key);
        
        if (strlen($key) != 10) {
            return false;
        }
        
        $hash = md5($key);
        
        return substr($hash, -3) == 'f11';
    }

    /**
     * @return bool
     */
    public function isLicensed()
    {
        if (!$this->isKeyValid($this->_configScopeConfigInterface->getValue('integernet_solr/general/license_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))) {

            if ($installTimestamp = $this->_configScopeConfigInterface->getValue('integernet_solr/general/install_date', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                $diff = time() - $installTimestamp;
                if (($diff < 0) || ($diff > 2419200)) {

                    $this->_logLoggerInterface->debug('The IntegerNet_Solr module is not correctly licensed. Please enter your license key at System -> Configuration -> Solr or contact us via http://www.integer-net.com/solr-magento/.', \Zend\Log\Logger::WARN, 'exception.log');
                    return false;

                } else if ($diff > 1209600) {

                    $this->_logLoggerInterface->debug('The IntegerNet_Solr module is not correctly licensed. Please enter your license key at System -> Configuration -> Solr or contact us via http://www.integer-net.com/solr-magento/.', \Zend\Log\Logger::WARN, 'exception.log');
                }
            }
        }

        return true;
    }
}