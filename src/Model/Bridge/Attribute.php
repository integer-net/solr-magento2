<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Attribute as AttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;

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
        return $this->magentoAttribute->getData('solr_boost');
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
        return $this->magentoAttribute->getFrontendInput();
    }

    /**
     * @return bool
     */
    public function getIsSearchable()
    {
        return $this->magentoAttribute->getIsSearchable();
    }

    /**
     * @return string
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

    /**
     * @return string
     */
    public function getInputType()
    {
        // TODO: Implement getInputType() method.
    }

}