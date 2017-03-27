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

use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

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
     * @param EntityFactoryInterface $entityFactory
     * @param CurrentStoreConfig $config
     * @param CategoriesResult $result
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        CurrentStoreConfig $config,
        CategoriesResult $result
    ) {
        parent::__construct($entityFactory);
        $this->config = $config;
        $this->result = $result;
    }

    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->config->getCategoryConfig()->canUseInSearchResults()) {
            $this->_items = $this->result->getSolrResult()->response->docs;
        }
        return $this;
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