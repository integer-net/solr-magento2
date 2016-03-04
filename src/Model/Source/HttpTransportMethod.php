<?php
use IntegerNet\Solr\Resource\HttpTransportMethod;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */ 

class Integer\Net\Solr\Model\Source\HttpTransportMethod
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => HttpTransportMethod::HTTP_TRANSPORT_METHOD_FILEGETCONTENTS,
                'label' => __('file_get_contents'),
            ],
            [
                'value' => HttpTransportMethod::HTTP_TRANSPORT_METHOD_CURL,
                'label' => __('cURL'),
            ],
        ];
    }
}