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
use IntegerNet\Solr\Model\Bridge\AttributeRepository;
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
     * @var \IntegerNet\Solr\Implementor\AttributeRepository
     */
    private $attributeRepository;

    /**
     * @param SolrRequestFactoryInterface $requestFactory
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     * @param SearchRequest $searchRequest
     */
    public function __construct(
        \IntegerNet\Solr\Implementor\AttributeRepository $attributeRepository,
        \IntegerNet\Solr\Implementor\SolrRequestFactoryInterface $requestFactory,
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory,
        SearchRequest $searchRequest
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->searchRequest = $searchRequest;
        $this->attributeRepository = $attributeRepository;
    }

    /**
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
        /** @var \IntegerNet\Solr\Request\SearchRequest $solrRequest */
        $solrRequest = $this->requestFactory->getSolrRequest(
            SolrRequestFactoryInterface::REQUEST_MODE_SEARCH
        );
        $query = $magentoRequest->getQuery();
        if ($query instanceof BoolExpression) {
            foreach ($query->getShould() as $shouldQuery) {
                if ($shouldQuery instanceof Filter) {
                    /** @var \Magento\Framework\Search\Request\Filter\Term $reference */
                    $reference = $shouldQuery->getReference();
                    $solrRequest->getFilterQueryBuilder()->addAttributeFilter(
                        $this->attributeRepository->getAttributeByCode(
                            $reference->getField(),
                            $this->getStoreIdFromRequest($magentoRequest)
                        ),
                        $reference->getValue()
                    );
                }
            }
            foreach ($query->getMust() as $mustQuery)
            {
                if ($mustQuery instanceof Filter) {
                    /** @var \Magento\Framework\Search\Request\Filter\Term $reference */
                    $reference = $mustQuery->getReference();
                    if ($reference->getField() === 'category_ids') {
                        $solrRequest->getFilterQueryBuilder()->addCategoryFilter($reference->getValue());
                    }
                }
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
        };
        return $storeId;
    }
}