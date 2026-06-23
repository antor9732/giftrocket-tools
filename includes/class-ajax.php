<?php

defined( 'ABSPATH' ) || exit;

class GiftRocket_Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_giftrocket_capture_lead', array( __CLASS__, 'capture_lead' ) );
		add_action( 'wp_ajax_nopriv_giftrocket_capture_lead', array( __CLASS__, 'capture_lead' ) );
	}

	public static function capture_lead(): void {
		check_ajax_referer( 'giftrocket_capture_lead', 'nonce' );

		$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$store_url   = isset( $_POST['store_url'] ) ? esc_url_raw( wp_unslash( $_POST['store_url'] ) ) : '';
		$monthly     = isset( $_POST['monthly_loss'] ) ? sanitize_text_field( wp_unslash( $_POST['monthly_loss'] ) ) : '';
		$yearly      = isset( $_POST['yearly_loss'] ) ? sanitize_text_field( wp_unslash( $_POST['yearly_loss'] ) ) : '';

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'giftrocket-tools' ) ), 400 );
		}

		if ( empty( $store_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Store URL is required.', 'giftrocket-tools' ) ), 400 );
		}

		$notify = GiftRocket_Settings::get( 'notify_email' );

		if ( $notify && is_email( $notify ) ) {
			$subject = sprintf(
				/* translators: %s: store URL */
				__( 'New GiftRocket Forecaster Lead — %s', 'giftrocket-tools' ),
				$store_url
			);

			$body = sprintf(
				"Email: %s\nStore: %s\nMonthly Lost Revenue: %s\nYearly Lost Revenue: %s\nSubmitted: %s",
				$email,
				$store_url,
				$monthly,
				$yearly,
				current_time( 'mysql' )
			);

			wp_mail( $notify, $subject, $body );
		}

		wp_send_json_success(
			array(
				'redirect' => GiftRocket_Settings::get( 'redirect_url' ),
			)
		);
	}
}
