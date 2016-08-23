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

use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Implementor\PagedProductIterator as PagedProductIteratorInterface;
use IntegerNet\Solr\Implementor\PagedProductIteratorFactory as PagedProductIteratorInterfaceFactory;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductIteratorFactory as ProductIteratorInterfaceFactory;
use IntegerNet\Solr\Implementor\ProductRepository as ProductRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory as StockStatusFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ProductIteratorInterfaceFactory
     */
    private $iteratorFactory;
    /**
     * @var LinkManagementInterface
     */
    private $productLinkManagement;
    /**
     * @var int
     */
    private $pageSize;
    /**
     * @var PagedProductIteratorInterfaceFactory
     */
    private $pagedIteratorFactory;
    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;
    /**
     * @var StockStatusFactory
     */
    private $stockStatusFactory;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @param LinkManagementInterface $productLinkManagement
     * @param ProductIteratorInterfaceFactory $iteratorFactory
     * @param PagedProductIteratorInterfaceFactory $pagedIteratorFactory
     * @param ProductCollectionFactory $collectionFactory
     * @param StockStatusFactory $stockStatusFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Config $catalogConfig
     */
    public function __construct(LinkManagementInterface $productLinkManagement,
                                ProductIteratorInterfaceFactory $iteratorFactory,
                                PagedProductIteratorInterfaceFactory $pagedIteratorFactory,
                                ProductCollectionFactory $collectionFactory,
                                StockStatusFactory $stockStatusFactory,
                                AttributeRepositoryInterface $attributeRepository,
                                CatalogConfig $catalogConfig)
    {
        $this->productLinkManagement = $productLinkManagement;
        $this->iteratorFactory = $iteratorFactory;
        $this->pagedIteratorFactory = $pagedIteratorFactory;
        $this->collectionFactory = $collectionFactory;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->attributeRepository = $attributeRepository;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * Set maximum number of products to load at once during index
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSizeForIndex($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * Return product iterator, which should implement lazy loading and allows a callback for batch processing
     *
     * @param int $storeId Products will be returned that are visible in this store and with store specific values
     * @param null|int[] $productIds filter by product ids
     * @return PagedProductIteratorInterface
     */
    public function getProductsForIndex($storeId, $productIds = null)
    {
        $productAttributes = array_unique(array_merge(
            $this->catalogConfig->getProductAttributes(),
            array('visibility', 'status', 'url_key', 'solr_boost', 'solr_exclude'),
            $this->attributeRepository->getAttributeCodesToIndex()
        ));

        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($storeId);
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->addTaxPercents();
        $collection->addUrlRewrite();
        $collection->addAttributeToSelect($productAttributes);
        if ($productIds !== null) {
            $collection->addIdFilter($productIds);
        }
        $this->addStockDataWithoutFilter($collection);

        return $this->createProductIterator($storeId, $collection->getItems());
        //TODO use PagedProductIterator with lazy loading (at least if count($productIds)>$pageSize)
    }

    /**
     * Return product iterator for child products
     *
     * @param ProductInterface|Product $parent The composite parent product. Child products will be returned that are visible in the same store and with store specific values
     * @return ProductIteratorInterface
     */
    public function getChildProducts(ProductInterface $parent)
    {
        $products = $this->productLinkManagement->getChildren($parent->getSku());
        return $this->createProductIterator($parent->getStoreId(), $products);
    }

    /**
     * @param $storeId
     * @param $products
     * @return ProductIteratorInterface
     */
    private function createProductIterator($storeId, $products)
    {
        return $this->iteratorFactory->create([
            ProductIterator::PARAM_STORE_ID => $storeId,
            ProductIterator::PARAM_MAGENTO_PRODUCTS => $products
        ]);
    }

    /**
     * @return StockStatus
     */
    protected function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = $this->stockStatusFactory->create();
        }
        return $this->stockStatusResource;
    }

    /**
     * @param $collection
     */
    private function addStockDataWithoutFilter(ProductCollection $collection)
    {
        $stockFlag = 'has_stock_status_filter';
        if (!$collection->hasFlag($stockFlag)) {
            $resource = $this->getStockStatusResource();
            $resource->addStockDataToCollection($collection, false);
            $collection->setFlag($stockFlag, true);
        }
    }
}