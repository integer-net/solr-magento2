<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Implementor\IndexCategoryRepository;
use IntegerNet\SolrSuggest\Implementor\SuggestCategoryRepository;

class Integer\Net\Solr\Model\Bridge\CategoryRepository implements IndexCategoryRepository, SuggestCategoryRepository
{
    protected $_pathCategoryIds = [];
    protected $_excludedCategoryIds = [];

    protected $_categoryNames = [];

    /**
     * @param $categoryIds
     * @param $storeId
     * @return array
     */
    public function getCategoryNames($categoryIds, $storeId)
    {
        $categoryNames = [];

        /** @var \Magento\Catalog\Model\ResourceModel\Category $categoryResource */
        $categoryResource = $this->_resourceModelCategory;
        foreach($categoryIds as $key => $categoryId) {
            if (!isset($this->_categoryNames[$storeId][$categoryId])) {
                $this->_categoryNames[$storeId][$categoryId] = $categoryResource->getAttributeRawValue($categoryId, 'name', $storeId);
            }
            $categoryNames[] = $this->_categoryNames[$storeId][$categoryId];
        }
        return $categoryNames;
    }

    /**
     * Get category ids of assigned categories and all parents
     *
     * @param Product $product
     * @return int[]
     */
    public function getCategoryIds($product)
    {
        $categoryIds = $product->getCategoryIds();

        if (!sizeof($categoryIds)) {
            return [];
        }

        $storeId = $product->getStoreId();
        if (!isset($this->_pathCategoryIds[$storeId])) {
            $this->_pathCategoryIds[$storeId] = [];
        }
        $lookupCategoryIds = array_diff($categoryIds, array_keys($this->_pathCategoryIds[$storeId]));
        $this->_lookupCategoryIdPaths($lookupCategoryIds, $storeId);

        $foundCategoryIds = [];
        foreach($categoryIds as $categoryId) {
            $categoryPathIds = $this->_pathCategoryIds[$storeId][$categoryId];
            $foundCategoryIds = array_merge($foundCategoryIds, $categoryPathIds);
        }

        $foundCategoryIds = array_unique($foundCategoryIds);

        $foundCategoryIds = array_diff($foundCategoryIds, $this->_getExcludedCategoryIds($storeId));

        return $foundCategoryIds;
    }

    /**
     * Lookup and store all parent category ids and its own id of given category ids
     *
     * @param int[] $categoryIds
     * @param int $storeId
     */
    protected function _lookupCategoryIdPaths($categoryIds, $storeId)
    {
        if (!sizeof($categoryIds)) {
            return;
        }

        /** @var $categories \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $categories = $this->_categoryCollection
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
            ->addAttributeToSelect(['is_active', 'include_in_menu']);

        foreach ($categories as $category) {
            /** @var \Magento\Catalog\Model\Category $categoryPathIds */
            if (!$category->getIsActive() || !$category->getIncludeInMenu()) {
                $this->_pathCategoryIds[$storeId][$category->getId()] = [];
                continue;
            }

            $categoryPathIds = explode('/', $category->getPath());
            if (!in_array($this->_modelStoreManagerInterface->getStore($storeId)->getGroup()->getRootCategoryId(), $categoryPathIds)) {
                $this->_pathCategoryIds[$storeId][$category->getId()] = [];
                continue;
            }

            array_shift($categoryPathIds);
            array_shift($categoryPathIds);
            $this->_pathCategoryIds[$storeId][$category->getId()] = $categoryPathIds;
        }
    }


    /**
     * @param int $storeId
     * @return array
     */
    protected function _getExcludedCategoryIds($storeId)
    {
        if (!isset($this->_excludedCategoryIds[$storeId])) {

            // exclude categories which are configured as excluded
            /** @var $excludedCategories \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $excludedCategories = $this->_categoryCollection
                ->addFieldToFilter('solr_exclude', 1);

            $this->_excludedCategoryIds[$storeId] = $excludedCategories->getAllIds();

            // exclude children of categories which are configured as "children excluded"
            /** @var $categoriesWithChildrenExcluded \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $categoriesWithChildrenExcluded = $this->_categoryCollection
                ->setStoreId($storeId)
                ->addFieldToFilter('solr_exclude_children', 1);
            $excludePaths = $categoriesWithChildrenExcluded->getColumnValues('path');

            /** @var $excludedChildrenCategories \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $excludedChildrenCategories = $this->_categoryCollection
                ->setStoreId($storeId);

            $excludePathConditions = [];
            foreach($excludePaths as $excludePath) {
                $excludePathConditions[] = ['like' => $excludePath . '/%'];
            }
            if (sizeof($excludePathConditions)) {
                $excludedChildrenCategories->addAttributeToFilter('path', $excludePathConditions);
                $this->_excludedCategoryIds[$storeId] = array_merge($this->_excludedCategoryIds[$storeId], $excludedChildrenCategories->getAllIds());
            }
        }

        return $this->_excludedCategoryIds[$storeId];
    }

    /**
     * Retrieve product category identifiers
     *
     * @param Product $product
     * @return array
     */
    public function getCategoryPositions($product)
    {
        /** @var $setup \Magento\Catalog\Model\ResourceModel\Setup */
        $setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
        $adapter = $this->_modelResource->getConnection('catalog_read');

        $select = $adapter->select()
            ->from($setup->getTable('catalog/category_product_index'), ['category_id', 'position'])
            ->where('product_id = ?', (int)$product->getId())
            ->where('store_id = ?', $product->getStoreId());

        return $adapter->fetchAll($select);
    }

    /**
     * @prarm int $storeId
     * @param int[] $categoryIds
     * @return \IntegerNet\Solr\Implementor\Category[]
     */
    public function findActiveCategoriesByIds($storeId, $categoryIds)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $this->_categoryCollection;
        $categoryCollection
            ->setStoreId($storeId)
            ->addAttributeToSelect(['name', 'url_key'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('include_in_menu', 1)
            ->addAttributeToFilter('entity_id', ['in' => array_keys($categoryIds)]);
        return array_map(
            function(\Magento\Catalog\Model\Category $category) {
                $categoryPathIds = $category->getPathIds();
                array_shift($categoryPathIds);
                array_shift($categoryPathIds);
                array_pop($categoryPathIds);

                $categoryPathNames = $this->getCategoryNames($categoryPathIds, 0);
                $categoryPathNames[] = $category->getName();

                return new Integer\Net\Solr\Model\Bridge\Category($category, $categoryPathNames);
            },
            $categoryCollection->getItems()
        );
    }


}