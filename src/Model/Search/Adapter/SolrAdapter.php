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
        //TODO transform response into (docs, aggregates) using SolrResponse interface and separate class

        $solrResponse = $this->requestFactory->getSolrRequest(SolrRequestFactory::REQUEST_MODE_SEARCH)->doRequest();

        $response = [
            'documents' => [
            ],
            'aggregations' => [
                'manufacturer_bucket' => [],
                'category_bucket' => []
            ],
        ];
        $count = count($solrResponse->response->docs);
        foreach ($solrResponse->response->docs as $doc) {
            $response['documents'][] =
            [
                'entity_id' => $doc->product_id,
                'score' => new \Zend_Db_Expr("(@score := ifnull(@score, $count) - 1)"),
            ];
        }
        return $this->responseFactory->create($response);
    }

}