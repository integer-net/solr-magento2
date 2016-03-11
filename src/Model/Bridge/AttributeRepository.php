<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Cms\Model\ResourceModel\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;

class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * AttributeRepository constructor.
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(ProductAttributeRepositoryInterface $attributeRepository, SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param int $storeId
     * @return Attribute[]
     */
    public function getSearchableAttributes($storeId)
    {
        // TODO: Implement getSearchableAttributes() method.
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
        $this->searchCriteriaBuilder->addSortOrder(EavAttributeInterface::FRONTEND_LABEL, AbstractCollection::SORT_ORDER_ASC);
        $this->searchCriteriaBuilder->addFilter(new Filter([
            Filter::KEY_FIELD => EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
            Filter::KEY_VALUE => '1'
        ]));
        $this->searchCriteriaBuilder->addFilter(new Filter([
            Filter::KEY_FIELD => EavAttributeInterface::ATTRIBUTE_CODE,
            Filter::KEY_CONDITION_TYPE => 'neq',
            Filter::KEY_VALUE => 'status'
        ]));
        $result = $this->attributeRepository->getList($this->searchCriteriaBuilder->create());
        return \array_map(function(ProductAttributeInterface $magentoAttribute) {
            return new Attribute($magentoAttribute);
        }, $result->getItems());
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

}