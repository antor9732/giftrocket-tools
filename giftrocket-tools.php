<?php
/*
Plugin Name: GiftRocket Tools
Description: Gift Genius personality quiz and Revenue Forecaster lead magnet for GiftRocket.
Version:            1.0.0
Requires at least:  6.5
Requires PHP:       8.1
Tested up to:       6.9
Author:             WebCartisan
Author URI:         https://webcartisan.com/
License:            GPL v3 or later
License URI:        https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:        giftrocket-tools
Domain Path:        /languages
*/

defined( 'ABSPATH' ) || exit;

define( 'GIFTROCKET_TOOLS_VERSION', '1.0.0' );
define( 'GIFTROCKET_TOOLS_FILE', __FILE__ );
define( 'GIFTROCKET_TOOLS_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIFTROCKET_TOOLS_URL', plugin_dir_url( __FILE__ ) );

require_once GIFTROCKET_TOOLS_DIR . 'includes/class-database.php';
require_once GIFTROCKET_TOOLS_DIR . 'includes/class-mail.php';
require_once GIFTROCKET_TOOLS_DIR . 'includes/class-settings.php';
require_once GIFTROCKET_TOOLS_DIR . 'includes/class-shortcodes.php';
require_once GIFTROCKET_TOOLS_DIR . 'includes/class-ajax.php';

register_activation_hook( GIFTROCKET_TOOLS_FILE, array( 'GiftRocket_Database', 'activate' ) );

final class GiftRocket_Tools {

	private static ?GiftRocket_Tools $instance = null;

	public static function instance(): GiftRocket_Tools {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init(): void {
		GiftRocket_Database::init();
		GiftRocket_Mail::init();
		GiftRocket_Settings::init();
		GiftRocket_Shortcodes::init();
		GiftRocket_Ajax::init();
	}
}

GiftRocket_Tools::instance();
