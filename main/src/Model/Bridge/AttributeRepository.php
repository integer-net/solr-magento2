<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\Attribute as AttributeInterface;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeRepository implements AttributeRepositoryInterface
{
    private $filterableInSearchAttributes;
    private $filterableInCatalogAttributes;
    private $filterableInCatalogOrSearchAttributes;
    private $sortableAttributes;
    private $searchableAttributes;
    /**
     * Holds attribute instances with their Magento attributes as attached data
     *
     * @var \SplObjectStorage
     */
    private $attributeStorage;

    private $attributeCodesToIndex = null;
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
        $this->attributeStorage = new \SplObjectStorage;
    }

    /**
     * @param int $storeId
     * @return Attribute[]
     */
    public function getSearchableAttributes($storeId)
    {
        if ($this->searchableAttributes === null) {
            $this->searchableAttributes = $this->loadAttributes($storeId, $this->searchCriteriaBuilder->searchable());
        }

        return $this->searchableAttributes;
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        if ($this->filterableInSearchAttributes === null) {
            $attributeSearchCriteriaBuilder = $this->searchCriteriaBuilder->filterableInSearch();
            if ($useAlphabeticalSearch) {
                $this->filterableInSearchAttributes = $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByLabel());
            } else {
                $this->filterableInSearchAttributes = $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByPosition());
            }
        }
        return $this->filterableInSearchAttributes;
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch = true)
    {
        if ($this->filterableInCatalogAttributes === null) {
            $attributeSearchCriteriaBuilder = $this->searchCriteriaBuilder->filterable();
            if ($useAlphabeticalSearch) {
                $this->filterableInCatalogAttributes = $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByLabel());
            } else {
                $this->filterableInCatalogAttributes = $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByPosition());
            }
        }
        return $this->filterableInCatalogAttributes;
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogOrSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        if ($this->filterableInCatalogOrSearchAttributes === null) {
            $attributeSearchCriteriaBuilder = $this->searchCriteriaBuilder->filterableInCatalogOrSearch();
            if ($useAlphabeticalSearch) {
                $this->filterableInCatalogOrSearchAttributes = $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByLabel());
            } else {
                $this->filterableInCatalogOrSearchAttributes = $this->loadAttributes($storeId, $attributeSearchCriteriaBuilder->sortedByPosition());
            }
        }

        return $this->filterableInCatalogOrSearchAttributes;
    }

    /**
     * @param int $storeId
     * @return AttributeInterface[]
     */
    public function getSortableAttributes($storeId)
    {
        if ($this->sortableAttributes === null) {
            $this->sortableAttributes = $this->loadAttributes($storeId, $this->searchCriteriaBuilder->sortable());
        }
        return $this->sortableAttributes;
    }

    /**
     * @return string[]
     */
    public function getAttributeCodesToIndex()
    {
        if ($this->attributeCodesToIndex === null) {
            $attributes = $this->loadAttributes(null, $this->searchCriteriaBuilder->indexable());
            $this->attributeCodesToIndex = \array_map(function (Attribute $attribute) {
                return $attribute->getAttributeCode();
            }, $attributes);
        }
        return $this->attributeCodesToIndex;
    }

    /**
     * @param string $attributeCode
     * @param int|null $storeId
     * @return Attribute
     * @throws Exception
     */
    public function getAttributeByCode($attributeCode, $storeId)
    {
        try {
            $magentoAttribute = $this->attributeRepository->get($attributeCode);
        } catch (NoSuchEntityException $e) {
            throw new Exception(sprintf('Attribute %s does not exist', $attributeCode), 0, $e);
        }
        return $this->registerAttribute($storeId, $magentoAttribute);
    }

    /**
     * @param AttributeInterface $attribute
     * @return AttributeResource
     */
    public function getMagentoAttribute(AttributeInterface $attribute)
    {
        if ($this->attributeStorage->contains($attribute)) {
            return $this->attributeStorage->offsetGet($attribute);
        }
        return null;
    }

    /**
     * @param int|null $storeId
     * @param AttributeSearchCriteriaBuilder $criteriaBuilder
     * @return array
     */
    private function loadAttributes($storeId, AttributeSearchCriteriaBuilder $criteriaBuilder)
    {
        $result = $this->attributeRepository->getList($criteriaBuilder->create());
        return \array_map(function (AttributeResource $magentoAttribute) use ($storeId) {
            return $this->registerAttribute($storeId, $magentoAttribute);
        }, $result->getItems());
    }

    /**
     * @param $storeId
     * @param $magentoAttribute
     * @return Attribute
     */
    private function registerAttribute($storeId, $magentoAttribute)
    {
        $attribute = new Attribute($magentoAttribute, $storeId);
        $this->attributeStorage->attach($attribute, $magentoAttribute);
        return $attribute;
    }

}