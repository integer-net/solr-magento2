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
use Magento\Framework\Search\AdapterInterface;
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
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     */
    public function __construct(
        \IntegerNet\Solr\Implementor\SolrRequestFactory $requestFactory,
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        return $this->responseFactory->create(
            Response::fromSolrResponse($this->doRequest())->toArray()
        );
    }

    /**
     * @return \IntegerNet\Solr\Resource\SolrResponse
     */
    private function doRequest()
    {
        return $this->requestFactory->getSolrRequest(
            SolrRequestFactory::REQUEST_MODE_SEARCH
        )->doRequest();
    }

}