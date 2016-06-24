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
use Magento\Catalog\Api\CategoryRepositoryInterface as MagentoCategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface as MagentoCategoryInterface;
use Magento\Catalog\Model\Category as MagentoCategory;
use Magento\Catalog\Model\Category;
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
     * CategoryRepository constructor.
     * @param MagentoCategoryRepository $categoryRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(MagentoCategoryRepository $categoryRepository, CollectionFactory $collectionFactory, StoreManagerInterface $storeManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
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
            ->without([1, 2])
            ->without($this->getExcludedCategoryIds($product->getStoreId()))
            ->values();


        return $result->getArrayCopy();
    }

    /**
     * Retrieve product category identifiers
     *
     * @param Product $product
     * @return array
     */
    public function getCategoryPositions($product)
    {
        // TODO: Implement getCategoryPositions() method.
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
        $parentIds = $parentsCollection->getAllIds();
        $parentPaths = ArrayCollection::fromArray($parentsCollection->getColumnValues(Category::KEY_PATH))->values();

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
 * Utility class for collection pipelines
 *
 * @internal
 * @todo move to solr-base library if used more often
 */
class ArrayCollection extends \ArrayIterator
{
    public static function fromArray(array $array)
    {
        return new static($array);
    }
    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static(\array_map($callback, $this->getArrayCopy()));
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        return new static(\array_filter($this->getArrayCopy(), $callback));
    }

    public function keys()
    {
        return new static(\array_keys($this->getArrayCopy()));
    }

    /**
     * @param callable $callback
     * @param null $initial
     * @return static
     */
    public function reduce(callable $callback, $initial = null)
    {
        return new static(\array_reduce($this->getArrayCopy(), $callback, $initial));
    }

    /**
     * @return static
     */
    public function unique()
    {
        return new static(\array_unique($this->getArrayCopy()));
    }

    /**
     * @return static
     */
    public function collapse()
    {
        return $this->reduce(function($carry, $item) {
            if (\is_array($item)) {
                return \array_merge($carry, $item);
            } else {
                return \array_merge($carry, [$item]);
            }
        }, []);
    }

    /**
     * @param array $values
     * @return static
     */
    public function without(array $values)
    {
        return new static(\array_filter($this->getArrayCopy(), function($value) use ($values) {
            return ! \in_array($value, $values);
        }));
    }

    /**
     * @return static
     */
    public function values()
    {
        return new static(\array_values($this->getArrayCopy()));
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
     * @return IdCollection
     */
    public function idsWithParents()
    {
        return new IdCollection(
            $this
                ->map(function(MagentoCategoryInterface $category) {
                    return \explode('/', $category->getPath());
                })
                ->collapse()
                ->getArrayCopy()
        );
    }
}
/**
 * @internal
 */
class IdCollection extends ArrayCollection
{
}