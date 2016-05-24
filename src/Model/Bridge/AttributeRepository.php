<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Cms\Model\ResourceModel\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
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
        $this->searchCriteriaBuilder = $searchCriteriaBuilder->sortedByLabel()->except(['status']);
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
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableAttributes($storeId, $useAlphabeticalSearch = true)
    {
        // TODO: Implement getFilterableAttributes() method.
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        return $this->loadAttributes($storeId, $this->searchCriteriaBuilder->filterableInSearch());
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch = true)
    {
        // TODO: Implement getFilterableInCatalogAttributes() method.
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogOrSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        // TODO: Implement getFilterableInCatalogOrSearchAttributes() method.
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