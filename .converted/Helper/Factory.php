<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Config;
use IntegerNet\Solr\Implementor\SolrRequestFactory;
use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\SolrCms\Indexer\PageIndexer;
use IntegerNet\Solr\Request\ApplicationContext;
use IntegerNet\Solr\Request\RequestFactory;
use IntegerNet\Solr\Request\SearchRequestFactory;
use IntegerNet\Solr\Resource\ResourceFacade;
use IntegerNet\SolrCategories\Request\CategoryRequestFactory;
use IntegerNet\SolrSuggest\CacheBackend\File\CacheItemPool as FileCacheBackend;
use IntegerNet\SolrSuggest\Implementor\Factory\AppFactory;
use IntegerNet\SolrSuggest\Implementor\Factory\CacheReaderFactory;
use IntegerNet\SolrSuggest\Implementor\Factory\AutosuggestResultFactory;
use IntegerNet\SolrSuggest\Plain\Block\CustomHelperFactory;
use IntegerNet\SolrSuggest\Plain\Cache\CacheReader;
use IntegerNet\SolrSuggest\Plain\Cache\CacheWriter;
use IntegerNet\SolrSuggest\Plain\Cache\Convert\AttributesToSerializableAttributes;
use IntegerNet\SolrSuggest\Plain\Cache\PsrCache;
use IntegerNet\SolrSuggest\Request\AutosuggestRequestFactory;
use IntegerNet\SolrSuggest\Request\SearchTermSuggestRequestFactory;
use IntegerNet\SolrSuggest\Result\AutosuggestResult;
use Psr\Log\NullLogger;

class Integer\Net\Solr\Helper\Factory implements SolrRequestFactory, AutosuggestResultFactory, CacheReaderFactory, AppFactory
{

    /**
     * Returns new configured Solr recource. Instantiation separate from RequestFactory
     * for easy mocking in integration tests
     *
     * @return ResourceFacade
     */
    public function getSolrResource()
    {
        $storeConfig = $this->getStoreConfig();
        return new ResourceFacade($storeConfig);
    }

    /**
     * Returns new product indexer.
     *
     * @return ProductIndexer
     */
    public function getProductIndexer()
    {
        $defaultStoreId = $this->_modelStoreManagerInterface->getStore(true)->getId();
        return new ProductIndexer(
            $defaultStoreId,
            $this->getStoreConfig(),
            $this->getSolrResource(),
            $this->_helperEvent,
            $this->_bridgeAttributerepository,
            $this->_getIndexCategoryRepository(),
            $this->_bridgeProductrepositoryFactory->create(),
            $this->_bridgeProductrendererFactory->create(),
            $this->_bridgeStoreemulationFactory->create()
        );
    }

    /**
     * Returns new product indexer.
     *
     * @return PageIndexer
     */
    public function getPageIndexer()
    {
        $defaultStoreId = $this->_modelStoreManagerInterface->getStore(true)->getId();
        return new PageIndexer(
            $defaultStoreId,
            $this->getStoreConfig(),
            $this->getSolrResource(),
            $this->_helperEvent,
            $this->_bridgePagerepositoryFactory->create(),
            $this->_bridgePagerendererFactory->create(),
            $this->_bridgeStoreemulationFactory->create()
        );
    }

