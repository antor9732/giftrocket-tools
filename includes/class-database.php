<?php

defined( 'ABSPATH' ) || exit;

class GiftRocket_Database {

	const DB_VERSION     = '1.0.0';
	const DB_VERSION_KEY = 'giftrocket_tools_db_version';

	public static function init(): void {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_upgrade' ) );
	}

	public static function table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'giftrocket_revenue_audits';
	}

	public static function activate(): void {
		self::create_tables();
		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
	}

	public static function maybe_upgrade(): void {
		if ( get_option( self::DB_VERSION_KEY ) === self::DB_VERSION ) {
			return;
		}

		self::create_tables();
		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
	}

	public static function create_tables(): void {
		global $wpdb;

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			store_url varchar(512) NOT NULL,
			monthly_loss varchar(64) NOT NULL DEFAULT '',
			yearly_loss varchar(64) NOT NULL DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY email (email),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function insert_revenue_audit( string $email, string $store_url, string $monthly_loss = '', string $yearly_loss = '' ) {
		global $wpdb;

		$result = $wpdb->insert(
			self::table_name(),
			array(
				'email'        => $email,
				'store_url'    => $store_url,
				'monthly_loss' => $monthly_loss,
				'yearly_loss'  => $yearly_loss,
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}
}
