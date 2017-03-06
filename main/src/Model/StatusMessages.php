<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model;


interface StatusMessages
{
    /**
     * @param int|null $storeId
     * @return string[]
     */
    public function getMessages($storeId = null);
}