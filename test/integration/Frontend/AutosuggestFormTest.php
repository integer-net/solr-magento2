<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Frontend;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class AutosuggestFormTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @dataProvider dataAutosuggestConfiguration
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testAutosuggestTemplate(array $storeConfig, $expectedTemplate)
    {
        foreach ($storeConfig as $path => $value) {
            $this->setStoreConfig($path, $value);
        }
        $this->dispatch('/');
        /** @var LayoutInterface $layout */
        $layout = $this->_objectManager->get(LayoutInterface::class);
        /** @var Template $topSearchBlock */
        $topSearchBlock = $layout->getBlock('top.search');
        $this->assertInstanceOf(Template::class, $topSearchBlock);
        $this->assertEquals($expectedTemplate, $topSearchBlock->getTemplate());
    }
    public static function dataAutosuggestConfiguration()
    {
        return [
            'default' => [[], 'IntegerNet_Solr::form.mini.phtml'],
            'enabled' => [
                [
                    'integernet_solr/general/is_active' => true,
                    'integernet_solr/autosuggest/is_active' => true,
                ],
                'IntegerNet_Solr::form.mini.phtml'
            ],
            'module_disabled' => [
                [
                    'integernet_solr/general/is_active' => false,
                    'integernet_solr/autosuggest/is_active' => true,
                ],
                'Magento_Search::form.mini.phtml'
            ],
            'autosuggest_disabled' => [
                [
                    'integernet_solr/general/is_active' => true,
                    'integernet_solr/autosuggest/is_active' => false,
                ],
                'Magento_Search::form.mini.phtml'
            ],
        ];
    }

    private function setStoreConfig($configPath, $value, $storeCode = null)
    {
        $this->_objectManager->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            $configPath,
            $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}