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

	<div class="gr-header">
		<span class="gr-badge">🔥 <?php esc_html_e( 'Free Revenue Audit', 'giftrocket-tools' ); ?></span>
		<h2><?php esc_html_e( 'How much are you', 'giftrocket-tools' ); ?> <span class="gr-highlight"><?php esc_html_e( 'losing', 'giftrocket-tools' ); ?></span> <?php esc_html_e( 'by not selling gift cards?', 'giftrocket-tools' ); ?></h2>
		<p><?php esc_html_e( 'Enter your store metrics below. We\'ll calculate the exact monthly revenue slipping through your fingers.', 'giftrocket-tools' ); ?></p>
	</div>

	<div class="gr-calculator-card">
		<div class="gr-input-group">
			<div class="gr-field">
				<label for="gr_aov"><?php esc_html_e( 'Average Order Value (AOV)', 'giftrocket-tools' ); ?> <span class="gr-tooltip">$</span></label>
				<div class="gr-input-wrapper">
					<span class="gr-currency">$</span>
					<input type="number" id="gr_aov" placeholder="e.g. 75" value="75" min="1">
				</div>
			</div>

			<div class="gr-field">
				<label for="gr_visitors"><?php esc_html_e( 'Monthly Website Visitors', 'giftrocket-tools' ); ?></label>
				<div class="gr-input-wrapper">
					<input type="text" id="gr_visitors" inputmode="numeric" placeholder="e.g. 5,000" value="5,000" autocomplete="off">
				</div>
			</div>

			<div class="gr-field">
				<label for="gr_conversion"><?php esc_html_e( 'Current Conversion Rate', 'giftrocket-tools' ); ?> <span class="gr-tooltip">%</span></label>
				<div class="gr-input-wrapper">
					<input type="number" id="gr_conversion" placeholder="e.g. 2.5" value="2.5" min="0.1" step="0.1">
					<span class="gr-currency">%</span>
				</div>
			</div>
		</div>

		<button id="gr_calculate_btn" class="gr-cta-button" type="button" aria-controls="gr_result_area">
			<span class="gr-btn-text">🚀 <?php esc_html_e( 'Calculate My Lost Revenue', 'giftrocket-tools' ); ?></span>
			<span class="gr-btn-loader" style="display:none;" aria-hidden="true">⏳ <?php esc_html_e( 'Calculating…', 'giftrocket-tools' ); ?></span>
		</button>

		<div id="gr_result_area" class="gr-result-area" style="display: none;" aria-live="polite" aria-atomic="true">
			<div class="gr-divider"><span>📊 <?php esc_html_e( 'Your Audit Results', 'giftrocket-tools' ); ?></span></div>

			<div class="gr-result-grid">
				<div class="gr-result-item">
					<span class="gr-result-label"><?php esc_html_e( 'Monthly Lost Revenue', 'giftrocket-tools' ); ?></span>
					<span class="gr-result-number" id="gr_monthly_loss">$0</span>
				</div>
				<div class="gr-result-item gr-highlight-item">
					<span class="gr-result-label">⚠️ <?php esc_html_e( 'Yearly Lost Revenue', 'giftrocket-tools' ); ?></span>
					<span class="gr-result-number" id="gr_yearly_loss">$0</span>
				</div>
			</div>

			<div class="gr-upsell-box">
				<div class="gr-upsell-content">
					<span class="gr-upsell-icon">🎁</span>
					<div>
						<h4><?php esc_html_e( 'Stop leaving money on the table.', 'giftrocket-tools' ); ?></h4>
						<p><?php esc_html_e( 'Install GiftRocket today and start capturing this revenue in under 5 minutes.', 'giftrocket-tools' ); ?></p>
					</div>
					<a href="<?php echo esc_url( $upsell_url ); ?>" class="gr-upsell-button"><?php esc_html_e( 'Get GiftRocket Now →', 'giftrocket-tools' ); ?></a>
				</div>
			</div>
		</div>

		<div class="gr-trust-footer">
			<span><?php esc_html_e( 'Trusted by', 'giftrocket-tools' ); ?> <strong>1,200+</strong> <?php esc_html_e( 'WooCommerce stores', 'giftrocket-tools' ); ?></span>
			<span>⚡ <?php esc_html_e( '5-min setup', 'giftrocket-tools' ); ?></span>
			<span>⭐ <?php esc_html_e( '4.9/5 rating', 'giftrocket-tools' ); ?></span>
		</div>
	</div>
</div>

<div id="gr_lead_modal" class="gr-modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="gr_modal_title" aria-hidden="true">
	<div class="gr-modal-box">
		<button class="gr-modal-close" id="gr_close_modal" type="button" aria-label="<?php esc_attr_e( 'Close dialog', 'giftrocket-tools' ); ?>">✕</button>
		<div class="gr-modal-icon">📩</div>
		<h3 id="gr_modal_title"><?php esc_html_e( 'Where should we send your', 'giftrocket-tools' ); ?> <span style="color:#8b5cf6;"><?php esc_html_e( 'full report', 'giftrocket-tools' ); ?></span>?</h3>
		<p><?php esc_html_e( 'Enter your email and store URL to unlock your detailed breakdown, plus get a', 'giftrocket-tools' ); ?> <strong><?php esc_html_e( '20% discount code', 'giftrocket-tools' ); ?></strong> <?php esc_html_e( 'for GiftRocket.', 'giftrocket-tools' ); ?></p>

		<form id="gr_lead_form">
			<input type="email" id="gr_user_email" placeholder="<?php esc_attr_e( 'Your best email *', 'giftrocket-tools' ); ?>" required>
			<input type="url" id="gr_store_url" placeholder="<?php esc_attr_e( 'Your store URL (e.g. mystore.com)', 'giftrocket-tools' ); ?>" required>
			<button type="submit" class="gr-modal-submit"><?php esc_html_e( 'Send My Report & Unlock 20% Off →', 'giftrocket-tools' ); ?></button>
		</form>
		<p class="gr-modal-note">🔒 <?php esc_html_e( 'No spam. Unsubscribe anytime. Your data is safe.', 'giftrocket-tools' ); ?></p>
	</div>
</div>
