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

use IntegerNet\Solr\Model\Data\ArrayCollection;
use IntegerNet\Solr\Response\ApacheSolrFacet;
use IntegerNet\Solr\Response\Facet;
use IntegerNet\Solr\Response\FacetCount;
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
            ],
        ];
        $response['aggregations']['price_bucket'] = ArrayCollection::fromArray(
            $this->solrResponse->facets()->facetByName('price')->counts()
        )->filter(function (FacetCount $facetCount) {
            return $facetCount->count() > 0;
        })->flatMap(function (FacetCount $facetCount) {
            $facetCount = $facetCount
                ->withModifiedValue(function ($priceInterval) {
                    \preg_match('{[\(\[]([\d.*]+),([\d.*]+)[\)\]]}', $priceInterval, $matches);
                    list (, $priceFrom, $priceTo) = $matches;
                    return \sprintf("%s_%s", $priceFrom == '*' ? $priceFrom : 1 * $priceFrom, $priceTo == '*' ? $priceTo : 1 * $priceTo);
                });
            return [ $facetCount->value() => $facetCount->toArray() ];
        })->getArrayCopy();

        foreach ($this->solrResponse->facets()->exclude(['price']) as $facet) {
            $response['aggregations'][$facet->name() . '_bucket'] = ArrayCollection::fromArray(
                $facet->counts()
            )
            ->flatMap(function(FacetCount $facetCount) {
                return [ $facetCount->value() => $facetCount->toArray() ];
            }, ArrayCollection::FLAG_MAINTAIN_NUMERIC_KEYS
            )->getArrayCopy();
        }

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