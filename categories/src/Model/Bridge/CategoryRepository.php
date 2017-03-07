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

use IntegerNet\SolrCategories\Implementor\CategoryFactory as CategoryFactoryInterface;
use IntegerNet\SolrSuggest\Implementor\SuggestCategory;
use IntegerNet\SolrSuggest\Implementor\SuggestCategoryRepository;
use IntegerNet\SolrCategories\Implementor\CategoryRepository as CategoryRepositoryInterface;
use IntegerNet\SolrCategories\Implementor\CategoryIterator;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Indexer\Data\CategoryPositionCollection;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\Solr\Model\ResourceModel\CategoryPosition;
use Magento\Catalog\Api\CategoryRepositoryInterface as MagentoCategoryRepository;
use Magento\Catalog\Model\Category as MagentoCategory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class CategoryRepository implements SuggestCategoryRepository, CategoryRepositoryInterface
{
    /**
     * @var MagentoCategoryRepository
     */
    private $magentoCategoryRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CategoryPosition
     */
    private $categoryPositionResource;
    /**
     * @var CategoryResource
     */
    private $categoryResource;
    /**
     * @var string[]
     */
    private $categoryNames = [];
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var CategoryFactoryInterface
     */
    private $categoryFactory;
    /**
     * @var PagedCategoryIteratorFactory
     */
    private $pagedCategoryIteratorFactory;

    /**
     * CategoryRepository constructor.
     * @param MagentoCategoryRepository $magentoCategoryRepository
     * @param CategoryPosition $categoryPositionResource
     * @param StoreManagerInterface $storeManager
     * @param CategoryResource $categoryResource
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryFactoryInterface $categoryFactory
     * @param PagedCategoryIteratorFactory $pagedCategoryIteratorFactory
     */
    public function __construct(
        MagentoCategoryRepository $magentoCategoryRepository,
        CategoryPosition $categoryPositionResource,
        StoreManagerInterface $storeManager,
        CategoryResource $categoryResource,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryFactoryInterface $categoryFactory,
        PagedCategoryIteratorFactory $pagedCategoryIteratorFactory
    ) {
        $this->magentoCategoryRepository = $magentoCategoryRepository;
        $this->storeManager = $storeManager;
        $this->categoryPositionResource = $categoryPositionResource;
        $this->categoryResource = $categoryResource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->pagedCategoryIteratorFactory = $pagedCategoryIteratorFactory;
    }

    /**
     * @param int [] $categoryIds
     * @param int $storeId
     * @return array
     */
    public function getCategoryNames($categoryIds, $storeId)
    {
        $names = [];
        foreach ($categoryIds as $categoryId) {
            if (!isset($this->categoryNames[$storeId][$categoryId])) {
                $this->categoryNames[$storeId][$categoryId] = $this->categoryResource->getAttributeRawValue($categoryId, 'name', $storeId);
            }
            $names[] = $this->categoryNames[$storeId][$categoryId];
        }
        return $names;
    }

    /**
     * Get category ids of assigned categories and all parents and without excluded categories
     *
     * @param ProductInterface $product
     * @return int[]
     */
    public function getCategoryIds($product)
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore($product->getStoreId());
        $rootCategoryId = $store->getRootCategoryId();

        //TODO optimize: don't look up any category more than once if getCategoryIds() is called multiple times
        $collection = $this->categoryCollectionFactory->create();
        $collection
            ->addIdFilter($product->getCategoryIds())
            ->addAttributeToSelect([MagentoCategory::KEY_IS_ACTIVE, MagentoCategory::KEY_INCLUDE_IN_MENU]);

        $result = \IntegerNet\Solr\Model\Data\CategoryCollection::fromMagentoCollection($collection)
            ->filterVisibleInMenu()
            ->filterInRoot($rootCategoryId)
            ->idsWithParents()
            ->unique()
            ->without([1, $rootCategoryId])
            ->without($this->getExcludedCategoryIds($product->getStoreId()))
            ->values();


        return $result->getArrayCopy();
    }

    /**
     * Retrieve product category identifiers
     *
     * @param ProductInterface $product
     * @return CategoryPositionCollection
     */
    public function getCategoryPositions($product)
    {
        return CategoryPositionCollection::fromArray(
            $this->categoryPositionResource->getCategoryPositions($product->getId(), $product->getStoreId())
        );
    }

    /**
     * @param $storeId
     * @return int[]
     */
    private function getExcludedCategoryIds($storeId)
    {
        $result = [];
        $excludedIds = $this->categoryCollectionFactory->create()->setStoreId($storeId)
            ->addAttributeToFilter('solr_exclude', '1')
            ->getAllIds();
        $result = \array_merge($result, $excludedIds);

        $parentsCollection = $this->categoryCollectionFactory->create()->setStoreId($storeId)
            ->addAttributeToFilter('solr_exclude_children', '1');
        $parentPaths = ArrayCollection::fromArray($parentsCollection->getColumnValues(MagentoCategory::KEY_PATH))->values();

        if ($parentPaths->count() === 0) {
            return $result;
        }

        $pathFilter = $parentPaths->map(function($path) {
            return ['like' => $path . '/%'];
        })->getArrayCopy();
        $excludedChildIds = $this->categoryCollectionFactory->create()->setStoreId($storeId)
            ->addAttributeToFilter('path', $pathFilter)
            ->getAllIds();
        $result = \array_merge($result, $excludedChildIds);

        return $result;
    }

    /**
     * @param int $storeId
     * @param int[] $categoryIds
     * @return SuggestCategory[]
     */
    public function findActiveCategoriesByIds($storeId, $categoryIds)
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection
            ->setStoreId($storeId)
            ->addAttributeToSelect(['name', 'url_key'])
            ->addAttributeToFilter(MagentoCategory::KEY_IS_ACTIVE, 1)
            ->addAttributeToFilter(MagentoCategory::KEY_INCLUDE_IN_MENU, 1)
            ->addIdFilter($categoryIds);
        return array_map(
            function(MagentoCategory $category) {
                $categoryPathIds = $category->getPathIds();
                array_shift($categoryPathIds);
                array_shift($categoryPathIds);
                array_pop($categoryPathIds);

                $categoryPathNames = $this->getCategoryNames($categoryPathIds, 0);
                $categoryPathNames[] = $category->getName();

                return $this->categoryFactory->create([
                    Category::PARAM_MAGENTO_CATEGORY => $category,
                    Category::PARAM_CATEGORY_PATH_NAMES => $categoryPathNames,
                ]);
            },
            $categoryCollection->getItems()
        );
    }

    /**
     * Return category iterator, which may implement lazy loading
     *
     * @param int $storeId Categories will be returned that are visible in this store and with store specific values
     * @param null|int[] $categoryIds filter by category ids
     * @return CategoryIterator
     */
    public function getCategoriesForIndex($storeId, $categoryIds = null)
    {
        return $this->pagedCategoryIteratorFactory->create([
            PagedCategoryIterator::PARAM_STORE_ID => $storeId,
            PagedCategoryIterator::PARAM_CATEGORY_ID_FILTER => $categoryIds,
            PagedCategoryIterator::PARAM_PAGE_SIZE => $this->pageSize
        ]);
    }

    /**
     * @param int $pageSize
     * @return CategoryRepositoryInterface
     */
    public function setPageSizeForIndex($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }
}