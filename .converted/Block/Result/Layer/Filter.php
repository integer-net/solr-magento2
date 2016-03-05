<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Result\Layer\Filter extends \Magento\Framework\View\Element\Template
{
    /**
     * Whether to display product count for layer navigation items
     * @var bool
     */
    protected $_displayProductCount = null;

    protected $_categoryFilterItems = null;

    protected $_currentCategory = null;

    /**
     * @return \Magento\Catalog\Model\Entity\Attribute
     */
    public function getAttribute()
    {
        return $this->getData('attribute');
    }

    /**
     * @return bool
     */
    public function isCategory()
    {
        return (boolean)$this->getData('is_category');
    }

    /**
     * @return bool
     */
    public function isRange()
    {
        return (boolean)$this->getData('is_range');
    }

    /**
     * @return \Magento\Framework\DataObject[]
     * @throws \Magento\Framework\Exception
     */
    public function getItems()
    {
        if ($this->isCategory()) {
            return $this->_getCategoryFilterItems();
        }

        if ($this->isRange()) {
            return $this->_getRangeFilterItems();
        }

        return $this->_getAttributeFilterItems();
    }

    /**
     * Get filter item url
     *
     * @param int $optionId
     * @return string
     */
    protected function _getUrl($optionId)
    {
        if ($this->isCategory()) {
            $identifier = 'cat';
        } else {
            $identifier = $this->getAttribute()->getAttributeCode();
        }
        $query = $this->_getQuery($identifier, $optionId);
        return Mage::getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    /**
     * Get filter item url
     *
     * @param int $rangeStart
     * @param int $rangeEnd
     * @return string
     */
    protected function _getRangeUrl($rangeStart, $rangeEnd)
    {
        $identifier = 'price';
        $query = $this->_getQuery($identifier, floatval($rangeStart) . '-' . floatval($rangeEnd));
        return Mage::getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    /**
     * @return \Apache\Solr\Response
     */
    protected function _getSolrResult()
    {
        return $this->_modelResult->getSolrResult();
    }

    /**
     * Getter for $_displayProductCount
     * @return bool
     */
    public function shouldDisplayProductCount()
    {
        if ($this->_displayProductCount === null) {
            $this->_displayProductCount = $this->_helperData->shouldDisplayProductCountOnLayer();
        }
        return $this->_displayProductCount;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception
     */
    protected function _getCurrentChildrenCategories()
    {
        $currentCategory = $this->_getCurrentCategory();

        $childrenCategories = $this->_categoryCollection
            ->setStore($this->_modelStoreManagerInterface->getStore())
            ->addAttributeToSelect('name', 'url_key')
            ->addAttributeToFilter('level', $currentCategory->getLevel() + 1)
            ->addAttributeToFilter('path', ['like' => $currentCategory->getPath() . '_%'])
            ->setOrder('position', 'asc');

        return $childrenCategories;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    protected function _getCategoryFilterItems()
    {
        if (is_null($this->_categoryFilterItems)) {

            $facetName = 'category';
            if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$facetName})) {

                $categoryFacets = $this->_getSolrResult()->facet_counts->facet_fields->{$facetName};

                if ($this->_solrHelperData->isCategoryPage()) {

                    $childrenCategories = $this->_getCurrentChildrenCategories();

                    foreach ($childrenCategories as $childCategory) {
                        $childCategoryId = $childCategory->getId();
                        if (isset($categoryFacets->{$childCategoryId})) {
                            $item = new \Magento\Framework\DataObject();
                            $item->setCount($categoryFacets->{$childCategoryId});
                            $item->setLabel($this->_getCheckboxHtml('cat', $childCategoryId) . ' ' . $childCategory->getName());
                            $item->setUrl($this->_getUrl($childCategoryId));
                            $item->setIsChecked($this->_isSelected('cat', $childCategoryId));
                            $item->setType('category');

                            $this->_eventManagerInterface->dispatch('integernet_solr_filter_item_create', [
                                'item' => $item,
                                'solr_result' => $this->_getSolrResult(),
                                'type' => 'category',
                                'entity_id' => $childCategoryId,
                                'entity' => $childCategory,
                            ]);

                            $this->_categoryFilterItems[] = $item;
                        }
                    }

                } else {

                    foreach ((array)$categoryFacets as $optionId => $optionCount) {
                        $item = new \Magento\Framework\DataObject();
                        $item->setCount($optionCount);
                        $item->setLabel($this->_getCheckboxHtml('cat', $optionId) . ' ' . Mage::getResourceSingleton('catalog/category')->getAttributeRawValue($optionId, 'name', $this->_modelStoreManagerInterface->getStore()));
                        $item->setUrl($this->_getUrl($optionId));
                        $item->setIsChecked($this->_isSelected('cat', $optionId));
                        $item->setType('category');
                        
                        $this->_eventManagerInterface->dispatch('integernet_solr_filter_item_create', [
                            'item' => $item,
                            'solr_result' => $this->_getSolrResult(),
                            'type' => 'category',
                            'entity_id' => $optionId,
                        ]);

                        $this->_categoryFilterItems[] = $item;
                    }
                }
            }
        }

        return $this->_categoryFilterItems;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    protected function _getRangeFilterItems()
    {
        $items = [];

        $store = $this->_modelStoreManagerInterface->getStore();
        $attributeCodeFacetRangeName = $this->_solrHelperData->getFieldName($this->getAttribute());
        if (isset($this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName})) {

            $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName};

            $i = 0;
            foreach ($attributeFacetData as $range => $rangeCount) {
                $i++;
                if (!$rangeCount) {
                    continue;
                }

                $item = new \Magento\Framework\DataObject();
                $item->setCount($rangeCount);

                $commaPos = strpos($range, ',');
                $rangeStart = floatval(substr($range, 1, $commaPos - 1));
                $rangeEnd = floatval(substr($range, $commaPos + 1, -1));
                if ($rangeEnd == 0) {
                    $label = __('from %1', $store->formatPrice($rangeStart));
                } else {
                    $label = __('%1 - %2', $store->formatPrice($rangeStart), $store->formatPrice($rangeEnd));
                }

                $item->setLabel($this->_getCheckboxHtml('price', floatval($rangeStart) . '-' . floatval($rangeEnd)) . ' ' . $label);
                $item->setUrl($this->_getRangeUrl($rangeStart, $rangeEnd));
                $item->setIsChecked($this->_isSelected('price', floatval($rangeStart) . '-' . floatval($rangeEnd)));
                $item->setType('range');

                $this->_eventManagerInterface->dispatch('integernet_solr_filter_item_create', [
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'range',
                    'entity_id' => floatval($rangeStart) . '-' . floatval($rangeEnd),
                ]);

                $items[] = $item;
            }
        } elseif (isset($this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName})) {

            $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName};

            foreach ($attributeFacetData['counts'] as $rangeStart => $rangeCount) {
                $item = new \Magento\Framework\DataObject();
                $item->setCount($rangeCount);
                $rangeEnd = $rangeStart + $attributeFacetData['gap'];
                $item->setLabel($this->_getCheckboxHtml('price', floatval($rangeStart) . '-' . floatval($rangeEnd)) . ' ' . __(
                    '%s - %s',
                    $store->formatPrice($rangeStart),
                    $store->formatPrice($rangeEnd)
                ));
                $item->setUrl($this->_getRangeUrl($rangeStart, $rangeEnd));
                $item->setIsChecked($this->_isSelected('price', floatval($rangeStart) . '-' . floatval($rangeEnd)));
                $item->setType('range');
                
                $this->_eventManagerInterface->dispatch('integernet_solr_filter_item_create', [
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'range',
                    'entity_id' => floatval($rangeStart) . '-' . floatval($rangeEnd),
                ]);
                
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @return \Magento\Framework\DataObject[]
     * @throws \Magento\Framework\Exception
     */
    protected function _getAttributeFilterItems()
    {
        $items = [];
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeCodeFacetName = $attributeCode . '_facet';
        if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName})) {

            $attributeFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName};

            foreach ($attributeFacets as $optionId => $optionCount) {
                if (!$optionCount) {
                    continue;
                }
                /** @var \Magento\Catalog\Model\Category $currentCategory */
                $currentCategory = $this->_getCurrentCategory();
                if ($currentCategory) {
                    $removedFilterAttributeCodes = $currentCategory->getData('solr_remove_filters');
                    if (is_array($removedFilterAttributeCodes) && in_array($attributeCode, $removedFilterAttributeCodes)) {
                        continue;
                    }
                }
                $item = new \Magento\Framework\DataObject();
                $item->setCount($optionCount);
                $item->setLabel($this->_getCheckboxHtml($attributeCode, $optionId) . ' ' . $this->getAttribute()->getSource()->getOptionText($optionId));
                $item->setUrl($this->_getUrl($optionId));
                $item->setIsChecked($this->_isSelected($attributeCode, $optionId));
                $item->setType('attribute');
                
                $this->_eventManagerInterface->dispatch('integernet_solr_filter_item_create', [
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'attribute',
                    'entity_id' => $optionId,
                ]);
                
                $items[] = $item;
            }
        }

        return $items;
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

    protected function _getCheckboxHtml($attributeCode, $optionId)
    {
        /** @var $checkboxBlock Integer\Net\Solr\Block\Result\Layer\Checkbox */
        $checkboxBlock = $this->_viewLayoutFactory->create()->createBlock('IntegerNet\Solr\Block\Result\Layer\Checkbox');
        $checkboxBlock
            ->setIsChecked($this->_isSelected($attributeCode, $optionId))
            ->setOptionId($optionId)
            ->setAttributeCode($attributeCode)
            ->setIsTopNav(strpos($this->getNameInLayout(), 'topnav') !== false);
        return $checkboxBlock->toHtml();
    }

    /**
     * @param string $identifier
     * @param int $optionId
     * @return bool
     */
    protected function _isSelected($identifier, $optionId)
    {
        $selectedOptionIds = explode(',', $this->_getCurrentParamValue($identifier));
        if (in_array($optionId, $selectedOptionIds)) {
            return true;
        }
        return false;
    }

    /**
     * Get updated query params, depending on previously selected filters
     *
     * @param string $identifier
     * @param int $optionId
     * @return array
     */
    protected function _getQuery($identifier, $optionId)
    {
        $currentParamValue = $this->_getCurrentParamValue($identifier);
        if (strlen($currentParamValue)) {
            $selectedOptionIds = explode(',', $currentParamValue);
        } else {
            $selectedOptionIds = [];
        }
        if (in_array($optionId, $selectedOptionIds)) {
            $newParamValues = array_diff($selectedOptionIds, [$optionId]);
        } else {
            $newParamValues = $selectedOptionIds;
            $newParamValues[] = $optionId;
        }
        if (sizeof($newParamValues)) {
            $newParamValues = implode(',', $newParamValues);
        } else {
            $newParamValues = null;
        }
        return [
            $identifier => $newParamValues,
            $this->_htmlPager->getPageVarName() => null // exclude current page from urls
        ];
    }

    /**
     * @param $identifier
     * @return mixed
     */
    protected function _getCurrentParamValue($identifier)
    {
        return $this->_appRequestInterface->getParam($identifier);
    }
}