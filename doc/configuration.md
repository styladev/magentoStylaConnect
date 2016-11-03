# Configuration
* Login into your magento backend
* Navigate to "System -> Configuration" and open the section "Styla Connect"
* Please use the "Configuration Assistant" to connect your store with Styla. The assistant will automatically configure the required `RestApi` user and will take care of the authorization process.

## Connect Assistant

The assistant will use your Styla credentials to retrieve all the needed configuration data directly from Styla, it will also create the restApi User if needed.

## Configuration Values

<table>
<tr>
<th>Name</th>
<th>Description</th>
<th>Default</th>
</tr>

<tr>
<td>Mode</td>
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

<tr>
<td>Magazine frontend “Url”</td>
<td>The url where the magazine is available</td>
<td>/magazin</td>
</tr>

<tr>
<td>Client Name*</td>
<td>Name of the styla client (this could be different from the username)</td>
<td></td>
</tr>

<tr>
<td>Enabled</td>
<td>Allows to enable or disable the magazine</td>
<td>enabled</td>
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
<td>SEO Api Url*</td>
<td>The url to the seo api server</td>
<td>seo.styla.com</td>
</tr>

<tr>
<td>Styla Magazine Api Javascript Url*</td>
<td>The path to the styla javascript</td>
<td>live.styla.com</td>
</tr>

<tr>
<td>Cache Lifetime</td>
<td>The seo data cache lifetime</td>
<td>3600</td>
</tr>
</table>

Please do not modify these values. This configuration will be automatically set during the “Styla Connect” process from the previous step.
