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

use Magento\Framework\App\State;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class StoreEmulationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LocaleResolver
     */
    private $localeResolverMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Emulation
     */
    private $appEmulationMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $this->appEmulationMock = $this->getMockBuilder(Emulation::class)
            ->disableOriginalConstructor()
            ->setMethods(['startEnvironmentEmulation', 'stopEnvironmentEmulation'])
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(LocaleResolver::class)
            ->setMethods(['emulate', 'revert'])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore', 'setCurrentStore'])
            ->getMockForAbstractClass();
    }

    public function testRunInStoreEmulation()
    {
        $storeId = 2;
        $callback = $this->getMock(\stdClass::class, ['__invoke']);
        $callback->expects($this->once())->method('__invoke');

        $this->setStartStopExpectations($storeId);

        $storeEmulation = new StoreEmulation($this->appEmulationMock, $this->localeResolverMock, $this->storeManagerMock);
        $storeEmulation->runInStore($storeId, $callback);
    }

    public function testRunInStoreEmulationWithException()
    {
        $storeId = 2;
        $exceptionMessage = 'this is an exception';
        $callback = $this->getMock(\stdClass::class, ['__invoke']);
        $callback->expects($this->once())->method('__invoke')->willThrowException(new \Exception($exceptionMessage));

        $this->setStartStopExpectations($storeId);
        $this->setExpectedException(\Exception::class, $exceptionMessage);

        $storeEmulation = new StoreEmulation($this->appEmulationMock, $this->localeResolverMock, $this->storeManagerMock);
        $storeEmulation->runInStore($storeId, $callback);
    }

    /**
     * @param $storeId
     */
    private function setStartStopExpectations($storeId)
    {
        $this->appEmulationMock->expects($this->at(0))
            ->method('startEnvironmentEmulation')
            ->with($storeId);
        $this->appEmulationMock->expects($this->at(1))
            ->method('stopEnvironmentEmulation')
            ->willReturnSelf();

        $this->localeResolverMock->expects($this->at(0))
            ->method('emulate')
            ->with($storeId);
        $this->localeResolverMock->expects($this->at(1))
            ->method('revert');

        $storeStub = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();
        $storeStub->method('getCode')->willReturn(Store::ADMIN_CODE);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeStub);
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with(Store::ADMIN_CODE);
    }
}
