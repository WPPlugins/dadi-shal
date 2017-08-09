<?php
/*
 * uninstall.php
 * 
 * Remove data settings when plugin is uninstalled
 * 
 * @version 	1.0 2017/01/05
 * @package 	Dadi Shal
 * @copyright	Copyright 2017 DAVIDE MURA  (email : muradavi@gmail.com)
 * @license		GNU General Public License
 * @since 		Since Release 1.0
 * 
 **********************************************************************/
 

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

// For Single site
if ( !is_multisite() ) {
	delete_option( 'dadishal_options' );
} 
// For Multisite
else 
{
	// For regular options.
	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( 'dadishal_options' );
	}
	switch_to_blog( $original_blog_id );

	// For site options.
	delete_option( 'dadishal_options' );
}
