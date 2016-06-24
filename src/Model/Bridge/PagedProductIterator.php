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


use IntegerNet\Solr\Implementor\PagedProductIterator as PagedProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class PagedProductIterator implements PagedProductIteratorInterface, \OuterIterator
{
    /**
     * @var int
     */
    private $storeId;
    /**
     * @var null|int[]
     */
    private $productIdFilter;
    /**
     * @var callable
     */
    private $pageCallback;
    /**
     * @var int
     */
    private $currentPage;
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var Collection
     */
    private $collection;
    /**
     * @var \ArrayIterator
     */
    private $collectionIterator;
    /**
     * @var ProductFactory
     */
    private $productFactory;

    const PARAM_PRODUCT_ID_FILTER = 'productIdFilter';
    const PARAM_PAGE_SIZE = 'pageSize';
    const PARAM_STORE_ID = 'storeId';
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductFactory $productFactory
     * @param int[]|null $productIdFilter
     * @param int $pageSize
     * @param int $storeId
     */
    public function __construct(CollectionFactory $collectionFactory, ProductFactory $productFactory, $productIdFilter = null, $pageSize = 1000, $storeId = null)
    {
        $this->productIdFilter = $productIdFilter;
        $this->pageSize = $pageSize;
        $this->productFactory = $productFactory;
        $this->storeId = $storeId;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Collection
     */
    private function getProductCollection()
    {
        $collection = $this->collectionFactory->create();
        if ($this->storeId !== null) {
            $collection->setStoreId($this->storeId);
        }
        if ($this->productIdFilter !== null) {
            $collection->addIdFilter($this->productIdFilter);
        }
        $collection->setCurPage($this->currentPage);
        $collection->setPageSize($this->pageSize);

        //TODO joins, attributes
        //TODO events(?)
        $collection->load();
        return $collection;
    }


    /**
     * @link http://php.net/manual/en/outeriterator.getinneriterator.php
     * @return \Iterator The inner iterator for the current entry.
     */
    public function getInnerIterator()
    {
        return $this->collectionIterator;
    }

    /**
     * Define a callback that is called after each "page" iteration (i.e. finished inner iterator)
     *
     * @param callable $callback
     */
    public function setPageCallback($callback)
    {
        $this->pageCallback = $callback;
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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->validInner()) {
            return true;
        } elseif ($this->productIdFilter === []) {
            return false;
        } elseif ($this->currentPage < $this->collection->getLastPageNumber()) {
            $this->currentPage++;
            $this->collection = $this->getProductCollection();
            $this->collectionIterator = $this->collection->getIterator();
            $this->getInnerIterator()->rewind();
            return $this->validInner();
        }
        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->currentPage = 1;
        if ($this->productIdFilter === []) {
            $this->collectionIterator = new \EmptyIterator();
            return;
        }
        $this->collection = $this->getProductCollection();
        $this->collectionIterator = $this->collection->getIterator();
        $this->collectionIterator->rewind();
    }

    /**
     * @return Product
     */
    public function current()
    {
        $product = $this->getInnerIterator()->current();
        $product->setStoreId($this->storeId);
        return $this->productFactory->create([
            Product::PARAM_MAGENTO_PRODUCT => $product,
            Product::PARAM_STORE_ID => $this->storeId,
        ]);
    }

    /**
     * @return bool
     */
    private function validInner()
    {
        $valid = $this->getInnerIterator()->valid();
        if (! $valid && ! $this->getInnerIterator() instanceof \EmptyIterator) {
            \call_user_func($this->pageCallback, $this);
        }
        return $valid;
    }
}