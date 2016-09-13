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

use IntegerNet\Solr\Implementor\PagedProductIterator as PagedProductIteratorInterface;
use IntegerNet\Solr\Implementor\PagedProductIteratorFactory as PagedProductIteratorInterfaceFactory;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductIteratorFactory as ProductIteratorInterfaceFactory;
use IntegerNet\Solr\Implementor\ProductRepository as ProductRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
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

    public function __construct(LinkManagementInterface $productLinkManagement,
                                ProductIteratorInterfaceFactory $iteratorFactory,
                                PagedProductIteratorInterfaceFactory $pagedIteratorFactory)
    {
        $this->productLinkManagement = $productLinkManagement;
        $this->iteratorFactory = $iteratorFactory;
        $this->pagedIteratorFactory = $pagedIteratorFactory;
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
        return $this->pagedIteratorFactory->create([
            PagedProductIterator::PARAM_STORE_ID => $storeId,
            PagedProductIterator::PARAM_PAGE_SIZE => $this->pageSize,
            PagedProductIterator::PARAM_PRODUCT_ID_FILTER => $productIds,
        ]);
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

}