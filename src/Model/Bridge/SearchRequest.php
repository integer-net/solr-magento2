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

use IntegerNet\Solr\Implementor\HasUserQuery;
use Magento\Search\Model\QueryFactory;

class SearchRequest implements HasUserQuery
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * SearchRequest constructor.
     * @param QueryFactory $queryFactory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Returns query as entered by user
     *
     * @return string
     */
    public function getUserQueryText()
    {
        return $this->queryFactory->get()->getQueryText();
    }

}