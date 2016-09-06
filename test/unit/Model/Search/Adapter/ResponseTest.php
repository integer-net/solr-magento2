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


use IntegerNet\Solr\Request\FakeRequest;
use IntegerNet\Solr\Response\Response as SolrResponse;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataResponseAsArray
     * @param $solrResponse
     * @param array $expectedArray
     */
    public function testResponseAsArray($solrResponse, array $expectedArray)
    {
        $solrResponse = $this->fakeResponse($solrResponse);
        $response = ResponseWithProductIds::fromSolrResponse($solrResponse);
        $this->assertInstanceOf(ResponseWithProductIds::class, $response);
        $this->assertEquals($expectedArray, $response->toArray());
    }
    public static function dataResponseAsArray()
    {
        return [
            [
                'solrResponse' => \json_encode([
                    'response' => [
                        'docs' => [
                            [
                                'product_id' => 13,
                            ],
                            [
                                'product_id' => 37,
                            ],
                        ]
                    ]
                ]),
                'expectedArray' => [
                    'documents' => [
                        [
                            'entity_id' => 13,
                            'score' => 2,
                        ],
                        [
                            'entity_id' => 37,
                            'score' => 1,
                        ],
                    ],
                    'aggregations' => [
                        'price_bucket' => [],
                        'category_bucket' => [],
                        'manufacturer_bucket' => [],
                    ],
                ]
            ]
        ];
    }

    /**
     * @param $body
     * @return SolrResponse
     */
    private function fakeResponse($body)
    {
        $request = new FakeRequest($body);
        return $request->doRequest();
    }
}
