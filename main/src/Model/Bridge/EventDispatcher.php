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

use IntegerNet\Solr\Implementor\EventDispatcher as EventDispatcherInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * EventDispatcher constructor.
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Dispatch event
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function dispatch($eventName, array $data = array())
    {
        $this->eventManager->dispatch($eventName, $data);
    }

}