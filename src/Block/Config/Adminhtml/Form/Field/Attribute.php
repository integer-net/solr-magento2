<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Config\Adminhtml\Form\Field\Attribute extends \Magento\Framework\Block\Html\Select {

    public function _toHtml()
    {
        $attributes = $this->_bridgeAttributerepository
            ->getFilterableInSearchAttributes($this->_modelStoreManagerInterface->getStore()->getId());

        foreach($attributes as $attribute) {
            $this->addOption($attribute->getAttributeCode(), $attribute->getFrontendLabel() . ' [' . $attribute->getAttributeCode() . ']');
        }

        return parent::_toHtml();
    }

    /**
     * @param string $value
     * @return Integer\Net\Solr\Block\Config\Adminhtml\Form\Field\Attribute
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}