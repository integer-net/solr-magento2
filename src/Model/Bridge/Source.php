<?php
use IntegerNet\Solr\Implementor\Source;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class Integer\Net\Solr\Model\Bridge\Source implements Source
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface
     */
    private $_source;

    /**
     * @param $_source \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface
     */
    public function __construct(\Magento\Eav\Model\Entity\Attribute\Source\SourceInterface $_source)
    {
        $this->_source = $_source;
    }

    /**
     * @param int $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        return $this->_source->getOptionText($optionId);
    }

    /**
     * Returns [optionId => optionText] map
     *
     * @return string[]
     */
    public function getOptionMap()
    {
        $result = [];
        foreach ($this->_source->getAllOptions() as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }


    /**
     * Delegate all other calls (by Magento) to source model
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_arrayfunc([$this->_source, $name], $arguments);
    }
}