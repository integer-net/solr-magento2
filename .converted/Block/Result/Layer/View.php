<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Result\Layer\View extends \Magento\Framework\View\Element\Template
{
    protected $_filters = null;
    
    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        switch ($this->getNameInLayout()) {
            case 'catalogsearch.solr.leftnav':
                return $this->_configScopeConfigInterface->getValue('integernet_solr/results/filter_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_LEFT;
            case 'catalogsearch.solr.topnav':
                return $this->_configScopeConfigInterface->getValue('integernet_solr/results/filter_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_TOP;
            case 'catalog.solr.leftnav':
                switch ($this->_getCurrentCategory()->getData('filter_position')) {
                    case Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_DEFAULT:
                        return $this->_configScopeConfigInterface->getValue('integernet_solr/category/filter_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_LEFT;
                    case Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_LEFT:
                        return true;
                    case Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_TOP:
                        return false;
                }
            case 'catalog.solr.topnav':
                switch ($this->_getCurrentCategory()->getData('filter_position')) {
                    case Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_DEFAULT:
                        return $this->_configScopeConfigInterface->getValue('integernet_solr/category/filter_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_TOP;
                    case Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_LEFT:
                        return false;
                    case Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_TOP:
                        return true;
                }
        }
        return true;
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowOptions()
    {
        return (bool)sizeof($this->getFilters());
    }
    
    public function getStateHtml()
    {
        return $this->getChildHtml('state');
    }
    
    public function getFilters()
    {
        if (is_null($this->_filters)) {
            $this->_filters = [];
            $facetName = 'category';
            if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$facetName})) {

                $categoryFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$facetName};
                $categoryFilter = $this->_getCategoryFilter($categoryFacets);
                if ($categoryFilter->getHtml()) {
                    $this->_filters[] = $categoryFilter;
                }
            }
            foreach ($this->_bridgeAttributerepository->getFilterableAttributes($this->_modelStoreManagerInterface->getStore()->getId(), false) as $attribute) {
                /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */

                /** @var \Magento\Catalog\Model\Category $currentCategory */
                $currentCategory = $this->_getCurrentCategory();
                if ($currentCategory) {
                    $removedFilterAttributeCodes = $currentCategory->getData('solr_remove_filters');
                    
                    if (is_array($removedFilterAttributeCodes) && in_array($attribute->getAttributeCode(), $removedFilterAttributeCodes)) {
                        continue;
                    }
                }

                $attributeCodeFacetName = $attribute->getAttributeCode() . '_facet';
                if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName})) {

                    $attributeFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName};
                    $this->_filters[] = $this->_getFilter($attribute, $attributeFacets);
                }
                $attributeCodeFacetRangeName = $this->_helperData->getFieldName($attribute);
                if (isset($this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName})) {

                    $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName};
                    $this->_filters[] = $this->_getIntervalFilter($attribute, $attributeFacetData);
                } elseif (isset($this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName})) {

                    $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName};
                    $this->_filters[] = $this->_getRangeFilter($attribute, $attributeFacetData);
                }
            }
        }
        return $this->_filters;
    }

    /**
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @param int[] $attributeFacets
     * @return \Magento\Framework\DataObject
     */
    protected function _getFilter($attribute, $attributeFacets)
    {
        $filter = new \Magento\Framework\DataObject();
        $filter->setName($attribute->getStoreLabel());
        $filter->setItemsCount(sizeof($attributeFacets));
        $filter->setHtml(
            $this->getChild('filter')
                ->setData('is_category', false)
                ->setData('is_range', false)
                ->setData('attribute', $attribute)
                ->toHtml()
        );
        return $filter;
    }

    /**
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @param array $attributeFacetData
     * @return \Magento\Framework\DataObject
     */
    protected function _getIntervalFilter($attribute, $attributeFacetData)
    {
        $filter = new \Magento\Framework\DataObject();
        $filter->setName($attribute->getStoreLabel());
        $filter->setItemsCount(sizeof($attributeFacetData));
        $filter->setHtml(
            $this->getChild('filter')
                ->setData('is_category', false)
                ->setData('is_range', true)
                ->setData('attribute', $attribute)
                ->toHtml()
        );
        return $filter;
    }

    /**
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @param array $attributeFacetData
     * @return \Magento\Framework\DataObject
     */
    protected function _getRangeFilter($attribute, $attributeFacetData)
    {
        $filter = new \Magento\Framework\DataObject();
        $filter->setName($attribute->getStoreLabel());
        $filter->setItemsCount(sizeof($attributeFacetData['counts']));
        $filter->setHtml(
            $this->getChild('filter')
                ->setData('is_category', false)
                ->setData('is_range', true)
                ->setData('attribute', $attribute)
                ->toHtml()
        );
        return $filter;
    }

    /**
     * @param int[] $categoryFacets
     * @return \Magento\Framework\DataObject
     */
    protected function _getCategoryFilter($categoryFacets)
    {
        $filter = new \Magento\Framework\DataObject();
        $filter->setName(__('Category'));
        $filter->setItemsCount(sizeof($categoryFacets));
        
        /** @var Integer\Net\Solr\Block\Result\Layer\Filter $filterBlock */
        $filterBlock = $this->getChild('filter')
            ->setData('is_category', true);
        if (sizeof($filterBlock->getItems())) {
            $filter->setHtml(
                $filterBlock->toHtml()
            );
        } else {
            $filter->setHtml('');
        }
        return $filter;
    }

    /**
     * @return \Apache\Solr\Response
     */
    protected function _getSolrResult()
    {
        return $this->_modelResult->getSolrResult();
    }

    /**
     * @return Integer\Net\Solr\Block\Result\Layer
     */
    public function getLayer()
    {
        return $this->_viewLayoutFactory->create()->createBlock('IntegerNet\Solr\Block\Result\Layer');
    }
    
    /**
     * @return \Magento\Catalog\Model\Category|false
     */
    protected function _getCurrentCategory()
    {
        if (is_null($this->_currentCategory)) {
            if ($filteredCategoryId = $this->_appRequestInterface->getParam('cat')) {
                /** @var \Magento\Catalog\Model\Category $currentCategory */
                $this->_currentCategory = $this->_modelCategoryFactory->create()->load($filteredCategoryId);
            } else {
                /** @var \Magento\Catalog\Model\Category $currentCategory */
                $this->_currentCategory = $this->_frameworkRegistry->registry('current_category');
                if (is_null($this->_currentCategory)) {
                    $this->_currentCategory = false;
                }
            }
        }

        return $this->_currentCategory;
    }
}