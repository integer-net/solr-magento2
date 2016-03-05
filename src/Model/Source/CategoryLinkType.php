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

class CategoryLinkType
{
    const CATEGORY_LINK_TYPE_FILTER = 'filter';
    const CATEGORY_LINK_TYPE_DIRECT = 'direct';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CATEGORY_LINK_TYPE_FILTER,
                'label' => __('Search result page with set category filter')
            ],
            [
                'value' => self::CATEGORY_LINK_TYPE_DIRECT,
                'label' => __('Category page')
            ],
        ];
    }
}