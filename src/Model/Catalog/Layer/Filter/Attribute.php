<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Model\Catalog\Layer\Filter\Attribute extends \Magento\Catalog\Model\Layer\Filter\Attribute
{
    /**
     * Apply attribute option filter to product collection
     *
     * @param   \Zend\Controller\Request\AbstractRequest $request
     * @param   \Magento\Framework\DataObject $filterBlock
     * @return  \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    public function apply(\Zend\Controller\Request\AbstractRequest $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        foreach (explode(',', $filter) as $optionValue) {
            $text = $this->_getOptionText($optionValue);
            if ($filter && strlen($text)) {
                $this->_getResource()->applyFilterToCollection($this, $optionValue);
                $this->getLayer()->getState()->addFilter($this->_createItem($text, $optionValue));
                $this->_items = [];
            }
        }
        return $this;
    }
}