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

use IntegerNet\Solr\Model\Source\FilterOptionSorting as FilterOptionSortingSource;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as FilterItemDataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\StripTags as StripTagsFilter;
use Magento\Store\Model\StoreManagerInterface;

class AttributeFilter extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        FilterItemDataBuilder $itemDataBuilder,
        StripTagsFilter $tagFilter,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $tagFilter, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * @param int $optionCount Count of search results with this option
     * @param int $totalSize Current search results count
     * @return bool
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        return true;
    }

    /**
     * Sort filter options
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $itemsData = parent::_getItemsData();

        switch ($this->scopeConfig->getValue('integernet_solr/results/filter_option_sorting')) {
            case FilterOptionSortingSource::FILTER_SORTING_RESULTS_COUNT:
                usort($itemsData, function ($a, $b) {
                    return $b['count'] - $a['count'];
                });
                break;
            case FilterOptionSortingSource::FILTER_SORTING_ALPHABET:
                usort($itemsData, function ($a, $b) {
                    return strcasecmp($a['label'], $b['label']);
                });
                break;
        }

        return $itemsData;
    }
}
