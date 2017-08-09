<?php

/* 
 * Plugin Name: Dadi Share From Post List
 * Plugin URI: http://www.iljester.it/portfolio/share-from-post-list/
 * Description: Share your posts from post/page or custom post type list in admin panel
 * Version: 1.0
 * Author: Davide Mura
 * Author URI: http://www.iljester.it/davide/
 */
/*  Copyright 2017 DAVIDE MURA  (email: muradavi@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * Exit if not exists is_admin();
 */
if (!function_exists('is_admin')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

/*
 * Constants
 */
define( "DADISHAL_DIR", plugin_dir_path( __FILE__ ) );
define( "DADISHAL_URL", plugin_dir_url( __FILE__ ) );
define( 'DADISHAL_BASENAME', plugin_basename( __FILE__ ) );
define( 'DADISHAL_DOMAIN', 'dadishal' );
define( 'DADISHAL_HOST_DOMAIN', $_SERVER['HTTP_HOST'] );

/*
 * Add language domain
 */
add_action( 'plugins_loaded', 'dadishal_load_textdomain' );
function dadishal_load_textdomain() {
  load_plugin_textdomain( DADISHAL_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

/*
 * Add css options panel
 */
function dadishal_load_styles() {
	wp_enqueue_style('dadishal-options-css', DADISHAL_URL . 'css/dadishal-options.css' );
}
add_action( 'admin_enqueue_scripts', 'dadishal_load_styles' );

/*
 * Add socicons
 * By socicon.com - http://www.socicon.com
 * V. 3.0.5
 */
function dadishal_load_socicons() {
	wp_enqueue_style('dadishal-socicons', DADISHAL_URL . 'socicon/socicons.css');
}
add_action( 'admin_enqueue_scripts', 'dadishal_load_socicons' );

/*
 * Add option page
 */
function dadishal_add_option_page() {
add_options_page('Dadi Shal', 'Dadi Shal', 'administrator', 'dadishal-options-page', 'dadishal_update_options_form');
}
add_action('admin_menu', 'dadishal_add_option_page');

/*
 * Add options
 */
function dadishal_activate_set_default_options()
{
	$post_types = get_post_types( array( '_builtin' => false ) );
	
	add_option( 'enable_facebook', true );
	add_option( 'enable_twitter', true );
	add_option( 'enable_googleplus', true );
	add_option( 'enable_linkedin', true );
	add_option( 'enable_pinterest', false );
	add_option( 'enable_stumbleupon', false );
	add_option( 'twitter_username', '' );
	add_option( 'display_buttons_on_list_posts', true );
	add_option( 'display_buttons_on_list_pages', false );
	foreach( $post_types as $post_type ) {
		add_option( 'display_buttons_on_list_' . $post_type, false );
	}
	add_option( 'open_window', 'popup' );
}
register_activation_hook( __FILE__, 'dadishal_activate_set_default_options');

/*
 * Register options
 */
function dadishal_register_options_group() {
	register_setting('dadishal_options_group', 'dadishal_options', 'dadishal_validate_options');
}
add_action ('admin_init', 'dadishal_register_options_group');

/*
 * Set defaults options
 */
$defaults = array(

	'enable_facebook' => true,
	'enable_twitter' => true,
	'enable_googleplus' => true,
	'enable_linkedin' => true,
	'enable_pinterest' => false,
	'enable_stumbleupon' => false,
	'twitter_username' => '',
	'display_buttons_on_list_posts' => true,
	'display_buttons_on_list_pages' => false,
	'open_window' => 'popup'

);

/*
 * The Input Form
 */
function dadishal_update_options_form() { 
	
		global $defaults;

		// add custom post types to defaults options
		$post_types = get_post_types( array( '_builtin' => false ) );
		foreach( $post_types as $post_type ) {
			$defaults['display_buttons_on_list_' . $post_type] = false;
		}
	
		// delete options
		if( isset( $_REQUEST['action'] ) && ( 'reset' == $_REQUEST['action'] ) ) 
			update_option( 'dadishal_options', $defaults );
	
		// create options
		$options = get_option( 'dadishal_options', $defaults ); ?>

			<div id="dadishal-container" class="wrap">
				<div class="icon32" id="icon-options-general"><br /></div>
				<h2><?php _e('Dadi Shal - Display share buttons in the posts, pages and custom posts type list', 'dadishal' ); ?></h2>
				<form id="dadishal-form" method="post" action="options.php">
				<?php settings_fields('dadishal_options_group'); ?>
				<?php do_settings_sections( 'dadishal_options_group' ); ?>

					<div class="box">
						<h3><?php _e( 'Check buttons that you wish to display in the lists', DADISHAL_DOMAIN ); ?></h3>
						<p>
							<input type="checkbox" id="enable-facebook" name="dadishal_options[enable_facebook]" value="1" <?php echo checked( 1, $options['enable_facebook'] ); ?>>
							<label for="enable-facebook"><?php _e( 'Enable Facebook', DADISHAL_DOMAIN ); ?></label>
						</p>
						<p style="position:relative;">
							<input type="checkbox" id="enable-twitter" name="dadishal_options[enable_twitter]" value="1" <?php echo checked( 1, $options['enable_twitter'] ); ?>>
							<label for="enable-twitter"><?php _e( 'Enable Twitter', DADISHAL_DOMAIN ); ?></label>
							<span class="twitter-username" style="position:absolute;top:0;left:auto;<?php if( !$options['enable_twitter'] ) echo 'display:none'; ?>;">
								&nbsp;&nbsp;<input type="text" id="twitter-username" name="dadishal_options[twitter_username]" value="<?php echo sanitize_text_field( $options['twitter_username'] ); ?>" />
								<label for="twitter-username"><?php _e( 'Insert your twitter username', DADISHAL_DOMAIN ); ?></label>
							</span>
						</p>
						<p>
							<input type="checkbox" id="enable-googleplus" name="dadishal_options[enable_googleplus]" value="1" <?php echo checked( 1, $options['enable_googleplus'] ); ?>>
							<label for="enable-googleplus"><?php _e( 'Enable Google Plus', DADISHAL_DOMAIN ); ?></label>
						</p>
						<p>
							<input type="checkbox" id="enable-linkedin" name="dadishal_options[enable_linkedin]" value="1" <?php echo checked( 1, $options['enable_linkedin'] ); ?>>
							<label for="enable-linkedin"><?php _e( 'Enable Linkedin', DADISHAL_DOMAIN ); ?></label>
						</p>
						<p>
							<input type="checkbox" id="enable-pinterest" name="dadishal_options[enable_pinterest]" value="1" <?php echo checked( 1, $options['enable_pinterest'] ); ?>>
							<label for="enable-pinterest"><?php _e( 'Enable Pinterest', DADISHAL_DOMAIN ); ?></label>
						</p>
						<p>
							<input type="checkbox" id="enable-stumbleupon" name="dadishal_options[enable_stumbleupon]" value="1" <?php echo checked( 1, $options['enable_stumbleupon'] ); ?>>
							<label for="enable-stumbleupon"><?php _e( 'Enable Stumbleupon', DADISHAL_DOMAIN ); ?></label>
						</p>
					</div><!-- ./ enable button box -->

					<div class="box">
						<h3><?php _e( 'Window will be open...', DADISHAL_DOMAIN ); ?></h3>
						<p>
							<label for="open-popup"><?php _e( '... with a popup', DADISHAL_DOMAIN ); ?></label>
							<input type="radio" id="open-popup" name="dadishal_options[open_window]" value="popup" <?php checked( 'popup', $options['open_window'] ); ?>>
							<label for="open-target"><?php _e( '... with target blank', DADISHAL_DOMAIN ); ?></label>
							<input type="radio" id="open-blank" name="dadishal_options[open_window]" value="blank" <?php checked( 'blank', $options['open_window'] ); ?>>
						</p>
					</div><!-- ./ window open settings -->

					<div class="box">
						<h3><?php _e( 'Check the list where you wish to display buttons', DADISHAL_DOMAIN ); ?></h3>
						<p>
							<input type="checkbox" id="display-buttons-on-list-posts" name="dadishal_options[display_buttons_on_list_posts]" value="1" <?php echo checked( 1, $options['display_buttons_on_list_posts'] ); ?>>
							<label for="display-buttons-on-list-posts"><?php _e( 'Display buttons in "posts" list', DADISHAL_DOMAIN ); ?></label>
						</p>
						<p>
							<input type="checkbox" id="display-buttons-on-list-pages" name="dadishal_options[display_buttons_on_list_pages]" value="1" <?php echo checked( 1, $options['display_buttons_on_list_pages'] ); ?>>
							<label for="display-buttons-on-list-pages"><?php _e( 'Display buttons in "pages" list', DADISHAL_DOMAIN ); ?></label>
						</p>
						<?php foreach( $post_types as $post_type ) { ?>
							<p>
								<input type="checkbox" id="display-buttons-on-list-<?php echo $post_type; ?>" name="dadishal_options[display_buttons_on_list_<?php echo $post_type; ?>]" value="1" <?php echo checked( 1, $options['display_buttons_on_list_' . $post_type] ); ?>>
								<label for="display-buttons-on-list-<?php echo $post_type; ?>"><?php printf( __( 'Display buttons in "%s" list', DADISHAL_DOMAIN ), str_replace( array( '-', '_' ), ' ', $post_type ) ); ?></label>
							</p>

						<?php } ?>
					</div><!-- ./ lists -->

					<div class="submit-button">
						<p>
							<input type="submit" class="button-primary" value="<?php _e('Save Options', DADISHAL_DOMAIN ); ?>" />
						</p>
					</div><!-- ./ submit -->
				
				</form><!-- ./ form submit -->

				<div class="plugin-info">
					<h3><?php _e( 'Credits', DADISHAL_DOMAIN ); ?></h3>
					<p>
						<?php printf( __( 'Dadi Shal is created and developed by Davide Mura &copy;2017, GNU General License. Go to %splugin official page%s, or to %sWordpress Repository Plugin%s. Vers. 1.0.', DADISHAL_DOMAIN ), '<a target="_blank" href="http://www.iljester.it/portfolio/dadi-shal/">', '</a>', '<a target="_blank" href="http://worpress.org/plugins/dadi-shal/">', '</a>' ); ?>
					</p>
					<p>
						<?php printf( __( 'See others my plugins: %sDadi Breadcrumb%s and %sDadiFb Box%s.', DADISHAL_DOMAIN ), '<a target="_blank" href="https://wordpress.org/plugins/dadi-breadcrumb/">', '</a>', '<a target="_blank" href="https://wordpress.org/plugins/dadifb-box/">', '</a>' ); ?>
					</p>
					<p>
						<?php printf( __( 'Social Icons used in this plugin are created and developed by %sSocicon.com%s', DADISHAL_DOMAIN ), '<a target="_blank" href="http://socicon.com/">', '</a>' ); ?>
					</p>
				</div>

				<form method="post">
					<div class="reset">
						<p>
							<input class="button" name="reset" type="submit" value="<?php _e( 'Reset all Settings', DADISHAL_DOMAIN ); ?>" />
							<input type="hidden" name="action" value="reset" />
						</p>
					</div>
				</form><!-- ./ form reset -->

				<script type="text/javascript">
				jQuery( function($) {
					$( "#enable-twitter").change( function() {
						if( $("#enable-twitter").is( ':checked' ) ) { 
							$( ".twitter-username").css( 'display', 'inline-block' );
						} else {
							$( ".twitter-username").css( 'display', 'none' );
						}							
					});
				});
				</script><!-- ./ hide/show input field for twitter username -->
		<?php
}

/*
 * Options validation
 */
function dadishal_validate_options( $input ) {

	// get custom post types
	$post_types = get_post_types( array( '_builtin' => false ) );

	// validate checkboxs
	if ( ! isset( $input['enable_facebook'] ) ) $input['enable_facebook'] = null;
	$input['enable_facebook'] = ( $input['enable_facebook'] == 1 ? true : false );
	if ( ! isset( $input['enable_twitter'] ) ) $input['enable_twitter'] = null;
	$input['enable_twitter'] = ( $input['enable_twitter'] == 1 ? true : false );
	if ( ! isset( $input['enable_googleplus'] ) ) $input['enable_googleplus'] = null;
	$input['enable_googleplus'] = ( $input['enable_googleplus'] == 1 ? true : false );
	if ( ! isset( $input['enable_linkedin'] ) ) $input['enable_linkedin'] = null;
	$input['enable_linkedin'] = ( $input['enable_linkedin'] == 1 ? true : false );
	if ( ! isset( $input['enable_pinterest'] ) ) $input['enable_pinterest'] = null;
	$input['enable_pinterest'] = ( $input['enable_pinterest'] == 1 ? true : false );
	if ( ! isset( $input['enable_stumbleupon'] ) ) $input['enable_stumbleupon'] = null;
	$input['enable_stumbleupon'] = ( $input['enable_stumbleupon'] == 1 ? true : false );
	if ( ! isset( $input['display_buttons_on_list_posts'] ) ) $input['display_buttons_on_list_posts'] = null;
	$input['display_buttons_on_list_posts'] = ( $input['display_buttons_on_list_posts'] == 1 ? true : false );
	if ( ! isset( $input['display_buttons_on_list_pages'] ) ) $input['display_buttons_on_list_pages'] = null;
	$input['display_buttons_on_list_pages'] = ( $input['display_buttons_on_list_pages'] == 1 ? true : false );
	foreach( $post_types as $post_type ) {
		if ( ! isset( $input['display_buttons_on_list_' . $post_type] ) ) $input['display_buttons_on_list_' . $post_type] = null;
		$input['display_buttons_on_list_' . $post_type] = ( $input['display_buttons_on_list_' . $post_type] == 1 ? true : false );
	}

	// sanitize twitter username fields
	$input['twitter_username'] 	= wp_filter_nohtml_kses( $input['twitter_username'] );
	$input['open_window'] 		= wp_filter_nohtml_kses( $input['open_window'] );

	return $input;
	
}

/*
 * Build options function to print data
 */
function get_dadishal_options( $slug ) {
	
	global $defaults;

	// add custom post types to defaults options
	$post_types = get_post_types( array( '_builtin' => false ) );
	foreach( $post_types as $post_type ) {
		$defaults['display_buttons_on_list_' . $post_type] = false;
	}
	
	$option = get_option('dadishal_options', $defaults );
	
	// void|null return if is unset $options
	if( !isset( $option[$slug]  ) ) return;
	
	return $option[$slug];

}

/*
 * Set custom edit tickets columns
 */
function dadi_share_from_list( $columns ) {

	// add custom column called "Share"
	$columns['share_from_list'] = __( 'Share', DADISHAL_DOMAIN );

	return $columns;
}
if( get_dadishal_options( 'display_buttons_on_list_posts' ) == true ) {
	add_filter( 'manage_post_posts_columns', 'dadi_share_from_list' );
}
if( get_dadishal_options( 'display_buttons_on_list_pages' ) == true ) {
	add_filter( 'manage_pages_columns', 'dadi_share_from_list' );
}
if( get_dadishal_options( 'display_buttons_on_list_' . filter_var( $_GET['post_type'], FILTER_SANITIZE_STRING ) ) == true ) {
	add_filter( 'manage_' . filter_var( $_GET['post_type'], FILTER_SANITIZE_STRING ) . '_posts_columns', 'dadi_share_from_list' );
}

/*
 * Return values in list column
 */
function dadi_insert_buttons_share_from_list( $column, $post_id ) {

		// add pinterest image from single post
		$pin_image = dadi_share_from_list_pinterest_image( $post_id );

		// add twitter username
		$twitter_username = get_dadishal_options( 'twitter_username' ) != '' ? '&amp;via=' . get_dadishal_options( 'twitter_username' ) : '';

		// define $buttons array
		$buttons = array();

		/*
		 * add values to $buttons array
		 */
		if( get_dadishal_options( 'enable_facebook' ) ) {
			$buttons['facebook'] 	= array(
				'url' => 'https://www.facebook.com/sharer.php?u='. urlencode( get_permalink( $post_id ) ) .'&amp;t='. htmlspecialchars( urlencode( html_entity_decode( the_title_attribute( array( 'echo' => 0, 'post' => $post_id ) ), ENT_COMPAT, 'UTF-8') ), ENT_COMPAT, 'UTF-8'),
				'name' => 'Facebook',
				'icon' => 'socicon socicon-facebook',
				'color' => '#3b5998'
			);
		}
		if( get_dadishal_options( 'enable_twitter' ) ) {
			$buttons['twitter'] 	= array(
				'url' => 'https://twitter.com/intent/tweet?text='. htmlspecialchars( urlencode( html_entity_decode( the_title_attribute( array( 'echo' => 0, 'post' => $post_id ) ), ENT_COMPAT, 'UTF-8') ), ENT_COMPAT, 'UTF-8' ) .'&amp;url='. urlencode(get_permalink( $post_id ) ) .''. $twitter_username,
				'name' => 'Twitter',
				'icon' => 'socicon socicon-twitter',
				'color' => '#55acee'
			);
		}
		if( get_dadishal_options( 'enable_googleplus' ) ) {
			$buttons['googleplus']  = array(
				'url' => 'https://plus.google.com/share?url='. urlencode(get_permalink( $post_id )),
				'name' => 'Google Plus',
				'icon' => 'socicon socicon-googleplus',
				'color' => '#dd4b39'
			);
		}
		if( get_dadishal_options( 'enable_linkedin' ) ) {
			$buttons['linkedin']  	= array(
				'url' => 'https://www.linkedin.com/shareArticle?mini=true&amp;url='. urlencode( get_permalink( $post_id ) ) .'&amp;title='. htmlspecialchars( urlencode( html_entity_decode( the_title_attribute( array( 'echo' => 0, 'post' => $post_id ) ), ENT_COMPAT, 'UTF-8') ), ENT_COMPAT, 'UTF-8') .'&amp;source='. esc_url( home_url( '/' ) ),
				'name' => 'Linkedin',
				'icon' => 'socicon socicon-linkedin',
				'color' => '#007bb5'
			);
		}
		if( get_dadishal_options( 'enable_pinterest' ) ) {
			$buttons['pinterest'] 	= array(
				'url' => 'https://pinterest.com/pin/create/bookmarklet/?url='.urlencode( get_permalink( $post_id ) ) .'&amp;media='. $pin_image .'&amp;description='. htmlspecialchars( urlencode( html_entity_decode( the_title_attribute( array( 'echo' => 0, 'post' => $post_id ) ), ENT_COMPAT, 'UTF-8') ), ENT_COMPAT, 'UTF-8'),
				'name' => 'Pinterest',
				'icon' => 'socicon socicon-pinterest',
				'color' => '#cb2027'
			);
		}
		if( get_dadishal_options( 'enable_stumbleupon' ) ) {
			$buttons['stumbleupon'] = array(
				'url' => 'http://www.stumbleupon.com/submit?url='.urlencode( get_permalink( $post_id ) ) .'&amp;title='. htmlspecialchars( urlencode( html_entity_decode( the_title_attribute( array( 'echo' => 0, 'post' => $post_id ) ), ENT_COMPAT, 'UTF-8') ), ENT_COMPAT, 'UTF-8'),
				'name' => 'Stumbleupon',
				'icon' => 'socicon socicon-stumbleupon',
				'color' => '#eb4924'
			);
		}

		/*
		 * We apply filters for add custom share buttons or edit default buttons
		 * So, developers can add custom buttons or edit default buttons throught functions.php or another plugin
		 */
		$shal_buttons = apply_filters( 'shal_buttons', $buttons, $post_id );

		// open in a popup window
		$open_page  = "window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=700,height=450');return false;";

		// define type of open (popup or target blank)
		$open_window = '';
		if( get_dadishal_options( 'open_window' ) == 'popup' ) {
			$open_window = 'onclick="' . esc_attr( $open_page ) . '"';
		}
		elseif( get_dadishal_options( 'open_window' ) == 'blank' ) {
			$open_window = 'target="_blank"';
		}

		// add style inline
		$style = 'color: #fff;padding: 2px 5px;border: 1px solid #eee;display:inline-block;';
    
    
    if( $column == 'share_from_list' ) {
		foreach( $shal_buttons as $social ) {
			if( get_post_status( $post_id ) == 'publish' ) {
				echo '<a href="' . esc_url( $social['url'] ) . '" class="share-list-button" title="' . esc_attr( $social['name'] ) . '" style="' . esc_attr( $style ) . 'background-color: ' . esc_attr( $social['color'] ) . '" ' . $open_window . '><span class="' . esc_attr( $social['icon'] ) . '"></span></a>';
			}
			// if status of post/page or custom post type isn't published, retrieve disabled buttons
			else {
				echo '<span class="share-list-button disabled"  title="' . esc_attr( $social['name'] ) . '" style="' . esc_attr( $style ) . 'background-color:#ddd;"><span class="' . esc_attr( $social['icon'] ) . '"></span></span>';
			}
		}
    }
}
if( get_dadishal_options( 'display_buttons_on_list_posts' ) == true ) {
	add_action( 'manage_post_posts_custom_column', 'dadi_insert_buttons_share_from_list', 10, 2 );
}
if( get_dadishal_options( 'display_buttons_on_list_pages' ) == true ) {
	add_action( 'manage_pages_custom_column' , 'dadi_insert_buttons_share_from_list', 10, 2 );
}
if( get_dadishal_options( 'display_buttons_on_list_' . filter_var( $_GET['post_type'], FILTER_SANITIZE_STRING ) ) == true ) {
	add_action( 'manage_' . filter_var( $_GET['post_type'], FILTER_SANITIZE_STRING ) . '_posts_custom_column', 'dadi_insert_buttons_share_from_list', 10, 2 );
}	

/*
 * Get featured image from post/page or custom post type if exists
 */
function dadi_share_from_list_pinterest_image( $post_id ) {

	if ( '' != get_the_post_thumbnail( $post_id ) ) {
		$pinterestimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
		$pinImage = $pinterestimage[0];
	} else {
		$pinImage = plugins_url( '/images/image-not-found.jpg' , __FILE__ );
	}

	return $pinImage;

}
