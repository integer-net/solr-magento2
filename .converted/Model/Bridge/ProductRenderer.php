<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Implementor\ProductRenderer;
use IntegerNet\Solr\Indexer\IndexDocument;

class Integer\Net\Solr\Model\Bridge\ProductRenderer implements ProductRenderer
{
    /** @var Integer\Net\Solr\Block\Indexer\Item[] */
    private $_itemBlocks = [];

    /**
     * @param Product $product
     * @param IndexDocument $productData
     * @param bool $useHtmlInResults
     */
    public function addResultHtmlToProductData(Product $product, IndexDocument $productData, $useHtmlInResults)
    {
        if (! $product instanceof Integer\Net\Solr\Model\Bridge\Product) {
            // We need direct access to the Magento product
            throw new InvalidArgumentException('Magento 1 product bridge expected, '. get_class($product) .' received.');
        }
        $product = $product->getMagentoProduct();
        $product->getUrlModel()->getUrlInstance()->setUseSession(false);

        /** @var Integer\Net\Solr\Block\Indexer\Item $block */
        $block = $this->_getResultItemBlock();

        $block->setProduct($product);

        $block->setTemplate('IntegerNet_Solr::integernet/solr/result/autosuggest/item.phtml');
        $productData->setData('result_html_autosuggest_nonindex', $block->toHtml());

        if ($useHtmlInResults) {
            $block->setTemplate('IntegerNet_Solr::integernet/solr/result/list/item.phtml');
            $productData->setData('result_html_list_nonindex', $block->toHtml());

            $block->setTemplate('IntegerNet_Solr::integernet/solr/result/grid/item.phtml');
            $productData->setData('result_html_grid_nonindex', $block->toHtml());
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
            $this->_addPriceBlockTypes($block);
            // support for rwd theme
            $block->setChild('name.after', $this->_viewLayout->createBlock('Magento\Framework\Block\Text\ListText'));
            $block->setChild('after', $this->_viewLayout->createBlock('Magento\Framework\Block\Text\ListText'));
            $this->_itemBlocks[$this->_modelStoreManagerInterface->getStore()->getId()] = $block;
        }

        return $this->_itemBlocks[$this->_modelStoreManagerInterface->getStore()->getId()];
    }

    /**
     * Add custom price blocks for correct price display
     *
     * @param Integer\Net\Solr\Block\Indexer\Item $block
     */
    protected function _addPriceBlockTypes($block)
    {
        $block->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');

        $priceBlockType = 'germansetup/catalog_product_price';
        if (@class_exists(Mage::getConfig()->getBlockClassName($priceBlockType)) && $this->_viewLayout->createBlock($priceBlockType)) {

            $block->addPriceBlockType('simple', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('virtual', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('grouped', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('downloadable', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('configurable', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('bundle', 'germansetup/bundle_catalog_product_price', 'bundle/catalog/product/price.phtml');
        }

        $priceBlockType = 'magesetup/catalog_product_price';
        if (@class_exists(Mage::getConfig()->getBlockClassName($priceBlockType)) && $this->_viewLayout->createBlock($priceBlockType)) {

            $block->addPriceBlockType('simple', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('virtual', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('grouped', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('downloadable', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('configurable', $priceBlockType, 'catalog/product/price.phtml');
            $block->addPriceBlockType('bundle', 'magesetup/bundle_catalog_product_price', 'bundle/catalog/product/price.phtml');
        }
    }
}