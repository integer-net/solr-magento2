<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Block\Autosuggest\Item;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Implementor\ProductRenderer as ProductRendererInterface;
use IntegerNet\Solr\Indexer\IndexDocument;
use Magento\Framework\App\State as AppState;
use Magento\Framework\View\LayoutInterface;

class ProductRenderer implements ProductRendererInterface
{
    /**
     * @var LayoutInterface
     */
    protected $layout;
    /**
     * @var AppState
     */
    private $appState;

    public function __construct(LayoutInterface $layout, AppState $appState)
    {
        $this->layout = $layout;
        $this->appState = $appState;
    }


    /**
     * Render product block and add HTML to index document:
     *  - result_html_autosuggest_nonindex - Block in auto suggest (always)
     *  - result_html_list_nonindex - Block in list view (only if $useHtmlInResults is true)
     *  - result_html_grid_nonindex - Block in grid view (only if $useHtmlInResults is true)
     *
     * @param ProductInterface $product
     * @param IndexDocument $productData
     * @param bool $useHtmlInResults
     */
    public function addResultHtmlToProductData(ProductInterface $product, IndexDocument $productData, $useHtmlInResults)
    {
        if (! $product instanceof Product) {
            // We need direct access to the Magento product
            throw new \InvalidArgumentException('Magento 2 product bridge expected, '. get_class($product) .' received.');
        }
        $this->addAutosuggestItemHtml($product, $productData);
        //TODO if $useHtmlInResult, render product list and grid HTML
    }

    /**
     * @param Product $product
     * @param IndexDocument $productData
     */
    private function addAutosuggestItemHtml(Product $product, IndexDocument $productData)
    {
        $this->appState->emulateAreaCode(
            'frontend',
            function() use ($product, $productData)
            {
                /** @var Item $itemBlock */
                $itemBlock = $this->layout->createBlock(Item::class);
                $itemBlock->setProduct($product->getMagentoProduct());
                $productData->setData('result_html_autosuggest_nonindex', $itemBlock->toHtml());
            }
        );
    }

}