<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\ProductIterator;
use IntegerNet\Solr\Implementor\Product;

/**
 * Product iterator implementation with lazy loading of multiple collections (chunking).
 * Collections are prepared to be used by the indexer.
 */
class Integer\Net\Solr\Model\Bridge\LazyProductIterator implements ProductIterator, OuterIterator
{
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var null|int[]
     */
    protected $_productIdFilter;
    /**
     * @var int
     */
    protected $_pageSize;
    /**
     * @var int
     */
    protected $_currentPage;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_collection;
    /**
     * @var ArrayIterator
     */
    protected $_collectionIterator;

    /**
     * @link http://php.net/manual/en/outeriterator.getinneriterator.php
     * @return Iterator The inner iterator for the current entry.
     */
    public function getInnerIterator()
    {
        return $this->_collectionIterator;
    }


    /**
     * @param int $_storeId store id for the collections
     * @param int[]|null $_productIdFilter array of product ids to be loaded, or null for all product ids
     * @param int $_pageSize Number of products per loaded collection (chunk)
     */
    public function __construct($_storeId, $_productIdFilter, $_pageSize, \Magento\Store\Model\StoreManagerInterface $modelStoreManagerInterface, 
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection, 
        \Magento\Catalog\Model\Config $modelConfig, 
        \Integer\Net\Solr\Model\Bridge\Attributerepository $bridgeAttributerepository, 
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface, 
        \Magento\Tax\Model\ObserverFactory $modelObserverFactory)
    {
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->_productCollection = $productCollection;
        $this->_modelConfig = $modelConfig;
        $this->_bridgeAttributerepository = $bridgeAttributerepository;
        $this->_eventManagerInterface = $eventManagerInterface;
        $this->_modelObserverFactory = $modelObserverFactory;

        $this->_storeId = $_storeId;
        $this->_productIdFilter = $_productIdFilter;
        $this->_pageSize = $_pageSize;
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getInnerIterator()->next();
    }

    /**
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->getInnerIterator()->key();
    }

    /**
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->getInnerIterator()->valid()) {
            return true;
        } elseif ($this->_currentPage < $this->_collection->getLastPageNumber()) {
            $this->_currentPage++;
            $this->_collection = self::getProductCollection($this->_storeId, $this->_productIdFilter, $this->_pageSize, $this->_currentPage);
            $this->_collectionIterator = $this->_collection->getIterator();
            $this->getInnerIterator()->rewind();
            return $this->getInnerIterator()->valid();
        }
        return false;
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_currentPage = 1;
        $this->_collection = self::getProductCollection($this->_storeId, $this->_productIdFilter, $this->_pageSize, $this->_currentPage);
        $this->_collectionIterator = $this->_collection->getIterator();
        $this->_collectionIterator->rewind();
    }

    /**
     * @return Product
     */
    public function current()
    {
        $product = $this->getInnerIterator()->current();
        $product->setStoreId($this->_storeId);
        return new Integer\Net\Solr\Model\Bridge\Product($product);
    }

    /**
     * @param int $storeId
     * @param int[]|null $productIds
     * @param int $pageSize
     * @param int $pageNumber
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private static function getProductCollection($storeId, $productIds = null, $pageSize = null, $pageNumber = 0)
    {
        $this->_modelStoreManagerInterface->getStore($storeId)->setConfig('catalog/frontend/flat_catalog_product', 0);

        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->_productCollection
            ->setStoreId($storeId)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->addAttributeToSelect($this->_modelConfig->getProductAttributes())
            ->addAttributeToSelect(['visibility', 'status', 'url_key', 'solr_boost', 'solr_exclude'])
            ->addAttributeToSelect($this->_bridgeAttributerepository->getAttributeCodesToIndex());

        if (is_array($productIds)) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        }

        if (!is_null($pageSize)) {
            $productCollection->setPageSize($pageSize);
            $productCollection->setCurPage($pageNumber);
        }

        $this->_eventManagerInterface->dispatch('integernet_solr_product_collection_load_before', [
            'collection' => $productCollection
        ]);

        $event = new \Magento\Framework\Event();
        $event->setCollection($productCollection);
        $observer = new \Magento\Framework\Event\Observer();
        $observer->setEvent($event);

        $this->_modelObserverFactory->create()->addTaxPercentToProductCollection($observer);

        $productCollection->load();

        $this->_eventManagerInterface->dispatch('integernet_solr_product_collection_load_after', [
            'collection' => $productCollection
        ]);

        return $productCollection;
    }
}
