<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Search\Adapter;

use IntegerNet\Solr\Implementor\SolrRequestFactory;
use IntegerNet\Solr\Model\Bridge\SearchRequest;
use IntegerNet\SolrCategories\Request\CategoryRequest;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;

/**
 * Custom search adapter, only used in non-HTML mode (fetch ids from solr, load products from Magento)
 */
class SolrAdapter implements AdapterInterface
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    protected $responseFactory;
    /**
     * @var \IntegerNet\Solr\Implementor\SolrRequestFactory
     */
    private $requestFactory;
    /**
     * @var SearchRequest
     */
    private $searchRequest;

    /**
     * @param SolrRequestFactory $requestFactory
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     * @param SearchRequest $searchRequest
     */
    public function __construct(
        \IntegerNet\Solr\Implementor\SolrRequestFactory $requestFactory,
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory,
        SearchRequest $searchRequest
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->searchRequest = $searchRequest;
    }
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        if ($request->getName() === 'catalog_view_container') {
            /** @var BoolExpression $queryExpression */
            $queryExpression = $request->getQuery();
            /** @var Filter $queryFilter */
            $queryFilter = $queryExpression->getMust()['category'];
            /** @var Term $queryFilterTerm */
            $queryFilterTerm = $queryFilter->getReference();
            $categoryId = $queryFilterTerm->getValue();
            return $this->responseFactory->create(
                ResponseWithProductIds::fromSolrResponse($this->categoryRequest($categoryId))->toArray()
            );
        }
        return $this->responseFactory->create(
            ResponseWithProductIds::fromSolrResponse($this->searchRequest())->toArray()
        );
    }

    /**
     * @return \IntegerNet\Solr\Response\Response
     */
    private function searchRequest()
    {
        return $this->requestFactory->getSolrRequest(
            SolrRequestFactory::REQUEST_MODE_SEARCH
        )->doRequest();
    }

    /**
     * @param int $categoryId
     * @return \IntegerNet\Solr\Response\Response
     */
    private function categoryRequest($categoryId)
    {
        $this->searchRequest->setCategoryId($categoryId);
        return $this->requestFactory->getSolrRequest(
            SolrRequestFactory::REQUEST_MODE_CATEGORY
        )->doRequest();
    }

}