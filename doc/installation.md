# Installation

If you currently have version 0.1.1.11 or older installed, please first go throught the below for 0.1.1.12 and only then install 0.2.0.0 or later. There are changes in the plugin structure that potentially might cause issues when updating directly from pre-0.1.1.12 to 0.2.0.0 or later.

* Copy the content of the zip file into your "Magento Root" directory
* Change the folder permissions recursivly if needed
* Clear the cache
* Make sure you relogin in magento admin if getting a 404 error during changing styla plugin settings
* Make sure to activate [Magento REST API](http://devdocs.magento.com/guides/m1x/api/rest/introduction.html) to share product information with Styla. You have to configure the below in your .htaccess:
   * change `Options +FollowSymLinks` to `Options +FollowSymLinks -MultiViews` 
   * insert `RewriteRule ^api api.php?type=rest [QSA,L]` directly under `#RewriteBase /magento/`
* Start the [Configuration](configuration.md)

### Please do not create any subpages in your CMS or directories for your magazine. The plugin itself will take care of setting up the /magazine/ (or any other) page on which the magazine will appear and of the routing as well. 
