<?php 
/*
Plugin Name: Add on for SendPortal on Gravity Forms
Description:       Add on for SendPortal on Gravity Forms
Version:           1.0
Author:            Parisa Parvizi
Author URI:       https://parvizi96.ir
*/

define('GF_Sendportal_ADDON_VERSION', 1.0);
add_action( 'gform_loaded', array( 'GF_sendportal_AddOn_Bootstrap', 'load' ), 5 );
class GF_Sendportal_AddOn_Bootstrap {
    public static function load() {
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
        require_once( 'sendportal-functions.php' );
        GFAddOn::register( 'GFSendportalAddOn' );
    }
}

function gf_sendportal_addon() {
    return GFSendportalAddOn::get_instance();
}