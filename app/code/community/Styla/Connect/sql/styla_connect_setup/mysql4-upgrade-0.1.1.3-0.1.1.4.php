<?php
$connector = Mage::getModel('styla_connect/styla_api_oauth_connector');

if(false !== $connector->getAdminUser(false)) {
    //there's an existing admin user account, we need to update it's allowed rest attributes

    $connector->addStylaAttributesToAdminRole();
}