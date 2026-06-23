<?php
/**
 * Gift Genius Quiz template.
 *
 * @var array $atts Shortcode attributes.
 */

defined( 'ABSPATH' ) || exit;

$shop_url = esc_url( $atts['shop_url'] ?? '' );
?>
<div id="gift-quiz-wrap" data-shop-url="<?php echo esc_attr( $shop_url ); ?>">

	<div id="quiz-intro" class="quiz-screen active">
		<div class="quiz-header">
			<span class="quiz-badge">🧠 Gift Genius</span>
			<h1><?php esc_html_e( 'The', 'giftrocket-tools' ); ?> <span class="gradient-text"><?php esc_html_e( 'Gift Genius', 'giftrocket-tools' ); ?></span> <?php esc_html_e( 'Quiz', 'giftrocket-tools' ); ?></h1>
			<p class="quiz-subtitle"><?php esc_html_e( 'Answer 5 questions — occasion, personality, budget (or enter your own), relationship, and timing. Gift Genius picks specific items, not generic "gift card" fluff.', 'giftrocket-tools' ); ?></p>
		</div>

		<div class="quiz-features">
			<div class="feature-item">
				<span class="feature-icon">⚡</span>
				<span><?php esc_html_e( '~90 seconds', 'giftrocket-tools' ); ?></span>
			</div>
			<div class="feature-item">
				<span class="feature-icon">🎯</span>
				<span><?php esc_html_e( '10+ occasions', 'giftrocket-tools' ); ?></span>
			</div>
			<div class="feature-item">
				<span class="feature-icon">💡</span>
				<span><?php esc_html_e( 'Item-level picks', 'giftrocket-tools' ); ?></span>
			</div>
			<div class="feature-item">
				<span class="feature-icon">✏️</span>
				<span><?php esc_html_e( 'Custom budget', 'giftrocket-tools' ); ?></span>
			</div>
		</div>

		<button id="start-quiz-btn" class="quiz-cta" type="button">
			<span>🎉 <?php esc_html_e( 'Start the Gift Genius Quiz', 'giftrocket-tools' ); ?></span>
			<span class="arrow" aria-hidden="true">→</span>
		</button>

		<p class="quiz-trust">❤️ <?php esc_html_e( 'Used by 12,000+ gift givers • No signup required', 'giftrocket-tools' ); ?></p>
	</div>

	<div id="quiz-container" class="quiz-screen">
		<div class="quiz-progress-wrap" aria-hidden="true">
			<div class="quiz-progress-track">
				<div class="quiz-progress-bar" id="progress-bar"></div>
			</div>
			<span class="quiz-progress-text" id="progress-text">1 / 5</span>
		</div>

		<div class="quiz-question-area" role="group" aria-labelledby="question-text">
			<div class="question-counter" id="question-counter"><?php esc_html_e( 'Question 1 of 5', 'giftrocket-tools' ); ?></div>
			<h2 class="quiz-question" id="question-text"><?php esc_html_e( 'Loading…', 'giftrocket-tools' ); ?></h2>
			<p class="quiz-question-hint" id="question-hint"></p>
		</div>

		<div class="quiz-options" id="options-container" role="listbox" aria-label="<?php esc_attr_e( 'Answer options', 'giftrocket-tools' ); ?>"></div>

		<div class="budget-custom-panel" id="budget-custom-panel">
			<label for="budget-custom-input"><?php esc_html_e( 'Enter your exact budget', 'giftrocket-tools' ); ?></label>
			<div class="budget-custom-input-wrap">
				<span>$</span>
				<input type="number" id="budget-custom-input" min="10" max="2000" step="1" placeholder="e.g. 85" inputmode="numeric">
			</div>
			<p class="budget-custom-hint"><?php esc_html_e( 'Between $10 and $2,000 — we\'ll tailor picks to this number.', 'giftrocket-tools' ); ?></p>
		</div>

		<div class="quiz-nav">
			<button id="prev-btn" class="quiz-nav-btn secondary" type="button" style="visibility:hidden;">← <?php esc_html_e( 'Back', 'giftrocket-tools' ); ?></button>
			<span class="quiz-nav-spacer"></span>
			<button id="next-btn" class="quiz-nav-btn primary" type="button" disabled><?php esc_html_e( 'Next →', 'giftrocket-tools' ); ?></button>
		</div>
	</div>

	<div id="quiz-results" class="quiz-screen" aria-live="polite">
		<div class="result-badge">🧠 <?php esc_html_e( 'Your Gift Genius Result', 'giftrocket-tools' ); ?></div>

		<div class="result-card" id="result-card">
			<div class="result-emoji" id="result-emoji">🎁</div>
			<h2 class="result-title" id="result-title"><?php esc_html_e( 'Gift Card', 'giftrocket-tools' ); ?></h2>
			<div class="result-occasion" id="result-occasion"><?php esc_html_e( 'For: Birthday', 'giftrocket-tools' ); ?></div>
			<div class="result-amount" id="result-amount">$50</div>
			<p class="result-description" id="result-description"></p>
			<div class="result-personality" id="result-personality"></div>
		</div>

		<div class="result-message">
			<p>💡 <strong><?php esc_html_e( 'Why this works:', 'giftrocket-tools' ); ?></strong> <span id="result-why"></span></p>
		</div>

		<div class="result-items-box">
			<h4>🎯 <?php esc_html_e( 'Specific picks Gift Genius recommends', 'giftrocket-tools' ); ?></h4>
			<ul class="result-items-list" id="result-items-list"></ul>
		</div>

		<p class="result-genius-insight" id="result-insight"></p>

		<div class="result-actions">
			<h4>📤 <?php esc_html_e( 'Share this recommendation', 'giftrocket-tools' ); ?></h4>
			<p><?php esc_html_e( 'Send this to your friend to see if they\'d love it!', 'giftrocket-tools' ); ?></p>
			<div class="share-buttons">
				<button class="share-btn whatsapp" type="button" data-share="whatsapp">
					<span aria-hidden="true">📱</span> WhatsApp
				</button>
				<button class="share-btn email" type="button" data-share="email">
					<span aria-hidden="true">📧</span> <?php esc_html_e( 'Email', 'giftrocket-tools' ); ?>
				</button>
				<button class="share-btn copy" type="button" data-share="copy">
					<span aria-hidden="true">📋</span> <?php esc_html_e( 'Copy Link', 'giftrocket-tools' ); ?>
				</button>
				<button class="share-btn twitter" type="button" data-share="twitter">
					<span aria-hidden="true">𝕏</span> <?php esc_html_e( 'Share', 'giftrocket-tools' ); ?>
				</button>
			</div>
		</div>

		<div class="result-footer-actions">
			<button id="retake-quiz-btn" class="quiz-cta secondary" type="button">🔄 <?php esc_html_e( 'Retake Quiz', 'giftrocket-tools' ); ?></button>
			<button id="explore-gifts-btn" class="quiz-cta outline" type="button">🎁 <?php esc_html_e( 'Shop Gift Cards', 'giftrocket-tools' ); ?></button>
		</div>
	</div>
</div>
