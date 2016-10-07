<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Element\Template;

/**
 * Plugin for dynamic layout updates in the frontend
 */
class LayoutPlugin
{
    /**
     * @var CurrentStoreConfig
     */
    private $currentStoreConfig;

    public function __construct(CurrentStoreConfig $currentStoreConfig)
    {
        $this->currentStoreConfig = $currentStoreConfig;
    }

    public function beforeRenderResult(\Magento\Framework\View\Result\Layout $subject, ResponseInterface $response)
    {
        $layout = $subject->getLayout();
        //TODO if new updates are added, extract layout updates to separate classes
        $this->setTopSearchTemplate($layout);
        return [$response];
    }

    /**
     * @param $layout
     */
    private function setTopSearchTemplate(\Magento\Framework\View\Layout $layout)
    {
        $searchBlock = $layout->getBlock('top.search');
        if ($searchBlock instanceof Template) {
            if (
                $this->currentStoreConfig->getGeneralConfig()->isActive() &&
                $this->currentStoreConfig->getAutosuggestConfig()->isActive()
            ) {
                $searchBlock->setTemplate('IntegerNet_Solr::form.mini.phtml');
            }
        }
    }
}