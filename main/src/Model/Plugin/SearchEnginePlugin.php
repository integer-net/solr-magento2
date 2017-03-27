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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;

/**
 * Plugin to set search engine per store view based on module configuration
 *
 * Since \Magento\Search\Model\EngineResolver is not used everywhere and can only have one scope,
 * we have to plug in the scope configuration directly
 */
class SearchEnginePlugin
{
    const ENGINE_INTEGERNET_SOLR = 'integernet_solr';
    const ENGINE_DEFAULT = 'mysql';
    /**
     * @var CurrentStoreConfig
     */
    private $currentStoreConfig;
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(CurrentStoreConfig $currentStoreConfig, Registry $registry)
    {
        $this->currentStoreConfig = $currentStoreConfig;
        $this->registry = $registry;
    }

    public function aroundGetValue(ScopeConfigInterface $subject, \Closure $proceed, $path,
                                   $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        if ($path === \Magento\CatalogSearch\Model\ResourceModel\EngineInterface::CONFIG_ENGINE_PATH) {
            if ($this->registry->registry('current_category')) {
                return self::ENGINE_DEFAULT;
            }
            if ($this->currentStoreConfig->getGeneralConfig()->isActive()) {
                return self::ENGINE_INTEGERNET_SOLR;
            }
            $configuredValue = $proceed($path, $scopeType, $scopeCode);
            if ($configuredValue === self::ENGINE_INTEGERNET_SOLR) {
                return self::ENGINE_DEFAULT;
            }
        }
        return $proceed($path, $scopeType, $scopeCode);
    }
}