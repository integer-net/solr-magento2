<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCms\Implementor\Page;
use IntegerNet\SolrCms\Implementor\PageIterator;

class Integer\Net\Solr\Model\Bridge\PageIterator extends IteratorIterator implements PageIterator
{

    /**
     * @param \Magento\Cms\Model\ResourceModel\Page\Collection $_collection
     */
    public function __construct(\Magento\Cms\Model\ResourceModel\Page\Collection $_collection)
    {
        parent::__construct($_collection->getIterator());
    }

    /**
     * @return Page
     */
    public function current()
    {
        return new Integer\Net\Solr\Model\Bridge\Page($this->getInnerIterator()->current());
    }

}