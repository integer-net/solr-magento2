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
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Indexer\Data\CategoryPositionCollection;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\Solr\Model\ResourceModel\CategoryPosition;
use Magento\Catalog\Api\CategoryRepositoryInterface as MagentoCategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface as MagentoCategoryInterface;
use Magento\Catalog\Model\Category as MagentoCategory;
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
     * CategoryRepository constructor.
     * @param MagentoCategoryRepository $categoryRepository
     * @param CollectionFactory $collectionFactory
     * @param CategoryPosition $categoryPositionResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(MagentoCategoryRepository $categoryRepository, CollectionFactory $collectionFactory, CategoryPosition $categoryPositionResource, StoreManagerInterface $storeManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->categoryPositionResource = $categoryPositionResource;
    }

    /**
     * @param int [] $categoryIds
     * @param int $storeId
     * @return array
     */
    public function getCategoryNames($categoryIds, $storeId)
    {
        // This code loads each category separately (from instance cache or db) and will make the indexer slow and
        // memory hungry, but it is the only way to get category data from the API interfaces as of Magento 2.0
        //TODO optimize
        $names = [];
        foreach ($categoryIds as $categoryId) {
            $names[] = $this->categoryRepository->get($categoryId, $storeId)->getName();
        }
        return $names;
    }

    /**
     * Get category ids of assigned categories and all parents and without excluded categories
     *
     * @param Product $product
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

        $result = CategoryCollection::fromMagentoCollection($collection)
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
     * @param Product $product
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

}


/**
 * @internal
 */
class CategoryCollection extends ArrayCollection
{
    /**
     * @param Collection $magentoCollection
     * @return static
     */
    public static function fromMagentoCollection(Collection $magentoCollection)
    {
        return new static($magentoCollection->getIterator()->getArrayCopy());
    }

    /**
     * @return static
     */
    public function filterVisibleInMenu()
    {
        return $this->filter(function(MagentoCategoryInterface $category) {
            return $category->getIsActive() && $category->getIncludeInMenu();
        });
    }

    /**
     * @param $rootCategoryId
     * @return static
     */
    public function filterInRoot($rootCategoryId)
    {
        return $this->filter(function(MagentoCategoryInterface $category) use ($rootCategoryId) {
            $parentIds = \explode('/', $category->getPath());
            return \in_array($rootCategoryId, $parentIds);
        });
    }

    /**
     * @return ArrayCollection
     */
    public function idsWithParents()
    {
        return new ArrayCollection(
            $this
                ->map(function(MagentoCategoryInterface $category) {
                    return \explode('/', $category->getPath());
                })
                ->collapse()
                ->getArrayCopy()
        );
    }
}