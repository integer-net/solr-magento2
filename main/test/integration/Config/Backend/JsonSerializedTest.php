<?php

namespace IntegerNet\Solr\Model\Config\Backend;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 */
class JsonSerializedTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testArraySavedAsJson()
    {
        $arrayValue = ['foo' => 'bar'];
        $jsonValue = '{"foo":"bar"}';

        $savedConfig = $this->saveConfig('test/test', $arrayValue);
        $this->assertJsonStringEqualsJsonString($jsonValue, $savedConfig->getValue());
    }

    public function testJsonLoadedAsArray()
    {
        $arrayValue = ['foo' => 'bar'];

        $loadedConfig = $this->loadConfig($this->saveConfig('test/test', $arrayValue));

        $this->assertEquals($arrayValue, $loadedConfig->getValue());
    }

    public function testPhpSerializedLoadedAsArray()
    {
        $arrayValue = ['foo' => 'bar'];

        $loadedConfig = $this->loadConfig($this->saveConfig('test/test', \serialize($arrayValue)));

        $this->assertEquals($arrayValue, $loadedConfig->getValue());
    }

    private function saveConfig($path, $value): JsonSerialized
    {
        /** @var JsonSerialized $configValue */
        $configValue = $this->objectManager->create(JsonSerialized::class);
        $configValue->setPath($path);
        $configValue->setValue($value);
        $configValue->save();
        return $configValue;
    }

    private function loadConfig($savedConfig): JsonSerialized
    {
        /** @var JsonSerialized $loadedConfig */
        $loadedConfig = $this->objectManager->create(JsonSerialized::class);
        $loadedConfig->load($savedConfig->getId());
        return $loadedConfig;
    }
}
