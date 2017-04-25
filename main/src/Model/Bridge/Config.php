<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;


use IntegerNet\Solr\Config\AutosuggestConfig;
use IntegerNet\Solr\Config\CategoryConfig;
use IntegerNet\Solr\Config\FuzzyConfig;
use IntegerNet\Solr\Config\GeneralConfig;
use IntegerNet\Solr\Config\IndexingConfig;
use IntegerNet\Solr\Config\ResultsConfig;
use IntegerNet\Solr\Config\ServerConfig;
use IntegerNet\Solr\Config\StoreConfig;
use IntegerNet\Solr\Config\CmsConfig;
use IntegerNet\Solr\Implementor\Config as ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Url\ScopeInterface as UrlScopeInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Holds configuration values for a given store view.
 *
 * Not to be used as shared instance, use one of the following for constructor injection instead:
 * - IntegerNet\Solr\Model\Config\CurentStoreConfig
 * - IntegerNet\Solr\Model\Config\AllStoresConfig
 * - IntegerNet\Solr\Model\Config\FrontendStoresConfig
 *
 * @package IntegerNet\Solr\Model\Bridge
 */
class Config implements ConfigInterface
{
    /**
     * @var int
     */
    private $storeId;
    /**
     * @var UrlScopeInterface
     */
    private $urlScope;
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GeneralConfig
     */
    private $general;
    /**
     * @var ServerConfig
     */
    private $server;
    /**
     * @var IndexingConfig
     */
    private $indexing;
    /**
     * @var AutosuggestConfig
     */
    private $autosuggest;
    /**
     * @var FuzzyConfig
     */
    private $fuzzySearch;
    /**
     * @var FuzzyConfig
     */
    private $fuzzyAutosuggest;
    /**
     * @var ResultsConfig
     */
    private $results;
    /**
     * @var CmsConfig
     */
    private $cms;
    /**
     * @var CategoryConfig
     */
    private $category;

