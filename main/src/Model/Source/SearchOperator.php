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

use Magento\Framework\Data\OptionSourceInterface;

class SearchOperator implements OptionSourceInterface
{
    const SEARCH_OPERATOR_AND = 'AND';
    const SEARCH_OPERATOR_OR = 'OR';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SEARCH_OPERATOR_AND,
                'label' => __('AND')
            ],
            [
                'value' => self::SEARCH_OPERATOR_OR,
                'label' => __('OR')
            ],
        ];
    }
}