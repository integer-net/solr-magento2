<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\Result\Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Collection constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     */
    public function __construct($resource = null, \Magento\Framework\Registry $frameworkRegistry, 
        \Integer\Net\Solr\Model\Result $modelResult)
    {
        $this->_frameworkRegistry = $frameworkRegistry;
        $this->_modelResult = $modelResult;
}

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return  \Magento\Framework\Data\Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->_items = $this->_getSolrResult()->response->docs;

        return $this;
    }
    
    public function getColumnValues($colName)
    {
        $this->load();

        $col = [];
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
        if (is_null($this->_totalRecords)) {
            $this->_totalRecords = $this->_getSolrResult()->response->numFound;
        }
        return intval($this->_totalRecords);
    }

    /**
     * Adding product count to categories collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection
     * @return Integer\Net\Solr\Model\Result\Collection
     */
    public function addCountToCategories($categoryCollection)
    {
        $isAnchor    = [];
        $isNotAnchor = [];
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[]    = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = [];
        if ($isAnchor || $isNotAnchor) {

            foreach((array)$this->_getSolrResult()->facet_counts->facet_fields->category as $categoryId => $productCount) {
                $productCounts[intval($categoryId)] = intval($productCount);
            }
        }

        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            $category->setProductCount($_count);
        }

        return $this;
    }

    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return Integer\Net\Solr\Model\Result\Collection
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        $categoryFilters = $this->_frameworkRegistry->registry('category_filters');
        if (!is_array($categoryFilters)) {
            $categoryFilters = [];
        }
        $categoryFilters[] = $category;
        $this->_frameworkRegistry->unregister('category_filters');
        $this->_frameworkRegistry->register('category_filters', $categoryFilters);
        return $this;
    }

    /**
     * Retrieve maximal price
     *
     * @return float
     */
    public function getMaxPrice()
    {
        /** @var \Apache\Solr\Response $result */
        $result = $this->_modelResult->getSolrResult();
        if (isset($result->stats->stats_fields->price_f->max)) {
            return $result->stats->stats_fields->price_f->max;
        }

        return 0;
    }

    public function addPriceData($customerGroupId = null, $websiteId = null)
    {
        return $this;
    }

    /**
     * @return \Apache\Solr\Response
     */
    protected function _getSolrResult()
    {
        return $this->_modelResult->getSolrResult();
    }

    public function getLoadedIds ()
    {}
}