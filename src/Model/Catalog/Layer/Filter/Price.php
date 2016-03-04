<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Model\Catalog\Layer\Filter\Price extends \Magento\Catalog\Model\Layer\Filter\Price 
{
    /**
     * Get price range for building filter steps
     *
     * @return int
     */
    public function getPriceRange()
    {
        if (!$this->_helperData->isActive()) {
            return parent::getPriceRange();
        }

        if ($this->_appRequestInterface->getModuleName() != 'catalogsearch' && !$this->_helperData->isCategoryPage()) {
            return parent::getPriceRange();
        }

        return $this->_configScopeConfigInterface->getValue('integernet_solr/results/price_step_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Apply price range filter
     *
     * @param \Zend\Controller\Request\AbstractRequest $request
     * @param $filterBlock
     *
     * @return \Magento\Catalog\Model\Layer\Filter\Price
     */
    public function apply(\Zend\Controller\Request\AbstractRequest $request, $filterBlock)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        foreach(explode(',', $filter) as $subFilter) {

            //validate filter
            $filterParams = explode(',', $subFilter);
            $subFilter = $this->_validateFilter($filterParams[0]);
            if (!$subFilter) {
                return $this;
            }

            list($from, $to) = $subFilter;

            $this->setInterval([$from, $to]);

            $priorFilters = [];
            for ($i = 1; $i < count($filterParams); ++$i) {
                $priorFilter = $this->_validateFilter($filterParams[$i]);
                if ($priorFilter) {
                    $priorFilters[] = $priorFilter;
                } else {
                    //not valid data
                    $priorFilters = [];
                    break;
                }
            }
            if ($priorFilters) {
                $this->setPriorIntervals($priorFilters);
            }

            $this->_applyPriceRange();
            $this->getLayer()->getState()->addFilter($this->_createItem(
                $this->_renderRangeLabel(empty($from) ? 0 : $from, $to),
                $filter
            ));
        }

        return $this;
    }
}