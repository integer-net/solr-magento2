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


use IntegerNet\Solr\Config\AutosuggestConfig;
use IntegerNet\Solr\Config\FuzzyConfig;
use IntegerNet\Solr\Config\GeneralConfig;
use IntegerNet\Solr\Config\IndexingConfig;
use IntegerNet\Solr\Config\ResultsConfig;
use IntegerNet\Solr\Config\ServerConfig;
use IntegerNet\Solr\Config\StoreConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Url\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ScopeInterface
     */
    private $urlScopeMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    private $storeManagerMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DirectoryList
     */
    private $directoryListMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();
        $this->urlScopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPath'])
            ->getMock();

    }

    /**
     * @dataProvider dataStoreId
     * @param $storeId
     */
    public function testInstantiateConfigSections($storeId)
    {
        $this->storeManagerMock->method('getStore')->with($storeId)->willReturn($this->urlScopeMock);

        $config = new Config($this->scopeConfigMock, $this->storeManagerMock, $this->directoryListMock, $storeId);

        $this->assertInstanceOf(GeneralConfig::class, $config->getGeneralConfig());
        $this->assertInstanceOf(ServerConfig::class, $config->getServerConfig());
        $this->assertInstanceOf(AutosuggestConfig::class, $config->getAutosuggestConfig());
        $this->assertInstanceOf(FuzzyConfig::class, $config->getFuzzyAutosuggestConfig());
        $this->assertInstanceOf(FuzzyConfig::class, $config->getFuzzySearchConfig());
        $this->assertInstanceOf(IndexingConfig::class, $config->getIndexingConfig());
        $this->assertInstanceOf(ResultsConfig::class, $config->getResultsConfig());
        $this->assertInstanceOf(StoreConfig::class, $config->getStoreConfig());
    }
    public static function dataStoreId()
    {
        return [
            [1]
        ];
    }
}
