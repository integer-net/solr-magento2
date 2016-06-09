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

use IntegerNet\Solr\Implementor\ProductRepository as ProductRepositoryInterface;
use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductIteratorFactory;
use IntegerNet\Solr\Model\SearchCriteria\ProductSearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

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
     * ProductRepository constructor.
     * @param MagentoProductRepository $productRepository
     * @param ProductIteratorFactory $iteratorFactory
     * @param ProductSearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(MagentoProductRepository $productRepository,
                                ProductIteratorFactory $iteratorFactory,
                                ProductSearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->productRepository = $productRepository;
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

}