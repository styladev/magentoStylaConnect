# Add To Cart

**Before continue please make sure you read the "[Customization’s Guide](./../customization.md)"**

* [Update Mini Cart](#update-mini-cart)
* [Returning Additional html](#returning-additional-html)

## Update Mini Cart
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

### Returning Additional html

For example if you want to use an overlay.

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
