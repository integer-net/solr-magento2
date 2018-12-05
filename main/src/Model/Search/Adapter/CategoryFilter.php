<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Search\Adapter;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;

class CategoryFilter extends \Magento\CatalogSearch\Model\Layer\Filter\Category
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;
    /**
     * @var RequestInterface
     */
    private $request;


    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $escaper, $categoryDataProviderFactory, $data);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->request = $request;
    }

    /**
     * Apply category filter to product collection
     *
     * @param   RequestInterface $request
     * @return  $this
     */
    public function apply(RequestInterface $request)
    {
        $categoryIds = $request->getParam($this->_requestVar);
        if (empty($categoryIds)) {
            return $this;
        }

        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        /** @var CategoryCollection $categories */
        $categories = $this->categoryCollectionFactory->create()
            ->addIdFilter($categoryIds)
            ->addIsActiveFilter()
            ->addAttributeToFilter('path', ['like' => '%/' . $this->getRootCategoryId() . '/%'])
            ->addAttributeToSelect(['name']);

        $this->getLayer()->getProductCollection()->addCategoriesFilter(['in' => $categories->getAllIds()]);

        foreach($categories as $category) {

            $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $category->getId()));
        }
        return $this;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $productCollection->setOrder('relevance', \Zend_Db_Select::SQL_DESC);
        $optionsFacetedData = $productCollection->getFacetedData('category');

        /** @var CategoryCollection $categories */
        $categories = $this->categoryCollectionFactory->create()
            ->addIdFilter(array_keys($optionsFacetedData))
            ->addIsActiveFilter()
            ->addAttributeToFilter('path', ['like' => '%/' . $this->getRootCategoryId() . '/%'])
            ->addAttributeToSelect(['is_active', 'name']);

        if ($currentCategoryId = $this->request->getParam('id')) {
            $categories->addAttributeToFilter('parent_id', $currentCategoryId);
        }

        $collectionSize = $productCollection->getSize();

        foreach ($categories as $category) {
            if ($category->getIsActive()
                && isset($optionsFacetedData[$category->getId()])
                && $this->isOptionReducesResults($optionsFacetedData[$category->getId()]['count'], $collectionSize)
            ) {
                $this->itemDataBuilder->addItemData(
                    $this->escaper->escapeHtml($category->getName()),
                    $category->getId(),
                    $optionsFacetedData[$category->getId()]['count']
                );
            }
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * @return int
     */
    private function getRootCategoryId()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        return $store->getRootCategoryId();
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * @param int $optionCount Count of search results with this option
     * @param int $totalSize Current search results count
     * @return bool
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        return true;
    }
}
