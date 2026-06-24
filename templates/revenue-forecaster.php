<?php
/**
 * Revenue Forecaster template.
 *
 * @var array $atts Shortcode attributes.
 */

defined( 'ABSPATH' ) || exit;

$upsell_url = esc_url( $atts['upsell_url'] ?? '' );
?>
<div id="gr-forecaster-wrap">

	<div class="grt-header">
		<span class="grt-badge">🔥 <?php esc_html_e( 'Free Revenue Audit', 'giftrocket-tools' ); ?></span>
		<h2><?php esc_html_e( 'How much are you', 'giftrocket-tools' ); ?> <span class="grt-highlight"><?php esc_html_e( 'losing', 'giftrocket-tools' ); ?></span> <?php esc_html_e( 'by not selling gift cards?', 'giftrocket-tools' ); ?></h2>
		<p><?php esc_html_e( 'Enter your store metrics below. We\'ll calculate the exact monthly revenue slipping through your fingers.', 'giftrocket-tools' ); ?></p>
	</div>

	<div class="grt-calculator-card">
		<div class="grt-input-group">
			<div class="grt-field">
				<label for="grt_aov"><?php esc_html_e( 'Average Order Value (AOV)', 'giftrocket-tools' ); ?> <span class="grt-tooltip">$</span></label>
				<div class="grt-input-wrapper">
					<span class="grt-currency">$</span>
					<input type="number" id="grt_aov" placeholder="e.g. 75" value="75" min="1">
				</div>
			</div>

			<div class="grt-field">
				<label for="grt_visitors"><?php esc_html_e( 'Monthly Website Visitors', 'giftrocket-tools' ); ?></label>
				<div class="grt-input-wrapper">
					<input type="text" id="grt_visitors" inputmode="numeric" placeholder="e.g. 5,000" value="5,000" autocomplete="off">
				</div>
			</div>

			<div class="grt-field">
				<label for="grt_conversion"><?php esc_html_e( 'Current Conversion Rate', 'giftrocket-tools' ); ?> <span class="grt-tooltip">%</span></label>
				<div class="grt-input-wrapper">
					<input type="number" id="grt_conversion" placeholder="e.g. 2.5" value="2.5" min="0.1" step="0.1">
					<span class="grt-currency">%</span>
				</div>
			</div>
		</div>

		<button id="grt_calculate_btn" class="grt-cta-button" type="button" aria-controls="grt_result_area">
			<span class="grt-btn-text">🚀 <?php esc_html_e( 'Calculate My Lost Revenue', 'giftrocket-tools' ); ?></span>
			<span class="grt-btn-loader" style="display:none;" aria-hidden="true">⏳ <?php esc_html_e( 'Calculating…', 'giftrocket-tools' ); ?></span>
		</button>

		<div id="grt_result_area" class="grt-result-area" style="display: none;" aria-live="polite" aria-atomic="true">
			<div class="grt-divider"><span>📊 <?php esc_html_e( 'Your Audit Results', 'giftrocket-tools' ); ?></span></div>

			<div class="grt-result-grid">
				<div class="grt-result-item">
					<span class="grt-result-label"><?php esc_html_e( 'Monthly Lost Revenue', 'giftrocket-tools' ); ?></span>
					<span class="grt-result-number" id="gr_monthly_loss">$0</span>
				</div>
				<div class="grt-result-item grt-highlight-item">
					<span class="grt-result-label">⚠️ <?php esc_html_e( 'Yearly Lost Revenue', 'giftrocket-tools' ); ?></span>
					<span class="grt-result-number" id="gr_yearly_loss">$0</span>
				</div>
			</div>

			<div class="grt-upsell-box">
				<div class="grt-upsell-content">
					<span class="grt-upsell-icon">🎁</span>
					<div>
						<h4><?php esc_html_e( 'Stop leaving money on the table.', 'giftrocket-tools' ); ?></h4>
						<p><?php esc_html_e( 'Install GiftRocket today and start capturing this revenue in under 5 minutes.', 'giftrocket-tools' ); ?></p>
					</div>
					<a href="<?php echo esc_url( $upsell_url ); ?>" class="grt-upsell-button"><?php esc_html_e( 'Get GiftRocket Now →', 'giftrocket-tools' ); ?></a>
				</div>
			</div>
		</div>

		<div class="grt-trust-footer">
			<span><?php esc_html_e( 'Trusted by', 'giftrocket-tools' ); ?> <strong>1,200+</strong> <?php esc_html_e( 'WooCommerce stores', 'giftrocket-tools' ); ?></span>
			<span>⚡ <?php esc_html_e( '5-min setup', 'giftrocket-tools' ); ?></span>
			<span>⭐ <?php esc_html_e( '4.9/5 rating', 'giftrocket-tools' ); ?></span>
		</div>
	</div>
</div>

<div id="grt_lead_modal" class="grt-modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="grt_modal_title" aria-hidden="true">
	<div class="grt-modal-box">
		<button class="grt-modal-close" id="grt_close_modal" type="button" aria-label="<?php esc_attr_e( 'Close dialog', 'giftrocket-tools' ); ?>">✕</button>
		
		<!-- Form Section -->
		<div id="grt_form_section">
			<div class="grt-modal-icon">📩</div>
			<h3 id="grt_modal_title"><?php esc_html_e( 'Unlock ', 'giftrocket-tools' ); ?> <span style="color:#8b5cf6;"><?php esc_html_e( '20% Off ', 'giftrocket-tools' ); ?> </span> <?php esc_html_e( 'GiftRocket ', 'giftrocket-tools' ); ?>?</h3>
			<p><?php esc_html_e( 'Enter your email and store URL to claim your', 'giftrocket-tools' ); ?> <strong><?php esc_html_e( '20% discount code', 'giftrocket-tools' ); ?></strong> <?php esc_html_e( 'instantly.', 'giftrocket-tools' ); ?></p>

			<form id="grl_lead_form">
				<input type="email" id="grt_user_email" placeholder="<?php esc_attr_e( 'Your best email *', 'giftrocket-tools' ); ?>" required>
				<input type="url" id="grt_store_url" placeholder="<?php esc_attr_e( 'Your store URL (e.g. mystore.com)', 'giftrocket-tools' ); ?>" required>
				<button type="submit" class="grt-modal-submit"><?php esc_html_e( 'Send My Report & Unlock 20% Off →', 'giftrocket-tools' ); ?></button>
			</form>
			<p class="grt-modal-note">🔒 <?php esc_html_e( 'No spam. Unsubscribe anytime. Your data is safe.', 'giftrocket-tools' ); ?></p>
		</div>

		<!-- Thank You Section -->
		<div id="grt_thank_you_section" style="display: none; text-align: center;">
			<div class="grt-modal-icon" style="font-size: 56px;">✅</div>
			<h3 style="margin-top: 16px;"><?php esc_html_e( 'Thank You!', 'giftrocket-tools' ); ?></h3>
			<p><?php esc_html_e( 'Check your email for your exclusive 20% discount code.', 'giftrocket-tools' ); ?></p>
			<p style="font-size: 13px; color: #999; margin-top: 12px;"><?php esc_html_e( 'Redirecting you now…', 'giftrocket-tools' ); ?></p>
		</div>
	</div>
</div>
