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

use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductIteratorFactory;
use IntegerNet\Solr\Implementor\ProductRepository as ProductRepositoryInterface;
use IntegerNet\Solr\Model\SearchCriteria\ProductSearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var MagentoProductRepository
     */
    private $productRepository;
    /**
     * @var ProductSearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ProductIteratorFactory
     */
    private $iteratorFactory;
    /**
     * @var LinkManagementInterface
     */
    private $productLinkManagement;

    /**
     * ProductRepository constructor.
     * @param MagentoProductRepository $productRepository
     * @param LinkManagementInterface $productLinkManagement
     * @param ProductIteratorFactory $iteratorFactory
     * @param ProductSearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(MagentoProductRepository $productRepository,
                                LinkManagementInterface $productLinkManagement,
                                ProductIteratorFactory $iteratorFactory,
                                ProductSearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->productRepository = $productRepository;
        $this->productLinkManagement = $productLinkManagement;
        $this->iteratorFactory = $iteratorFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Return product iterator, which may implement lazy loading
     *
     * @param int $storeId Products will be returned that are visible in this store and with store specific values
     * @param null|int[] $productIds filter by product ids
     * @return ProductIteratorInterface
     */
    public function getProductsForIndex($storeId, $productIds = null)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder->forStore($storeId);
        if ($productIds !== null) {
            $searchCriteriaBuilder = $searchCriteriaBuilder->withIds($productIds);
        }
        $products = $this->productRepository->getList($searchCriteriaBuilder->create())->getItems();
        return $this->iteratorFactory->create([
            ProductIterator::PARAM_STORE_ID => null,
            ProductIterator::PARAM_MAGENTO_PRODUCTS => $products
            ]);
        //TODO implement LazyProductIterator that uses paginated product collection
    }

    /**
     * Return product iterator for child products
     *
     * @param int $storeId Child products will be returned that are visible in this store and with store specific values
     * @param string $parentSku SKU of the composite parent product
     * @return ProductIteratorInterface
     */
    public function getChildProducts($storeId, $parentSku)
    {
        // TODO: Implement getChildProducts() method.
    }


}