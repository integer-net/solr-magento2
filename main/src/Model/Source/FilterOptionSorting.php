<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FilterOptionSorting implements OptionSourceInterface
{
    const FILTER_SORTING_RESULTS_COUNT = 'results_count';
    const FILTER_SORTING_ALPHABET = 'alphabet';
    const FILTER_SORTING_POSITION = 'position';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::FILTER_SORTING_POSITION,
                'label' => __('Option position (Magento default)')
            ],
            [
                'value' => self::FILTER_SORTING_ALPHABET,
                'label' => __('Alphabet')
            ],
            [
                'value' => self::FILTER_SORTING_RESULTS_COUNT,
                'label' => __('Result Count')
            ],
        ];
    }
}