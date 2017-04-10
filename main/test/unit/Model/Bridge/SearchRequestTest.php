<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;


use Magento\Search\Model\QueryFactory;

class SearchRequestTest extends \PHPUnit_Framework_TestCase
{
    private $queryStub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|QueryFactory
     */
    private $queryFactoryMock;

    protected function setUp()
    {
        $this->queryStub = $this->getMockBuilder(\Magento\Search\Model\Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueryText'])
            ->getMock();
        $this->queryFactoryMock = $this->getMockBuilder(QueryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->queryFactoryMock->method('get')->willReturn($this->queryStub);
    }
    public function testHasUserQuery()
    {
        $queryText = 'möp';

        $this->queryStub->method('getQueryText')->willReturn($queryText);
        $searchRequest = new SearchRequest($this->queryFactoryMock);
        $this->assertEquals($queryText, $searchRequest->getUserQueryText());
    }
}
