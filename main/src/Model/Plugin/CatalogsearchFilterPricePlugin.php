<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Price as Subject;
use Magento\Framework\App\RequestInterface;

/**
 * Plugin to display multiple filters for same attribute in state block
 */
class CatalogsearchFilterPricePlugin
{
    /**
     * @var FilterItemFactory
     */
    private $filterItemFactory;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory
     */
    private $dataProviderFactory;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory
    ) {
        $this->filterItemFactory = $filterItemFactory;
        $this->priceCurrency = $priceCurrency;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    public function aroundApply(Subject $subject, \Closure $proceed, RequestInterface $request)
    {
        $attributeCode = $subject->getRequestVar();
        $attributeValue = $request->getParam($attributeCode);

        if ((!$attributeValue) || !is_array($attributeValue)) {
            return $proceed($request);
        }

        $fromParts = [];
        $toParts = [];
        foreach ($attributeValue as $attributeValueArrayPart) {
            $filterParams = explode(',', $attributeValueArrayPart);
            $dataProvider = $this->getDataProvider($subject);
            $filter = $dataProvider->validateFilter($filterParams[0]);
            if (!$filter) {
                continue;
            }

            $dataProvider->setInterval($filter);
            $priorFilters = $dataProvider->getPriorFilters($filterParams);
            if ($priorFilters) {
                $dataProvider->setPriorIntervals($priorFilters);
            }

            list($from, $to) = $filter;

            $fromParts[] = $from;
            $toParts[] = empty($to) || $from == $to ? $to : $to - Subject::PRICE_DELTA;

            $subject->getLayer()->getState()->addFilter(
                $this->_createItem($subject, $this->_renderRangeLabel($subject, empty($from) ? 0 : $from, $to), $filter)
            );
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $subject->getLayer()
            ->getProductCollection();
        $productCollection->addFieldToFilter(
            'price',
            ['from' => $fromParts, 'to' =>  $toParts]
        );


        return $subject;
    }

    /**
     * Create filter item object
     *
     * @param   Subject $subject
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected function _createItem(Subject $subject, $label, $value, $count = 0)
    {
        return $this->filterItemFactory->create()
            ->setFilter($subject)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }

    /**
     * Prepare text of range label
     *
     * @param Subject $subject
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return float|\Magento\Framework\Phrase
     */
    protected function _renderRangeLabel(Subject $subject, $fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->getDataProvider($subject)->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }

            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * @param Subject $subject
     * @return \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private function getDataProvider(Subject $subject)
    {
        return $this->dataProviderFactory->create(['layer' => $subject->getLayer()]);
    }

    /**
     * @param Subject $subject
     * @param string $attributeValue
     */
    private function addFilterToState(Subject $subject, $attributeValue)
    {

    }
}
