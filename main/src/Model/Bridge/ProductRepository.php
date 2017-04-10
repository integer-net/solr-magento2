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
use IntegerNet\Solr\Implementor\ProductRepository as ProductRepositoryInterface;
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;
use IntegerNet\Solr\Model\ResourceModel\MergedProductAssociations;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableType;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var PagedProductIteratorInterfaceFactory
     */
    private $pagedIteratorFactory;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var MergedProductAssociations
     */
    private $productAssociationsResource;

    /**
     * @param PagedProductIteratorInterfaceFactory $pagedIteratorFactory
     * @param CollectionFactory $productCollectionFactory
     * @param ConfigurableType $configurableType
     */
    public function __construct(PagedProductIteratorInterfaceFactory $pagedIteratorFactory,
                                CollectionFactory $productCollectionFactory,
                                MergedProductAssociations $mergedProductAssociations)
    {
        $this->pagedIteratorFactory = $pagedIteratorFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAssociationsResource = $mergedProductAssociations;
    }

    /**
     * Return product iterator which may implement lazy loading but must ensure that given chunks are loaded together
     *
     * @param int $storeId
     * @param ProductIdChunks $chunks
     * @return PagedProductIteratorInterface
     */
    public function getProductsInChunks($storeId, ProductIdChunks $chunks)
    {
        return $this->pagedIteratorFactory->create([
            PagedProductIterator::PARAM_STORE_ID => $storeId,
            PagedProductIterator::PARAM_PRODUCT_ID_CHUNKS => $chunks,
        ]);
    }

    /**
     * @return int[]
     */
    public function getAllProductIds()
    {
        return $this->productCollectionFactory->create()->getAllIds();
    }

    /**
     * @param null|int[] $productIds
     * @return \IntegerNet\Solr\Indexer\Data\ProductAssociation[] An array with parent_id as key and association metadata as value
     */
    public function getProductAssociations($productIds)
    {
        return $this->productAssociationsResource->getAssociations($productIds);
    }

}