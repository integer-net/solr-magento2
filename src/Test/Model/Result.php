<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Resource\ResourceFacade;
use IntegerNet\Solr\Resource\ResponseDecorator;
use IntegerNet\Solr\Resource\SolrResponse;

/**
 * @loadFixture config
 */
class Integer\Net\Solr\Test\Model\Result extends Ecom\Dev\PHPUnit\Test\CaseTest\Controller
{
    /**
     * @var PH\PUnit\Framework\MockObject\MockObject|\IntegerNet\Solr\Resource\ResourceFacade
     */
    protected $_resourceMock;
    protected $_resourceMockMethods = ['search'];

    protected function setUp()
    {
        parent::setUp();
        $this->_resourceMock = $this->getMock(ResourceFacade::class, $this->_resourceMockMethods);
        $factoryStub = $this->mockHelper('integernet_solr/factory', ['getSolrResource']);
        $factoryStub->expects($this->any())->method('getSolrResource')->willReturn($this->_resourceMock);
        $this->replaceByMock('helper', 'integernet_solr/factory', $factoryStub);
    }

    /**
     * @return Ecom\Dev\PHPUnit\Mock\Proxy|\Psr\Log\LoggerInterface
     */
    protected function _mockLog()
    {
        $mock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $this->replaceByMock('helper', 'integernet_solr/log', $mock);
        return $mock;
    }

    /**
     * @test
     * @helper integernet_solr/factory
     * @singleton integernet_solr/result
     */
    public function shouldTriggerSearchAndEvents()
    {
        $this->_resourceMock->expects($this->once())
            ->method('search')
            ->willReturn($this->_getDummyResponse());
        $result = $this->_modelResultFactory->create();
        $result->getSolrResult();
        $this->assertEventDispatchedExactly('integernet_solr_update_query_text', 1);
        $this->assertEventDispatchedExactly('integernet_solr_before_search_request', 1);
        $this->assertEventDispatchedExactly('integernet_solr_after_search_request', 1);

    }

    /**
     * @test
     * @helper integernet_solr/factory
     * @singleton integernet_solr/result
     */
    public function shouldTriggeSearchTwiceIfFuzzy()
    {
        $storeId = 1;
        $this->app()->getStore($storeId)->setConfig('integernet_solr/fuzzy/is_active', 1);
        $this->_resourceMock->expects($this->exactly(2))
            ->method('search')
            ->willReturn($this->_getDummyResponse());
        $result = $this->_modelResultFactory->create();
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
        $this->assertEventDispatchedExactly('integernet_solr_update_query_text', 2);
        $this->assertEventDispatchedExactly('integernet_solr_before_search_request', 2);
        $this->assertEventDispatchedExactly('integernet_solr_after_search_request', 2);
    }

    /**
     * @test
     * @helper integernet_solr/factory
     * @helper integernet_solr/log
     * @helper catalogsearch
     * @singleton integernet_solr/result
     */
    public function shouldUseParametersBasedOnToolbar()
    {
        $storeId = 1;
        $query = 'tshirt';
        $currentPage = 2;
        $pageSize = 10;

        $logMock = $this->_mockLog();
        $logMock->expects($this->at(3))->method('debug')->with(
            $this->stringContains('name_t:"tshirt"~100^5 short_description_t_mv:"tshirt"~100^1'));
        $logMock->expects($this->at(4))->method('debug')->with(
            'Filter Query: store_id:1 AND is_visible_in_search_i:1');

        /* @var \Magento\Framework\Block\Text $toolbar Not using actual toolbar block which reads from session */
        $toolbar = $this->app()->getLayout()->createBlock('core/text', 'product_list_toolbar');
        $toolbar->addData([
            'current_page' => $currentPage,
            'current_order' => 'price',
            'current_direction' => 'asc',
            'limit' => $pageSize
        ]);
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->once())
            ->method('search')
            ->with(
                $storeId,
                $this->stringContains($query),
                0,
                $currentPage * $pageSize,
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->contains('price_f asc', true, true),
                    $this->logicalNot($this->arrayHasKey('rows'))
                ))
            ->willReturn($this->_getDummyResponse());
        $result = $this->_modelResultFactory->create();
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }

    /**
     * @test
     * @helper integernet_solr/factory
     * @helper catalogsearch
     * @singleton integernet_solr/result
     */
    public function shouldUseDefaultParametersWithoutToolbar()
    {
        $storeId = 1;
        $query = 'tshirt';

        $this->app()->getLayout()->unsetBlock('product_list_toolbar');
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->once())
            ->method('search')
            ->with(
                $storeId,
                $this->stringContains($query),
                0,
                $this->_configScopeConfigInterface->getValue('integernet_solr/autosuggest/max_number_product_suggestions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->logicalAnd(
                    $this->isType('array'),
                    $this->contains('score desc', true, true),
                    $this->logicalNot($this->arrayHasKey('rows'))
                ))
            ->willReturn($this->_getDummyResponse());
        $result = $this->_modelResultFactory->create();
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }

    /**
     * @test
     * @helper integernet_solr/factory
     * @helper catalogsearch
     * @singleton integernet_solr/result
     */
    public function shouldBroadenMultiwordSearchIfNoResults()
    {
        $storeId = 1;
        $query = 'blue tshirt';

        $this->app()->getLayout()->unsetBlock('product_list_toolbar');
        $searchHelperStub = $this->mockHelper('catalogsearch', ['getQueryText']);
        $searchHelperStub->expects($this->any())
            ->method('getQueryText')
            ->willReturn($query);
        $this->replaceByMock('helper', 'catalogsearch', $searchHelperStub);

        $this->_resourceMock->expects($this->exactly(2))
            ->method('search')
            ->willReturn($this->_getDummyResponse());
        $result = $this->_modelResultFactory->create();
        $this->setCurrentStore($storeId);
        $result->getSolrResult();
    }

    /**
     * @return SolrResponse
     */
    protected function _getDummyResponse()
    {
        $result = [
            'response' => ['docs' => [], 'numFound' => 0],
            'facet_counts' => ['facet_fields' => []]
        ];
        return new ResponseDecorator(new \Apache\Solr\Response(new \Apache\Solr\HttpTransport\Response(
            200, 'application/json', json_encode($result))));
    }
}