<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Model\Data\CategoryCollection;
use IntegerNet\SolrSuggest\Implementor\SerializableSuggestCategory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Store\Model\StoreManager;

class SerializableCategoryRepository implements \IntegerNet\SolrSuggest\Implementor\SerializableCategoryRepository
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var SidResolverInterface
     */
    private $sidResolver;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var CategoryResource
     */
    private $categoryResource;
    /**
     * @var StoreManager
     */
    private $storeManager;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        SidResolverInterface $sidResolver,
        ScopeConfigInterface $scopeConfig,
        CategoryResource $categoryResource,
        StoreManager $storeManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->sidResolver = $sidResolver;
        $this->scopeConfig = $scopeConfig;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $storeId
     * @return SerializableSuggestCategory[]
     */
    public function findActiveCategories($storeId)
    {
        $origUseSessionInUrl = $this->sidResolver->getUseSessionInUrl();
        $this->sidResolver->setUseSessionInUrl(false);
        try {
            $rootCategoryId = $this->storeManager->getGroup(
                $this->storeManager->getStore($storeId)->getStoreGroupId()
            )->getRootCategoryId();
            return CategoryCollection::fromMagentoCollection(
                $this->categoryCollectionFactory->create()
                    ->setStoreId($storeId)
                    ->addAttributeToSelect(['name', 'url_key'])
                    ->addAttributeToFilter('is_active', 1)
                    ->addAttributeToFilter('include_in_menu', 1)
                    ->addAttributeToFilter('level', ['gt' => 1])
                    ->addAttributeToFilter('path', ['like' => '%/' . $rootCategoryId . '/%'])
            )->map(
                function (Category $category) use ($storeId) {
                    $category->setStoreId($storeId);
                    return new \IntegerNet\SolrSuggest\Plain\Bridge\Category(
                        $category->getId(), $this->getCategoryTitle($category), $category->getUrl()
                    );
                }
            )->getArrayCopy();
        } finally {
            $this->sidResolver->setUseSessionInUrl($origUseSessionInUrl);
        }
    }

    /**
     * @param Category $category
     * @return string
     */
    private function getCategoryTitle(Category $category)
    {
        if ($this->scopeConfig->isSetFlag('integernet_solr/autosuggest/show_complete_category_path')) {
            $categoryPathIds = $category->getPathIds();
            array_shift($categoryPathIds);
            array_shift($categoryPathIds);
            array_pop($categoryPathIds);

            $categoryPathNames = array();
            foreach($categoryPathIds as $categoryId) {
                $categoryPathNames[] = $this->getCategoryName($categoryId, $category->getStoreId());
            }
            $categoryPathNames[] = $category->getName();
            return implode(' > ', $categoryPathNames);
        }
        return $category->getName();
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
        $categoryCollection = $this->categoryCollectionFactory->create();
        $category = $categoryCollection->addAttributeToSelect('name')->addIdFilter([$categoryId])->getFirstItem();
        return $category->getData('name');
    }
}