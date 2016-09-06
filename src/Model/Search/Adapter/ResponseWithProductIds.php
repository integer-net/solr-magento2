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

use IntegerNet\Solr\Response\Response as SolrResponse;

/**
 * Converts response from Solr library to array format used by Magento
 *
 * @see \Magento\Framework\Search\Adapter\Mysql\ResponseFactory::create()
 */
class ResponseWithProductIds
{
    /**
     * @var SolrResponse
     */
    private $solrResponse;

    /**
     * Response constructor.
     * @param SolrResponse $solrResponse
     */
    private function __construct(SolrResponse $solrResponse)
    {
        $this->solrResponse = $solrResponse;
    }

    /**
     * @param SolrResponse $solrResponse
     * @return ResponseWithProductIds
     */
    public static function fromSolrResponse(SolrResponse $solrResponse)
    {
        return new static($solrResponse);
    }

    public function toArray()
    {
        $response = [
            'documents' => [
            ],
            'aggregations' => [
                'manufacturer_bucket' => [],
                'category_bucket' => []
            ],
        ];
        $score = $count = $this->solrResponse->documents()->count();
        foreach ($this->solrResponse->documents() as $document) {
            $response['documents'][] =
                [
                    'entity_id' => $document->field('product_id')->value(),
                    'score' => $score--,
                ];
        }
        return $response;
    }
}