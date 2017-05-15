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


use IntegerNet\Solr\Implementor\IndexCategoryRepository;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Indexer\Data\CategoryPositionCollection;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\Solr\Model\ResourceModel\CategoryPosition;
use Magento\Catalog\Api\CategoryRepositoryInterface as MagentoCategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface as MagentoCategoryInterface;
use Magento\Catalog\Model\Category as MagentoCategory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class CategoryRepository implements IndexCategoryRepository
{
    /**
     * @var MagentoCategoryRepository
     */
    private $categoryRepository;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
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
     * CategoryRepository constructor.
     * @param MagentoCategoryRepository $categoryRepository
     * @param CollectionFactory $collectionFactory
     * @param CategoryPosition $categoryPositionResource
     * @param StoreManagerInterface $storeManager
     * @param CategoryResource $categoryResource
     */
    public function __construct(
        MagentoCategoryRepository $categoryRepository,
        CollectionFactory $collectionFactory,
        CategoryPosition $categoryPositionResource,
        StoreManagerInterface $storeManager,
        CategoryResource $categoryResource
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->categoryPositionResource = $categoryPositionResource;
        $this->categoryResource = $categoryResource;
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
                $this->categoryNames[$storeId][$categoryId] = $this->getCategoryName($categoryId, $storeId);
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
        $collection = $this->collectionFactory->create();
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
        $excludedIds = $this->collectionFactory->create()->setStoreId($storeId)
            ->addAttributeToFilter('solr_exclude', '1')
            ->getAllIds();
        $result = \array_merge($result, $excludedIds);

        $parentsCollection = $this->collectionFactory->create()->setStoreId($storeId)
            ->addAttributeToFilter('solr_exclude_children', '1');
        $parentPaths = ArrayCollection::fromArray($parentsCollection->getColumnValues(MagentoCategory::KEY_PATH))->values();

        if ($parentPaths->count() === 0) {
            return $result;
        }

        $pathFilter = $parentPaths->map(function($path) {
            return ['like' => $path . '/%'];
        })->getArrayCopy();
        $excludedChildIds = $this->collectionFactory->create()->setStoreId($storeId)
            ->addAttributeToFilter('path', $pathFilter)
            ->getAllIds();
        $result = \array_merge($result, $excludedChildIds);

        return $result;
    }

    /**
     * @param int $categoryId
     * @param int $storeId
     * @return string
     */
    private function getCategoryName($categoryId, $storeId)
    {
        if ($categoryName = $this->categoryResource->getAttributeRawValue($categoryId, 'name', $storeId)) {
            return $categoryName;
        }

        // Workaround for Magento < 2.2 where "getAttributeRawValue" wasn't implemented correctly for categories.
        $categoryCollection = $this->collectionFactory->create();
        $category = $categoryCollection->addAttributeToSelect('name')->addIdFilter([$categoryId])->getFirstItem();
        return $category->getData('name');
    }
}