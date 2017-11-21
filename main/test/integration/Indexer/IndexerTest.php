<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Indexer;

use IntegerNet\Solr\Model\Indexer\Fulltext;
use Magento\TestFramework\ObjectManager;

class IndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }
    public function testIndexerInstantiation()
    {
        $indexer = $this->objectManager->create(Fulltext::class);
        $this->assertInstanceOf(Fulltext::class, $indexer);
    }
}