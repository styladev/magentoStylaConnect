# Customization's


##Best Practice
* Do not modify the plugin directly you will loose the benefit of easy updates!
* Create your own module the depends on the `Styla_Connect` module
```xml
<?xml version="1.0"?>
<config>
    <modules>
        <{namespace}_Styla>
            <active>true</active>
            <codePool>local</codePool>
            <depends>
                <Styla_Connect/>
            </depends>
        </{namespace}_Styla>
    </modules>
</config>
```
do not omit the `depends` tag it will assure that your customization's will work as intended!
 

## Add To Cart
Styla allows the customer to directly add products from the magazin into the cart.
While adding the product to the cart should work out of the box, 
the feedback to the customer like an overlay or an increment of the numbers in the cart the customer does not. 
However we tried to make it as easy as possible for you to make the adjustments while maintaining the update 
capability of the module.

To run your own javascript instead of the default one please copy `/skin/frontend/base/default/js/styla/connect.js` 
into your own theme folder and change it in the way you need it.
The function `stylaUpdateCart` will be called from the styla javascript directly after a product was added. 
The `data` argument contains all the data returned by the ajax call.

Example response:

```json
{
    "html" : {
        "minicart_content" : "…"
    },
    "meta" : {
        "grand_total" : 173.2,
        "subtotal" : 160,
        "subtotal_with_discount" : 160,
        "num_items" : 2,
        "items_qty" : 1
    }
}
```

### Returning Additional html (like for an overlay)

1. Add a new layout xml file to your `{namespace}_Styla` configuration
    ```xml
        <frontend>
            <layout>
                <updates>
                    <{namespace}_styla>
                        <file>{namespace}_styla.xml</file>
                    </{namespace}_styla>
                </updates>
            </layout>
        </frontend>
    ```
    
2. Create `{namespace}_styla.xml` file in your themes layout directory with for example this content:
    ```xml
    <?xml version="1.0"?>
    <layout version="0.1.0">
        <styla_connect_product_cart_add>
            <reference name="styla.cart_update_content">
                <block type="core/text" name="example">
                    <action method="setText">
                        <text>test-text-content</text>
                    </action>
                </block>
            </reference>
        </styla_connect_product_cart_add>
    </layout>
    ```
    
3. Clear the cache
4. If you now add a product from the magazine to your cart the response `html` part should have an additional entry `example`
    ```json
    {
        "html" : {
            "minicart_content" : "…",
            "example": "test-text-content"
        },
        "meta" : "..."
    }
    ```

5. You can now access the new html within the `stylaUpdateCart` for example you could add
    ```javascript
    window.stylaUpdateCart = function stylaUpdateCart(data) {
        //...
        alert(data.html.example);
        //...
    };
    ```

which should prompt you with an alert box after you have added a product to the cart.

## Change Product Values Returned By The Api

Sometime its need to change a value returned to styla.
A good example is the product image attribute in case you are using a cdn you may want to manipulate the image url
before returning it. One way to do this are " converters" anther is the usage of events.

### Converters
"Converters" are an easy way to change values or keys for the data returned by the api without the need to use magento rewrites.
Every converter must always extend `Styla_Connect_Model_Api2_Converter_Abstract`

Converters do not return a value but change the object itself to allow max flexibility.

The basic value converters are defined int the `config.xml` of the styla module.

A basic example for an image converter can be found [here](example/converter-image.md)

### Events

* `styla_connect_api_prepare_response_before`
* `styla_connect_api_prepare_response_after`

You can use this events to manipulate the returned objects in the way you want.

`styla_connect_api_prepare_response_before` runs before the `converters` are applied and `*_after` afterwards

A full list of events can be found [here](events.md). 
