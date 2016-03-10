<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;

class AttributeRepository implements AttributeRepositoryInterface
{
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
        // TODO: Implement getFilterableInSearchAttributes() method.
        return array();
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