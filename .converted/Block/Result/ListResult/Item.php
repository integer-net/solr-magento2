<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Result\ListResult\Item extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Override this method in descendants to produce html
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var \Apache\Solr\Document $product */
        $product = $this->getProduct();

        switch ($this->getListType()) {
            case 'autosuggest':
                $field = $product->getField('result_html_autosuggest_nonindex');
                break;
            
            case 'list':
                $field = $product->getField('result_html_list_nonindex');
                break;
            
            default:
                $field = $product->getField('result_html_grid_nonindex');
        }
        return $field['value'];
    }
}