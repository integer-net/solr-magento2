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


use IntegerNet\Solr\Implementor\Config as ConfigInterface;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Implementor\EventDispatcher as EventDispatcherInterface;
use IntegerNet\Solr\Model\Config\FrontendStoresConfig;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\SolrSuggest\Block\DefaultCustomHelper;
use IntegerNet\SolrSuggest\Implementor\CustomHelper;
use IntegerNet\SolrSuggest\Implementor\Factory\AppFactory as AppFactoryInterface;
use IntegerNet\SolrSuggest\Implementor\SerializableCategoryRepository as SerializableCategoryRepositoryInterface;
use IntegerNet\SolrSuggest\Implementor\TemplateRepository as TemplateRepositoryInterface;
use IntegerNet\SolrSuggest\Plain\Block\CustomHelperFactory;
use IntegerNet\SolrSuggest\Plain\Cache\CacheWriter;
use IntegerNet\SolrSuggest\Plain\Cache\Convert\AttributesToSerializableAttributes;
use IntegerNet\SolrSuggest\Plain\Cache\PsrCache;
use IntegerNet\SolrSuggest\CacheBackend\File\CacheItemPool as FileCacheBackend;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfigInterface;

class AppFactory implements AppFactoryInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var SerializableCategoryRepositoryInterface
     */
    private $serializableCategoryRepository;
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var FrontendStoresConfig
     */
    private $storesConfig;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var ObjectManagerConfigInterface
     */
    private $objectManagerConfig;

    public function __construct(AttributeRepositoryInterface $attributeRepository,
                                SerializableCategoryRepositoryInterface $serializableCategoryRepository,
                                TemplateRepositoryInterface $templateRepository,
                                EventDispatcherInterface $eventDispatcher,
                                FrontendStoresConfig $storesConfig,
                                DirectoryList $directoryList,
                                ObjectManagerConfigInterface $objectManagerConfig)
    {
        $this->attributeRepository = $attributeRepository;
        $this->serializableCategoryRepository = $serializableCategoryRepository;
        $this->templateRepository = $templateRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->storesConfig = $storesConfig;
        $this->directoryList = $directoryList;
        $this->objectManagerConfig = $objectManagerConfig;
    }

    /**
     * @return CacheWriter
     */
    public function getCacheWriter()
    {
        $customHelperClass = new \ReflectionClass(
            $this->objectManagerConfig->getPreference(CustomHelper::class)
        );
        //TODO at least the cache backend should come from DI preferences for easier replacement
        return new CacheWriter(
            new PsrCache(
                new FileCacheBackend(
                    $this->directoryList->getPath(DirectoryList::CACHE) . '/integernet_solr'
                )
            ),
            new AttributesToSerializableAttributes(
                $this->attributeRepository,
                $this->eventDispatcher,
                $this->autosuggestConfigByStore()
            ),
            $this->serializableCategoryRepository,
            new CustomHelperFactory($customHelperClass->getFileName(), $customHelperClass->getName()),
            $this->eventDispatcher,
            $this->templateRepository
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