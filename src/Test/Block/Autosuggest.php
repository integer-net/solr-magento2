<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_SolrSuggest
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class Integer\Net\Solr\Test\Block\Autosuggest extends  Ecom\Dev\PHPUnit\Test\CaseTest
{
    /**
     * @test
     * @singleton core/session
     * @singleton integernet_solr/observer
     */
    public function shouldLoadCustomHelperFromCacheWithEmptyCache()
    {
        $this->setupObserverMock(false);
        $this->setupCache();
        $this->instantiateCustomHelper();
    }
    /**
     * @test
     * @singleton core/session
     * @singleton integernet_solr/observer
     */
    public function shouldLoadCustomHelperFromCacheWithPreparedCache()
    {
        $this->setupObserverMock(true);
        $this->setupCache();
        $this->instantiateCustomHelper();
    }

    private function setupObserverMock($proxyCacheRebuild)
    {
        $observerMockBuilder = Ecom\Dev\PHPUnit\Test\CaseTest\Util::getGroupedClassMockBuilder($this, 'model', 'integernet_solr/observer')
            ->setMethods(['applicationCleanCache']);
        if ($proxyCacheRebuild) {
            $observerMockBuilder->enableProxyingToOriginalMethods();
        }
        $observerMock = $observerMockBuilder->getMock();
        $observerMock->expects($this->once())->method('applicationCleanCache');
        $this->replaceByMock('singleton', 'integernet_solr/observer', $observerMock);
    }

    private function setupCache()
    {
        $this->mockSession('core/session');
        Mage::app()->cleanCache();
    }

    private function instantiateCustomHelper()
    {
        $block = $this->app()->getLayout()->createBlock('IntegerNet\Solr\Block\Autosuggest');
        $this->assertInstanceOf(Integer\Net\Solr\Helper\Custom::class, $block->getCustomHelper());
    }
}