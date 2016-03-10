<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Attribute as AttributeInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;

class Attribute implements AttributeInterface
{
    /**
     * @var EavAttributeInterface
     */
    protected $magentoAttribute;

    /**
     * @param AttributeResource $magentoAttribute
     */
    public function __construct(AttributeResource $magentoAttribute)
    {
        $this->magentoAttribute = $magentoAttribute;
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
        return $this->magentoAttribute->getDefaultFrontendLabel();
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

}