<?php

namespace IntegerNet\Solr\Fixtures;

use Magento\Framework\App\Config\MutableScopeConfigInterface;

class SolrConfig
{
    /**
     * @var array
     */
    private $globalConfig;
    /**
     * @var MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    public function __construct(array $globalConfig, MutableScopeConfigInterface $mutableScopeConfig = null)
    {
        $this->globalConfig = $globalConfig;
        $this->mutableScopeConfig = $mutableScopeConfig ?? self::mutableScopeConfig();
    }

    /**
     * Load Solr server configuration from _files/solr_config[.dist].php into Magento configuration
     */
    public static function loadFromConfigFile()
    {
        if (file_exists(__DIR__ . '/../_files/solr_config.php')) {
            $configValues = (array)include __DIR__ . '/../_files/solr_config.php';
        } else {
            $configValues = (array)include __DIR__ . '/../_files/solr_config.dist.php';
        }
        $config = new self($configValues);
        $config->apply();
    }

    /**
     * Load any global configuration values into Magento configuration
     *
     * @param string[] $configValues Array in the form [config path => value]
     */
    public static function loadAdditional(array $configValues)
    {
        $config = new self($configValues);
        $config->apply();
    }

    public function apply()
    {
        foreach ($this->globalConfig as $path => $value) {
            $this->mutableScopeConfig->setValue($path, $value, 'default');
            $this->mutableScopeConfig->setValue($path, $value, 'stores', 'default');
        }
    }

    private static function mutableScopeConfig(): MutableScopeConfigInterface
    {
        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        return $objectManager->create(MutableScopeConfigInterface::class);
    }
}