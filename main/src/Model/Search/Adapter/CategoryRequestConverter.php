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
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;

/**
 * Creates category request for Solr library based on Magento request
 *
 * @package IntegerNet\Solr\Model\Search\Adapter
 */
class CategoryRequestConverter
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
        $this->searchRequest = $searchRequest;
    }

    /**
     * @return \IntegerNet\Solr\Request\Request
     */
    public function convert(\Magento\Framework\Search\RequestInterface $request)
    {
        /** @var BoolExpression $queryExpression */
        $queryExpression = $request->getQuery();
        /** @var Filter $queryFilter */
        $queryFilter = $queryExpression->getMust()['category'];
        /** @var Term $queryFilterTerm */
        $queryFilterTerm = $queryFilter->getReference();
        $categoryId = $queryFilterTerm->getValue();
        $this->searchRequest->setCategoryId($categoryId);
        return $this->requestFactory->getSolrRequest(
            SolrRequestFactoryInterface::REQUEST_MODE_CATEGORY
        );
    }

}
