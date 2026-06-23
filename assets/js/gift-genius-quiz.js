(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var wrap = document.getElementById('gift-quiz-wrap');
        if (!wrap) return;

        var DATA = null;
        var lastResult = null;
        var currentQuestionIndex = 0;
        var answers = {};
        var totalQuestions = 5;
        var isQuizActive = false;

        var screens = {
            intro: document.getElementById('quiz-intro'),
            quiz: document.getElementById('quiz-container'),
            results: document.getElementById('quiz-results')
        };

        var startBtn = document.getElementById('start-quiz-btn');
        var questionText = document.getElementById('question-text');
        var questionHint = document.getElementById('question-hint');
        var questionCounter = document.getElementById('question-counter');
        var optionsContainer = document.getElementById('options-container');
        var budgetCustomPanel = document.getElementById('budget-custom-panel');
        var budgetCustomInput = document.getElementById('budget-custom-input');
        var progressBar = document.getElementById('progress-bar');
        var progressText = document.getElementById('progress-text');
        var prevBtn = document.getElementById('prev-btn');
        var nextBtn = document.getElementById('next-btn');
        var retakeBtn = document.getElementById('retake-quiz-btn');
        var exploreBtn = document.getElementById('explore-gifts-btn');

        function loadGiftGeniusData() {
            var jsonUrl = (window.giftRocketQuiz && giftRocketQuiz.jsonUrl) || wrap.dataset.jsonUrl || 'gift-genius-data.json';
            return fetch(jsonUrl)
                .then(function (res) {
                    if (!res.ok) throw new Error('Failed to load quiz data');
                    return res.json();
                })
                .catch(function () {
                    throw new Error('Could not load gift quiz data.');
                });
        }

        function showScreen(name) {
            Object.keys(screens).forEach(function (key) {
                var screen = screens[key];
                var isActive = key === name;
                screen.classList.toggle('active', isActive);
                screen.style.display = isActive ? 'block' : 'none';
            });
            isQuizActive = name === 'quiz';
        }

        function getOptionsForQuestion(questionId) {
            var map = {
                occasion: DATA.occasions,
                personality: DATA.personalities,
                budget: DATA.budgetPresets,
                relationship: DATA.relationships,
                delivery: DATA.delivery
            };
            var source = map[questionId];
            if (!source) return [];

            return Object.keys(source).map(function (key) {
                var item = source[key];
                return {
                    id: key,
                    emoji: item.emoji,
                    label: item.label,
                    desc: item.desc || ''
                };
            });
        }

        function isQuestionAnswered(question) {
            if (question.id === 'budget') {
                if (answers.budget === 'custom') {
                    return !!answers.customAmount && answers.customAmount >= 10;
                }
                return !!answers.budget;
            }
            return !!answers[question.id];
        }

        function updateNextButtonState(question) {
            nextBtn.disabled = !isQuestionAnswered(question);
        }

        function renderQuestion(index) {
            var question = DATA.questions[index];
            if (!question) return;

            questionText.textContent = (question.emoji ? question.emoji + ' ' : '') + question.question;
            questionHint.textContent = question.hint || '';
            questionCounter.textContent = 'Question ' + (index + 1) + ' of ' + totalQuestions;

            var progress = ((index + 1) / totalQuestions) * 100;
            progressBar.style.width = progress + '%';
            progressText.textContent = (index + 1) + ' / ' + totalQuestions;

            optionsContainer.className = 'quiz-options';
            if (question.layout === 'compact' || question.layout === 'budget') {
                optionsContainer.classList.add('quiz-options--compact');
            }

            budgetCustomPanel.classList.toggle('visible', question.type === 'budget' && answers.budget === 'custom');
            if (question.type === 'budget' && answers.customAmount) {
                budgetCustomInput.value = answers.customAmount;
            }

            optionsContainer.innerHTML = '';
            var options = getOptionsForQuestion(question.id);

            options.forEach(function (option) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'quiz-option';
                btn.setAttribute('role', 'option');
                btn.setAttribute('aria-selected', answers[question.id] === option.id ? 'true' : 'false');
                btn.dataset.value = option.id;
                btn.innerHTML =
                    '<span class="option-emoji" aria-hidden="true">' + option.emoji + '</span>' +
                    '<span class="option-label">' + option.label + '</span>' +
                    (option.desc ? '<span class="option-description">' + option.desc + '</span>' : '');

                if (answers[question.id] === option.id) {
                    btn.classList.add('selected');
                }

                btn.addEventListener('click', function () {
                    selectOption(question, option.id);
                });

                optionsContainer.appendChild(btn);
            });

            prevBtn.style.visibility = index === 0 ? 'hidden' : 'visible';
            nextBtn.textContent = index === totalQuestions - 1 ? '✨ See My Result' : 'Next →';
            updateNextButtonState(question);

            screens.quiz.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function selectOption(question, optionId) {
            answers[question.id] = optionId;

            if (question.id === 'budget') {
                if (optionId === 'custom') {
                    budgetCustomPanel.classList.add('visible');
                    budgetCustomInput.focus();
                } else {
                    budgetCustomPanel.classList.remove('visible');
                    delete answers.customAmount;
                }
            }

            optionsContainer.querySelectorAll('.quiz-option').forEach(function (opt) {
                var selected = opt.dataset.value === optionId;
                opt.classList.toggle('selected', selected);
                opt.setAttribute('aria-selected', selected ? 'true' : 'false');
            });

            updateNextButtonState(question);

            var autoAdvance = question.id !== 'budget' || optionId !== 'custom';
            if (autoAdvance && window.matchMedia('(max-width: 600px)').matches) {
                setTimeout(goToNext, 400);
            }
        }

        function goToNext() {
            var question = DATA.questions[currentQuestionIndex];
            if (nextBtn.disabled || !isQuestionAnswered(question)) return;

            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                renderQuestion(currentQuestionIndex);
            } else {
                showResults();
            }
        }

        function goToPrev() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                renderQuestion(currentQuestionIndex);
            }
        }

        function formatMoney(amount) {
            var sym = DATA.meta.currencySymbol || '$';
            return sym + Math.round(amount).toLocaleString('en-US');
        }

        function resolveBudget() {
            if (answers.budget === 'custom' && answers.customAmount) {
                var amt = parseFloat(answers.customAmount);
                return {
                    min: amt * 0.85,
                    max: amt * 1.15,
                    sweetSpot: amt,
                    display: formatMoney(amt),
                    isCustom: true
                };
            }

            var preset = DATA.budgetPresets[answers.budget] || DATA.budgetPresets.medium;
            var relMod = DATA.relationshipModifiers[answers.relationship];
            var boost = relMod ? relMod.amountBoost : 1;
            var range = preset.max - preset.min;
            var variance = (Math.random() - 0.5) * range * 0.2;
            var sweet = Math.round((preset.sweetSpot + variance) * boost);
            sweet = Math.max(preset.min, Math.min(preset.max, sweet));

            return {
                min: preset.min,
                max: preset.max,
                sweetSpot: sweet,
                display: formatMoney(sweet),
                isCustom: false
            };
        }

        function scoreItem(item, ctx) {
            var score = 0;
            var budget = ctx.budget.sweetSpot;

            if (budget >= item.budgetMin && budget <= item.budgetMax) {
                score += 48;
            } else if (budget < item.budgetMin) {
                score += Math.max(0, 28 - (item.budgetMin - budget) / 4);
            } else {
                score += Math.max(0, 28 - (budget - item.budgetMax) / 6);
            }

            if (item.personalities.indexOf(ctx.personality) !== -1) score += 26;

            if (item.occasions.indexOf('*') !== -1 || item.occasions.indexOf(ctx.occasion) !== -1) {
                score += 18;
                if (item.occasions.length <= 2) score += 10;
            }

            if (item.relationships.indexOf('any') !== -1 || item.relationships.indexOf(ctx.relationship) !== -1) {
                score += 14;
            }

            var delivery = DATA.delivery[ctx.delivery] || DATA.delivery.flexible;
            if (item.digitalFriendly && delivery.digitalBonus) {
                score += delivery.digitalBonus * 0.6;
            }

            score += (item.surpriseFactor || 0.5) * 22 * Math.random();
            score += Math.random() * 10;

            return score;
        }

        function pickWeightedItems(scored, count) {
            var pool = scored.slice(0, Math.min(15, scored.length)).map(function (entry) {
                return { item: entry.item, score: entry.score };
            });
            var picked = [];
            var usedIds = {};

            while (picked.length < count && pool.length > 0) {
                var totalWeight = pool.reduce(function (sum, entry, idx) {
                    return sum + Math.max(1, entry.score - idx * 1.5);
                }, 0);
                var roll = Math.random() * totalWeight;
                var chosenIndex = 0;

                for (var i = 0; i < pool.length; i++) {
                    roll -= Math.max(1, pool[i].score - i * 1.5);
                    if (roll <= 0) {
                        chosenIndex = i;
                        break;
                    }
                }

                var chosen = pool.splice(chosenIndex, 1)[0];
                if (!usedIds[chosen.item.id]) {
                    picked.push(chosen);
                    usedIds[chosen.item.id] = true;
                }
            }

            return picked;
        }

        function pickFromArray(arr) {
            return arr[Math.floor(Math.random() * arr.length)];
        }

        function buildGeniusInsight(ctx) {
            var personality = DATA.personalities[ctx.personality];
            var templates = DATA.geniusInsights.slice();
            var text = pickFromArray(templates);

            text = text.replace('{trait}', (personality.traits && personality.traits[0]) || personality.label.toLowerCase());
            text = text.replace('{occasion}', DATA.occasions[ctx.occasion].label.toLowerCase());

            var hook = DATA.occasionHooks[ctx.occasion];
            if (hook && Math.random() > 0.45) {
                text = hook + ' ' + text;
            }

            return text;
        }

        function buildWhyText(ctx, primaryItem) {
            var pool = DATA.whyTemplates[ctx.personality] || DATA.whyTemplates.experience || [];
            var why = pickFromArray(pool);

            if (ctx.budget.isCustom) {
                why += ' At ' + ctx.budget.display + ', you set the ceiling — these picks fit that lane.';
            }

            if (primaryItem.digitalFriendly && DATA.delivery[ctx.delivery].urgency > 0.6) {
                why += ' Digital delivery means you can still nail a last-minute save.';
            }

            return why;
        }

        function buildDescription(item, ctx) {
            var occasionMeta = DATA.occasions[ctx.occasion];
            var personalityMeta = DATA.personalities[ctx.personality];
            var openers = [
                'For a ' + occasionMeta.label.toLowerCase() + ' gift to a ' + personalityMeta.label.toLowerCase() + ', ',
                'Given their ' + personalityMeta.desc.toLowerCase() + ' energy, ',
                'Here\'s the move: '
            ];
            return pickFromArray(openers) + item.name.toLowerCase() + ' hits the sweet spot — specific enough to feel personal, flexible enough to never miss.';
        }

        function getResult() {
            var personality = answers.personality || 'experience';
            var occasion = answers.occasion || 'justbecause';
            var relationship = answers.relationship || 'friend';
            var delivery = answers.delivery || 'flexible';
            var budget = resolveBudget();

            var ctx = { personality: personality, occasion: occasion, relationship: relationship, delivery: delivery, budget: budget };

            var scored = DATA.giftItems.map(function (item) {
                return { item: item, score: scoreItem(item, ctx) };
            }).sort(function (a, b) {
                return b.score - a.score;
            });

            var picks = pickWeightedItems(scored, 3);
            var primary = picks[0] ? picks[0].item : DATA.giftItems[0];
            var alternates = picks.slice(1).map(function (p) { return p.item; });

            var personalityMeta = DATA.personalities[personality];
            var relMod = DATA.relationshipModifiers[relationship];
            var tags = personalityMeta.traits.slice(0, 2).map(function (t) {
                return t.charAt(0).toUpperCase() + t.slice(1);
            });
            if (relMod && relMod.tag) tags.push(relMod.tag);

            return {
                emoji: primary.emoji,
                title: primary.name,
                amount: budget.display,
                description: buildDescription(primary, ctx),
                why: buildWhyText(ctx, primary),
                insight: buildGeniusInsight(ctx),
                tags: tags,
                occasionLabel: DATA.occasions[occasion].label + ' ' + DATA.occasions[occasion].emoji,
                primary: primary,
                alternates: alternates,
                allPicks: picks.map(function (p) { return p.item; })
            };
        }

        function renderResultItems(result) {
            var list = document.getElementById('result-items-list');
            list.innerHTML = '';

            result.allPicks.forEach(function (item, idx) {
                var li = document.createElement('li');
                li.className = 'result-item-row' + (idx === 0 ? ' is-primary' : '');

                var priceHint = item.budgetMin === item.budgetMax
                    ? formatMoney(item.budgetMin)
                    : formatMoney(item.budgetMin) + '–' + formatMoney(item.budgetMax);

                li.innerHTML =
                    '<span class="result-item-emoji" aria-hidden="true">' + item.emoji + '</span>' +
                    '<div class="result-item-body">' +
                        '<strong>' + item.name + '</strong>' +
                        '<span>Typical range: ' + priceHint + '</span>' +
                    '</div>' +
                    (idx === 0 ? '<span class="result-item-badge">Top pick</span>' : '<span class="result-item-badge">Alt</span>');

                list.appendChild(li);
            });
        }

        function showResults() {
            showScreen('results');
            progressBar.style.width = '100%';

            lastResult = getResult();
            var result = lastResult;

            document.getElementById('result-emoji').textContent = result.emoji;
            document.getElementById('result-title').textContent = result.title;
            document.getElementById('result-occasion').textContent = 'For: ' + result.occasionLabel;
            document.getElementById('result-amount').textContent = result.amount;
            document.getElementById('result-description').textContent = result.description;
            document.getElementById('result-why').textContent = result.why;
            document.getElementById('result-insight').textContent = '🧠 ' + result.insight;

            var tagsContainer = document.getElementById('result-personality');
            tagsContainer.innerHTML = '';
            result.tags.forEach(function (tag) {
                var span = document.createElement('span');
                span.className = 'personality-tag';
                span.textContent = '✨ ' + tag;
                tagsContainer.appendChild(span);
            });

            renderResultItems(result);
            trackQuizCompletion();
            screens.results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function shareResult(platform) {
            var result = lastResult || getResult();
            var items = result.allPicks.map(function (i) { return i.emoji + ' ' + i.name; }).join('\n• ');
            var text = '🧠 Gift Genius picked:\n• ' + items + '\n\nBudget: ' + result.amount + '\n\nTake the quiz: ' + window.location.href;

            switch (platform) {
                case 'whatsapp':
                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank', 'noopener');
                    break;
                case 'email':
                    window.open('mailto:?subject=' + encodeURIComponent('My Gift Genius picks') + '&body=' + encodeURIComponent(text), '_self');
                    break;
                case 'twitter':
                    window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(text), '_blank', 'noopener');
                    break;
                case 'copy':
                    copyShareText(text);
                    break;
            }
        }

        function copyShareText(text) {
            var btn = wrap.querySelector('.share-btn.copy');
            var originalText = btn.innerHTML;

            function showCopied() {
                btn.innerHTML = '✅ Copied!';
                setTimeout(function () { btn.innerHTML = originalText; }, 2000);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(showCopied).catch(function () {
                    fallbackCopy(text);
                    showCopied();
                });
            } else {
                fallbackCopy(text);
                showCopied();
            }
        }

        function fallbackCopy(text) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }

        function resetQuiz() {
            currentQuestionIndex = 0;
            Object.keys(answers).forEach(function (key) { delete answers[key]; });
            budgetCustomInput.value = '';
            budgetCustomPanel.classList.remove('visible');
            lastResult = null;
            progressBar.style.width = '0%';
            showScreen('quiz');
            renderQuestion(0);
        }

        function trackQuizCompletion() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'quiz_completed', {
                    occasion: answers.occasion,
                    personality: answers.personality,
                    budget: answers.budget,
                    custom_amount: answers.customAmount || null,
                    relationship: answers.relationship,
                    delivery: answers.delivery
                });
            }
        }

        budgetCustomInput.addEventListener('input', function () {
            var val = parseFloat(budgetCustomInput.value);
            if (val >= 10 && val <= 2000) {
                answers.customAmount = val;
            } else {
                delete answers.customAmount;
            }
            updateNextButtonState(DATA.questions[currentQuestionIndex]);
        });

        startBtn.addEventListener('click', function () {
            showScreen('quiz');
            renderQuestion(0);
        });

        nextBtn.addEventListener('click', goToNext);
        prevBtn.addEventListener('click', goToPrev);
        retakeBtn.addEventListener('click', resetQuiz);

        exploreBtn.addEventListener('click', function () {
            var shopUrl = wrap.dataset.shopUrl;
            if (shopUrl) {
                window.location.href = shopUrl;
                return;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        wrap.querySelectorAll('[data-share]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                shareResult(btn.dataset.share);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (!isQuizActive) return;

            if (e.key === 'Enter' && !nextBtn.disabled) {
                e.preventDefault();
                goToNext();
            }
            if (e.key === 'ArrowLeft' && prevBtn.style.visibility !== 'hidden') {
                e.preventDefault();
                goToPrev();
            }
            if (e.key === 'ArrowRight' && !nextBtn.disabled) {
                e.preventDefault();
                goToNext();
            }

            var num = parseInt(e.key, 10);
            if (num >= 1 && num <= 9) {
                var option = optionsContainer.querySelectorAll('.quiz-option')[num - 1];
                if (option) option.click();
            }
        });

        startBtn.disabled = true;
        startBtn.querySelector('span').textContent = '⏳ Loading Gift Genius…';

        loadGiftGeniusData()
            .then(function (data) {
                DATA = data;
                totalQuestions = DATA.questions.length;
                startBtn.disabled = false;
                startBtn.querySelector('span').textContent = '🎉 Start the Gift Genius Quiz';
                showScreen('intro');
            })
            .catch(function (err) {
                startBtn.querySelector('span').textContent = '⚠️ Quiz data failed to load';
                questionText.textContent = err.message;
                console.error(err);
            });
    });
})();
