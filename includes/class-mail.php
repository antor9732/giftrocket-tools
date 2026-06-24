<?php

defined( 'ABSPATH' ) || exit;

class GiftRocket_Mail {

	private static bool $sending = false;

	public static function init(): void {
	}

	public static function get_from_email(): string {
		// Use admin email as authoritative source for outgoing mail sender.
		$email = get_option( 'admin_email' );
		return sanitize_email( $email );
	}

	public static function get_from_name(): string {
		// Use site name for From name.
		$name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		return wp_specialchars_decode( $name, ENT_QUOTES );
	}

	public static function send( string $to, string $subject, string $body ): bool {
		if ( ! is_email( $to ) ) {
			return false;
		}

		$from_email = self::get_from_email();
		$from_name  = self::get_from_name();

		if ( ! is_email( $from_email ) ) {
			error_log( '[GiftRocket Tools] Cannot send email: no valid From address configured.' );
			return false;
		}

		$from_email_filter = static function () use ( $from_email ): string {
			return $from_email;
		};
		$from_name_filter = static function () use ( $from_name ): string {
			return $from_name;
		};

		add_filter( 'wp_mail_from', $from_email_filter );
		add_filter( 'wp_mail_from_name', $from_name_filter );

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $from_name, $from_email ),
		);

		self::$sending = true;
		$sent          = wp_mail( $to, $subject, $body, $headers );
		self::$sending = false;

		remove_filter( 'wp_mail_from', $from_email_filter );
		remove_filter( 'wp_mail_from_name', $from_name_filter );

		if ( ! $sent ) {
			error_log(
				sprintf(
					'[GiftRocket Tools] wp_mail failed for recipient: %s',
					$to
				)
			);
		}

		return $sent;
	}

	public static function configure_phpmailer( $phpmailer ): void {
		// PHPMailer configuration removed. Plugin relies on wp_mail() and WordPress' mail transport.
	}

}
