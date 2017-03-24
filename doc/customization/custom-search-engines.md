# Api - Using Custom Search Engines

**Before continue please make sure you read the "[Customization’s Guide](./../customization.md)"**

* [Adding Additional Attributes](#adding-additional-attributes)
* [Modify Values Returned By The Api](#modify-values-returned-by-the-api)


## Searching For Products

The collection of products returned by the REST Api can be filtered by using search terms.
This URI will return product collection filtered by your _YOUR_SEARCH_TERM_:
`http://yourmagentodomain.com/api/rest/styla_product?search={YOUR_SEARCH_TERM}[&store=STORE_ID]`

**By default, the Magento CatalogSearch Fulltext Index** is used for filtering the results.

If your Magento instance uses a custom search engine (like Algolia, Solr, or similar), it's possible that the default search solution will not work for you. In that case, you'll need to implement a bridge between your custom search engine and the REST response containing the products matching your search.

## Custom Search Engines

If the default search doesn't work for your search engine, you can easily add your own handler for the searches. The easiest way to do this, is to add an observer for the `styla_connect_search_products` event, exposed by the _Styla_Connect_Model_Api2_Product_Rest_Admin_V1_ class.

``The goal will be to disable the default search handler, and return entity_ids matching the search term back to the REST response.``

**_First_, if you haven't already done so, please follow the basic customization guide and create your localized version of the Styla_Connect module: "[Customization’s Guide](./../customization.md)"**

#
Once you have a local version of the module ready, you can add the following section to your module's _config.xml_ file, to start observing the search event:

`{yournamespace}_Connect/etc/config.xml`
```xml
<?xml version="1.0"?>
<config>
    <modules>
        <{yournamespace}_Connect>
            {...}
        </{yournamespace}_Connect>
    </modules>
    {...}
    <global>
        <events>
            <styla_connect_search_products> <!-- the product search event -->
                <observers>
                    <styla_connect_search>
                        <class>{yournamespace}_Connect_Model_Observer</class>
                        <method>customProductSearch</method>
                    </styla_connect_search>
                </observers>
            </styla_connect_search_products>
        </events>
    </global>
```

#
Now, you need to create the Observer class, as was defined above:

`{yournamespace}_Connect/Model/Observer.php`
```php
<?php
class {yournamespace}_Connect_Model_Observer {
    public function customProductSearch(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        
        /** @var Varien_Object $transportObject */
        $transportObject = $event->getTransportObject(); //this object is used to return the search result back to the REST response
        
        /** @var Mage_Core_Model_Store $store */
        $store = $event->getStore();
        
        /** @var string $searchTerm */
        $searchTerm = $event->getSearchTerm();
        
        /**
            TODO: depending on the implementation of the Custom Search Engine that you use,
            you need to retrieve the entity_ids of all products matching the $searchTerm in given $store.
            Since this depends on your custom search engine, we cannot provide this part of the code to you.
            
            Your code should return an array containing the product entity IDs, like: array(1, 2, 3, ...)
        */
        
        //example:
        $matchingProductIds = array(1, 2, 3);
        
        //Once we have the result, we need to return it back to the REST response.
        //This is done by filling the Transport Object exposed by this event.
        $transportObject->setProductIds($matchingProductIds); //return the array of matching entity_ids
        
        $transportObject->setIsProcessed(true); //IMPORTANT: this tells the system that a custom search was finished, and the default search is not needed, anymore.
    }
}
```

At this point, your new observer should respond to all search queries and filter the returned product collection by the `$matchingProductIds` that you provided.

**Note:** Please make sure that you don't forget to set the `$transportObject->setIsProcessed(true);` flag to TRUE on the transport object. This flag tells us that you have a custom search handler running. If you fail to do that, after your custom search is complete, the system will do the default search on top of it, effectively cancelling your search out.