    const PARAM_STORE_ID = 'storeId';
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param int $storeId
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, DirectoryList $directoryList, $storeId)
    {
        $this->storeId = $storeId;
        $this->urlScope = $storeManager->getStore($this->storeId);
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Returns required module independent store configuration
     *
     * @return StoreConfig
     */
    public function getStoreConfig()
    {
        /** @var UrlScopeInterface $store */
        return new StoreConfig(
            $this->urlScope->getBaseUrl(),
            $this->directoryList->getPath(DirectoryList::LOG)
        );
    }

    /**
     * Returns general Solr module configuration
     *
     * @return GeneralConfig
     */
    public function getGeneralConfig()
    {
        if ($this->general === null) {
            $prefix = 'integernet_solr/general/';
            $this->general = new GeneralConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'license_key'),
                $this->_getConfigFlag($prefix . 'log'),
                $this->_getConfigFlag($prefix . 'debug')
            );
        }
        return $this->general;
    }

    /**
     * Returns Solr server configuration
     *
     * @return ServerConfig
     */
    public function getServerConfig()
    {
        if ($this->server === null) {
            $prefix = 'integernet_solr/server/';
            $this->server = new ServerConfig(
                $this->_getConfig($prefix . 'host'),
                $this->_getConfig($prefix . 'port'),
                $this->_getConfig($prefix . 'path'),
                $this->_getConfig($prefix . 'core'),
                $this->_getConfig('integernet_solr/indexing/swap_core'),
                $this->_getConfigFlag($prefix . 'use_https'),
                $this->_getConfig($prefix . 'http_method'),
                $this->_getConfigFlag($prefix . 'use_http_basic_auth'),
                $this->_getConfig($prefix . 'http_basic_auth_username'),
                $this->_getConfig($prefix . 'http_basic_auth_password')
            );
        }
        return $this->server;
    }

    /**
     * Returns indexing configuration
     *
     * @return IndexingConfig
     */
    public function getIndexingConfig()
    {
        if ($this->indexing === null) {
            $prefix = 'integernet_solr/indexing/';
            $this->indexing = new IndexingConfig(
                $this->_getConfig($prefix . 'pagesize'),
                $this->_getConfigFlag($prefix . 'delete_documents_before_indexing'),
                $this->_getConfigFlag($prefix . 'swap_cores')
            );
        }
        return $this->indexing;
    }
    /**
     * Returns autosuggest configuration
     *
     * @return AutosuggestConfig
     */
    public function getAutosuggestConfig()
    {
        if ($this->autosuggest === null) {
            $prefix = 'integernet_solr/autosuggest/';
            $this->autosuggest = new AutosuggestConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'use_php_file_in_home_dir'),
                $this->_getConfig($prefix . 'max_number_searchword_suggestions'),
                $this->_getConfig($prefix . 'max_number_product_suggestions'),
                $this->_getConfig($prefix . 'max_number_category_suggestions'),
                $this->_getConfig($prefix . 'max_number_cms_page_suggestions'),
                $this->_getConfigFlag($prefix . 'show_complete_category_path'),
                $this->_getConfigFlag($prefix . 'category_link_type'),
                @unserialize($this->_getConfig($prefix . 'attribute_filter_suggestions'))
            );
        }
        return $this->autosuggest;
    }
    /**
     * Returns fuzzy configuration for search
     *
     * @return FuzzyConfig
     */
    public function getFuzzySearchConfig()
    {
        if ($this->fuzzySearch === null) {
            $prefix = 'integernet_solr/fuzzy/';
            $this->fuzzySearch = new FuzzyConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'sensitivity'),
                $this->_getConfig($prefix . 'minimum_results')
            );
        }
        return $this->fuzzySearch;
    }
    /**
     * Returns fuzzy configuration for autosuggest
     *
     * @return FuzzyConfig
     */
    public function getFuzzyAutosuggestConfig()
    {
        if ($this->fuzzyAutosuggest === null) {
            $prefix = 'integernet_solr/fuzzy/';
            $this->fuzzyAutosuggest = new FuzzyConfig(
                $this->_getConfigFlag($prefix . 'is_active_autosuggest'),
                $this->_getConfig($prefix . 'sensitivity_autosuggest'),
                $this->_getConfig($prefix . 'minimum_results_autosuggest')
            );
        }
        return $this->fuzzyAutosuggest;
    }
    /**
     * Returns search results configuration
     *
     * @return ResultsConfig
     */
    public function getResultsConfig()
    {
        if ($this->results === null) {
            $prefix = 'integernet_solr/results/';
            $this->results = new ResultsConfig(
                $this->_getConfigFlag($prefix . 'use_html_from_solr'),
                $this->_getConfig($prefix . 'search_operator'),
                $this->_getConfig($prefix . 'priority_categories'),
                $this->_getConfig($prefix . 'price_step_size'),
                $this->_getConfig($prefix . 'max_price'),
                $this->_getConfigFlag($prefix . 'use_custom_price_intervals'),
                explode(',', $this->_getConfig($prefix . 'custom_price_intervals'))
            );
        }
        return $this->results;
    }
    /**
     * Returns search results configuration
     *
     * @return CmsConfig
     */
    public function getCmsConfig()
    {
        if ($this->cms === null) {
            $prefix = 'integernet_solr/cms/';
            $this->cms = new CmsConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                //TODO create system configuration for the following:
                $this->_getConfigFlag($prefix . 'use_in_search_results'),
                $this->_getConfig($prefix . 'max_number_results'),
                $this->_getConfigFlag($prefix . 'is_fuzzy_active'),
                $this->_getConfig($prefix . 'fuzzy_sensitivity')
            );
        }
        return $this->cms;
    }

    /**
     * Returns category configuration
     *
     * @return CategoryConfig
     */
    public function getCategoryConfig()
    {
        if ($this->category === null) {
            $prefix = 'integernet_solr/category/';
            $this->category = new CategoryConfig(
                $this->_getConfigFlag($prefix . 'is_active'),
                $this->_getConfig($prefix . 'filter_position'),
                $this->_getConfigFlag($prefix . 'is_indexer_active'),
                $this->_getConfigFlag($prefix . 'use_in_search_results'),
                $this->_getConfig($prefix . 'max_number_results'),
                $this->_getConfigFlag($prefix . 'fuzzy_is_active'),
                $this->_getConfig($prefix . 'fuzzy_sensitivity')
            );
        }
        return $this->category;
    }

    /**
     * @param $path
     * @return mixed
     */
    private function _getConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $this->storeId);
    }
    /**
     * @param $path
     * @return mixed
     */
    private function _getConfigFlag($path)
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $this->storeId);
    }

}