<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\ResourceModel;

use IntegerNet\Solr\Indexer\Data\ProductAssociation;

interface ProductAssociations
{
    /**
     * Returns product associations for given parent ids
     *
     * @param int[]|null $parentIds
     * @return ProductAssociation[] An array with parent id as keys and association data as values
     */
    public function getAssociations($parentIds);
}