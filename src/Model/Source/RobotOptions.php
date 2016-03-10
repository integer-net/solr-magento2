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

class RobotOptions implements OptionSourceInterface
{
    const ROBOT_OPTION_SEARCH_RESULTS_ALL = 'search_results_all';
    const ROBOT_OPTION_SEARCH_RESULTS_FILTERED = 'search_results_filtered';
    const ROBOT_OPTION_CATEGORIES_FILTERED = 'categories_filtered';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => ''
            ],
            [
                'value' => self::ROBOT_OPTION_SEARCH_RESULTS_ALL,
                'label' => __('Search Result Page (always)'),
            ],
            [
                'value' => self::ROBOT_OPTION_SEARCH_RESULTS_FILTERED,
                'label' => __('Search Result Page with active Filters'),
            ],
            [
                'value' => self::ROBOT_OPTION_CATEGORIES_FILTERED,
                'label' => __('Categoy Page with active Filters'),
            ],
        ];
    }
}