<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use IntegerNet\Solr\Model\Config\AllStoresConfig;
use IntegerNet\Solr\Model\Search\Adapter\SolrAdapterFactory;
use IntegerNet\Solr\Resource\ResourceFacade;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Search\AdapterInterface;
use Magento\Search\Model\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to set search engine per store view based on module configuration
 */
class AdapterFactoryPlugin
{
    const ENGINE_INTEGERNET_SOLR = 'integernet_solr';
    const ENGINE_DEFAULT = 'mysql';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var SolrAdapterFactory
     */
    private $solrAdapterFactory;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ResourceFacade
     */
    private $solrResource;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param SolrAdapterFactory $solrAdapterFactory
     * @param RequestInterface $request
     * @param AllStoresConfig $solrConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        SolrAdapterFactory $solrAdapterFactory,
        RequestInterface $request,
        AllStoresConfig $solrConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->solrAdapterFactory = $solrAdapterFactory;
        $this->request = $request;
        $this->solrResource = new ResourceFacade($solrConfig->getArrayCopy());
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param AdapterFactory $subject
     * @param \Closure $proceed
     * @param array $data
     * @return AdapterInterface
     */
    public function aroundCreate(AdapterFactory $subject, \Closure $proceed, $data = [])
    {
        if (!$this->scopeConfig->isSetFlag('integernet_solr/general/is_active')) {
            return $proceed($data);
        }
        if ($this->request->getModuleName() == 'catalog'
            && !$this->scopeConfig->isSetFlag('integernet_solr/category/is_active')) {
            return $proceed($data);
        }
        $storeId = $this->storeManager->getStore()->getId();
        if (!$this->canPingSolrServer($storeId)) {
            $this->logger->warning(__('Connection to Solr server failed.'));
            return $proceed($data);
        }
        return $this->solrAdapterFactory->create($data);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    private function canPingSolrServer($storeId)
    {
        $solr = $this->solrResource->getSolrService($storeId);

        return boolval($solr->ping());
    }
}