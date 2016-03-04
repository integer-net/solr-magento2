<?php
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Implementor\EventDispatcher;
use IntegerNet\Solr\Implementor\HasUserQuery;
use IntegerNet\SolrSuggest\Implementor\SearchUrl;
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 
class Integer\Net\Solr\Helper\SearchUrl implements SearchUrl
{
    /**
     * Returns search URL for given user query text
     *
     * @param string $queryText
     * @param string[] $additionalParameters
     * @return string
     */
    public function getUrl($queryText, array $additionalParameters = [])
    {
        return Mage::getUrl('catalogsearch/result',
            ['_query' => array_merge(['q' => $queryText], $additionalParameters)]);
    }

}