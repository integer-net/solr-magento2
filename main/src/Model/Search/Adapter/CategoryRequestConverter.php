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
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Request\Query\BoolExpression;
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
    public function convert(RequestInterface $magentoRequest)
    {
        if (! $magentoRequest->getQuery() instanceof BoolExpression) {
            $this->logger->notice(sprintf('[SOLR] Unknown query type %s', get_class($magentoRequest->getQuery())));
            return $this->createSolrRequest();
        }
        $request = new Request($magentoRequest);
        $this->registerCategoryId($request->categoryId());
        return $this->applyFilters($request, $this->createSolrRequest());
    }

    /**
     * @return CategoryRequest
     */
    private function createSolrRequest()
    {
        /** @var CategoryRequest $solrRequest */
        $solrRequest = $this->requestFactory->getSolrRequest(
            SolrRequestFactoryInterface::REQUEST_MODE_CATEGORY
        );
        return $solrRequest;
    }

    /**
     * Registers category in search request object (application context)
     *
     * Must be called before creating seach request!
     *
     * @param int $categoryId
     */
    private function registerCategoryId($categoryId)
    {
        $this->searchRequest->setCategoryId($categoryId);
    }

    /**
     * @param $source
     * @param $target
     * @return CategoryRequest
     * @throws \IntegerNet\Solr\Exception
     */
    private function applyFilters(Request $source, CategoryRequest $target)
    {
        $fqBuilder = $target->getFilterQueryBuilder();
        foreach ($source->filters() as $filter) {
            /*
             * Categories are not filtered but searched via query text
             * (see \IntegerNet\SolrCategories\Query\CategoryQueryBuilder)
             */
            if ($filter->getName() !== 'category') {
                $this->filterConverter->configure($fqBuilder, $filter, $source->storeId());
            }
        }
        return $target;
    }
}
