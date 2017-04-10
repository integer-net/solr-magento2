<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Bridge;

use Magento\TestFramework\ObjectManager;
use IntegerNet\Solr\Model\Bridge\TemplateRepository;


class TemplateRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testTemplateByStoreId()
    {
        $storeId = 1;
        /** @var TemplateRepository $templateRepository */
        $templateRepository = $this->objectManager->create(TemplateRepository::class);
        $this->assertEquals(
            \realpath(__DIR__ . '/../../../src/view/frontend/templates/autosuggest/index.phtml'),
            $templateRepository->getTemplateByStoreId($storeId)->getFilename()
        );
    }
}
