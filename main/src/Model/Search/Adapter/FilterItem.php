<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Search\Adapter;

use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Store\Model\StoreManagerInterface;

class FilterItem extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var float
     */
    private $minAvailableValue;
    /**
     * @var float
     */
    private $maxAvailableValue;
    /**
     * @var \Magento\Framework\Search\RequestInterface
     */
    private $request;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Magento\Framework\App\RequestInterface $request,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
    }

    public function isActive()
    {
        $currentValues = $this->request->getParam($this->getFilter()->getRequestVar());
        if (!is_array($currentValues)) {
            $currentValues = [$currentValues];
        }

        return in_array($this->getValue(), $currentValues);
    }

    public function canUsePriceSlider()
    {
        return $this->getFilter() instanceof \Magento\CatalogSearch\Model\Layer\Filter\Price
            || $this->getFilter() instanceof \Magento\CatalogSearch\Model\Layer\Filter\Decimal;
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return float
     */
    public function getMinAvailableValue()
    {
        if (!$this->canUsePriceSlider()) {
            throw new \Exception('This method can only be called on price filters');
        }
        if ($this->minAvailableValue === null) {
            foreach ($this->getFilter()->getItems() as $item) {
                /** @var FilterItem $item */
                $itemMinValue = floatval(current(explode('-', $item->getData('value'))));
                if (($this->minAvailableValue === null) || ($itemMinValue < $this->minAvailableValue)) {
                    $this->minAvailableValue = $itemMinValue;
                }
            }
        }
        return $this->minAvailableValue;
    }

    public function getSelectedMinValue()
    {
        $activeFilters = $this->getFilter()->getLayer()->getState()->getFilters();
        foreach($activeFilters as $activeFilter) {
            if ($activeFilter->getFilter()->getRequestVar() == $this->getFilter()->getRequestVar()) {
                return $activeFilter->getData('value')[0];
            }
        }
        return $this->getMinAvailableValue();
    }

    public function getSelectedMinValueFormatted()
    {
        return $this->priceHelper->currency($this->getSelectedMinValue(), true, false);
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return float
     */
    public function getMaxAvailableValue()
    {
        if (!$this->canUsePriceSlider()) {
            throw new \Exception('This method can only be called on price filters');
        }
        if ($this->maxAvailableValue === null) {
            foreach ($this->getFilter()->getItems() as $item) {
                /** @var FilterItem $item */
                $itemMaxValue = floatval(explode('-', $item->getData('value'))[1]);
                if (($this->maxAvailableValue === null) || ($itemMaxValue > $this->maxAvailableValue)) {
                    $this->maxAvailableValue = $itemMaxValue;
                }
            }
        }
        return $this->maxAvailableValue;
    }

    public function getSelectedMaxValue()
    {
        $activeFilters = $this->getFilter()->getLayer()->getState()->getFilters();
        foreach($activeFilters as $activeFilter) {
            if ($activeFilter->getFilter()->getRequestVar() == $this->getFilter()->getRequestVar()) {
                return $activeFilter->getData('value')[1];
            }
        }
        return $this->getMaxAvailableValue();
    }

    public function getSelectedMaxValueFormatted()
    {
        return $this->priceHelper->currency($this->getSelectedMaxValue() - 0.01, true, false);
    }

    public function getCurrencySymbol()
    {
        if ($this->getFilter() instanceof \Magento\CatalogSearch\Model\Layer\Filter\Price) {
            return $this->storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
        }
        return '';
    }

    public function getFilterIdentifier()
    {
        return $this->getFilter()->getRequestVar();
    }

    /**
     * Get url for filter with "priceRange" as placeholder
     *
     * @return string
     */
    public function getPriceFilterUrlWithPlaceholder()
    {
        $query = [$this->getFilter()->getRequestVar() => 'priceRange'];
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = false;
        return $this->_url->getUrl('*/*/*', $params);
    }

    /**
     * Get initial URL for current filter
     *
     * @return string
     */
    public function getPriceFilterUrlWithCurrentValues()
    {
        return str_replace(
            'priceRange',
            $this->getSelectedMinValue() . '-' . $this->getSelectedMaxValue(),
            $this->getPriceFilterUrlWithPlaceholder()
        );
    }
}
