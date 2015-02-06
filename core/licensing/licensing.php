<?php
/*
 * Find and replace the following (also in settings page!):
 * EDD_RENDER_EDD_NAME
 * RENDER_EDD_VERSION
 * Render_EDD
 * renderedd_license_key
 * renderedd_license_status
 * edd_renderedd_nonce
 * renderedd_license_deactivate
 * renderedd_license_activate
 */

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
if ( ! defined( 'EDD_REALBIGPLUGINS_STORE_URL' ) ) {
	define( 'EDD_REALBIGPLUGINS_STORE_URL', 'http://realbigplugins.com' );
}

// the name of your product. This should match the download name in EDD exactly
define( 'EDD_RENDER_EDD_NAME', 'Render Easy Digital Downloads' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( __DIR__ . '/EDD_SL_Plugin_Updater.php' );
}

add_action( 'admin_init', function() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'renderedd_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( EDD_REALBIGPLUGINS_STORE_URL, RENDER_EDD_PATH . 'render-edd.php', array(
			'version' 	=> RENDER_EDD_VERSION, 				// current version number
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => EDD_RENDER_EDD_NAME, 	// name of this plugin
			'author' 	=> 'Joel Worsham & Kyle Maurer'  // author of this plugin
		)
	);
}, 0 );

/************************************
* this illustrates how to activate 
* a license key
*************************************/


add_action('admin_init', function() {

	// listen for our activate button to be clicked
	if( isset( $_POST['renderedd_license_activate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'edd_renderedd_nonce', 'edd_renderedd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'renderedd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_RENDER_EDD_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, EDD_REALBIGPLUGINS_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'renderedd_license_status', $license_data->license );
	}
});


/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

add_action('admin_init', function() {

	// listen for our activate button to be clicked
	if( isset( $_POST['renderedd_license_deactivate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'edd_renderedd_nonce', 'edd_renderedd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'renderedd_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_RENDER_EDD_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, EDD_REALBIGPLUGINS_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' )
			delete_option( 'renderedd_license_status' );
	}
});