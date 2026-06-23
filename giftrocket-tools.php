<?php
/**
 * Plugin Name: GiftRocket Tools
 * Plugin URI:  https://giftrocket.com
 * Description: Gift Genius personality quiz and Revenue Forecaster lead magnet for GiftRocket.
 * Version:     1.0.0
 * Author:      GiftRocket
 * Text Domain: giftrocket-tools
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'GIFTROCKET_TOOLS_VERSION', '1.0.0' );
define( 'GIFTROCKET_TOOLS_FILE', __FILE__ );
define( 'GIFTROCKET_TOOLS_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIFTROCKET_TOOLS_URL', plugin_dir_url( __FILE__ ) );

require_once GIFTROCKET_TOOLS_DIR . 'includes/class-settings.php';
require_once GIFTROCKET_TOOLS_DIR . 'includes/class-shortcodes.php';
require_once GIFTROCKET_TOOLS_DIR . 'includes/class-ajax.php';

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
		GiftRocket_Settings::init();
		GiftRocket_Shortcodes::init();
		GiftRocket_Ajax::init();
	}
}

GiftRocket_Tools::instance();
