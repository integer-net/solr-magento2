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
use IntegerNet\Solr\Request\SearchRequest;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates search request for Solr library based on Magento request
 *
 * @package IntegerNet\Solr\Model\Search\Adapter
 */
class SearchRequestConverter
{
    /**
     * @var SolrRequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var FilterConverter
     */
    private $filterConverter;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SolrRequestFactoryInterface $requestFactory
     * @param FilterConverter $filterConverter
     * @param ResponseFactory $responseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        SolrRequestFactoryInterface $requestFactory,
        FilterConverter $filterConverter,
        ResponseFactory $responseFactory,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->filterConverter = $filterConverter;
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $magentoRequest
     * @return \IntegerNet\Solr\Request\Request
     * @throws \IntegerNet\Solr\Exception
     */
    public function convert(RequestInterface $magentoRequest)
    {
        /*
         * Search term in request is ignored here because the library fetches it from the application context
         * via \IntegerNet\Solr\Model\Bridge\SearchRequest and \Magento\Search\Model\Query
         *
         * This should better be changed in M1 and M2, but in a backwards compatible way
         */
        if (! $magentoRequest->getQuery() instanceof BoolExpression) {
            $this->logger->notice(sprintf('[SOLR] Unknown query type %s', get_class($magentoRequest->getQuery())));
            return $this->createSolrRequest();
        }
        return $this->applyFilters(new Request($magentoRequest), $this->createSolrRequest());
    }

    /**
     * @return SearchRequest
     */
    private function createSolrRequest()
    {
        /** @var SearchRequest $solrRequest */
        $solrRequest = $this->requestFactory->getSolrRequest(
            SolrRequestFactoryInterface::REQUEST_MODE_SEARCH
        );
        return $solrRequest;
    }

    /**
     * @param $source
     * @param $target
     * @return SearchRequest
     * @throws \IntegerNet\Solr\Exception
     */
    private function applyFilters(Request $source, SearchRequest $target)
    {
        $fqBuilder = $target->getFilterQueryBuilder();
        foreach ($source->filters() as $filter) {
            $this->filterConverter->configure($fqBuilder, $filter, $source->storeId());
        }
        return $target;
    }
}