<?php

defined('ABSPATH') || exit;

class GiftRocket_Settings
{

	const OPTION_KEY = 'giftrocket_tools_settings';

	public static function init(): void
	{
		add_action('admin_menu', array(__CLASS__, 'add_menu'));
		add_action('admin_init', array(__CLASS__, 'register_settings'));
	}

	public static function defaults(): array
	{
		return array(
			'shop_url'       => '',
			'upsell_url'     => home_url('/'),
			'redirect_url'   => home_url('/'),
			'notify_email'   => get_option('admin_email'),
			'discount_code'  => 'GIFTROCKET20',
		);
	}

	public static function get(string $key = '')
	{
		$settings = wp_parse_args(
			get_option(self::OPTION_KEY, array()),
			self::defaults()
		);

		if ('' === $key) {
			return $settings;
		}

		return $settings[$key] ?? '';
	}

	public static function add_menu(): void
	{
		if (function_exists('wallregi_gift_cards_parent_menu_slug')) {
			add_submenu_page(
				wallregi_gift_cards_parent_menu_slug(),
				__('GiftRocket Tools', 'giftrocket-tools'),
				__('GiftRocket Tools', 'giftrocket-tools'),
				'manage_options',
				'giftrocket-tools',
				array(__CLASS__, 'render_page')
			);
			return;
		}

		add_options_page(
			__('GiftRocket Tools', 'giftrocket-tools'),
			__('GiftRocket Tools', 'giftrocket-tools'),
			'manage_options',
			'giftrocket-tools',
			array(__CLASS__, 'render_page')
		);
	}

	public static function register_settings(): void
	{
		register_setting(
			'giftrocket_tools',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array(__CLASS__, 'sanitize'),
			)
		);

		add_settings_section(
			'giftrocket_tools_main',
			__('General Settings', 'giftrocket-tools'),
			'__return_false',
			'giftrocket-tools'
		);

		$main_fields = array(
			'gift_quiz'     => __('Gift Genius Quiz Shortcode', 'giftrocket-tools'),
			'gift_forecaster' => __('Revenue Forecaster Shortcode', 'giftrocket-tools'),
			'shop_url'      => __('Shop / Gift Cards URL', 'giftrocket-tools'),
			'upsell_url'    => __('Forecaster Upsell Button URL', 'giftrocket-tools'),
			'redirect_url'  => __('Lead Form Redirect URL', 'giftrocket-tools'),
			'notify_email'  => __('Lead Notification Email', 'giftrocket-tools'),
			'discount_code' => __('Revenue Audit Discount Code', 'giftrocket-tools'),
		);

		foreach ($main_fields as $id => $label) {
			add_settings_field(
				$id,
				$label,
				array(__CLASS__, 'render_field'),
				'giftrocket-tools',
				'giftrocket_tools_main',
				array('id' => $id)
			);
		}

