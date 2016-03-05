<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
use IntegerNet\Solr\Query\Params\FilterQueryBuilder;
use IntegerNet\Solr\Request\HasFilter;
use IntegerNet\Solr\Request\Request;

class Integer\Net\Solr\Model\Result
{
    /**
     * @var $_solrRequest \IntegerNet\Solr\Request\Request
     */
    protected $_solrRequest;
    /**
     * @var $_filterQueryBuilder FilterQueryBuilder
     */
    protected $_filterQueryBuilder;
    /**
     * @var $_solrResult null|\IntegerNet\Solr\Resource\SolrResponse
     */
    protected $_solrResult = null;

    protected $activeFilterAttributeCodes = [];

    function __construct()
    {
        $this->_solrRequest = $this->_helperFactory->getSolrRequest();
        if ($this->_solrRequest instanceof HasFilter) {
            $this->_filterQueryBuilder = $this->_solrRequest->getFilterQueryBuilder();
            $this->_addCategoryFilters();
            $this->_addAttributeFilters();
            $this->_addPriceFilters();
        }
    }

    /**
     * Call Solr server twice: Once without fuzzy search, once with (if configured)
     *
     * @return \IntegerNet\Solr\Resource\SolrResponse
     */
    public function getSolrResult()
    {
        if (is_null($this->_solrResult)) {
            $this->_solrResult = $this->_solrRequest->doRequest($this->activeFilterAttributeCodes);
        }

        return $this->_solrResult;
    }


    /**
     * @param Integer\Net\Solr\Model\Bridge\Attribute $attribute
     * @param int $value
     */
    public function addAttributeFilter($attribute, $value)
    {
        $this->_filterQueryBuilder->addAttributeFilter($attribute, $value);
        $this->activeFilterAttributeCodes[] = $attribute->getAttributeCode();
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    public function addCategoryFilter($category)
    {
        $this->_filterQueryBuilder->addCategoryFilter($category->getId());
        $this->activeFilterAttributeCodes[] = 'category';
    }

    /**
     * @param int $range
     * @param int $index
     */
    public function addPriceRangeFilterByIndex($range, $index)
    {
        $this->_filterQueryBuilder->addPriceRangeFilterByConfiguration($range, $index);
        $this->activeFilterAttributeCodes[] = 'price';
    }

    /**
     * @param float $minPrice
     * @param float $maxPrice
     */
    public function addPriceRangeFilterByMinMax($minPrice, $maxPrice = null)
    {
        $this->_filterQueryBuilder->addPriceRangeFilterByMinMax($minPrice, $maxPrice);
        $this->activeFilterAttributeCodes[] = 'price';
    }

    /**
     * Store category filters in registry until request is done
     */
    private function _addCategoryFilters()
    {
        $categoryFilters = $this->_frameworkRegistry->registry('category_filters');
        if (is_array($categoryFilters)) {
            foreach ($categoryFilters as $category) {
                $this->addCategoryFilter($category);
            }
        }
        $this->_frameworkRegistry->unregister('category_filters');
    }

    /**
     * Store category filters in registry until request is done
     */
    private function _addAttributeFilters()
    {
        $attributeFilters = $this->_frameworkRegistry->registry('attribute_filters');
        if (is_array($attributeFilters)) {
            foreach ($attributeFilters as $attributeFilter) {
                $this->addAttributeFilter($attributeFilter['attribute'], $attributeFilter['value']);
            }
        }
        $this->_frameworkRegistry->unregister('attribute_filters');
    }

    /**
     * Store category filters in registry until request is done
     */
    private function _addPriceFilters()
    {
        $priceFilters = $this->_frameworkRegistry->registry('price_filters');
        if (is_array($priceFilters)) {
            foreach ($priceFilters as $priceFilter) {
                $this->addPriceRangeFilterByMinMax($priceFilter['min'], $priceFilter['max']);
            }
        }
        $this->_frameworkRegistry->unregister('price_filters');
    }

}