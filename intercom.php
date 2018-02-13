<?php
/**
Plugin Name: Gravity Forms Intercom Add-On
Plugin URI: http://www.gravityforms.com
Description: Integrates Gravity Forms with Intercom, enabling end users to create new Intercom conversations.
Version: 1.0.0
Author: SkyVerge
Author URI: http://www.skyverge.com
Text Domain: gravityformsintercom
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2018 rocketgenius and 2018 SkyVerge, Inc
last updated: January 31, 2018

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/

define( 'GF_INTERCOM_VERSION', '1.0.0' );

// If Gravity Forms is loaded, bootstrap the Intercom Add-On.
add_action( 'gform_loaded', array( 'GF_Intercom_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Intercom_Bootstrap
 *
 * Handles the loading of the Intercom Add-On and registers with the Add-On Framework.
 *
 * @since 1.0
 */
class GF_Intercom_Bootstrap {


	/** minimum PHP version required by this plugin */
	const MINIMUM_PHP_VERSION = '5.6.0';

	/** plugin namespace so we can instantiate the plugin here without PHP 5.2 errors */
	const PLUGIN_NAMESPACE = 'SkyVerge\GravityForms\Intercom';

	/**
	 * If the Feed Add-On Framework exists, Intercom Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		// Intercom PHP SDK requires PHP 5.6+
		if ( ! version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'render_php_outdated_notice' ) );
			return;
		}

		require_once( 'class-gf-intercom.php' );

		GFAddOn::register( '\SkyVerge\GravityForms\Intercom\GFIntercom' );
	}


	/**
	 * Renders an admin notice when PHP version is outdated.
	 *
	 * @access public
	 * @since 1.0
	 */
	public static function render_php_outdated_notice() {

		// can't translate this, as our textdomain won't be loaded
		$message = sprintf(  '%1$sGravity Forms Intercom Add-on is inactive.%2$s This plugin requires PHP %3$s or newer, and your site uses PHP version %4$s. Please contact your host to upgrade PHP.',
			'<strong>', '</strong>',
			self::MINIMUM_PHP_VERSION,
			PHP_VERSION
		);

		printf( '<div class="error"><p>%s</p></div>', $message );
	}


}


/**
 * Returns an instance of the GFIntercom class.
 *
 * @see  \SkyVerge\GravityForms\Intercom\GFIntercom::get_instance()
 *
 * @return \SkyVerge\GravityForms\Intercom\GFIntercom|false
 */
function gf_intercom() {

	$class = GF_Intercom_Bootstrap::PLUGIN_NAMESPACE . '\\GFIntercom';

	if ( class_exists( $class ) ) {
		return call_user_func( "$class::get_instance" );
	} else {
		return false;
	}
}
