<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;

class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var AttributeSearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * AttributeRepository constructor.
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param AttributeSearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(ProductAttributeRepositoryInterface $attributeRepository,
                                AttributeSearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder->except(['status']);
    }

    /**
     * @param int $storeId
     * @return Attribute[]
     */
    public function getSearchableAttributes($storeId)
    {
        return $this->loadAttributes($storeId, $this->searchCriteriaBuilder->searchable());
    }

    /**
     * Return filterable attributes in current context (catalog or search)
     *
     * @deprecated use getFilterableInSearchAttributes() or getFilterableInCatalogAttributes() directly!
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableAttributes($storeId, $useAlphabeticalSearch = true)
    {
        //TODO deprecate method in interface and eliminate usage. We don't know the context here!
        return $this->getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch);
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        $attributeSearchCriteriaBuilder = $this->searchCriteriaBuilder->filterableInSearch();
        if ($useAlphabeticalSearch) {
            return $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByLabel());
        }
        return $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByPosition());
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch = true)
    {
        $attributeSearchCriteriaBuilder = $this->searchCriteriaBuilder->filterable();
        if ($useAlphabeticalSearch) {
            return $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByLabel());
        }
        return $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByPosition());
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogOrSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        $attributeSearchCriteriaBuilder = $this->searchCriteriaBuilder->filterableInCatalogOrSearch();
        if ($useAlphabeticalSearch) {
            return $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByLabel());
        }
        return $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByPosition());
    }

    /**
     * @return string[]
     */
    public function getAttributeCodesToIndex()
    {
        // TODO: Implement getAttributeCodesToIndex() method.
    }

    /**
     * @param int $storeId
     * @param string $attributeCode
     * @return Attribute
     * @throws Exception
     */
    public function getAttributeByCode($storeId, $attributeCode)
    {
        // TODO: Implement getAttributeByCode() method.
    }

    /**
     * @param $storeId
     * @param AttributeSearchCriteriaBuilder $criteriaBuilder
     * @return array
     */
    private function loadAttributes($storeId, AttributeSearchCriteriaBuilder $criteriaBuilder)
    {
        $result = $this->attributeRepository->getList($criteriaBuilder->create());
        return \array_map(function (AttributeResource $magentoAttribute) use ($storeId) {
            return new Attribute($magentoAttribute, $storeId);
        }, $result->getItems());
    }

}