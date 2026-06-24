<?php

defined( 'ABSPATH' ) || exit;

class GiftRocket_Shortcodes {

	public static function init(): void {
		add_shortcode( 'gift_genius_quiz', array( __CLASS__, 'render_quiz' ) );
		add_shortcode( 'giftrocket_forecaster', array( __CLASS__, 'render_forecaster' ) );
	}

	public static function render_quiz( $atts ): string {
		$atts = shortcode_atts(
			array(
				'shop_url' => GiftRocket_Settings::get( 'shop_url' ),
			),
			$atts,
			'gift_genius_quiz'
		);

		self::enqueue_quiz_assets();

		ob_start();
		include GIFTROCKET_TOOLS_DIR . 'templates/gift-genius-quiz.php';
		return ob_get_clean();
	}

	public static function render_forecaster( $atts ): string {
		$atts = shortcode_atts(
			array(
				'upsell_url' => GiftRocket_Settings::get( 'upsell_url' ),
			),
			$atts,
			'giftrocket_forecaster'
		);

		self::enqueue_forecaster_assets();

		ob_start();
		include GIFTROCKET_TOOLS_DIR . 'templates/revenue-forecaster.php';
		return ob_get_clean();
	}

	private static function enqueue_quiz_assets(): void {
		wp_enqueue_style(
			'giftrocket-quiz',
			GIFTROCKET_TOOLS_URL . 'assets/css/gift-genius-quiz.css',
			array(),
			GIFTROCKET_TOOLS_VERSION
		);

		wp_enqueue_script(
			'twemoji',
			GIFTROCKET_TOOLS_URL . 'assets/js/vendor/twemoji.min.js',
			array(),
			'14.0.2',
			true
		);

		wp_enqueue_script(
			'html2canvas',
			GIFTROCKET_TOOLS_URL . 'assets/js/vendor/html2canvas.min.js',
			array(),
			'1.4.1',
			true
		);

		wp_enqueue_script(
			'jspdf',
			GIFTROCKET_TOOLS_URL . 'assets/js/vendor/jspdf.umd.min.js',
			array(),
			'2.5.2',
			true
		);

		wp_enqueue_script(
			'giftrocket-quiz',
			GIFTROCKET_TOOLS_URL . 'assets/js/gift-genius-quiz.js',
			array( 'twemoji', 'html2canvas', 'jspdf' ),
			GIFTROCKET_TOOLS_VERSION,
			true
		);

		wp_localize_script(
			'giftrocket-quiz',
			'giftRocketQuiz',
			array(
				'jsonUrl' => GIFTROCKET_TOOLS_URL . 'assets/data/gift-genius-data.json',
				'pdf'     => array(
					'filenamePrefix' => 'gift-genius-quiz',
					'completeFirst'  => __( 'Please complete the quiz before downloading your results.', 'giftrocket-tools' ),
					'libraryError'   => __( 'PDF download is unavailable. Please refresh the page and try again.', 'giftrocket-tools' ),
					'twemojiBase'    => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/',
					'marginX'        => 15,
				),
			)
		);
	}

	private static function enqueue_forecaster_assets(): void {
		wp_enqueue_style(
			'giftrocket-forecaster',
			GIFTROCKET_TOOLS_URL . 'assets/css/revenue-forecaster.css',
			array(),
			GIFTROCKET_TOOLS_VERSION
		);

		wp_enqueue_script(
			'giftrocket-forecaster',
			GIFTROCKET_TOOLS_URL . 'assets/js/revenue-forecaster.js',
			array(),
			GIFTROCKET_TOOLS_VERSION,
			true
		);

		wp_localize_script(
			'giftrocket-forecaster',
			'giftRocketForecaster',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'giftrocket_capture_lead' ),
				'redirectUrl'  => GiftRocket_Settings::get( 'redirect_url' ),
				'submitLabel'  => __( 'Send My Report & Unlock 20% Off →', 'giftrocket-tools' ),
				'errorMessage' => __( 'We could not send your discount email. Please try again.', 'giftrocket-tools' ),
			)
		);
	}
}
