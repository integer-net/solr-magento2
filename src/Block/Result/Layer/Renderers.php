<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Result\Layer\Renderers extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Dummy method to force old (pre-rwd) behavior of filters
     *
     * @return array
     */
    public function getSortedChildren()
    {
        return [];
    }
}