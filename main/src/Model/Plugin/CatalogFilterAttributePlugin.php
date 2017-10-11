<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use Magento\Catalog\Model\Layer\Search\Filter\Attribute as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use IntegerNet\Solr\Model\Source\FilterOptionSorting as FilterOptionSortingSource;

class CatalogFilterAttributePlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