		// Email settings UI removed - plugin will use wp_mail() for outgoing mail.
	}

	public static function sanitize($input): array
	{
		$existing = wp_parse_args(
			get_option(self::OPTION_KEY, array()),
			self::defaults()
		);
		$clean = array();
		foreach (self::defaults() as $key => $default) {
			if (! isset($input[$key])) {
				continue;
			}

			if (in_array($key, array('notify_email'), true)) {
				$clean[$key] = sanitize_email($input[$key]);
			} elseif (in_array($key, array('discount_code'), true)) {
				$clean[$key] = sanitize_text_field($input[$key]);
			} else {
				$clean[$key] = esc_url_raw($input[$key]);
			}
		}

		return wp_parse_args($clean, self::defaults());
	}
	public static function render_field(array $args): void
	{
		$id       = $args['id'];
		$settings = self::get();
		$value    = $settings[$id] ?? '';

		if ('gift_quiz' === $id) {
			echo '<code>[gift_genius_quiz]</code>';
			echo '<p class="description">' . __('Place this shortcode on any page to display the Gift Genius personality quiz.', 'giftrocket-tools') . '</p>';
			return;
		}

		if ('gift_forecaster' === $id) {
			echo '<code>[giftrocket_forecaster]</code>';
			echo '<p class="description">' . __('Place this shortcode on any page to display the Revenue Forecaster lead magnet form.', 'giftrocket-tools') . '</p>';
			return;
		}

		// Determine input type for remaining fields.
		if ('notify_email' === $id) {
			$type = 'email';
		} elseif ('discount_code' === $id) {
			$type = 'text';
		} else {
			$type = 'url';
		}

		printf(
			'<input type="%1$s" name="%2$s[%3$s]" id="%3$s" value="%4$s" class="regular-text" />',
			esc_attr($type),
			esc_attr(self::OPTION_KEY),
			esc_attr($id),
			esc_attr($value)
		);

		$hints = array(
			'shop_url'      	=> __('Used by the Gift Genius quiz "Shop Gift Cards" button.', 'giftrocket-tools'),
			'upsell_url'    	=> __('Used by the Revenue Forecaster "Get GiftRocket Now" button.', 'giftrocket-tools'),
			'redirect_url'  	=> __('Where users go after submitting the lead capture form.', 'giftrocket-tools'),
			'notify_email'  	=> __('Receives forecaster lead submissions.', 'giftrocket-tools'),
			'discount_code' 	=> __('Sent to users in the Revenue Audit discount email.', 'giftrocket-tools'),
		);

		if (isset($hints[$id])) {
			printf('<p class="description">%s</p>', esc_html($hints[$id]));
		}
	}

	public static function render_revenue_audit_table(): void
	{
		if (! current_user_can('manage_options')) {
			return;
		}

		$audits = GiftRocket_Database::get_revenue_audits();
?>
		<h2><?php esc_html_e('Saved Lead Form Records', 'giftrocket-tools'); ?></h2>
		<?php if (empty($audits)) : ?>
			<p><?php esc_html_e('No saved lead records are available yet.', 'giftrocket-tools'); ?></p>
		<?php else : ?>
			<table class="widefat fixed striped" style="max-width: 1000px;">
				<thead>
					<tr>
						<th><?php esc_html_e('Email', 'giftrocket-tools'); ?></th>
						<th><?php esc_html_e('Website URL', 'giftrocket-tools'); ?></th>
						<th><?php esc_html_e('Monthly Loss', 'giftrocket-tools'); ?></th>
						<th><?php esc_html_e('Yearly Loss', 'giftrocket-tools'); ?></th>
						<th><?php esc_html_e('Submitted', 'giftrocket-tools'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($audits as $audit) : ?>
						<tr>
							<td><?php echo esc_html($audit['email']); ?></td>
							<td><a href="<?php echo esc_url($audit['store_url']); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html($audit['store_url']); ?></a></td>
							<td><?php echo esc_html($audit['monthly_loss']); ?></td>
							<td><?php echo esc_html($audit['yearly_loss']); ?></td>
							<td><?php echo esc_html($audit['created_at']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	<?php
	}

	public static function render_page(): void
	{
		if (! current_user_can('manage_options')) {
			return;
		}
	?>
		<div class="wrap">
			<h1><?php esc_html_e('GiftRocket Tools', 'giftrocket-tools'); ?></h1>
			<div class="gtl-dashboard-container" style="display: grid; grid-template-columns: 0.7fr 1.3fr; gap: 2em; margin-top: 2em;">
				<div class="gtl-general-settings">
					<form method="post" action="options.php">
						<?php
						settings_fields('giftrocket_tools');
						do_settings_sections('giftrocket-tools');
						submit_button();
						?>
					</form>
				</div>
				<div class="gtl-revenue-audit-table">
					<?php self::render_revenue_audit_table(); ?>
				</div>
			</div>
		</div>
<?php
	}
}
