(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var forecasterWrap = document.getElementById('gr-forecaster-wrap');
        if (!forecasterWrap) return;

        var config = window.giftRocketForecaster || {};
        var aovInput = document.getElementById('gr_aov');
        var visitorsInput = document.getElementById('gr_visitors');
        var conversionInput = document.getElementById('gr_conversion');
        var calcBtn = document.getElementById('gr_calculate_btn');
        var btnText = calcBtn.querySelector('.gr-btn-text');
        var btnLoader = calcBtn.querySelector('.gr-btn-loader');
        var resultArea = document.getElementById('gr_result_area');
        var monthlyDisplay = document.getElementById('gr_monthly_loss');
        var yearlyDisplay = document.getElementById('gr_yearly_loss');
        var modal = document.getElementById('gr_lead_modal');
        var closeModalBtn = document.getElementById('gr_close_modal');
        var leadForm = document.getElementById('gr_lead_form');
        var emailInput = document.getElementById('gr_user_email');
        var urlInput = document.getElementById('gr_store_url');

        var fields = {
            aov: aovInput.closest('.gr-field'),
            visitors: visitorsInput.closest('.gr-field'),
            conversion: conversionInput.closest('.gr-field')
        };

        function formatCurrency(amount) {
            return '$' + Math.round(amount).toLocaleString('en-US');
        }

        function parseVisitorsValue() {
            return parseFloat(String(visitorsInput.value).replace(/[^\d.]/g, '')) || 0;
        }

        function formatVisitorsInput() {
            var raw = visitorsInput.value.replace(/[^\d]/g, '');
            visitorsInput.value = raw ? parseInt(raw, 10).toLocaleString('en-US') : '';
        }

        function clearFieldErrors() {
            Object.values(fields).forEach(function (field) {
                if (field) field.classList.remove('has-error');
            });
        }

        function markFieldError(field) {
            if (field) field.classList.add('has-error');
        }

        function animateNumber(element, targetValue) {
            var current = 0;
            var increment = Math.max(1, Math.floor(targetValue / 40));
            var interval = setInterval(function () {
                current += increment;
                if (current >= targetValue) {
                    current = targetValue;
                    clearInterval(interval);
                }
                element.textContent = formatCurrency(current);
            }, 20);
        }

        function calculateRevenue() {
            var aov = parseFloat(aovInput.value) || 0;
            var visitors = parseVisitorsValue();
            var conversion = parseFloat(conversionInput.value) || 0;

            if (aov < 1) aov = 1;
            if (visitors < 1) visitors = 1;
            if (conversion < 0.1) conversion = 0.1;

            var monthlySales = visitors * (conversion / 100) * aov;
            var giftCardPenetrationRate = 0.065;
            var monthlyLoss = monthlySales * giftCardPenetrationRate;
            var yearlyLoss = monthlyLoss * 12;

            return { monthlyLoss: monthlyLoss, yearlyLoss: yearlyLoss };
        }

        function showResults() {
            var totals = calculateRevenue();
            resultArea.style.display = 'block';
            animateNumber(monthlyDisplay, totals.monthlyLoss);
            animateNumber(yearlyDisplay, totals.yearlyLoss);
        }

        function openModal() {
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { emailInput.focus(); }, 100);
        }

        function closeModalFunc() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            calcBtn.focus();
        }

        function setCalculating(isCalculating) {
            calcBtn.disabled = isCalculating;
            btnText.style.display = isCalculating ? 'none' : '';
            btnLoader.style.display = isCalculating ? '' : 'none';
        }

        calcBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearFieldErrors();

            var aov = parseFloat(aovInput.value);
            var visitors = parseVisitorsValue();
            var conversion = parseFloat(conversionInput.value);

            if (!aov || aov < 1) {
                markFieldError(fields.aov);
                aovInput.focus();
                return;
            }
            if (!visitors || visitors < 1) {
                markFieldError(fields.visitors);
                visitorsInput.focus();
                return;
            }
            if (!conversion || conversion < 0.1) {
                markFieldError(fields.conversion);
                conversionInput.focus();
                return;
            }

            setCalculating(true);

            setTimeout(function () {
                showResults();
                setCalculating(false);
                setTimeout(openModal, 700);
            }, 400);
        });

        closeModalBtn.addEventListener('click', closeModalFunc);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModalFunc();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModalFunc();
            }
        });

        leadForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var email = emailInput.value.trim();
            var storeUrl = urlInput.value.trim();

            if (!email || !email.includes('@')) {
                emailInput.focus();
                return;
            }
            if (!storeUrl || !storeUrl.includes('.')) {
                urlInput.focus();
                return;
            }

            var submitBtn = leadForm.querySelector('.gr-modal-submit');
            var submitLabel = config.submitLabel || 'Send My Report & Unlock 20% Off →';
            submitBtn.textContent = '✅ Sending…';
            submitBtn.disabled = true;

            var formData = new FormData();
            formData.append('action', 'giftrocket_capture_lead');
            formData.append('nonce', config.nonce || '');
            formData.append('email', email);
            formData.append('store_url', storeUrl);
            formData.append('monthly_loss', monthlyDisplay.textContent);
            formData.append('yearly_loss', yearlyDisplay.textContent);

            fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.data || !result.data.success) {
                        var message = (result.data && result.data.data && result.data.data.message) ||
                            config.errorMessage ||
                            'We could not send your discount email. Please try again.';
                        window.alert(message);
                        submitBtn.textContent = submitLabel;
                        submitBtn.disabled = false;
                        return;
                    }

                    var redirect = (result.data.data && result.data.data.redirect) || config.redirectUrl || '/';
                    window.location.href = redirect;
                })
                .catch(function () {
                    window.alert(config.errorMessage || 'We could not send your discount email. Please try again.');
                    submitBtn.textContent = submitLabel;
                    submitBtn.disabled = false;
                });
        });

        forecasterWrap.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calcBtn.click();
                }
            });
            input.addEventListener('input', clearFieldErrors);
        });

        visitorsInput.addEventListener('input', formatVisitorsInput);
        formatVisitorsInput();
    });
})();
