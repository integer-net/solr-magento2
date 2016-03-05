<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class Integer\Net\Solr\Model\Source\FilterPosition
{
    const FILTER_POSITION_DEFAULT = 0;
    const FILTER_POSITION_LEFT = 1;
    const FILTER_POSITION_TOP = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::FILTER_POSITION_LEFT,
                'label' => __('Left column (Magento default)')
            ],
            [
                'value' => self::FILTER_POSITION_TOP,
                'label' => __('Content column (above products)')
            ],
        ];
    }
}