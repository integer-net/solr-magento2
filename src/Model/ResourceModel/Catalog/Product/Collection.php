<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\ResourceModel\Catalog\Product\Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /** @var Integer\Net\Solr\Model\Result\Collection */
    protected $_solrResultCollection;

    /**
     * @param Integer\Net\Solr\Model\Result\Collection $solrResultCollection
     * @return Integer\Net\Solr\Model\ResourceModel\Catalog\Product\Collection
     */
    public function setSolrResultCollection($solrResultCollection)
    {
        $productIds = $solrResultCollection->getColumnValues('product_id');
        $this->addAttributeToFilter('entity_id', ['in' => $productIds]);
        $this->_solrResultCollection = $solrResultCollection;
        return $this;
    }

    /**
     * @return Integer\Net\Solr\Model\Result\Collection
     */
    public function getSolrResultCollection()
    {
        if (is_null($this->_solrResultCollection)) {
            $this->setSolrResultCollection($this->_resultCollection);
        }
        return $this->_solrResultCollection;
    }

    protected function _beforeLoad()
    {
        if ($this->_helperData->isActive() && is_null($this->_solrResultCollection)) {
            $this->setSolrResultCollection($this->_resultCollection);
        }

        return parent::_beforeLoad();
    }

    /**
     * Bring collection items into order from solr
     *
     * @return Integer\Net\Solr\Model\ResourceModel\Catalog\Product\Collection
     */
    protected function _afterLoad()
    {
        if (!$this->_helperData->isActive()) {
            return parent::_afterLoad();
        }

        parent::_afterLoad();

        $tempItems = [];
        foreach ($this->getSolrResultCollection()->getColumnValues('product_id') as $itemId) {
            $item = $this->getItemById($itemId);
            if (!is_null($item)) {
                $tempItems[$itemId] = $item;
            }
        }
        $this->_items = $tempItems;

        return $this;
    }

    /**
     * Get Collection size from Solr
     *
     * @return int
     */
    public function getSize()
    {
        if (!$this->_helperData->isActive()) {
            return parent::getSize();
        }
        return $this->getSolrResultCollection()->getSize();
    }

    /**
     * Add attribute to sort order
     * Fix for search result display with enabled flat index and not html from solr index
     *
     * @param string $attribute
     * @param string $dir
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (!$this->isEnabledFlat()) {
            return parent::addAttributeToSort($attribute, $dir);
        }

        if (!$this->_helperData->isActive()) {
            return parent::addAttributeToSort($attribute, $dir);
        }

        if ($attribute == 'position') {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
                return $this;
            }
            // optimize if using cat index
            $filters = $this->_productLimitationFilters;
            if (isset($filters['category_id']) || isset($filters['visibility'])) {
                $this->getSelect()->order('cat_index.position ' . $dir);
            } else {
                $this->getSelect()->order('e.entity_id ' . $dir);
            }

            return $this;
        }
    }
}