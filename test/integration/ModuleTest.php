<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\ObjectManager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    const MODULE_NAME = 'IntegerNet_Solr';

    /**
     * @return ModuleList
     */
    private function getTestModuleList()
    {
        /** @var ModuleList $moduleList */
        $moduleList = $this->objectManager->create(ModuleList::class);
        return $moduleList;
    }

    /**
     * @return ModuleList
     */
    private function getReasModuleList()
    {
        $directoryList = $this->objectManager->create(DirectoryList::class, ['root' => BP]);
        $configReader = $this->objectManager->create(DeploymentConfig\Reader::class, ['dirList' => $directoryList]);
        $deploymentConfig = $this->objectManager->create(DeploymentConfig::class, ['reader' => $configReader]);

        /** @var ModuleList $moduleList */
        $moduleList = $this->objectManager->create(ModuleList::class, ['config' => $deploymentConfig]);
        return $moduleList;
    }

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testTheModuleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $paths = $registrar->getPaths(ComponentRegistrar::MODULE);
        $this->assertArrayHasKey(self::MODULE_NAME, $paths, 'Module should be registered');
    }

    public function testTheModuleIsKnownAndEnabled()
    {
        $moduleList = $this->getTestModuleList();

        $this->assertTrue($moduleList->has(self::MODULE_NAME),  'Module should be enabled');
    }

    public function testTheModuleIsKnownAndEnabledInTheRealEnvironment()
    {
        $moduleList = $this->getReasModuleList();
        $this->assertTrue($moduleList->has(self::MODULE_NAME), 'Module should be enabled in real environment');

    }

}