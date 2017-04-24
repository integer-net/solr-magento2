<?php
namespace {
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'IntegerNet_Solr',
        __DIR__ . '/main/src'
    );
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'IntegerNet_SolrCategories',
        __DIR__ . '/categories/src'
    );
}



