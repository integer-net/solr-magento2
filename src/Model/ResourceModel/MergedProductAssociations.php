<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\ResourceModel;


use IntegerNet\Solr\Indexer\Data\ProductAssociation;
use IntegerNet\Solr\Model\Data\ArrayCollection;

class MergedProductAssociations implements ProductAssociations
{
    /**
     * @var ProductAssociations[]
     */
    private $sources;

    /**
     * @param ProductAssociations[] $sources
     */
    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    /**
     * Returns product associations for given parent ids
     *
     * @param int[]|null $parentIds
     * @return ProductAssociation[] An array with parent id as keys and association data as values
     */
    public function getAssociations($parentIds)
    {
        // parent ids are distinct, so we can just collapse the results into a flat array
        return ArrayCollection::fromArray($this->sources)
            ->flatMap(
                function(ProductAssociations $source) use ($parentIds) {
                    return $source->getAssociations($parentIds);
                },
                ArrayCollection::FLAG_MAINTAIN_NUMERIC_KEYS
            )->getArrayCopy();
    }

}