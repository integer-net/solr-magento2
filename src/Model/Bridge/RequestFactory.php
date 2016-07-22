<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\SolrRequestFactory;
use IntegerNet\Solr\Request\Request;
use IntegerNet\Solr\Resource\ResourceFacade;

class RequestFactory implements SolrRequestFactory
{
    /**
     * Returns new configured Solr recource
     *
     * @deprecated should not be used directly from application
     * @return ResourceFacade
     */
    public function getSolrResource()
    {
        // TODO: Implement getSolrResource() method.
    }

    /**
     * Returns new Solr service (search, autosuggest or category service, depending on application state or parameter)
     *
     * @param int $requestMode
     * @return Request
     */
    public function getSolrRequest($requestMode = self::REQUEST_MODE_AUTODETECT)
    {
        // TODO: Implement getSolrRequest() method.
    }

}