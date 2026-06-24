<?php
/**
 * Gift Genius Quiz template.
 *
 * @var array $atts Shortcode attributes.
 */

defined( 'ABSPATH' ) || exit;

$shop_url = esc_url( $atts['shop_url'] ?? '' );
?>
<div id="grt-gift-quiz-wrap" data-shop-url="<?php echo esc_attr( $shop_url ); ?>">

	<div id="grt-quiz-intro" class="grt-quiz-screen grt-active">
		<div class="grt-quiz-header">
			<span class="grt-quiz-badge">🧠 Gift Genius</span>
			<h1><?php esc_html_e( 'The', 'giftrocket-tools' ); ?> <span class="grt-gradient-text"><?php esc_html_e( 'Gift Genius', 'giftrocket-tools' ); ?></span> <?php esc_html_e( 'Quiz', 'giftrocket-tools' ); ?></h1>
			<p class="grt-quiz-subtitle"><?php esc_html_e( 'Answer 5 questions — occasion, personality, budget (or enter your own), relationship, and timing. Gift Genius picks specific items, not generic "gift card" fluff.', 'giftrocket-tools' ); ?></p>
		</div>

		<div class="grt-quiz-features">
			<div class="grt-feature-item">
				<span class="grt-feature-icon">⚡</span>
				<span><?php esc_html_e( '~30 seconds', 'giftrocket-tools' ); ?></span>
			</div>
			<div class="grt-feature-item">
				<span class="grt-feature-icon">🎯</span>
				<span><?php esc_html_e( '10+ occasions', 'giftrocket-tools' ); ?></span>
			</div>
			<div class="grt-feature-item">
				<span class="grt-feature-icon">💡</span>
				<span><?php esc_html_e( 'Item-level picks', 'giftrocket-tools' ); ?></span>
			</div>
			<div class="grt-feature-item">
				<span class="grt-feature-icon">✏️</span>
				<span><?php esc_html_e( 'Custom budget', 'giftrocket-tools' ); ?></span>
			</div>
		</div>

		<button id="grt-start-quiz-btn" class="grt-quiz-cta" type="button">
			<span>🎉 <?php esc_html_e( 'Start the Gift Genius Quiz', 'giftrocket-tools' ); ?></span>
			<span class="grt-arrow" aria-hidden="true">→</span>
		</button>

		<p class="grt-quiz-trust">❤️ <?php esc_html_e( 'Used by 12,000+ gift givers • No signup required', 'giftrocket-tools' ); ?></p>
	</div>

	<div id="grt-quiz-container" class="grt-quiz-screen">
		<div class="grt-quiz-progress-wrap" aria-hidden="true">
			<div class="grt-quiz-progress-track">
				<div class="grt-quiz-progress-bar" id="grt-progress-bar"></div>
			</div>
			<span class="grt-quiz-progress-text" id="grt-progress-text">1 / 5</span>
		</div>

		<div class="grt-quiz-question-area" role="group" aria-labelledby="grt-question-text">
			<div class="grt-question-counter" id="grt-question-counter"><?php esc_html_e( 'Question 1 of 5', 'giftrocket-tools' ); ?></div>
			<h2 class="grt-quiz-question" id="grt-question-text"><?php esc_html_e( 'Loading…', 'giftrocket-tools' ); ?></h2>
			<p class="grt-quiz-question-hint" id="grt-question-hint"></p>
		</div>

		<div class="grt-quiz-options" id="grt-options-container" role="listbox" aria-label="<?php esc_attr_e( 'Answer options', 'giftrocket-tools' ); ?>"></div>

		<div class="grt-budget-custom-panel" id="grt-budget-custom-panel">
			<label for="grt-budget-custom-input"><?php esc_html_e( 'Enter your exact budget', 'giftrocket-tools' ); ?></label>
			<div class="grt-budget-custom-input-wrap">
				<span>$</span>
				<input type="number" id="grt-budget-custom-input" min="10" max="2000" step="1" placeholder="e.g. 85" inputmode="numeric">
			</div>
			<p class="grt-budget-custom-hint"><?php esc_html_e( 'Between $10 and $2,000 — we\'ll tailor picks to this number.', 'giftrocket-tools' ); ?></p>
		</div>

		<div class="grt-quiz-nav">
			<button id="grt-prev-btn" class="grt-quiz-nav-btn grt-secondary" type="button" style="visibility:hidden;">← <?php esc_html_e( 'Back', 'giftrocket-tools' ); ?></button>
			<span class="grt-quiz-nav-spacer"></span>
			<button id="grt-next-btn" class="grt-quiz-nav-btn grt-primary" type="button" disabled><?php esc_html_e( 'Next →', 'giftrocket-tools' ); ?></button>
		</div>
	</div>

	<div id="grt-quiz-results" class="grt-quiz-screen" aria-live="polite">
		<div class="grt-result-badge">🧠 <?php esc_html_e( 'Your Gift Genius Result', 'giftrocket-tools' ); ?></div>

		<div class="grt-result-card" id="grt-result-card">
			<div class="grt-result-emoji" id="grt-result-emoji">🎁</div>
			<h2 class="grt-result-title" id="grt-result-title"><?php esc_html_e( 'Gift Card', 'giftrocket-tools' ); ?></h2>
			<div class="grt-result-occasion" id="grt-result-occasion"><?php esc_html_e( 'For: Birthday', 'giftrocket-tools' ); ?></div>
			<div class="grt-result-amount" id="grt-result-amount">$50</div>
			<p class="grt-result-description" id="grt-result-description"></p>
			<div class="grt-result-personality" id="grt-result-personality"></div>
		</div>

		<div class="grt-result-message">
			<p>💡 <strong><?php esc_html_e( 'Why this works:', 'giftrocket-tools' ); ?></strong> <span id="grt-result-why"></span></p>
		</div>

		<div class="grt-result-items-box">
			<h4>🎯 <?php esc_html_e( 'Specific picks Gift Genius recommends', 'giftrocket-tools' ); ?></h4>
			<ul class="grt-result-items-list" id="grt-result-items-list"></ul>
		</div>

		<p class="grt-result-genius-insight" id="grt-result-insight"></p>

		
		<div class="grt-result-footer-actions">
			<button id="grt-retake-quiz-btn" class="grt-quiz-cta grt-secondary" type="button">🔄 <?php esc_html_e( 'Retake Quiz', 'giftrocket-tools' ); ?></button>
			<button id="grt-download-quiz-btn" class="grt-quiz-cta grt-outline" type="button">⬇️ <?php esc_html_e( 'Download PDF', 'giftrocket-tools' ); ?></button>
		</div>
	</div>
</div>
