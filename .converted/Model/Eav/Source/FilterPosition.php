<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class Integer\Net\Solr\Model\Eav\Source\FilterPosition extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [[
            'value' => Integer\Net\Solr\Model\Source\FilterPosition::FILTER_POSITION_DEFAULT,
            'label' => __('Default Value from Configuration'),
        ]];
        
        foreach($this->_sourceFilterposition->toOptionArray() as $option) {
            $options[] = $option;
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