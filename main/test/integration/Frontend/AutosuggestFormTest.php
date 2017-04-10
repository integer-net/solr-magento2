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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class AutosuggestFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Bootstrap application before any test
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->removeSharedInstance(\Magento\Framework\View\Layout\Builder::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @dataProvider dataAutosuggestConfiguration
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testAutosuggestTemplate(array $storeConfig, $expectedTemplate)
    {
        $this->markTestSkipped('Testing rendered layout does not work like this, need to figure out another way.');
        $this->storeManager->setCurrentStore(1);

        foreach ($storeConfig as $path => $value) {
            $this->setStoreConfig($path, $value);
        }
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setRouteName('cms')->setControllerName('index')->setActionName('index');
        /** @var \Magento\Framework\App\ViewInterface $view */
        $view = $this->objectManager->create(\Magento\Framework\App\ViewInterface::class);
        $view->loadLayout();
        $view->renderLayout();

        /** @var Template $topSearchBlock */
        $topSearchBlock = $view->getLayout()->getBlock('top.search');
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
        $this->objectManager->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            $configPath,
            $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
