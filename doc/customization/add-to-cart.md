# Api - Customization's

**Before continue please make sure you read the "[Customization’s Guide](./../customization.md)"**

* [Adding Additional Attributes](#adding-additional-attributes)
* [Modify Values Returned By The Api](#modify-values-returned-by-the-api)


## Adding Additional Attributes

Additional attributes can easily be added by creating a new `api2.xml` under you local styla module.
For example if you like to also return the product `description`attribute you want your `api2.xml` to look like this:
```xml
<?xml version="1.0"?>
<config>
    <api2>
        <resources>
            <styla_product>
                <attributes>
                    <description>Description</description>
                </attributes>
            </styla_product>
        </resources>
    </api2>
</config>
```

## Modify Values Returned By The Api

Sometime its need to change a value returned to styla.
A good example is the product image attribute in case you are using a cdn you may want to manipulate the image url
before returning it. One way to do this are "converters" anther is the usage of events.

### Converters
**Converters** are an easy way to change values or keys for the data returned by the api without the need to use magento rewrites.
Every converter must always extend `Styla_Connect_Model_Api2_Converter_Abstract`

Converters do not return a value but change the object itself to allow max flexibility.

The basic value converters are defined int the `config.xml` of the styla module.

A basic example for an image converter can be found [here](/example/converter-image.md)