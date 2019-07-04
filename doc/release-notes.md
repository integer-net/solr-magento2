IntegerNet_Solr for Magento 2
===============
Release Notes

Version 1.7.1 (Jul 4, 2019)
----------------------------

- Compatibility to Magento 2.3.2 (thanks Gerrit Pechmann!)

Version 1.7.0 (May 22, 2019)
----------------------------

- Compatibility to Magento 2.3.1
- Define correct indexer dependencies so they always run in the right order (Thanks Dominik Meglič!)
- Abandoned compatibility to Magento 2.1.x and Php < 7.1 Use the branch "2.1-lts" if you need to use any of those.

Version 1.6.2 (Feb 13, 2019)
----------------------------

- Fix another bug with the Magento 2 Commerce staging feature on category pages

Version 1.6.1 (Feb 11, 2019)
----------------------------

- Fix a bug with the Magento 2 Commerce staging feature on category pages

Version 1.6.0 (Feb 4, 2019)
---------------------------

- Improve search results by boosting full word matches

Version 1.5.1 (Dec 21, 2018)
----------------------------

- Add section about command line tools to documentation

Version 1.5.0 (Dec 19, 2018)
----------------------------

- Add seperate command line tools for full and partial reindexing
- Compatibility fixes for Magento 2.2
- Add compatibility to Magento 2.2 Commerce Edition
- Fix bugs with area emulation on command line
- Improve declaration of category attributes
- Fix bug with price filter and special prices
- Add automatical reindexing of products if they are assigned to categories in the category view

Version 1.4.2 (Dec 21, 2017)
--------------

- Fix Category URLs in autosuggest if links to category pages are activated.
- Show complete category path in autosuggest if activated in configuration
- Optimize product search results for cases when parts of search query occur in different attributes
- Full compatibility to Magento 2.2 regarding (using JSON serialization now)
- Fix Autosuggest URL for stores with different base url
- Use require.js to handle filter options link
- Hide categories tab while initialising search results page
- Fix behavior of search_weight field for boosting attributes
- Change search_weight field for boosting attributes from dropdown to text field
- Add category attribute for solr priority
- Solve problems with indexing on some stores
- Eliminate all calls of `getCustomAttribute()` in order to improve indexing performance
- Support really small boost values (like 0.00000001)
- Don't store categories in cache which don't belong to the correct category tree

Version 1.4.1 (Oct 20, 2017)
---------------

- Fix behavior of product attributes "Solr Priority" and "Exclude this Product from Solr Index"
- Define compatibility to final Magento 2.2.0 in composer.json

Version 1.4.0 (Oct 11, 2017)
---------------

- Add configuration to enable/disable fuzzy for categories search in autosuggest
- Add configuration to choose sorting of filter options (by alphabet / by number results / by admin sorting)
- Fix filters for swatches attributes
- Fix URLs in autosuggest for multiple store views
- Fix display of category suggestions in case of more than one category with the same name

Version 1.3.2 (Aug 23, 2017)
---------------

- Introduction of new events around product collection
- Skip indexing a product instead of aborting if child product cannot be loaded 

Version 1.3.1 (Aug 17, 2017)
---------------

- Compatibility fixes for Magento 2.2 RC20

Version 1.3.0 (Aug 17, 2017)
---------------

- Show products which are out of stock depending on the configuration for search results, category pages and autosuggest results

Version 1.2.0 (Aug 17, 2017)
---------------

- Introduce multiselect filters for search pages and categories
- Show products which are out of stock depending on the configuration for category pages
- Fix translation of autosuggest if being generated from frontend
- Optimize loading of autosuggest so window with outdated information doesn't open and possibly replaces newer window
- Add additional backend translations (de_DE)

Version 1.1.3 (May 16, 2017)
---------------

- Fix bug during DI compilation

Version 1.1.2 (May 15, 2017)
---------------

- Improve search requests for queries including numbers
- Fix bug with fuzzy search for categories not working
- Fix bug with autosuggest in multistore environments
- Add check for correct attribute configuration

Version 1.1.1 (Apr 12, 2017)
---------------

- Support for installing the module as a package to `app/code` and `lib/internal/` if installation via composer
    is not possible

Version 1.1.0 (Apr 11, 2017)
---------------

- Fix filtering
- Searching through categories
- Display category search results on search results page with a tabbed layout
- Deliver category pages with product information from Solr 
- Redirect to product page on a 100% match of the search query with one of the configured product attributes
- Redirect to category page on a 100% match of the search query with one of the configured category attributes
- Provide number of total search results for autosuggest (not used by default)

Version 1.0.0 (Feb 21, 2017)
---------------

- Search functionality via Solr
- Autosuggest window, running without Magento access for performance reasons
- Extensive configuration
- Boosting of product attributes
- Boosting of products and categories
