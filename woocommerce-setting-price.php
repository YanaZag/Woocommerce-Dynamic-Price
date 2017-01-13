<?php
/*
Plugin Name:  Woocommerce Dynamic Price
Plugin URI: http://testplugins.barbotkin.com
Description:  Woocommerce Dynamic Price for country and city
Version: 1.0.0
Author: Yana
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	define( 'SP_VERSION', '1.0.0' );
	define( 'SP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); 
	define( 'SP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'SP_PLUGIN_CLASS', plugin_dir_path( __FILE__ ) . 'class/');
	define( 'SP_API_KEY', 'AIzaSyD-7GB6d0SVlpZ1xI_ERfcZzrSjY4Kys8g');


	require_once( SP_PLUGIN_CLASS . 'class.woocommerce-setting-price.php' );

	add_action( 'init', array( 'Woocommerce_Setting_Price', 'init' ));

}