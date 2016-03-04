<?php
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\Source;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
final class Integer\Net\Solr\Model\Bridge\Attribute implements Attribute
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;
    /**
     * @var Integer\Net\Solr\Model\Bridge\Source
     */
    protected $_source;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @throws \Magento\Framework\Exception
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $this->_attribute = $attribute;
        $this->_source = new Integer\Net\Solr\Model\Bridge\Source($this->_attribute->getSource());
    }
    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_attribute->getAttributeCode();
    }

    /**
     * @return string
     */
    public function getStoreLabel()
    {
        return $this->_attribute->getStoreLabel();
    }

    /**
     * @return float
     */
    public function getSolrBoost()
    {
        return $this->_attribute->getData('solr_boost');
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getFacetType()
    {
        return $this->_attribute->getFrontendInput();
    }

    /**
     * @return bool
     */
    public function getIsSearchable()
    {
        return $this->_attribute->getIsSearchable();
    }

    /**
     * @return string
     */
    public function getBackendType()
    {
        return $this->_attribute->getBackendType();
    }

    /**
     * @return bool
     */
    public function getUsedForSortBy()
    {
        return $this->_attribute->getUsedForSortBy();
    }

    /**
     * Delegate all other calls (by Magento) to attribute
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_arrayfunc([$this->_attribute, $name], $arguments);
    }


}