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
use IntegerNet\SolrCategories\Request\CategoryRequest;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;

/**
 * Creates search request for Solr library based on Magento request
 *
 * @package IntegerNet\Solr\Model\Search\Adapter
 */
class SearchRequestConverter
{
    /**
     * @var \IntegerNet\Solr\Implementor\SolrRequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var SearchRequest
     */
    private $searchRequest;

    /**
     * @param SolrRequestFactoryInterface $requestFactory
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     * @param SearchRequest $searchRequest
     */
    public function __construct(
        \IntegerNet\Solr\Implementor\SolrRequestFactoryInterface $requestFactory,
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory,
        SearchRequest $searchRequest
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->searchRequest = $searchRequest;
    }

    /**
     * @return \IntegerNet\Solr\Request\Request
     */
    public function convert(RequestInterface $request)
    {
        /*
         * Search term in request is ignored here because the library fetches it from the application context
         * via \IntegerNet\Solr\Model\Bridge\SearchRequest and \Magento\Search\Model\Query
         *
         * This should better be changed in M1 and M2, but in a backwards compatible way
         */
        return $this->requestFactory->getSolrRequest(
            SolrRequestFactoryInterface::REQUEST_MODE_SEARCH
        );
    }
}