<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Cache;

use IntegerNet\SolrSuggest\Plain\Cache\CacheStorage;
use IntegerNet\SolrSuggest\Plain\Cache\PsrCache;
use IntegerNet\SolrSuggest\CacheBackend\File\CacheItemPool as FileCacheBackend;
use Magento\Framework\App\Filesystem\DirectoryList;

class PsrFileCacheStorageFactory implements CacheStorageFactoryInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * @return CacheStorage
     */
    public function create()
    {
        return new PsrCache(
            new FileCacheBackend(
                $this->rootDir()
            )
        );
    }

    /**
     * @return string
     */
    public function rootDir()
    {
        return $this->directoryList->getPath(DirectoryList::CACHE) . DIRECTORY_SEPARATOR . 'integernet_solr';
    }
}