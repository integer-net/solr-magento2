<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\SolrCategories\Model\ResourceModel;

use IntegerNet\Solr\Model\Config\AllStoresConfig;
use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use IntegerNet\Solr\Resource\ResourceFacade;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class CategoriesCollection extends DataCollection
{
    /**
     * @var CurrentStoreConfig
     */
    private $config;
    /**
     * @var CategoriesResult
     */
    private $result;
    /**
     * @var int|null
     */
    protected $totalRecords;
    /**
     * @var ResourceFacade
     */
    private $solrResource;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param CurrentStoreConfig $config
     * @param CategoriesResult $result
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        CurrentStoreConfig $config,
        CategoriesResult $result,
        AllStoresConfig $solrConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($entityFactory);
        $this->config = $config;
        $this->result = $result;
        $this->solrResource = new ResourceFacade($solrConfig->getArrayCopy());
        $this->storeManager = $storeManager;
    }

    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->config->getCategoryConfig()->canUseInSearchResults()) {
            $storeId = $this->storeManager->getStore()->getId();
            if (!$this->canPingSolrServer($storeId)) {
                return $this;
            }
            $this->_items = $this->result->getSolrResult()->response->docs;
        }
        return $this;
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

    public function getColumnValues($colName)
    {
        $this->load();

        $col = array();
        foreach ($this->getItems() as $item) {
            $field = $item->getField($colName);
            $col[] = $field['value'];
        }
        return $col;
    }

    /**
     * Retrieve collection all items count
     *
     * @return int
     */
    public function getSize()
    {
        $this->load();
        if (is_null($this->totalRecords)) {
            $this->totalRecords = 0;
            if ($this->config->getCategoryConfig()->canUseInSearchResults()) {
                $maxNumberResults = $this->config->getCategoryConfig()->getMaxNumberResults();
                if ($maxNumberResults) {
                    $this->totalRecords = min($maxNumberResults, $this->result->getSolrResult()->response->numFound);
                } else {
                    $this->totalRecords = $this->result->getSolrResult()->response->numFound;
                }

            }
        }
        return intval($this->totalRecords);
    }

    public function getLoadedIds()
    {}
}