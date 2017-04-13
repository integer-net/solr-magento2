IntegerNet_Solr for Magento 2
===============
Release Notes

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