<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\SolrCategories\Model\ResourceModel;

use IntegerNet\Solr\Model\Bridge\RequestFactory as SolrRequestFactory;
use IntegerNet\Solr\Request\Request as SolrRequest;
use IntegerNet\Solr\Response\Response as SolrResponse;

class CategoriesResult
{
    /**
     * @var SolrRequest
     */
    private $solrRequest;
    /**
     * @var SolrResponse|null
     */
    private $solrResult;

    /**
     * CategoriesResult constructor.
     * @param SolrRequestFactory $requestFactory
     */
    public function __construct(SolrRequestFactory $requestFactory)
    {
        $this->solrRequest = $requestFactory->getSolrRequest(SolrRequestFactory::REQUEST_MODE_CATEGORY_SEARCH);
    }

    /**
     * Call Solr server twice: Once without fuzzy search, once with (if configured)
     *
     * @return SolrResponse
     */
    public function getSolrResult()
    {
        if (is_null($this->solrResult)) {
            $this->solrResult = $this->solrRequest->doRequest();
        }

        return $this->solrResult;
    }
}