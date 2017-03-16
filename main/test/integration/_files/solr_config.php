<?php

$solrConfig = [
    'integernet_solr/server/host' => 'localhost',
    'integernet_solr/server/port' => '8983',
    'integernet_solr/server/path' => '/solr/',
    'integernet_solr/server/core' => 'solr-magento2-tests',
];

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig */
$mutableScopeConfig = $objectManager->create(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);

foreach ($solrConfig as $path => $value) {
    $mutableScopeConfig->setValue($path, $value, 'stores', 'default');
}