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

use IntegerNet\Solr\Implementor\SolrRequestFactoryInterface;
use IntegerNet\Solr\Model\Bridge\SearchRequest;
use IntegerNet\SolrCategories\Request\CategoryRequest;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
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
     * @var ResponseFactory
     */
    protected $responseFactory;
    /**
     * @var SearchRequestConverter
     */
    private $searchRequestBuilder;
    /**
     * @var CategoryRequestConverter
     */
    private $categoryRequestBuilder;

    /**
     * @param SearchRequestConverter $searchRequestBuilder
     * @param CategoryRequestConverter $categoryRequestBuilder
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        SearchRequestConverter $searchRequestBuilder,
        CategoryRequestConverter $categoryRequestBuilder,
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->categoryRequestBuilder = $categoryRequestBuilder;
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
            $solrResponse = $this->makeCategoryRequest($request);
        } else {
            $solrResponse = $this->makeSearchRequest($request);
        }
        return $this->responseFactory->create(
            ResponseWithProductIds::fromSolrResponse($solrResponse)->toArray()
        );
    }

    /**
     * @return \IntegerNet\Solr\Response\Response
     */
    private function makeSearchRequest(RequestInterface $request)
    {
        return $this->searchRequestBuilder->convert($request)->doRequest($this->getActiveAttributeCodes($request));
    }

    /**
     * @param RequestInterface $request
     * @return \IntegerNet\Solr\Response\Response
     */
    private function makeCategoryRequest(RequestInterface $request)
    {
        return $this->categoryRequestBuilder->convert($request)->doRequest($this->getActiveAttributeCodes($request));
    }

    /**
     * @param RequestInterface $request
     * @return string[]
     */
    private function getActiveAttributeCodes(RequestInterface $request)
    {
        $activeAttributeCodes = [];
        /** @var BoolExpression $query */
        $query = $request->getQuery();
        $activeAttributeCodes = $this->addActiveAttributeCodes($query->getMust(), $activeAttributeCodes);
        $activeAttributeCodes = $this->addActiveAttributeCodes($query->getShould(), $activeAttributeCodes);
        return $activeAttributeCodes;
    }

    /**
     * @param \Magento\Framework\Search\Request\QueryInterface[]
     * @param string[] $activeAttributeCodes
     * @return string[]
     */
    private function addActiveAttributeCodes($filters, $activeAttributeCodes)
    {
        if (is_array($filters)) {
            foreach ($filters as $queryFilter) {
                if ($queryFilter instanceof \Magento\Framework\Search\Request\Query\Filter) {
                    if ($queryFilter->getName() == 'category') {
                        $activeAttributeCodes[] = 'category';
                    } else {
                        $activeAttributeCodes[] = $queryFilter->getReference()->getField();
                    }
                }
            }
        }
        return $activeAttributeCodes;
    }
}