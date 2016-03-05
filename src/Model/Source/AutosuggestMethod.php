<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Source;

class AutosuggestMethod
{
    const AUTOSUGGEST_METHOD_MAGENTO_CONTROLLER = 0;
    const AUTOSUGGEST_METHOD_PHP = 1;
    const AUTOSUGGEST_METHOD_MAGENTO_DIRECT = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTOSUGGEST_METHOD_MAGENTO_CONTROLLER,
                'label' => __('Magento Controller')
            ],
            [
                'value' => self::AUTOSUGGEST_METHOD_MAGENTO_DIRECT,
                'label' => __('Magento with separate PHP file')
            ],
            [
                'value' => self::AUTOSUGGEST_METHOD_PHP,
                'label' => __('PHP without Magento instantiation')
            ],
        ];
    }
}