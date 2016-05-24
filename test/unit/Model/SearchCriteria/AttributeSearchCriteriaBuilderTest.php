<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\SearchCriteria;


use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeSearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryGeneration()
    {
        $searchCriteriaBuilderFactory = (new ObjectManager($this))->getObject(SearchCriteriaBuilderFactory::class);
        $this->assertInstanceOf(SearchCriteriaBuilderFactory::class, $searchCriteriaBuilderFactory);
    }

    public function testImmutability()
    {
        $this->markTestSkipped('The test has been moved to the integration test suite until we figure out how to instantiate generated factories in unit tests.');
        $objectManager = new ObjectManager($this);
        /** @var AttributeSearchCriteriaBuilder $varcharAttributes */
        $varcharAttributes = $objectManager->getObject(AttributeSearchCriteriaBuilder::class);
        $this->assertInstanceOf(AttributeSearchCriteriaBuilder::class, $varcharAttributes);
        $defaultCriteria = $varcharAttributes->create();
        $this->assertEquals($defaultCriteria, $varcharAttributes->create(), 'Second call to create() should return equal criteria');
        $this->assertNotEquals($defaultCriteria, $varcharAttributes->except(['status'])->create(), 'With except(), create() should return different criteria');
        $this->assertEquals($defaultCriteria, $varcharAttributes->create(), 'Another call to create() should still return default criteria');
    }
}
