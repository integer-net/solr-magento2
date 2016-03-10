<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Model;

class SolrStatusMessages implements StatusMessages
{
    protected $_messages = [];

    /**
     * @param int|null $storeId
     * @return string[]
     */
    public function getMessages($storeId = null)
    {
        return []; //TODO implement
        $this->_checkConfiguration($storeId);
        return $this->_messages;
    }

    /**
     * @param int|null $storeId
     */
    protected function _checkConfiguration($storeId = null)
    {
        $this->_createGeneralInfoMessages($storeId);
        
        if (!$this->_isModuleActive($storeId)) {
            return;
        }

        if (!$this->_isModuleLicensed()) {
            return;
        }

        if (!$this->_isServerConfigurationComplete($storeId)) {
            return;
        }

        if (!$this->_canPingSolrServer($storeId)) {
            return;
        }

        if (!$this->_canIssueSearchRequest($storeId)) {
            return;
        }

        if ($this->_configScopeConfigInterface->isSetFlag('integernet_solr/indexing/swap_cores', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            if (!$this->_isSwapcoreConfigurationComplete($storeId)) {
                return;
            }

            if (!$this->_canPingSwapCore($storeId)) {
                return;
            }

            if (!$this->_canIssueSearchRequestToSwapCore($storeId)) {
                return;
            }
        }
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _createGeneralInfoMessages($storeId)
    {
        $this->_addNoticeMessage(
            __('Module version: %1', Mage::getConfig()->getModuleConfig('IntegerNet_Solr')->version)
        );
        if (method_exists('Mage', 'getEdition')) {
            $this->_addNoticeMessage(
                __('Magento version: %1 (%2 Edition)', $this->_appProductMetadataInterface->getVersion(), Mage::getEdition())
            );
        } else {
            $this->_addNoticeMessage(
                __('Magento version: %1', $this->_appProductMetadataInterface->getVersion())
            );
        }
        if (!$this->_helperData->isModuleEnabled('Aoe_LayoutConditions')) {
            $this->_addWarningMessage(
                __('The module Aoe_LayoutConditions is not installed. Please get it from <a href="%1" target="_blank">%2</a>.', 'https://github.com/aoepeople/Aoe_LayoutConditions', 'https://github.com/aoepeople/Aoe_LayoutConditions')
            );
        }
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isModuleActive($storeId)
    {
        if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            $this->_addNoticeMessage(
                __('Solr Module is not activated.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            __('Solr Module is activated.')
        );
        return true;
    }

    /**
     * @return boolean
     */
    protected function _isModuleLicensed()
    {
        if (!trim($this->_configScopeConfigInterface->getValue('integernet_solr/general/license_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))) {

            if ($installTimestamp = $this->_configScopeConfigInterface->getValue('integernet_solr/general/install_date', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                $diff = time() - $installTimestamp;
                if (($diff < 0) || ($diff > 2419200)) {

                    $this->_addErrorMessage(
                        __('You haven\'t entered your license key yet.')
                    );
                    return false;

                } else {

                    $this->_addNoticeMessage(
                        __('You haven\'t entered your license key yet.')
                    );
                }
            }

        } else {
            if (!$this->_helperData->isKeyValid($this->_configScopeConfigInterface->getValue('integernet_solr/general/license_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))) {
    
                if ($installTimestamp = $this->_configScopeConfigInterface->getValue('integernet_solr/general/install_date', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                    $diff = time() - $installTimestamp;
                    if (($diff < 0) || ($diff > 2419200)) {

                        $this->_addErrorMessage(
                            __('The license key you have entered is incorrect.')
                        );
                        return false;

                    } else {

                        $this->_addNoticeMessage(
                            __('The license key you have entered is incorrect.')
                        );
                    }
                }
            } else {
                $this->_addSuccessMessage(
                    __('Your license key is valid.')
                );
            }
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isServerConfigurationComplete($storeId)
    {
        if (!$this->_configScopeConfigInterface->getValue('integernet_solr/server/host', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            || !$this->_configScopeConfigInterface->getValue('integernet_solr/server/port', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            || !$this->_configScopeConfigInterface->getValue('integernet_solr/server/path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
        ) {
            $this->_addErrorMessage(
                __('Solr server configuration is incomplete.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            __('Solr server configuration is complete.')
        );
        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canPingSolrServer($storeId)
    {
        $solr = $this->_helperFactory->getSolrResource()->getSolrService($storeId);

        if (!$solr->ping()) {
            $this->_addErrorMessage(
                __('Connection to Solr server failed.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            __('Connection to Solr server established successfully.')
        );

        $info = $this->_helperFactory->getSolrResource()->getInfo($storeId);
        if ($info instanceof \Apache\Solr\Response) {
            if (isset($info->lucene->{'solr-spec-version'})) {
                $solrVersion = $info->lucene->{'solr-spec-version'};
                $this->_addNoticeMessage(
                    __('Solr version: %1', $solrVersion)
                );
            }
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canIssueSearchRequest($storeId)
    {
        $solr = $this->_helperFactory->getSolrResource()->getSolrService($storeId);

        try {
            $solr->search('text_autocomplete:test');

            $this->_addSuccessMessage(
                __('Test search request issued successfully.')
            );
            return true;
        } catch (\Exception $e) {
            $this->_addErrorMessage(
                __('Test search request failed.')
            );
            $this->_addNoticeMessage(
                __('Maybe the configuration files are not installed correctly on the Solr server.')
            );
            $this->_addNoticeMessage(
                __('You can get a meaningful error message from the tab "Logging" on the Solr Admin Interface.')
            );

            return false;
        }

    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isSwapcoreConfigurationComplete($storeId)
    {
        if (!$this->_configScopeConfigInterface->getValue('integernet_solr/server/core', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) || !$this->_configScopeConfigInterface->getValue('integernet_solr/indexing/swap_core', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            $this->_addErrorMessage(
                __('Please enter name of core and swap core.')
            );
            return false;
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canPingSwapCore($storeId)
    {
        $solr = $this->_helperFactory->getSolrResource()->setUseSwapIndex()->getSolrService($storeId);

        if (!$solr->ping()) {
            $this->_addErrorMessage(
                __('Solr Connection to swap core could not be established.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            __('Solr Connection to swap core established successfully.')
        );
        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _canIssueSearchRequestToSwapCore($storeId)
    {
        $solr = $this->_helperFactory->getSolrResource()->
        setUseSwapIndex()->getSolrService($storeId);

        try {
            $solr->search('text_autocomplete:test');

            $this->_addSuccessMessage(
                __('Test search request to swap core issued successfully.')
            );
            return true;
        } catch (\Exception $e) {
            $this->_addErrorMessage(
                __('Test search request to swap core failed.')
            );
            $this->_addNoticeMessage(
                __('Maybe the configuration files are not installed correctly on the Solr swap core.')
            );

            return false;
        }

    }

    /**
     * @param string $text
     * @param string $type
     */
    protected function _addMessage($text, $type)
    {
        $this->_messages[$type][] = $text;
    }

    /**
     * @param string $text
     */
    protected function _addErrorMessage($text)
    {
        $this->_addMessage($text, 'error');
    }

    /**
     * @param string $text
     */
    protected function _addSuccessMessage($text)
    {
        $this->_addMessage($text, 'success');
    }

    /**
     * @param string $text
     */
    protected function _addWarningMessage($text)
    {
        $this->_addMessage($text, 'warning');
    }

    /**
     * @param string $text
     */
    protected function _addNoticeMessage($text)
    {
        $this->_addMessage($text, 'notice');
    }
}