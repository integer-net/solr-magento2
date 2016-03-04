<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Implementor\Attribute;

class Integer\Net\Solr\Model\Bridge\Product implements Product
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param \Magento\Catalog\Model\Product $_product
     */
    public function __construct(\Magento\Catalog\Model\Product $_product, \Magento\Catalog\Model\Product\Visibility $productVisibility, 
        \Magento\Tax\Helper\Data $helperData, 
        \Integer\Net\Solr\Model\Bridge\Attributerepository $bridgeAttributerepository, 
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection, 
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface, 
        \Magento\CatalogInventory\Helper\Data $catalogInventoryHelperData)
    {
        $this->_productVisibility = $productVisibility;
        $this->_helperData = $helperData;
        $this->_bridgeAttributerepository = $bridgeAttributerepository;
        $this->_productCollection = $productCollection;
        $this->_eventManagerInterface = $eventManagerInterface;
        $this->_catalogInventoryHelperData = $catalogInventoryHelperData;

        $this->_product = $_product;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getMagentoProduct()
    {
        return $this->_product;
    }


    public function getId()
    {
        return $this->_product->getId();
    }

    public function getStoreId()
    {
        return $this->_product->getStoreId();
    }

    public function isVisibleInCatalog()
    {
        return intval(in_array($this->_product->getVisibility(),
            $this->_productVisibility->getVisibleInCatalogIds()));
    }

    public function isVisibleInSearch()
    {
        return intval(in_array($this->_product->getVisibility(),
            $this->_productVisibility->getVisibleInSearchIds()));
    }

    public function getSolrBoost()
    {
        $this->_product->getData('solr_boost');
    }

    public function getPrice()
    {
        $price = $this->_product->getFinalPrice();
        if ($price == 0) {
            $price = $this->_product->getMinimalPrice();
        }
        $price = $this->_helperData->getPrice($this->_product, $price, null, null, null, null, $this->_product->getStoreId());
        return $price;
    }

    public function getAttributeValue(Attribute $attribute)
    {
        return $this->_product->getData($attribute->getAttributeCode());
    }

    public function getSearchableAttributeValue(Attribute $attribute)
    {
        $magentoAttribute = $this->_bridgeAttributerepository->getMagentoAttribute($attribute);
        $value = trim(strip_tags($magentoAttribute->getFrontend()->getValue($this->_product)));
        $attributeCode = $attribute->getAttributeCode();
        if ($magentoAttribute->getData('backend_type') == 'int'
            && $magentoAttribute->getData('frontend_input') == 'select' 
            && $this->_product->getData($attributeCode) == 0) {
            return null;
        }
        if (! empty($value) && $attribute->getFacetType() == Attribute::FACET_TYPE_MULTISELECT) {
            $value = array_map('trim', explode(',', $value));
        }
        return $value;
    }


    public function getCategoryIds()
    {
        return $this->_product->getCategoryIds();
    }


    /**
     * @return \IntegerNet\Solr\Implementor\ProductIterator
     */
    public function getChildren()
    {
        $childProductIds = $this->_product->getTypeInstance(true)->getChildrenIds($this->_product->getId());

        if (sizeof($childProductIds) && is_array(current($childProductIds))) {
            $childProductIds = current($childProductIds);
        }

        if (!sizeof($childProductIds)) {
            throw new \Exception('Product ' . $this->_product->getSku() . ' doesn\'t have any child products.');
        }

        /** @var $childProductCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $childProductCollection = $this->_productCollection
            ->setStoreId($this->_product->getStoreId())
            ->addAttributeToFilter('entity_id', ['in' => $childProductIds])
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
            ->addAttributeToSelect($this->_bridgeAttributerepository->getAttributeCodesToIndex());

        return new Integer\Net\Solr\Model\Bridge\ProductIterator($childProductCollection);

    }

    /**
     * @return int
     */
    public function getSolrId()
    {
        return $this->getId() . '_' . $this->getStoreId();
    }

    /**
     * @return bool
     */
    public function isIndexable()
    {
        $this->_eventManagerInterface->dispatch('integernet_solr_can_index_product', ['product' => $this->_product]);

        if ($this->_product->getSolrExclude()) {
            return false;
        }
        if ($this->_product->getStatus() != \Magento\Catalog\Model\Product\Status::STATUS_ENABLED) {
            return false;
        }
        if (!in_array($this->_product->getVisibility(), $this->_productVisibility->getVisibleInSiteIds())) {
            return false;
        }
        if (!in_array($this->_product->getStore()->getWebsiteId(), $this->_product->getWebsiteIds())) {
            return false;
        }
        if (!$this->_product->getStockItem()->getIsInStock() && !$this->_catalogInventoryHelperData->isShowOutOfStock()) {
            return false;
        }
        return true;

    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @deprecated only use interface methods!
     */
    public function __call($method, $args)
    {
        return call_user_func_arrayfunc([$this->_product, $method], $args);
    }
}