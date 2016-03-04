<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Model\ResourceModel\Catalog\Layer\Filter\Attribute extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
{
    /**
     * Apply attribute filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filter
     * @param int $value
     * @return \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (!$this->_helperData->isActive()) {
            return parent::applyFilterToCollection($filter, $value);
        }

        if ($this->_appRequestInterface->getModuleName() != 'catalogsearch' && !$this->_helperData->isCategoryPage()) {
            return parent::applyFilterToCollection($filter, $value);
        }

        $bridgeAttribute = new Integer\Net\Solr\Model\Bridge\Attribute($filter->getAttributeModel());
        
        $attributeFilters = $this->_frameworkRegistry->registry('attribute_filters');
        if (!is_array($attributeFilters)) {
            $attributeFilters = [];
        }
        $attributeFilters[] = [
            'attribute' => $bridgeAttribute,
            'value' => $value,
        ];
        $this->_frameworkRegistry->unregister('attribute_filters');
        $this->_frameworkRegistry->register('attribute_filters', $attributeFilters);

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        if (!$this->_helperData->isActive()) {
            return parent::getCount($filter);
        }

        if ($this->_appRequestInterface->getModuleName() != 'catalogsearch' && !$this->_helperData->isCategoryPage()) {
            return parent::getCount($filter);
        }

        /** @var $solrResult StdClass */
        $solrResult = $this->_modelResult->getSolrResult();

        $attribute = $filter->getAttributeModel();

        $count = [];
        if (isset($solrResult->facet_counts->facet_fields->{$attribute->getAttributeCode() . '_facet'})) {
            foreach ((array)$solrResult->facet_counts->facet_fields->{$attribute->getAttributeCode() . '_facet'} as $key => $value) {
                $count[intval($key)] = $value;
            }
            return $count;
        }

        return [];
    }
}