<?php
namespace IntegerNet\Solr\Model\SearchCriteria;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SimpleBuilderInterface;

class VarcharAttributes implements SimpleBuilderInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * VarcharAttributes constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $searchCriteriaBuilder->addSortOrder('frontend_label', AbstractCollection::SORT_ORDER_ASC);
        $searchCriteriaBuilder->addFilter(new Filter([
            Filter::KEY_FIELD => 'backend_type',
            Filter::KEY_CONDITION_TYPE => 'in',
            Filter::KEY_VALUE => ['static', 'varchar']
        ]));
        $searchCriteriaBuilder->addFilter(new Filter([
            Filter::KEY_FIELD => 'frontend_input',
            Filter::KEY_VALUE => 'text'
        ]));
    }

    /**
     * Exclude attributes by code
     *
     * @param array $attributeCodes
     * @return $this
     */
    public function except(array $attributeCodes)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $searchCriteriaBuilder->addFilter(new Filter([
            Filter::KEY_FIELD => 'attribute_code',
            Filter::KEY_CONDITION_TYPE => 'nin',
            Filter::KEY_VALUE => $attributeCodes
        ]));
        return $this;
    }

    public function create()
    {
        return $this->searchCriteriaBuilder->create();
    }

    public function getData()
    {
        return $this->searchCriteriaBuilder->getData();
    }

}