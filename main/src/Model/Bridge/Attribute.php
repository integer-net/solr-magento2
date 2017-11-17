<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Attribute as AttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;

class Attribute implements AttributeInterface
{

    /**
     * @var AttributeResource
     */
    protected $magentoAttribute;
    /**
     * @var int|null
     */
    private $storeId;

    /**
     * Note: needs concrete Attribute class for getSource() and getData(), which the EavAttributeInterface
     * does not provide (Magento 2.0).
     *
     * @param AttributeResource $magentoAttribute
     * @param int|null $storeId store id for store label (null for default)
     */
    public function __construct(AttributeResource $magentoAttribute, $storeId = null)
    {
        $this->magentoAttribute = $magentoAttribute;
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->magentoAttribute->getAttributeCode();
    }

    /**
     * @return string
     */
    public function getStoreLabel()
    {
        if ($this->storeId === null) {
            return $this->magentoAttribute->getDefaultFrontendLabel();
        }
        /** @var AttributeFrontendLabelInterface[] $labels */
        $labels = $this->magentoAttribute->getFrontendLabels();
        if (! isset($labels[$this->storeId])) {
            return $this->magentoAttribute->getDefaultFrontendLabel();
        }
        return $labels[$this->storeId]->getLabel();
    }

    /**
     * @return float
     */
    public function getSolrBoost()
    {
        $solrBoost = $this->magentoAttribute->getData('search_weight');
        if ($solrBoost === null) {
            return 1.0;
        }
        return (float) $solrBoost;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return new Source($this->magentoAttribute->getSource());
    }

    /**
     * @return string
     */
    public function getFacetType()
    {
        switch ($this->magentoAttribute->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';
            case 'multiselect':
                return 'multiselect';
            default:
                return 'text';
        }
    }

    /**
     * @return bool
     */
    public function getIsSearchable()
    {
        return $this->magentoAttribute->getIsSearchable();
    }

    /**
     * @return string See constants. 'decimal', 'text', 'int', or 'varchar' (default)
     */
    public function getBackendType()
    {
        return $this->magentoAttribute->getBackendType();
    }

    /**
     * @return bool
     */
    public function getUsedForSortBy()
    {
        return $this->magentoAttribute->getUsedForSortBy();
    }

}