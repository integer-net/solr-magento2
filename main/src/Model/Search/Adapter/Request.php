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

use IntegerNet\Solr\Exception;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;

/**
 * Wrapper for Magento Search Request
 *
 * @package IntegerNet\Solr\Model\Search\Adapter
 */
class Request
{
    const DEFAULT_STORE_ID = 1;

    /**
     * @var RequestInterface
     */
    private $magentoRequest;

    /**
     * Request constructor.
     * @param RequestInterface $magentoRequest
     */
    public function __construct(RequestInterface $magentoRequest)
    {
        $this->magentoRequest = $magentoRequest;
    }

    /**
     * @return int
     */
    public function storeId()
    {
        $storeId = self::DEFAULT_STORE_ID;
        foreach ($this->magentoRequest->getDimensions() as $dimension) {
            if ($dimension->getName() === 'scope') {
                $storeId = (int) $dimension->getValue();
                break;
            }
        }
        return $storeId;
    }

    /**
     * @return Filter[]
     * @throws \IntegerNet\Solr\Exception
     */
    public function filters()
    {
        $queryExpression = $this->expression();
        /** @var Filter[] $filters */
        $filters = array_filter(
            array_merge($queryExpression->getMust(), $queryExpression->getShould()),
            function ($part) {
                return $part instanceof Filter;
            }
        );
        return $filters;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function categoryId()
    {
        $queryExpression = $this->expression();
        $must = $queryExpression->getMust();
        if (!isset($must['category'])) {
            throw new Exception('Query does not contain any category filter');
        }
        /** @var Filter $queryFilter */
        $queryFilter = $must['category'];
        /** @var Term $queryFilterTerm */
        $queryFilterTerm = $queryFilter->getReference();
        return (int) $queryFilterTerm->getValue();
    }

    /**
     * @return BoolExpression
     * @throws Exception
     */
    private function expression()
    {
        $query = $this->magentoRequest->getQuery();
        if (!$query instanceof BoolExpression) {
            throw new Exception(
                sprintf('Query is expected to be BoolExpression, was %s instead', get_class($query))
            );
        }
        return $query;
    }
}