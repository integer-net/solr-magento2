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

use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\AttributeRepository as AttributeRepositoryInterface;
use IntegerNet\Solr\Implementor\EventDispatcher as EventDispatcherInterface;
use IntegerNet\Solr\Implementor\Pagination as PaginationInterface;
use IntegerNet\Solr\Implementor\SolrRequestFactoryInterface;
use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use IntegerNet\Solr\Request\ApplicationContext;
use IntegerNet\Solr\Request\Request;
use IntegerNet\Solr\Request\SearchRequestFactory;
use IntegerNet\Solr\Resource\ResourceFacade;
use IntegerNet\SolrCategories\Request\CategoryRequestFactory;
use IntegerNet\SolrCategories\Request\CategorySearchRequestFactory;
use Psr\Log\LoggerInterface;

class RequestFactory implements SolrRequestFactoryInterface
{
    /**
     * @var CurrentStoreConfig
     */
    private $storeConfig;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SearchRequest
     */
    private $searchRequest;
    /**
     * @var PaginationInterface
     */
    private $pagination;

    /**
     * @param CurrentStoreConfig $storeConfig
     * @param AttributeRepositoryInterface $attributeRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     * @param SearchRequest $searchRequest
     * @param PaginationInterface $pagination
     */
    public function __construct(CurrentStoreConfig $storeConfig, AttributeRepositoryInterface $attributeRepository,
                                EventDispatcherInterface $eventDispatcher, LoggerInterface $logger,
                                SearchRequest $searchRequest, PaginationInterface $pagination)
    {
        $this->storeConfig = $storeConfig;
        $this->attributeRepository = $attributeRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->searchRequest = $searchRequest;
        $this->pagination = $pagination;
    }

    /**
     * Returns new configured Solr recource
     *
     * @deprecated should not be used directly from application
     * @return ResourceFacade
     */
    public function getSolrResource()
    {
        return new ResourceFacade([$this->storeConfig->getStoreId() => $this->storeConfig]);
    }

    /**
     * Returns new Solr service (search, autosuggest or category service, depending on application state or parameter)
     *
     * @param int $requestMode
     * @return Request
     * @throws \Exception
     */
    public function getSolrRequest($requestMode = self::REQUEST_MODE_AUTODETECT)
    {
        //TODO implement different modes
        //TODO possibly use Magentos DI with virtual types for ApplicationContext
        // (pagination is already a virtual type)
        $applicationContext = new ApplicationContext(
            $this->attributeRepository,
            $this->storeConfig->getResultsConfig(),
            $this->storeConfig->getAutosuggestConfig(),
            $this->eventDispatcher,
            $this->logger
        );
        $applicationContext->setPagination($this->pagination);

        if ($requestMode === self::REQUEST_MODE_SEARCH) {
            $applicationContext->setFuzzyConfig($this->storeConfig->getFuzzySearchConfig());
            $applicationContext->setQuery($this->searchRequest);
            $factory = new SearchRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $this->storeConfig->getStoreId()
            );
            return $factory->createRequest();
        }

        if ($requestMode === self::REQUEST_MODE_CATEGORY) {
            $applicationContext->setCategoryConfig($this->storeConfig->getCategoryConfig());
            $factory = new CategoryRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $this->storeConfig->getStoreId(),
                $this->searchRequest->getCategoryId()
            );
            return $factory->createRequest();
        }

        if ($requestMode === self::REQUEST_MODE_CATEGORY_SEARCH) {
            $applicationContext->setCategoryConfig($this->storeConfig->getCategoryConfig());
            $applicationContext->setQuery($this->searchRequest);
            $factory = new CategorySearchRequestFactory(
                $applicationContext,
                $this->getSolrResource(),
                $this->storeConfig->getStoreId()
            );
            return $factory->createRequest();
        }

        throw new \Exception(sprintf('Request Mode "%s" is not implemented yet.', $requestMode));
    }

}