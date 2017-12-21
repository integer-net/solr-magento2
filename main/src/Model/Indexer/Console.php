<?php

namespace IntegerNet\Solr\Model\Indexer;

class Console extends Fulltext
{
    public function executeStores(array $storeIds)
    {
        $this->reindex(null, true, $storeIds);
    }

    public function executeStoresForceEmpty($storeIds)
    {
        $this->reindex(null, 'force', $storeIds);
    }

    public function executeStoresForceNotEmpty($storeIds)
    {
        $this->reindex(null, false, $storeIds);
    }
}