<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\CatalogSearch\Layer extends \Magento\CatalogSearch\Model\Layer 
{
    /**
     * Get current layer product collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getProductCollection()
    {
        if (!$this->_helperData->isActive()) {
            return parent::getProductCollection();
        }

        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->_resultCollectionFactory->create();
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        return $collection;
    }

    /**
     * Get collection of all filterable attributes for layer products set
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getFilterableAttributes()
    {
        if (!$this->_helperData->isActive()) {
            return parent::getFilterableAttributes();
        }

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
        $collection = $this->_attributeCollection;
        $collection
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->addStoreLabel($this->_modelStoreManagerInterface->getStore()->getId())
            ->addIsFilterableInSearchFilter()
            ->setOrder('position', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        return $collection;
    }

}