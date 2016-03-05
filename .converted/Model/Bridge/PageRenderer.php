<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\SolrCms\Implementor\PageRenderer;
use IntegerNet\SolrCms\Implementor\Page;
use IntegerNet\Solr\Indexer\IndexDocument;

class Integer\Net\Solr\Model\Bridge\PageRenderer implements PageRenderer
{
    /** @var Integer\Net\Solr\Block\Indexer\Item[] */
    private $_itemBlocks = [];

    /**
     * @param Page $page
     * @param IndexDocument $pageData
     * @param bool $useHtmlInResults
     */
    public function addResultHtmlToPageData(Page $page, IndexDocument $pageData, $useHtmlInResults)
    {
        if (! $page instanceof Integer\Net\Solr\Model\Bridge\Page) {
            // We need direct access to the Magento page
            throw new InvalidArgumentException('Magento 1 page bridge expected, '. get_class($page) .' received.');
        }
        $page = $page->getMagentoPage();

        /** @var Integer\Net\Solr\Block\Indexer\Item $block */
        $block = $this->_getResultItemBlock();

        $block->setPage($page);

        $block->setTemplate('IntegerNet_Solr::integernet/solr/result/autosuggest/item.phtml');
        $pageData->setData('result_html_autosuggest_nonindex', $block->toHtml());

        if ($useHtmlInResults) {
            $block->setTemplate('IntegerNet_Solr::integernet/solr/result/list/item.phtml');
            $pageData->setData('result_html_list_nonindex', $block->toHtml());

            $block->setTemplate('IntegerNet_Solr::integernet/solr/result/grid/item.phtml');
            $pageData->setData('result_html_grid_nonindex', $block->toHtml());
        }
    }

    /**
     * @return Integer\Net\Solr\Block\Indexer\Item
     */
    protected function _getResultItemBlock()
    {
        if (!isset($this->_itemBlocks[$this->_modelStoreManagerInterface->getStore()->getId()])) {
            /** @var Integer\Net\Solr\Block\Indexer\Item _itemBlock */
            $block = $this->_viewLayout->createBlock('integernet_solr/indexer_item', 'solr_result_item');
            $this->_itemBlocks[$this->_modelStoreManagerInterface->getStore()->getId()] = $block;
        }

        return $this->_itemBlocks[$this->_modelStoreManagerInterface->getStore()->getId()];
    }


}