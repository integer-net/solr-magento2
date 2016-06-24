<?php
namespace IntegerNet\Solr\Model\SearchCriteria;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SimpleBuilderInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Provides an explicit interface to create SearchCriterias for the AttributeRepository.
 *
 * All modifiers return a new instance, so that it can be used multiple times with different parameters
 *
 * @package IntegerNet\Solr\Model\SearchCriteria
 */
class AttributeSearchCriteriaBuilder implements SimpleBuilderInterface
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
     * Only include varchar attributes
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function varchar()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(EavAttributeInterface::BACKEND_TYPE, ['static', 'varchar'], 'in');
            $builder->addFilter(EavAttributeInterface::FRONTEND_INPUT, 'text');
        };
        return $new;
    }

    /**
     * Only include searchable attributes
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function searchable()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(EavAttributeInterface::IS_SEARCHABLE, '1');
        };
        return $new;
    }
    /**
     * Only include sortable attributes
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function sortable()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(EavAttributeInterface::USED_FOR_SORT_BY, '1');
        };
        return $new;
    }

    /**
     * Only include filterable attributes
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function filterable()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(EavAttributeInterface::IS_FILTERABLE, '1');
        };
        return $new;
    }
    /**
     * Only include filterable in search attributes
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function filterableInSearch()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(EavAttributeInterface::IS_FILTERABLE_IN_SEARCH, '1');
        };
        return $new;
    }

    /**
     * Include attributes that are filterable OR filterable in serach
     *
     * @return AttributeSearchCriteriaBuilder
     */
    public function filterableInCatalogOrSearch()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            /*
             * Groups are combined with OR
             */
            $builder->addFilters([
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE,
                    Filter::KEY_VALUE => '1'
                ])
            ]);
            $builder->addFilters([
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                    Filter::KEY_VALUE => '1'
                ])
            ]);
        };
        return $new;
    }
    /**
     * Include attributes that are filterable OR filterable in serach OR searchable
     *
     * @return AttributeSearchCriteriaBuilder
     */
    public function indexable()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            /*
             * Groups are combined with OR
             */
            $builder->addFilters([
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE,
                    Filter::KEY_VALUE => '1'
                ])
            ]);
            $builder->addFilters([
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                    Filter::KEY_VALUE => '1'
                ])
            ]);
            $builder->addFilters([
                new Filter([
                    Filter::KEY_FIELD => EavAttributeInterface::IS_SEARCHABLE,
                    Filter::KEY_VALUE => '1'
                ])
            ]);
        };
        return $new;
    }
    /**
     * Sort alphabetically by frontend label
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function sortedByLabel()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addSortOrder(new SortOrder([
                SortOrder::FIELD => EavAttributeInterface::FRONTEND_LABEL,
                SortOrder::DIRECTION =>SortOrder::SORT_ASC
            ]));
        };
        return $new;
    }
    /**
     * Sort alphabetically by position
     *
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function sortedByPosition()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addSortOrder(new SortOrder([
                SortOrder::FIELD => EavAttributeInterface::POSITION,
                SortOrder::DIRECTION => SortOrder::SORT_ASC
            ]));
        };
        return $new;
    }
    /**
     * Exclude attributes by code
     *
     * @param array $attributeCodes
     * @return AttributeSearchCriteriaBuilder new, modified builder instance
     */
    public function except(array $attributeCodes)
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $searchCriteriaBuilder) use ($attributeCodes) {
            $searchCriteriaBuilder->addFilter(
                EavAttributeInterface::ATTRIBUTE_CODE,
                count($attributeCodes) > 1 ? $attributeCodes : reset($attributeCodes),
                count($attributeCodes) > 1 ? 'nin' : 'neq'
            );
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