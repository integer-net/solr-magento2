<?php
namespace {
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'IntegerNet_Solr',
        __DIR__ . '/src'
    );
}

namespace IntegerNet\SolrSuggest\Implementor\Factory
{
    class_alias(
        AppFactory::class,
        AppFactoryInterface::class);

    if (! \interface_exists(AppFactoryInterface::class)) {
        /** @deprecated this is an alias for AppFactory due to limitations of the Magento 2 object manager */
        interface AppFactoryInterface extends AppFactory {}
    }
}

namespace IntegerNet\Solr\Implementor
{
    class_alias(
        SolrRequestFactory::class,
        SolrRequestFactoryInterface::class);

    if (! \interface_exists(SolrRequestFactory::class)) {
        /** @deprecated this is an alias for SolrRequestFactory due to limitations of the Magento 2 object manager */
        interface SolrRequestFactoryInterface extends SolrRequestFactory {}
    }
}