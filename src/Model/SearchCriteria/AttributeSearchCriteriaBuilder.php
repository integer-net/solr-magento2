<?php
namespace IntegerNet\Solr\Model\SearchCriteria;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SimpleBuilderInterface;

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
     * @return AttributeSearchCriteriaBuilder
     */
    public function varchar()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::BACKEND_TYPE,
                Filter::KEY_CONDITION_TYPE => 'in',
                Filter::KEY_VALUE => ['static', 'varchar']
            ]));
            $builder->addFilter(new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::FRONTEND_INPUT,
                Filter::KEY_VALUE => 'text'
            ]));
        };
        return $new;
    }

    public function searchable()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::IS_SEARCHABLE,
                Filter::KEY_VALUE => '1'
            ]));
        };
        return $new;
    }
    public function filterableInSearch()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addFilter(new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                Filter::KEY_VALUE => '1'
            ]));
        };
        return $new;
    }

    public function sortedByLabel()
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $builder) {
            $builder->addSortOrder(EavAttributeInterface::FRONTEND_LABEL, AbstractCollection::SORT_ORDER_ASC);
        };
        return $new;
    }
    /**
     * Exclude attributes by code
     *
     * @param array $attributeCodes
     * @return $this
     */
    public function except(array $attributeCodes)
    {
        $new = clone $this;
        $new->buildCallbacks[] = function(SearchCriteriaBuilder $searchCriteriaBuilder) use ($attributeCodes) {
            $searchCriteriaBuilder->addFilter(new Filter([
                Filter::KEY_FIELD => EavAttributeInterface::ATTRIBUTE_CODE,
                Filter::KEY_CONDITION_TYPE => count($attributeCodes) > 1 ? 'nin' : 'neq',
                Filter::KEY_VALUE => count($attributeCodes) > 1 ? $attributeCodes : reset($attributeCodes)
            ]));
        };
        return $new;
    }

    public function create()
    {
        return $this->getBuilder()->create();
    }

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