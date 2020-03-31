# Configuration

In order to set up connection between your Magento and Styla, please do the following:
* Login into your magento backend
* Navigate to "System -> Configuration" and open the section "Styla Connect"
* Please use the "Configuration Assistant" to connect your store with Styla. The assistant will automatically configure the required `RestApi` user and will take care of the authorization process: 
  * Start with clicking the orange "Click here if you need to re-connect your store to the Styla API" 
  * Enter the email and password provided by Styla Account Manager. If there are several magazines that you are going to use, make sure to select a correct store view from the drop-down list above to match the email and password as this will define which magazine is shown on which store view.
  ![Styla Connect Process](/doc/styla_connect_process.png)
  * Click the orange "Send Login Data to Styla" button top-right

## Connect Assistant

The assistant will use your Styla credentials to retrieve all the needed configuration data directly from Styla, it will also create the restApi User if needed.

## Configuration Values

Once the connect process is done you will be able to change the below values for each store view/magazine.

Styla plugin version [0.2.0.0](https://github.com/styladev/magentoStylaConnect/releases/tag/v0.2.0.0) introduced a possibility to set up several magazines for the same store view. That’s why Styla plugin settings are now separated into two Magento menus:

### System > Configuration > Styla Connect (for all magazines):

<table>
<tr>
<th>Name (pre-0.2.0.0)</th>
<th>Description</th>
<th>Default</th>
</tr>

<tr>
<td>Use Relative Product Urls</td>
<td>Defines how product URLs for magazine front-end will be created:

<ul>
<li>yes - the product urls generated for the stories will be relative to store domain (ie: /product-name-SKU/)
</li>
<li>no - no - full urls will be generated (ie: http://www.yourdomain.com/product-name-SKU/)
</li>
</ul>

</td>
<td>no</td>
</tr>

<tr>
<td>Manufacturer Attribute</td>
<td>Magento field that will be used to display manufacturer name</td>
<td>Manufacturer</td>
</tr>

<tr>
<td>Cache Lifetime</td>
<td>The seo data cache lifetime</td>
<td>3600</td>
</tr>

<tr>
<td>Maximum Levels of Categories Loaded at Once</td>
<td>If you run a store with a category tree consisting of multiple levels, you may choose to limit the number of the branches loaded into styla backoffice editor at once, for performance reasons)</td>
<td>No limit - load all categories in a single API call</td>
</tr>

<tr>
<td>Is Developer Mode (Mode)</td>
<td>The Styla Connect endpoint:

<ul>
<li>Production -&gt; Uses our live system

</li>
<li>Stage -&gt; Uses our dev system

</li>
</ul>

</td>
<td>Production</td>
</tr>

</table>

Please switch the Developer Mode **ON**, then update the settings to the following ones:  
 `https://client-scripts.styla.com`   
 `http://seoapi.styla.com`   
 `https://client-scripts.styla.com`   
as shown on this screen shot:
![Styla New JS source](/doc/styla-plugin-client-scripts.png)  
Then switch the Developer Mode dropdown to **OFF again** and click the **Save Config** button top-right. 

### CMS > Styla Pages (for each Styla path separately, set up new configs there):

<table>
<tr>
<th>Name (pre-0.2.0.0)</th>
<th>Description</th>
<th>Default</th>
</tr>

<tr>
<td>Store name</td>
<td>Defines on which store view will your magazine be displayed</td>
<td>default store in your Magento</td>
</tr>

<tr>
<td>Active (Enabled)</td>
<td>Allows to enable or disable the magazine</td>
<td>enabled</td>
</tr>

<tr>
<td>Client Name*</td>
<td>Name of the styla client (this could be different from the username)</td>
<td></td>
</tr>

<tr>
<td>Front Name (Magazine frontend “Url”)</td>
<td>The part of the URL after your store view part where the magazine is available. Leave empty to override the home page content from Magento with Styla page with blank path. </td>
<td>/magazin</td>
</tr>

<tr>
<td>Use Magento Layout</td>
<td>Showing the current magento theme around the magazine:

<ul>
<li>yes - the Styla magazine page will be wrapped within a normal Magento header and
footer

</li>
<li>no - only the magazine content will be visible

</li>
</ul>

</td>
<td>yes</td>
</tr>

<tr>
<td>Include in navigation (Add Magazine Link to Navigation)</td>
<td>Turn to "No" if you don't want the magazine link to be available in the menu (good for testing the magazine before disclosing to your audience)</td>
<td>Yes</td>
</tr>

<tr>
<td>Navigation label (Label for the Magazine Menu Link)</td>
<td>Enter any name your audience see on the menu linking to the magazine</td>
<td>Magazine</td>
</tr>

</table>

/* - Please do not modify these values. This configuration will be automatically set during the “Styla Connect” process from the previous step.

For Styla plugin version [0.1.1.12](https://github.com/styladev/magentoStylaConnect/releases/tag/v0.1.1.12) and earlier all the settings above are available in **System > Configuration > Styla Connect**  as there is only one magazine available per store view. 

### Using Styla content on Magento home page

Starting with version [1.1.0.0](https://github.com/styladev/magentoStylaConnect/releases/tag/v1.1.0.0) you can override the default Magento content on your home page (the / path) with Styla content. In order to do this, please:

1. Create a page with blank path in Styla CMS at https://editor.styla.com/:
![Page slug setting in Styla Editor](/doc/home-page-path.png)  
2. Add a new config in CMS > Styla Pages with blank "Front name" field: 
![Blank path set for a Styla home page in Magento Backend](/doc/styla-content-on-home-page.png) 
3. Switch on the "Enabled Styla Homepage" setting in System > Configuration > Styla Connect:
![Enabling Styla home page in Magento Backend](/doc/home-page-override-enabled.png)

If you have pages for other Store Views with a locale in their paths, the contents of the "Front name" field needs to match the path of page you create in Styla Editor. 

## StylaApiAdminUser and StylaApi2Role - please don't modify

During the connect process a new 'StylaApiAdminUser' user with a 'StylaApi2Role' will be created. You can see it in System/Permissions/Users:

![Styla User](/doc/styla_user.png)

Please don't modify these user's and role's permissions. It needs the following permissions to propagate product data via Magento REST API:

![Styla Role](/doc/styla_role.png)

## Turn off http password-protection on API endpoints

Styla sources product data from Magento REST API which is protected by OAuth. Our application cannot access the endpoints if they are in addition password-protected, which is a common solution for protecting development and stage environments on which the plugin is first installed.

If your stage environment is password protected, please turn it off for `http://yourdomain/api/*` where the enddpoints are located. 

Alternatively, turn password-protection on your stage altogether for the time Styla is using it. 

## SEO Content from Styla's SEO API

The module uses data from Styla's SEO API to:
* generate tags like: meta tags including `<title>`, canonical link, og:tags, static content inserted into <body>, `robots` instructions
* insert these tags accordingly into HTML of the template the page with Styla content uses
  
This is done to provide search engine bots with data to crawl and index all Styal URLs, which are in fact a Single-Page-Application.

Once you install and configure the module, please open source of the page on which your Styla content is embedded and check if none of the tags mentioned below are duplicated. In case `robots`or `link rel="canonical"` or any other are in the HTML twice, make sure to remove the original ones coming from your default template. Otherwise search engine bots might not be able to crawl all the Styla content or crawl it incorrectly. 

You can finde more information on the SEO API on [this page](https://styladocs.atlassian.net/wiki/spaces/CO/pages/9961486/SEO+API+and+Sitemaps+Integration)
