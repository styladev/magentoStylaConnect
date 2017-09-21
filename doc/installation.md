# Installation

* Copy the content of the zip file into your "Magento Root" directory
* Change the folder permissions recursivly if needed
* Clear the cache
* Make sure you relogin in magento admin if getting a 404 error during changing styla plugin settings
* Make sure to activate [Magento REST API](http://devdocs.magento.com/guides/m1x/api/rest/introduction.html) to share product information with Styla. You have to configure the below in your .htaccess:
- change `Options +FollowSymLinks` to `Options +FollowSymLinks -MultiViews` 
- insert `RewriteRule ^api api.php?type=rest [QSA,L]` directly under `#RewriteBase /magento/`
* Start the [Configuration](configuration.md)

### Please do not create any subpages in your CMS or directories for your magazine. The plugin itself will take care of setting up the /magazine/ (or any other) page on which the magazine will appear and of the routing as well. 
