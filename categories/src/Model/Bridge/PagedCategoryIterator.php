<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\SolrCategories\Model\Bridge;


use IntegerNet\SolrCategories\Implementor\CategoryIterator as CategoryIteratorInterface;
use IntegerNet\SolrCategories\Implementor\Category as CategoryInterface;
use IntegerNet\SolrCategories\Implementor\CategoryFactory as CategoryFactoryInterface;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use Magento\Catalog\Model\ResourceModel\Category\Collection as MagentoCategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as MagentoCategoryCollectionFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\GroupRepository as StoreGroupRepository;

class PagedCategoryIterator implements CategoryIteratorInterface, \OuterIterator
{
    const EVENT_CATEGORY_COLLECTION_LOAD_BEFORE = 'integernet_solr_category_collection_load_before';
    const EVENT_CATEGORY_COLLECTION_LOAD_AFTER = 'integernet_solr_category_collection_load_after';

    /**
     * @var int
     */
    private $storeId;
    /**
     * @var null|int[]
     */
    private $categoryIdFilter;
    /**
     * @var callable
     */
    private $pageCallback;
    /**
     * @var int
     */
    private $currentPageNumber;
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var MagentoCategoryCollection
     */
    private $collection;
    /**
     * @var \ArrayIterator
     */
    private $collectionIterator;
    /**
     * @var CategoryFactoryInterface
     */
    private $categoryFactory;
    /**
     * @var MagentoCategoryCollectionFactory
     */
    private $magentoCategoryCollectionFactory;

    const PARAM_STORE_ID = 'storeId';
    const PARAM_PAGE_SIZE = 'pageSize';
    const PARAM_CATEGORY_ID_FILTER = 'categoryIdFilter';
    /**
     * @var EventManagerInterface
     */
    private $eventManager;
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var StoreGroupRepository
     */
    private $storeGroupRepository;

    /**
     * PagedCategoryIterator constructor.
     * @param MagentoCategoryCollectionFactory $magentoCategoryCollectionFactory
     * @param CategoryFactoryInterface $categoryFactory
     * @param int $pageSize
     * @param EventManagerInterface $eventManager
     * @param StoreManager $storeManager
     * @param StoreGroupRepository $storeGroupRepository
     * @param int[]|null $categoryIdFilter
     * @param int|null $storeId
     */
    public function __construct(
        MagentoCategoryCollectionFactory $magentoCategoryCollectionFactory,
        CategoryFactoryInterface $categoryFactory,
        $pageSize,
        EventManagerInterface $eventManager,
        StoreManager $storeManager,
        StoreGroupRepository $storeGroupRepository,
        $categoryIdFilter = null,
        $storeId = null
    ) {
        $this->magentoCategoryCollectionFactory = $magentoCategoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeId = $storeId;
        $this->pageSize = $pageSize;
        $this->categoryIdFilter = $categoryIdFilter;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->storeGroupRepository = $storeGroupRepository;
    }

    /**
     * @return MagentoCategoryCollection
     */
    private function getCategoryCollection()
    {
        $magentoCategoryCollection = $this->magentoCategoryCollectionFactory->create();
        $magentoCategoryCollection->setStoreId($this->storeId);
        $magentoCategoryCollection->addAttributeToSelect('*');

        if (is_array($this->categoryIdFilter)) {
            $magentoCategoryCollection->addIdFilter($this->categoryIdFilter);
        }

        $magentoCategoryCollection->addAttributeToFilter('path', ['like' => '1/' . $this->getBaseCategoryId() . '/%']);

        if (!is_null($this->pageSize)) {
            $magentoCategoryCollection->setPageSize($this->pageSize);
            $magentoCategoryCollection->setCurPage($this->currentPageNumber);
        }

        $this->eventManager->dispatch(self::EVENT_CATEGORY_COLLECTION_LOAD_BEFORE, [
            'collection' => $magentoCategoryCollection
        ]);

        $magentoCategoryCollection->load();

        $this->eventManager->dispatch(self::EVENT_CATEGORY_COLLECTION_LOAD_AFTER, [
            'collection' => $magentoCategoryCollection
        ]);

        return $magentoCategoryCollection;
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
        } elseif ($this->currentPageNumber < $this->collection->getLastPageNumber()) {
            $this->currentPageNumber++;
            $this->collection = $this->getCategoryCollection();
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
        $this->currentPageNumber = 1;
        $this->collection = $this->getCategoryCollection();
        $this->collectionIterator = $this->collection->getIterator();
        $this->collectionIterator->rewind();
    }

    /**
     * @return CategoryInterface
     */
    public function current()
    {
        $category = $this->getInnerIterator()->current();
        $category->setStoreId($this->storeId);
        return $this->categoryFactory->create([
            Category::PARAM_MAGENTO_CATEGORY => $category
        ]);
    }

    /**
     * Returns an iterator for a subset of categorys. The ids must be part of the current chunk, otherwise an
     * OutOfBoundsException will be thrown
     *
     * @param int[] $ids
     * @return CategoryIteratorInterface
     * @throws \OutOfBoundsException
     */
    public function subset($ids)
    {
        $categorys = ArrayCollection::fromArray($ids)
            ->map(function($id) {
                $category = $this->collection->getItemById($id);
                if ($category === null) {
                    throw new \OutOfBoundsException("Item with id $id is not loaded in current chunk");
                }
                return $category;
            });

        return new CategoryIterator($this->categoryFactory, $categorys->getArrayCopy(), $this->storeId);
    }

    /**
     * @return bool
     */
    private function validInner()
    {
        return $this->getInnerIterator()->valid();
    }

    /**
     * @return int|mixed
     */
    private function getBaseCategoryId()
    {
        $storeGroupId = $this->storeManager->getStore($this->storeId)->getStoreGroupId();
        $baseCategoryId = $this->storeGroupRepository->get($storeGroupId)->getRootCategoryId();
        return $baseCategoryId;
    }
}