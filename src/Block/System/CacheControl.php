<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\System;

class CacheControl extends \Magento\Backend\Block\Template
{

    public function getFlushConfigCacheUrl()
    {
        return $this->getUrl('integernet_solr/system/flushCache');
    }
}