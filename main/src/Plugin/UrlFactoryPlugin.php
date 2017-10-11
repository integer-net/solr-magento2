<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Plugin;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlFactory as Subject;

class UrlFactoryPlugin
{
    /**
     * @var bool
     */
    private $forceFrontend = false;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param bool $value
     */
    public function setForceFrontend($value = true)
    {
        $this->forceFrontend = $value;
    }

    /**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param array $data
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(Subject $subject, \Closure $proceed, array $data = [])
    {
        if (!$this->forceFrontend) {
            return $proceed($data);
        }

        return $this->objectManager->create(\Magento\Framework\Url::class, $data);
    }
}