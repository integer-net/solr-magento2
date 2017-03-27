<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory as StockStatusFactory;

/**
 * Factory to prepare product collection for indexing
 */
class ProductCollectionFactory
{
    const PARAM_STORE_ID = 'storeId';
    const PARAM_PRODUCT_IDS = 'productIds';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CatalogConfig
     */
    private $catalogConfig;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var StockStatusFactory
     */
    private $stockStatusFactory;
    /**
     * @var StockStatus
     */
    private $stockStatusResource;

    /**
     * ProductCollectionFactory constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param CatalogConfig $catalogConfig
     * @param AttributeRepositoryInterface $attributeRepository
     * @param StockStatusFactory $stockStatusFactory
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory, CatalogConfig $catalogConfig, AttributeRepositoryInterface $attributeRepository, StockStatusFactory $stockStatusFactory)
    {
        $this->collectionFactory = $collectionFactory;
        $this->catalogConfig = $catalogConfig;
        $this->attributeRepository = $attributeRepository;
        $this->stockStatusFactory = $stockStatusFactory;
    }

    public function create($storeId = null, $productIds = null)
    {
        $collection = $this->collectionFactory->create();

        $collection->addStoreFilter($storeId);
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->addTaxPercents();
        $collection->addUrlRewrite();
        $collection->addAttributeToSelect($this->productAttributes());
        if ($productIds !== null) {
            $collection->addIdFilter($productIds);
        }
        $this->addStockDataWithoutFilter($collection);

        return $collection;
    }

    /**
     * @return array
     */
    private function productAttributes()
    {
        $productAttributes = array_unique(array_merge(
            $this->catalogConfig->getProductAttributes(),
            array('visibility', 'status', 'url_key', 'solr_boost', 'solr_exclude'),
            $this->attributeRepository->getAttributeCodesToIndex()
        ));
        return $productAttributes;
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
    /**
     * @return StockStatus
     */
    private function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = $this->stockStatusFactory->create();
        }
        return $this->stockStatusResource;
    }
}