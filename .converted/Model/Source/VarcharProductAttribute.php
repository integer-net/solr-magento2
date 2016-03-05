<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class Integer\Net\Solr\Model\Source\VarcharProductAttribute
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [[
            'value' => '',
            'label' => '',
        ]];
        $attributes = $this->_bridgeAttributerepository->getVarcharProductAttributes();

        foreach($attributes as $attribute) { /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
            ];
        }
        return $options;
    }
}