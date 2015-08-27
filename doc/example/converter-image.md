# Converter Product Image Example

This is  an example how to change the product image. 
For this example we assume you already have created a new styla module under you local namespace. 
In this example we will exchange every product image with the logo from styla.com.
For the real world you may want to modify the image url to point to your cdn instead of the magento base url.

1. Open the `config.xml`of your module and add the following lines:
```xml
    <default>
        <styla_connect>
            <response_fields>
                <product>
                    <image>
                        <class>your_styla/api2_converter_image</class>
                    </image>
                </product>
            </response_fields>
        </styla_connect>
    </default>
```

- Now create `app/code/local/Your/Styla/Model/Api2/Converter/Image.php`. With the following content:
```php
    class Your_Styla_Model_Api2_Converter_Image
        extends Styla_Connect_Model_Api2_Converter_Product_Image
    {
        /**
         * @param Varien_Object $dataObject
         */
        public function runConverter(Varien_Object $dataObject)
        {
            /** @var Mage_Catalog_Model_Product $dataObject */
            $stylaField = $this->getStylaField();
            $dataObject->setData($stylaField, 'http://www.styla.com/themes/stylaV2/assets/images/styla_logo.png');
        }
    }
```
- Clear the cache and your done. Now all product images displayed in the Styla Editor should be the styla_logo.png.