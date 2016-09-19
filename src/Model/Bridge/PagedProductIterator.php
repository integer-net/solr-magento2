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
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Implementor\ProductFactory;
use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\Solr\Model\Indexer\ProductCollectionFactory;
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
    private $currentChunkId;
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
     * @var ProductCollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ProductIdChunks
     */
    private $productIdChunks;

    /**
     * @param ProductCollectionFactory $collectionFactory
     * @param ProductFactory $productFactory
     * @param ProductIdChunks $productIdChunks parent and children product ids to be loaded
     * @param int $storeId
     */
    public function __construct(ProductCollectionFactory $collectionFactory, ProductFactory $productFactory, ProductIdChunks $productIdChunks, $storeId = null)
    {
        $this->productFactory = $productFactory;
        $this->storeId = $storeId;
        $this->collectionFactory = $collectionFactory;
        $this->productIdChunks = $productIdChunks;
    }

    /**
     * @return Collection
     */
    private function getProductCollection()
    {
        $collection = $this->collectionFactory->create($this->storeId, $this->currentChunk()->getAllIds());

        $collection->setCurPage($this->currentChunkId);
        $collection->setPageSize($this->pageSize);

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
        } elseif ($this->isEmpty()) {
            return false;
        } elseif ($this->currentChunkId < sizeof($this->productIdChunks) - 1 ) {
            $this->currentChunkId++;
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
        $this->currentChunkId = 0;
        if ($this->isEmpty()) {
            $this->collectionIterator = new \EmptyIterator();
            return;
        }
        $this->collection = $this->getProductCollection();
        $this->collectionIterator = $this->collection->getIterator();
        $this->collectionIterator->rewind();
    }

    /**
     * @return ProductInterface
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
     * @return \IntegerNet\Solr\Indexer\Data\ProductIdChunk
     */
    public function currentChunk()
    {
        return $this->productIdChunks[$this->currentChunkId];
    }

    /**
     * Returns an iterator for a subset of products. The ids must be part of the current chunk, otherwise an
     * OutOfBoundsException will be thrown
     *
     * @param int[] $ids
     * @return ProductIteratorInterface
     * @throws \OutOfBoundsException
     */
    public function subset($ids)
    {
        $products = ArrayCollection::fromArray($ids)
            ->map(function($id) {
                $product = $this->collection->getItemById($id);
                if ($product === null) {
                    throw new \OutOfBoundsException("Item with id $id is not loaded in current chunk");
                }
                return $product;
            });

        return new ProductIterator($this->productFactory, $products->getArrayCopy(), $this->storeId);
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

    /**
     * Returns true if there are no chunks or only one empty chunk
     *
     * @return bool
     */
    private function isEmpty()
    {
        if (sizeof($this->productIdChunks) === 0) {
            return true;
        }
        if (sizeof($this->productIdChunks) === 1 && $this->productIdChunks[0]->getSize() === 0) {
            return true;
        }
        return false;
    }
}