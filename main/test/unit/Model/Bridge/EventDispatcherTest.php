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

use Magento\Framework\Event\ManagerInterface as EventManager;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatch()
    {
        $eventName = 'some_event';
        $eventParams = ['key' => 'value'];

        $eventManagerMock = $this->getMockBuilder(EventManager::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();

        $eventManagerMock->expects($this->once())->method('dispatch')->with($eventName, $eventParams);

        $eventDispatcher = new EventDispatcher($eventManagerMock);
        $eventDispatcher->dispatch($eventName, $eventParams);
    }
}
