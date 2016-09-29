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
use IntegerNet\Solr\Model\Config\CurrentStoreConfig;

/**
 * Plugin to change autosuggest URL
 */
class SearchHelperPlugin
{
    /**
     * @var CurrentStoreConfig
     */
    private $solrStoreConfig;
    public function aroundGetSuggestUrl(\Magento\CatalogSearch\Helper\Data $subject, \Closure $proceed)
    {
        if ($this->solrStoreConfig->getAutosuggestConfig()->isActive()) {
            return $this->solrStoreConfig->getStoreConfig()->getBaseUrl() . '/autosuggest.php';
        }
        return $proceed();
    }
}