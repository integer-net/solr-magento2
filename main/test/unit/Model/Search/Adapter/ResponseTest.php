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
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
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
                                'product_id' => 3,
                            ],
                            [
                                'product_id' => 7,
                            ],
                        ]
                    ],
                    'facet_counts' => [
                        'facet_fields' => [
                            'category' => [
                                3 => 3,
                                31 => 1,
                                32 => 2,
                            ],
                            'manufacturer_facet' => [
                                100 => 1,
                                101 => 1,
                            ]
                        ],
                        'facet_intervals' => [
                            'price_f' => [
                                '(0.000000,10.000000]' => 0,
                                '(10.000000,20.000000]' => 0,
                                '(20.000000,30.000000]' => 0,
                                '(30.000000,40.000000]' => 1,
                                '(40.000000,50.000000]' => 0,
                                '(50.000000,*]' => 2,
                            ]
                        ]
                    ]
                ]),
                'expectedArray' => [
                    'documents' => [
                        [
                            'entity_id' => 13,
                            'score' => 3,
                        ],
                        [
                            'entity_id' => 3,
                            'score' => 2,
                        ],
                        [
                            'entity_id' => 7,
                            'score' => 1,
                        ],
                    ],
                    'aggregations' => [
                        'price_bucket' => [
                            '30_40' => [
                                'value' => '30_40',
                                'count' => '1',
                            ],
                            '50_*' => [
                                'value' => '50_*',
                                'count' => '2',
                            ],
                        ],
                        'category_bucket' => [
                            3 => [
                                'value' => '3',
                                'count' => '3',
                            ],
                            31 => [
                                'value' => '31',
                                'count' => '1',
                            ],
                            32 => [
                                'value' => '32',
                                'count' => '2',
                            ],
                        ],
                        'manufacturer_bucket' => [
                            100 => [
                                'value' => '100',
                                'count' => '1',
                            ],
                            101 => [
                                'value' => '101',
                                'count' => '1',
                            ],
                        ],
                    ],
                    'total' => 3,
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
