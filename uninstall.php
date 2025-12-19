<?php
/**
 * Uninstall Welcome Dashboard
 *
 * Removes all plugin data when the plugin is deleted.
 *
 * @package WelcomeDashboardForWordPress
 * @since 1.0.0
 */

// Exit if accessed directly or not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete main plugin options.
delete_option( '_umy_wdw_templates' );
delete_option( 'umy_wdw_hide_from_subsites' );
delete_option( 'umy_wdw_version' );

// Delete any transients.
delete_transient( 'umy_wdw_settings_saved' );

// For multisite, clean up each site's options.
if ( is_multisite() ) {
	$umy_wdw_sites = get_sites( array( 'fields' => 'ids' ) );
	
	foreach ( $umy_wdw_sites as $umy_wdw_site_id ) {
		switch_to_blog( $umy_wdw_site_id );
		
		delete_option( '_umy_wdw_templates' );
		delete_option( 'umy_wdw_hide_from_subsites' );
		delete_transient( 'umy_wdw_settings_saved' );
		
		restore_current_blog();
	}
}
