<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\CatalogSearch\Layer\Filter\Attribute extends Integer\Net\Solr\Model\Catalog\Layer\Filter\Attribute
{
    /**
     * Check whether specified attribute can be used in LN
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute  $attribute
     * @return bool
     */
    protected function _getIsFilterableAttribute($attribute)
    {
        return $attribute->getIsFilterableInSearch();
    }
}