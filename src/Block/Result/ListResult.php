<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Result\ListResult extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Retrieve loaded category collection
     *
     * @return Integer\Net\Solr\Model\Result\Collection|Integer\Net\Solr\Model\ResourceModel\Catalog\Product\Collection
     */
    protected function _getProductCollection()
    {
        if (!$this->_helperData->isActive()) {
            return parent::_getProductCollection();
        }

        if ($this->_configScopeConfigInterface->getValue('integernet_solr/results/use_html_from_solr', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->_resultCollection;
        }

        if (is_null($this->_productCollection) || !($this->_productCollection instanceof Integer\Net\Solr\Model\ResourceModel\Catalog\Product\Collection)) {

            /** @var $productCollection Integer\Net\Solr\Model\ResourceModel\Catalog\Product\Collection */
            $productCollection = $this->_productCollection
                ->setStoreId($this->_modelStoreManagerInterface->getStore()->getId())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addUrlRewrite()
                ->addAttributeToSelect($this->_modelConfig->getProductAttributes())
                ->addAttributeToSelect(['url_key']);

            $this->_eventManagerInterface->dispatch('catalog_block_product_listproduct_collection', [
                'collection' => $productCollection
            ]);

            $this->_productCollection = $productCollection;
        }

        return $this->_productCollection;
    }

    /**
     * @return Integer\Net\Solr\Block\Result\ListResult
     */
    protected function _beforeToHtml()
    {
        if (!$this->_helperData->isActive()) {
            return parent::_beforeToHtml();
        }

        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);

        if (! $this->_configScopeConfigInterface->isSetFlag('integernet_solr/results/use_html_from_solr', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            // disable pagination for collection loading because we select ids based on solr result
            // FS: the previous solution had a bug where the pagination toolbar always showed "Item(s) 1-X of Y"
            $_pageSize = $collection->getPageSize();
            $collection->setPageSize(false);
            $collection->load();
            $collection->setPageSize($_pageSize);
        } else {
            $collection->load();
        }


        return $this;
    }
}