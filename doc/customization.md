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
 

## Customization's

 * [Add To Cart](/customization/add-to-cart.md)
 * [Changes Api](/customization/api.md)
 


## Events

* `styla_connect_api_prepare_response_before`
* `styla_connect_api_prepare_response_after`

You can use this events to manipulate the returned objects in the way you want.

`styla_connect_api_prepare_response_before` runs before the `converters` are applied and `*_after` afterwards

A full list of events can be found [here](events.md). 
