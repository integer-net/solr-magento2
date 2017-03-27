<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Search\Adapter;


use IntegerNet\Solr\Implementor\SolrRequestFactoryInterface;
use IntegerNet\Solr\Model\Bridge\SearchRequest;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates category request for Solr library based on Magento request
 *
 * @package IntegerNet\Solr\Model\Search\Adapter
 */
class CategoryRequestConverter
{
    /**
     * @var SolrRequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var SearchRequest
     */
    private $searchRequest;

    /**
     * @param SolrRequestFactoryInterface $requestFactory
     * @param ResponseFactory $responseFactory
     * @param SearchRequest $searchRequest
     */
    public function __construct(
        SolrRequestFactoryInterface $requestFactory,
        FilterConverter $filterConverter,
        ResponseFactory $responseFactory,
        LoggerInterface $logger,
        SearchRequest $searchRequest
    )
    {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->filterConverter = $filterConverter;
        $this->logger = $logger;
        $this->searchRequest = $searchRequest;
    }

    /**
     * @return \IntegerNet\Solr\Request\Request
     * @throws \IntegerNet\Solr\Exception
     */
    public function convert(\Magento\Framework\Search\RequestInterface $magentoRequest)
    {
        /** @var BoolExpression $queryExpression */
        $queryExpression = $magentoRequest->getQuery();
        if (! $queryExpression instanceof BoolExpression) {
            $this->logger->notice(sprintf('[SOLR] Unknown query type %s', get_class($queryExpression)));
        }
        $solrRequest = $this->createSolrRequest($queryExpression);
        $fqBuilder = $solrRequest->getFilterQueryBuilder();
        foreach ($this->getFiltersFromQuery($queryExpression) as $filter) {
            /*
             * Categories are not filtered but searched via query text
             * (see \IntegerNet\SolrCategories\Query\CategoryQueryBuilder)
             */
            if ($filter->getName() !== 'category') {
                $this->filterConverter->configure($fqBuilder, $filter, $this->getStoreIdFromRequest($magentoRequest));
            }
        }

        return $solrRequest;
    }


    /**
     * @param RequestInterface $magentoRequest
     * @return int|mixed
     */
    private function getStoreIdFromRequest(RequestInterface $magentoRequest)
    {
        $storeId = 1;
        foreach ($magentoRequest->getDimensions() as $dimension) {
            if ($dimension->getName() === 'scope') {
                $storeId = $dimension->getValue();
                break;
            }
        }
        return $storeId;
    }

    /**
     * @param BoolExpression $query
     * @return Filter[]
     */
    private function getFiltersFromQuery(BoolExpression $query)
    {
        /** @var Filter[] $filters */
        $filters = array_filter(
            array_merge($query->getMust(), $query->getShould()),
            function ($part) {
                return $part instanceof Filter;
            }
        );
        return $filters;
    }

    /**
     * @param BoolExpression $queryExpression
     * @return \IntegerNet\SolrCategories\Request\CategoryRequest
     */
    private function createSolrRequest(BoolExpression $queryExpression)
    {
        /** @var Filter $queryFilter */
        $queryFilter = $queryExpression->getMust()['category'];
        /** @var Term $queryFilterTerm */
        $queryFilterTerm = $queryFilter->getReference();
        $categoryId = $queryFilterTerm->getValue();
        $this->searchRequest->setCategoryId($categoryId);

        /** @var \IntegerNet\SolrCategories\Request\CategoryRequest $solrRequest */
        $solrRequest = $this->requestFactory->getSolrRequest(
            SolrRequestFactoryInterface::REQUEST_MODE_CATEGORY
        );
        return $solrRequest;
    }
}
