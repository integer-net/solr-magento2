<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class Integer\Net\Solr\Model\Eav\Source\FilterableAttribute extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [[
            'value' => '',
            'label' => '',
        ]];
        $attributes = $this->_bridgeAttributerepository
            ->getFilterableInCatalogAttributes($this->_modelStoreManagerInterface->getStore()->getId());

        foreach($attributes as $attribute) { /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
            ];
        }
        return $options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}