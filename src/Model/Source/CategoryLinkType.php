<?php
use IntegerNet\Solr\Config\AutosuggestConfig;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class Integer\Net\Solr\Model\Source\CategoryLinkType
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AutosuggestConfig::CATEGORY_LINK_TYPE_FILTER,
                'label' => __('Search result page with set category filter')
            ],
            [
                'value' => AutosuggestConfig::CATEGORY_LINK_TYPE_DIRECT,
                'label' => __('Category page')
            ],
        ];
    }
}