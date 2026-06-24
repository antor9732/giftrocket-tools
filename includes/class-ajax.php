<?php

defined( 'ABSPATH' ) || exit;

class GiftRocket_Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_giftrocket_capture_lead', array( __CLASS__, 'capture_lead' ) );
		add_action( 'wp_ajax_nopriv_giftrocket_capture_lead', array( __CLASS__, 'capture_lead' ) );
	}

	public static function capture_lead(): void {
		check_ajax_referer( 'giftrocket_capture_lead', 'nonce' );

		$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$store_url = self::normalize_store_url(
			isset( $_POST['store_url'] ) ? wp_unslash( $_POST['store_url'] ) : ''
		);
		$monthly   = isset( $_POST['monthly_loss'] ) ? sanitize_text_field( wp_unslash( $_POST['monthly_loss'] ) ) : '';
		$yearly    = isset( $_POST['yearly_loss'] ) ? sanitize_text_field( wp_unslash( $_POST['yearly_loss'] ) ) : '';

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'giftrocket-tools' ) ), 400 );
		}

		if ( empty( $store_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Store URL is required.', 'giftrocket-tools' ) ), 400 );
		}

		$inserted = GiftRocket_Database::insert_revenue_audit( $email, $store_url, $monthly, $yearly );

		if ( false === $inserted ) {
			wp_send_json_error( array( 'message' => __( 'Could not save your submission. Please try again.', 'giftrocket-tools' ) ), 500 );
		}

		$email_sent = self::send_discount_email( $email, $store_url, $monthly, $yearly );

		if ( ! $email_sent ) {
			wp_send_json_error(
				array(
					'message' => __( 'Your details were saved, but we could not send the discount email. Please contact support or try again later.', 'giftrocket-tools' ),
				),
				500
			);
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

			GiftRocket_Mail::send( $notify, $subject, $body );
		}

		wp_send_json_success(
			array(
				'redirect' => GiftRocket_Settings::get( 'redirect_url' ),
			)
		);

	}

	private static function normalize_store_url( string $raw_url ): string {
		$raw_url = trim( $raw_url );

		if ( '' === $raw_url ) {
			return '';
		}

		if ( ! preg_match( '#^https?://#i', $raw_url ) ) {
			$raw_url = 'https://' . $raw_url;
		}

		return esc_url_raw( $raw_url );
	}

	private static function send_discount_email( string $email, string $store_url, string $monthly, string $yearly ): bool {
		$discount_code = GiftRocket_Settings::get( 'discount_code' );
		$upsell_url    = GiftRocket_Settings::get( 'upsell_url' );
		$site_name     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Your 20%% Off GiftRocket Discount — %s', 'giftrocket-tools' ),
			$site_name
		);

		$body = sprintf(
			/* translators: 1: store URL, 2: monthly lost revenue, 3: yearly lost revenue, 4: discount code, 5: upsell URL, 6: site name */
			__(
				"Hi there,\n\nThank you for completing your free Revenue Audit for %1\$s.\n\nHere is a snapshot of what we found:\n• Monthly lost revenue: %2\$s\n• Yearly lost revenue: %3\$s\n\nAs promised, here is your exclusive 20%% discount on GiftRocket:\n\nDiscount code: %4\$s\n\nUse it at checkout to start capturing gift card revenue in minutes:\n%5\$s\n\nQuestions? Just reply to this email — we are happy to help.\n\n— The %6\$s team",
				'giftrocket-tools'
			),
			$store_url,
			$monthly,
			$yearly,
			$discount_code,
			$upsell_url,
			$site_name
		);

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
		);

		return wp_mail( $email, $subject, $body, $headers );
	}
}