    /**
     * Returns new Solr service (search, autosuggest or category service, depending on application state)
     *
     * @param int $requestMode
     * @return \IntegerNet\Solr\Request\Request
     */
    public function getSolrRequest($requestMode = self::REQUEST_MODE_AUTODETECT)
    {
        $storeId = $this->_modelStoreManagerInterface->getStore()->getId();
        $config = new Integer\Net\Solr\Model\Config\Store($storeId);
        if ($config->getGeneralConfig()->isLog()) {
            $logger = $this->_helperLog;
            if ($logger instanceof Integer\Net\Solr\Helper\Log) {
                $logger->setFile(
                    $requestMode === self::REQUEST_MODE_SEARCHTERM_SUGGEST ? 'solr_suggest.log' : 'solr.log'
                );
            }
        } else {
            $logger = new NullLogger;
        }

        $isCategoryPage = $this->_helperData->isCategoryPage();
        $applicationContext = new ApplicationContext(
            $this->_bridgeAttributerepository,
            $config->getResultsConfig(),
            $config->getAutosuggestConfig(),
            $this->_helperEvent,
            $logger
        );
        if ($this->_viewLayout && $block = $this->_viewLayout->getBlock('product_list_toolbar')) {
            $pagination = \Magento\Framework\App\ObjectManager::getInstance()->create('integernet_solr/bridge_pagination_toolbar', $block);
            $applicationContext->setPagination($pagination);
        }
        /** @var RequestFactory $factory */
        if ($requestMode === self::REQUEST_MODE_SEARCHTERM_SUGGEST) {
            $applicationContext->setQuery($this->_helperSearchterm);
            $factory = new SearchTermSuggestRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $storeId);
        } elseif ($isCategoryPage) {
            $factory = new CategoryRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $storeId,
                $this->_frameworkRegistry->registry('current_category')->getId()
            );
        } elseif ($requestMode === self::REQUEST_MODE_AUTOSUGGEST) {
            $applicationContext
                ->setFuzzyConfig($config->getFuzzyAutosuggestConfig())
                ->setQuery($this->_helperSearchtermsynonym);
            $factory = new AutosuggestRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $storeId
            );
        } else {
            $applicationContext
                ->setFuzzyConfig($config->getFuzzySearchConfig())
                ->setQuery($this->_helperSearchtermsynonym);
            $factory = new SearchRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $storeId
            );
        }
        return $factory->createRequest();
    }

    /**
     * @return Config[]
     */
    public function getStoreConfig()
    {
        $storeConfig = [];
        foreach ($this->_modelStoreManagerInterface->getStores(true) as $store) {
            /** @var \Magento\Store\Model\Store $store */
            if ($store->getIsActive()) {
                $storeConfig[$store->getId()] = new Integer\Net\Solr\Model\Config\Store($store->getId());
            }
        }
        return $storeConfig;
    }

    /**
     * @return Integer\Net\Solr\Model\Config\Store
     */
    public function getCurrentStoreConfig()
    {
        return new Integer\Net\Solr\Model\Config\Store($this->_modelStoreManagerInterface->getStore()->getId());
    }

    /**
     * @return AutosuggestResult
     */
    public function getAutosuggestResult()
    {
        $storeConfig = $this->getCurrentStoreConfig();
        return new AutosuggestResult(
            $this->_modelStoreManagerInterface->getStore()->getId(),
            $storeConfig->getGeneralConfig(),
            $storeConfig->getAutosuggestConfig(),
            $this->_helperSearchterm,
            $this->_helperSearchurl,
            $this->_getSuggestCategoryRepository(),
            $this->_getAttributeRepository(),
            $this->getSolrRequest(self::REQUEST_MODE_AUTOSUGGEST),
            $this->getSolrRequest(self::REQUEST_MODE_SEARCHTERM_SUGGEST)
        );
    }

    /**
     * @return \IntegerNet\SolrSuggest\Plain\Cache\CacheReader
     */
    public function getCacheReader()
    {
        return new CacheReader($this->_getCacheStorage());
    }

    /**
     * @return \IntegerNet\SolrSuggest\Plain\Cache\CacheWriter
     */
    public function getCacheWriter()
    {
        $customHelperClass = new ReflectionClass(
            Mage::getConfig()->getHelperClassName('integernet_solr/custom')
        );
        $autosuggestConfigByStore = array_map(
            function (Config $config) {
                return $config->getAutosuggestConfig();
            },
            $this->getStoreConfig()
        );
        return new CacheWriter(
            $this->_getCacheStorage(),
            new AttributesToSerializableAttributes($this->_getAttributeRepository(), $this->_helperEvent, $autosuggestConfigByStore),
            $this->_helperAutosuggest,
            new CustomHelperFactory($customHelperClass->getFileName(), $customHelperClass->getName()),
            $this->_helperEvent,
            $this->_helperAutosuggest
        );
    }

    /**
     * Override this if you want to use a different cache backend. It is important to use the same
     * cache backend in the autosuggest.php bootstrap file
     *
     * @return \IntegerNet\SolrSuggest\Plain\Cache\CacheStorage
     */
    protected function _getCacheStorage()
    {
        return new PsrCache(new FileCacheBackend($this->_frameworkFilesystem->getDirectoryWrite('cache')->getAbsolutePath() . DIRECTORY_SEPARATOR . 'integernet_solr'));
    }

    /**
     * @return \IntegerNet\Solr\Implementor\AttributeRepository
     */
    protected function _getAttributeRepository()
    {
        return $this->_bridgeAttributerepository;
    }

    /**
     * @return Integer\Net\Solr\Model\Bridge\CategoryRepository
     */
    protected function _getIndexCategoryRepository()
    {
        return $this->_bridgeCategoryrepository;
    }

    /**
     * @return Integer\Net\Solr\Model\Bridge\CategoryRepository
     */
    protected function _getSuggestCategoryRepository()
    {
        return $this->_bridgeCategoryrepository;
    }
}