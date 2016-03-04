<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

if (@class_exists('Go\Magento\Navigation\Model\ResourceModel\Layer\Filter\Price')) {
    class Integer\Net\Solr\Model\ResourceModel\Catalog\Layer\Filter\Price\AbstractPrice extends Go\Magento\Navigation\Model\ResourceModel\Layer\Filter\Price
    {}
} else {
    class Integer\Net\Solr\Model\ResourceModel\Catalog\Layer\Filter\Price\AbstractPrice extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
    {}
}

class Integer\Net\Solr\Model\ResourceModel\Catalog\Layer\Filter\Price extends Integer\Net\Solr\Model\ResourceModel\Catalog\Layer\Filter\Price\AbstractPrice
{
    /**
     * Retrieve maximal price for attribute
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @return float
     */
    public function getMaxPrice($filter)
    {
        if (!$this->_helperData->isActive()) {
            return parent::getMaxPrice($filter);
        }

        if ($this->_appRequestInterface->getModuleName() != 'catalogsearch' && !$this->_helperData->isCategoryPage()) {
            return parent::getMaxPrice($filter);
        }

        /** @var \Apache\Solr\Response $result */
        $result = $this->_modelResult->getSolrResult();
        if (isset($result->stats->stats_fields->price_f->max)) {
            return $result->stats->stats_fields->price_f->max;
        }
        
        return 0;
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @param int $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        if (!$this->_helperData->isActive()) {
            return parent::getCount($filter, $range);
        }

        if ($this->_appRequestInterface->getModuleName() != 'catalogsearch' && !$this->_helperData->isCategoryPage()) {
            return parent::getCount($filter, $range);
        }

        /** @var \Apache\Solr\Response $result */
        $result = $this->_modelResult->getSolrResult();
        if (isset($result->facet_counts->facet_intervals->price_f)) {
            $counts = [];
            $i = 1;
            foreach($result->facet_counts->facet_intervals->price_f as $borders => $qty) {
                if ($qty) {
                    $counts[$i] = $qty;
                }
                $i++;
            }
            return $counts;
        }

        if (isset($result->facet_counts->facet_ranges->price_f->counts)) {
            $counts = [];
            $stepSize = $this->_configScopeConfigInterface->getValue('integernet_solr/results/price_step_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($stepSize <= 0) {
                return [];
            }
            foreach($result->facet_counts->facet_ranges->price_f->counts as $lowerEndPrice => $qty) {
                $counts[intval($lowerEndPrice / $stepSize) + 1] = $qty;
            }
            return $counts;
        }

        return [];
    }

    /**
     * Apply price range filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Price $filter
     * @return \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    public function applyPriceRange($filter)
    {
        if (!$this->_helperData->isActive()) {
            return parent::applyPriceRange($filter);
        }

        if ($this->_appRequestInterface->getModuleName() != 'catalogsearch' && !$this->_helperData->isCategoryPage()) {
            return parent::applyPriceRange($filter);
        }

        $interval = $filter->getInterval();
        if (!$interval) {
            return $this;
        }

        list($from, $to) = $interval;
        if ($from === '' && $to === '') {
            return $this;
        }

        $priceFilters = $this->_frameworkRegistry->registry('price_filters');
        if (!is_array($priceFilters)) {
            $priceFilters = [];
        }
        $priceFilters[] = [
            'min' => $from,
            'max' => $to,
        ];
        $this->_frameworkRegistry->unregister('price_filters');
        $this->_frameworkRegistry->register('price_filters', $priceFilters);

        return $this;

    }
}