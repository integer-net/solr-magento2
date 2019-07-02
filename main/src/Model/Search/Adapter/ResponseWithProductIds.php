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
use IntegerNet\Solr\Response\Document;
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
            'aggregations' => $this->aggregationsFromSolrResponse(),
            'documents' => $this->documentsFromSolrResponse(),
            'total' => $this->solrResponse->documents()->count(),
        ];
        return $response;
    }

    /**
     * @return array
     */
    private function aggregationsFromSolrResponse()
    {
        return ArrayCollection::fromTraversable(
            $this->solrResponse->facets()
        )->flatMap(function (Facet $facet) {
            return [
                $facet->name() . '_bucket' => ArrayCollection::fromArray(
                    $facet->counts()
                )->filter(function (FacetCount $facetCount) {
                    return $facetCount->count() > 0;
                })->map(function (FacetCount $facetCount) use ($facet) {
                    if ($facet->name() == 'price') {
                        return $this->transformIntervalSyntax($facetCount);
                    }
                    return $facetCount;
                })->flatMap(function (FacetCount $facetCount) {
                    return [$facetCount->value() => $facetCount->toArray()];
                }, ArrayCollection::FLAG_MAINTAIN_NUMERIC_KEYS
                )->getArrayCopy()
            ];
        })->getArrayCopy();
    }

    /**
     * @return array
     */
    private function documentsFromSolrResponse()
    {
        $count = $this->solrResponse->documents()->count();

        return ArrayCollection::fromTraversable(
            $this->solrResponse->documents()
        )->values()->map(function (Document $document, $index) use ($count) {
            return [
                'entity_id' => $document->field('product_id')->value(),
                'score' => $count - $index,
            ];
        })->getArrayCopy();
    }

    /**
     * Transforms value of FacetCount from "[x.xx000,*)" to "x.xx_*"
     *
     * @param FacetCount $facetCount
     * @return FacetCount
     */
    private function transformIntervalSyntax(FacetCount $facetCount)
    {
        return $facetCount->withModifiedValue(function ($priceInterval) {
            \preg_match('{[\(\[]([\d.*]+),([\d.*]+)[\)\]]}', $priceInterval, $matches);
            list (, $priceFrom, $priceTo) = $matches;
            return \sprintf("%s_%s", $priceFrom == '*' ? $priceFrom : 1 * $priceFrom, $priceTo == '*' ? $priceTo : 1 * $priceTo);
        });
    }
}