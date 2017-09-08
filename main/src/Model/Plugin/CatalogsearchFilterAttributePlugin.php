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
use Magento\CatalogSearch\Model\Layer\Filter\Attribute as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use IntegerNet\Solr\Model\Source\FilterOptionSorting as FilterOptionSortingSource;

/**
 * Plugin to display multiple filters for same attribute in state block
 */
class CatalogsearchFilterAttributePlugin
{
    /**
     * @var FilterItemFactory
     */
    private $filterItemFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filterItemFactory = $filterItemFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundApply(Subject $subject, \Closure $proceed, RequestInterface $request)
    {
        $attribute = $subject->getAttributeModel();
        $attributeValue = $request->getParam($attribute->getAttributeCode());
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $attributeValueArrayPart) {
                if (!is_numeric($attributeValueArrayPart)) {
                    return $this;
                }
            }
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $subject->getLayer()
            ->getProductCollection();
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $attributeValueArrayPart) {
                $this->addFilterToState($subject, $attributeValueArrayPart);
            }
        } else {
            $this->addFilterToState($subject, $attributeValue);
        }
        //$this->setItems([]); // set items to disable show filtering
        return $this;
    }

    protected function addFilterToState(Subject $subject, $attributeValue)
    {
        $attribute = $subject->getAttributeModel();

        $label = $attribute->getFrontend()->getOption($attributeValue);
        $subject->getLayer()
            ->getState()
            ->addFilter($this->_createItem($subject, $label, $attributeValue));
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

    public function afterGetItems(Subject $subject, array $items)
    {
        switch ($this->scopeConfig->getValue('integernet_solr/results/filter_option_sorting')) {
            case FilterOptionSortingSource::FILTER_SORTING_RESULTS_COUNT:
                usort($items, function ($a, $b) {
                    return $b['count'] - $a['count'];
                });
                break;
            case FilterOptionSortingSource::FILTER_SORTING_ALPHABET:
                usort($items, function ($a, $b) {
                    return strcasecmp($a['label'], $b['label']);
                });
                break;
        }

        return $items;
    }
}
