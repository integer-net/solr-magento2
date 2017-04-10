<?php

namespace IntegerNet\Solr\Model\SearchCriteria;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SimpleBuilderInterface;


/**
 * Provides an explicit interface to create SearchCriterias for the ProductRepository.
 *
 * All modifiers return a new instance, so that it can be used multiple times with different parameters
 *
 * @package IntegerNet\Solr\Model\SearchCriteria
 */
class ProductSearchCriteriaBuilder implements SimpleBuilderInterface
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /** @var callable[] */
    private $buildCallbacks = [];

    /**
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory)
    {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Add ID filter
     *
     * @return ProductSearchCriteriaBuilder new, modified builder instance
     */
    public function withIds(array $ids)
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) use ($ids) {
            $builder->addFilter('entity_id', $ids, 'in');
        };
        return $new;
    }

    /**
     * Add store filter
     *
     * @return ProductSearchCriteriaBuilder new, modified builder instance
     */
    public function forStore($storeId)
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) use ($storeId) {
            $builder->addFilter(Product::STORE_ID, $storeId);
        };
        return $new;
    }

    /**
     * Builds and returns SearchCriteria instance
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    public function create()
    {
        return $this->getBuilder()->create();
    }

    /**
     * Returns data from underlying SearchCriteriaBuilder
     *
     * @return array
     */
    public function getData()
    {
        return $this->getBuilder()->getData();
    }

    /**
     * @return SearchCriteriaBuilder
     */
    private function getBuilder()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        foreach ($this->buildCallbacks as $buildCallback) {
            $buildCallback($searchCriteriaBuilder);
        }
        return $searchCriteriaBuilder;
    }

}