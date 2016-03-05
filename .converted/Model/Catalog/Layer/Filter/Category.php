<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\Catalog\Layer\Filter\Category extends \Magento\Catalog\Model\Layer\Filter\Category
{


    /**
     * Apply category filter to layer
     *
     * @param   \Zend\Controller\Request\AbstractRequest $request
     * @param   \Magento\Framework\View\Element\AbstractBlock $filterBlock
     * @return  \Magento\Catalog\Model\Layer\Filter\Category
     */
    public function apply(\Zend\Controller\Request\AbstractRequest $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        foreach(explode(',', $filter) as $subFilter) {

            $this->_categoryId = $subFilter;

            $this->_frameworkRegistry->register('current_category_filter', $this->getCategory(), true);

            $this->_appliedCategory = $this->_modelCategoryFactory->create()
                ->setStoreId($this->_modelStoreManagerInterface->getStore()->getId())
                ->load($subFilter);

            if ($this->_isValidCategory($this->_appliedCategory)) {
                $this->getLayer()->getProductCollection()
                    ->addCategoryFilter($this->_appliedCategory);

                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($this->_appliedCategory->getName(), $filter)
                );
            }
        }

        return $this;
    }
}