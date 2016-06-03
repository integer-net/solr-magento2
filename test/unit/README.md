To run the unit tests, the following has to be added to the `autoload-dev` section of the root composer.json:

    "IntegerNet\\Solr\\": ["src/IntegerNet/Solr/test/unit", "vendor/integer-net/solr-base/test/Solr" ]
    
Then run `phpunit` in the module directory.
