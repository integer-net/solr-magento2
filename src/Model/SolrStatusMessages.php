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

use IntegerNet\Solr\Model\Config\AllStoresConfig;
use IntegerNet\Solr\Resource\ResourceFacade;
use Magento\Backend\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;

class SolrStatusMessages implements StatusMessages
{
    protected $_messages = [];
    private $solrResource;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Url
     */
    private $urlBuilder;
    /**
     * @var AllStoresConfig
     */
    private $moduleConfig;

    public function __construct(ProductMetadataInterface $productMetadata, ModuleListInterface $moduleList,
                                ScopeConfigInterface $scopeConfig, Url $urlBuilder, AllStoresConfig $moduleConfig)
    {
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->moduleConfig = $moduleConfig;
        $this->solrResource = new ResourceFacade($this->moduleConfig->getArrayCopy());
    }

    /**
     * @param int|null $storeId
     * @return string[]
     */
    public function getMessages($storeId = null)
    {
        $this->_checkConfiguration($storeId);
        return $this->_messages;
    }

    /**
     * @param int|null $storeId
     */
    protected function _checkConfiguration($storeId = null)
    {
        $this->_createGeneralInfoMessages();

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
        if ($this->moduleConfig[(int)$storeId]->getIndexingConfig()->isSwapCores()) {
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

    protected function _createGeneralInfoMessages()
    {
        $this->_addNoticeMessage(
            __('Module version: <span class="version">%1</span>', $this->moduleList->getOne('IntegerNet_Solr')['setup_version'])
        );
        $this->_addNoticeMessage(
            __('Magento version: <span class="version">%1</span> (%2 Edition)', $this->productMetadata->getVersion(), $this->productMetadata->getEdition())
        );
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isModuleActive($storeId)
    {
        //TODO set search engine via plugin based on module activation, so that it can be activated per store and does not require double activation
        $searchEngine = $this->scopeConfig->getValue(\Magento\CatalogSearch\Model\ResourceModel\EngineInterface::CONFIG_ENGINE_PATH);
        if ('integernet_solr' !== $searchEngine) {
            $this->_addWarningMessage(
                __(
                    'Solr search engine is not activated (Current search engine: <strong>%1</strong>). <a href="%2">Click here to change search engine configuration</a>',
                    $searchEngine, $this->urlBuilder->getUrl('integernet_solr/system/configureSearchEngine')
                )
            );
            return false;
        }
        if (! $this->moduleConfig[(int)$storeId]->getGeneralConfig()->isActive()) {
            $this->_addErrorMessage(
                __('Solr search engine is activated but module is not active. Activate the module below.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            __('Solr search engine is activated and module is active.')
        );
        return true;
    }

    /**
     * @return boolean
     */
    protected function _isModuleLicensed()
    {
        //TODO extract licensing from M1 module to solr-pro package, reuse
        return true;
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    protected function _isServerConfigurationComplete($storeId)
    {
        $serverConfig = $this->moduleConfig[(int) $storeId]->getServerConfig();
        if (
            ! $serverConfig->getHost()
            || ! $serverConfig->getPort()
            || ! $serverConfig->getPath()
            || ! $serverConfig->getCore()
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
        $solr = $this->solrResource->getSolrService($storeId);

        if (!$solr->ping()) {
            $this->_addErrorMessage(
                __('Connection to Solr server failed.')
            );
            return false;
        }

        $this->_addSuccessMessage(
            __('Connection to Solr server established successfully.')
        );

        $info = $this->solrResource->getInfo($storeId);
        if ($info instanceof \Apache_Solr_Response) {
            if (isset($info->lucene->{'solr-spec-version'})) {
                $solrVersion = $info->lucene->{'solr-spec-version'};
                $this->_addNoticeMessage(
                    __('Solr version: <span class="version">%1</span>', $solrVersion)
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
        $solr = $this->solrResource->getSolrService($storeId);

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
        if (
            ! $this->moduleConfig[(int)$storeId]->getServerConfig()->getCore() ||
            ! $this->moduleConfig[(int)$storeId]->getServerConfig()->getSwapCore()
        ) {
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
        $solr = $this->solrResource->setUseSwapIndex()->getSolrService($storeId);

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
        $solr = $this->solrResource->setUseSwapIndex()->getSolrService($storeId);

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