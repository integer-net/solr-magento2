IntegerNet_Solr for Magento 2
===============
Installation Instructions

Preparation of the Solr Server
---------------
You need an Apache Solr server in order to run the IntegerNet_Solr module. Apache Solr is a free software (open source).
Please see 
[the official installation instructions for Apache Solr](https://cwiki.apache.org/confluence/display/solr/Installing+Solr)
for information on how to install it or ask your administrator or hosting provider. Please install Apache Solr in the 
same network or on the same server as your Magento installation in order to improve performance.
1. Create at least one new Solr core. If you don't know how, please refer to your administrator or hosting partner.
2. You can download the configuration files which should be installed in the "conf" directory of your core(s) from
[https://github.com/integer-net/solr-magento1/tree/master/solr_conf](https://github.com/integer-net/solr-magento1/tree/master/solr_conf).
Please don't use any other configuration files unless you know exactly what you are doing.

From there, you have two options:

a) Installation via Composer
---------------
This is the preferred method. Please follow these steps: 

1. Make sure you have sent us your GitHub account name and we have granted you the permissions to the Repositories on GitHub. 
To check, try to access https://github.com/integer-net/solr-magento2/.
2. Add the necessary private repositories by calling the following commands from the command line inside your Magento base directory:
```
composer config repositories.solr-magento2 vcs https://github.com/integer-net/solr-magento2/
composer config repositories.solr-magento2-autosuggest vcs https://github.com/integer-net/solr-magento2-autosuggest/
composer config repositories.solr-pro vcs https://github.com/integer-net/solr-pro/

```
3. Download the repositories by calling:
```
composer require integer-net/solr-magento2
```
4. Activate the module by calling:
```
bin/magento module:enable IntegerNet_Solr
bin/magento module:enable IntegerNet_SolrCategories
```
5. Install the module by calling:
```
bin/magento setup:upgrade
```
6. Configure the module in the Magento Administration area. Go to System -> Configuration -> Services -> IntegerNet_Solr 
for that, activate the module and enter your server data. Please make sure that there are no errors displayed above the configuration
area after saving.
7. Run the indexers by calling:
```
bin/magento indexer:reindex integernet_solr
bin/magento indexer:reindex integernet_solr_categories
```
8. You are ready. Try the functionality by typing a few letters into your store's frontend search box.

b) Installation from Package
---------------
If you received a package from us, you can install it as follows:

1. Extract all files from the "src" directory inside the package to your Magento main directory.
2. Continue the installation as described from point 4 onwards in "a) Installation via Composer" above

For additional information and configuration setting please see the module documentation at [http://integernet-solr.com/documentation/](http://integernet-solr.com/documentation/).
