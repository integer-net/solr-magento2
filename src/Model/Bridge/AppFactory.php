<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;


use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Implementor\Config as ConfigInterface;
use IntegerNet\Solr\Implementor\EventDispatcher as EventDispatcherInterface;
use IntegerNet\Solr\Model\Cache\CacheStorageFactory;
use IntegerNet\Solr\Model\Config\FrontendStoresConfig;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\SolrSuggest\Implementor\CustomHelper;
use IntegerNet\SolrSuggest\Implementor\Factory\AppFactory as AppFactoryInterface;
use IntegerNet\SolrSuggest\Plain\Block\CustomHelperFactory;
use IntegerNet\SolrSuggest\Plain\Cache\CacheWriter;
use IntegerNet\SolrSuggest\Plain\Cache\CacheWriterFactory;
use IntegerNet\SolrSuggest\Plain\Cache\Convert\AttributesToSerializableAttributes;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfigInterface;

class AppFactory implements AppFactoryInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var FrontendStoresConfig
     */
    private $storesConfig;
    /**
     * @var ObjectManagerConfigInterface
     */
    private $objectManagerConfig;
    /**
     * @var CacheWriterFactory
     */
    private $cacheWriterFactory;
    /**
     * @var CacheStorageFactory
     */
    private $cacheStorageFactory;

    public function __construct(AttributeRepositoryInterface $attributeRepository,
                                EventDispatcherInterface $eventDispatcher,
                                FrontendStoresConfig $storesConfig,
                                ObjectManagerConfigInterface $objectManagerConfig,
                                CacheWriterFactory $cacheWriterFactory,
                                CacheStorageFactory $cacheStorageFactory)
    {
        $this->attributeRepository = $attributeRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->storesConfig = $storesConfig;
        $this->objectManagerConfig = $objectManagerConfig;
        $this->cacheWriterFactory = $cacheWriterFactory;
        $this->cacheStorageFactory = $cacheStorageFactory;
    }

    /**
     * @return CacheWriter
     */
    public function getCacheWriter()
    {
        $customHelperClass = new \ReflectionClass(
            $this->objectManagerConfig->getPreference(CustomHelper::class)
        );
        return $this->cacheWriterFactory->create(
            [
                'cache' => $this->cacheStorageFactory->create(),
                'customHelperFactory' => new CustomHelperFactory(
                    $customHelperClass->getFileName(),
                    $customHelperClass->getName()
                ),
                'attributeRepository' => new AttributesToSerializableAttributes(
                    $this->attributeRepository,
                    $this->eventDispatcher,
                    $this->autosuggestConfigByStore()
                )
            ]
        );
    }

    /**
     * @return ConfigInterface[]
     */
    public function getStoreConfig()
    {
        return $this->storesConfig->getArrayCopy();
    }

    /**
     * @return mixed
     */
    private function autosuggestConfigByStore()
    {
        return ArrayCollection::fromTraversable($this->storesConfig)
            ->map(
                function (\IntegerNet\Solr\Implementor\Config $config) {
                    return $config->getAutosuggestConfig();
                },
                ArrayCollection::FLAG_MAINTAIN_NUMERIC_KEYS
            )->getArrayCopy();
    }

}