<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\SearchCriteria;


use Magento\Framework\Api\SearchCriteriaBuilderFactory;

/**
 * Note: This is an integration test because behavior of generated factory and side effects of builder operations are being tested
 *
 * @package IntegerNet\Solr\Model\SearchCriteria
 */
class AttributeSearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testFactoryGeneration()
    {
        $searchCriteriaBuilderFactory = $this->objectManager->create(SearchCriteriaBuilderFactory::class);
        $this->assertInstanceOf(SearchCriteriaBuilderFactory::class, $searchCriteriaBuilderFactory);
    }

    public function testImmutability()
    {
        /** @var AttributeSearchCriteriaBuilder $attributes */
        $attributes = $this->objectManager->create(AttributeSearchCriteriaBuilder::class);
        $this->assertInstanceOf(AttributeSearchCriteriaBuilder::class, $attributes);
        $defaultCriteria = $attributes->varchar()->sortedByLabel()->create();
        $this->assertEquals($defaultCriteria, $attributes->varchar()->sortedByLabel()->create(), 'Second call to create() should return equal criteria');
        $this->assertNotEquals($defaultCriteria, $attributes->varchar()->sortedByLabel()->except(['status'])->create(), 'With except(), create() should return different criteria');
        $this->assertEquals($defaultCriteria, $attributes->varchar()->sortedByLabel()->create(), 'Another call to create() should still return default criteria');
    }
}
