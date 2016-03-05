<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\Config\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class Attribute extends Select {

    public function _toHtml()
    {
        return parent::_toHtml(); //TODO implement
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