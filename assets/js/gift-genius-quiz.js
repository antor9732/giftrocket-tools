(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var wrap = document.getElementById('grt-gift-quiz-wrap');
        if (!wrap) return;

        var DATA = null;
        var lastResult = null;
        var currentQuestionIndex = 0;
        var answers = {};
        var totalQuestions = 5;
        var isQuizActive = false;

        var screens = {
            intro: document.getElementById('grt-quiz-intro'),
            quiz: document.getElementById('grt-quiz-container'),
            results: document.getElementById('grt-quiz-results')
        };

        var startBtn = document.getElementById('grt-start-quiz-btn');
        var questionText = document.getElementById('grt-question-text');
        var questionHint = document.getElementById('grt-question-hint');
        var questionCounter = document.getElementById('grt-question-counter');
        var optionsContainer = document.getElementById('grt-options-container');
        var budgetCustomPanel = document.getElementById('grt-budget-custom-panel');
        var budgetCustomInput = document.getElementById('grt-budget-custom-input');
        var progressBar = document.getElementById('grt-progress-bar');
        var progressText = document.getElementById('grt-progress-text');
        var prevBtn = document.getElementById('grt-prev-btn');
        var nextBtn = document.getElementById('grt-next-btn');
        var retakeBtn = document.getElementById('grt-retake-quiz-btn');
        var downloadBtn = document.getElementById('grt-download-quiz-btn');

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

            optionsContainer.className = 'grt-quiz-options';
            if (question.layout === 'compact' || question.layout === 'budget') {
                optionsContainer.classList.add('grt-quiz-options--compact');
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
                btn.className = 'grt-quiz-option';
                btn.setAttribute('role', 'option');
                btn.setAttribute('aria-selected', answers[question.id] === option.id ? 'true' : 'false');
                btn.dataset.value = option.id;
                btn.innerHTML =
                    '<span class="grt-option-emoji" aria-hidden="true">' + option.emoji + '</span>' +
                    '<span class="grt-option-label">' + option.label + '</span>' +
                    (option.desc ? '<span class="grt-option-description">' + option.desc + '</span>' : '');

                if (answers[question.id] === option.id) {
                    btn.classList.add('selected');
                }

                btn.addEventListener('click', function () {
                    selectOption(question, option.id);
                });

                optionsContainer.appendChild(btn);
            });

            prevBtn.style.visibility = index === 0 ? 'hidden' : 'visible';
            nextBtn.textContent = index === totalQuestions - 1 ? '✨ Show Result' : 'Next →';
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

            optionsContainer.querySelectorAll('.grt-quiz-option').forEach(function (opt) {
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
            var list = document.getElementById('grt-result-items-list');
            list.innerHTML = '';

            result.allPicks.forEach(function (item, idx) {
                var li = document.createElement('li');
                li.className = 'grt-result-item-row' + (idx === 0 ? ' is-primary' : '');

                var priceHint = item.budgetMin === item.budgetMax
                    ? formatMoney(item.budgetMin)
                    : formatMoney(item.budgetMin) + '–' + formatMoney(item.budgetMax);

                li.innerHTML =
                    '<span class="grt-result-item-emoji" aria-hidden="true">' + item.emoji + '</span>' +
                    '<div class="grt-result-item-body">' +
                        '<strong>' + item.name + '</strong>' +
                        '<span>Typical range: ' + priceHint + '</span>' +
                    '</div>' +
                    (idx === 0 ? '<span class="grt-result-item-badge">Top pick</span>' : '<span class="grt-result-item-badge">Alt</span>');

                list.appendChild(li);
            });
        }

        function showResults() {
            showScreen('results');
            progressBar.style.width = '100%';

            lastResult = getResult();
            var result = lastResult;

            document.getElementById('grt-result-emoji').textContent = result.emoji;
            document.getElementById('grt-result-title').textContent = result.title;
            document.getElementById('grt-result-occasion').textContent = 'For: ' + result.occasionLabel;
            document.getElementById('grt-result-amount').textContent = result.amount;
            document.getElementById('grt-result-description').textContent = result.description;
            document.getElementById('grt-result-why').textContent = result.why;
            document.getElementById('grt-result-insight').textContent = '🧠 ' + result.insight;

            var tagsContainer = document.getElementById('grt-result-personality');
            tagsContainer.innerHTML = '';
            result.tags.forEach(function (tag) {
                var span = document.createElement('span');
                span.className = 'grt-personality-tag';
                span.textContent = '✨ ' + tag;
                tagsContainer.appendChild(span);
            });

            renderResultItems(result);
            trackQuizCompletion();
            screens.results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function waitForImages(root) {
            var images = root.querySelectorAll('img');
            if (!images.length) {
                return Promise.resolve();
            }

            return Promise.all(Array.prototype.map.call(images, function (img) {
                if (img.complete && img.naturalWidth > 0) {
                    return Promise.resolve();
                }

                return new Promise(function (resolve) {
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                });
            }));
        }

        function applyPdfEmojiImages(root) {
            if (typeof twemoji === 'undefined') {
                return;
            }

            var pdfConfig = (window.giftRocketQuiz && giftRocketQuiz.pdf) || {};
            twemoji.parse(root, {
                folder: '72x72',
                ext: '.png',
                base: pdfConfig.twemojiBase || 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/',
                className: 'grt-quiz-pdf-emoji',
                attributes: function () {
                    return {
                        crossorigin: 'anonymous',
                        referrerpolicy: 'no-referrer',
                        loading: 'eager',
                        decoding: 'sync'
                    };
                }
            });

            root.querySelectorAll('img.grt-quiz-pdf-emoji').forEach(function (img) {
                var src = img.currentSrc || img.src || img.getAttribute('src');
                if (!src) {
                    return;
                }
                img.crossOrigin = 'anonymous';
                img.referrerPolicy = 'no-referrer';
                img.loading = 'eager';
                img.decoding = 'sync';
                img.setAttribute('crossorigin', 'anonymous');
                img.setAttribute('referrerpolicy', 'no-referrer');
                img.setAttribute('loading', 'eager');
                img.setAttribute('decoding', 'sync');
                if (img.src !== src) {
                    img.src = src;
                }
            });
        }

        function inlineImagesAsDataUrls(root) {
            var images = Array.prototype.slice.call(root.querySelectorAll('img'));
            if (!images.length) {
                return Promise.resolve();
            }

            return Promise.all(images.map(function (img) {
                if (!img.src || img.src.indexOf('data:') === 0) {
                    return Promise.resolve();
                }

                var src = img.src;

                return fetch(src, { mode: 'cors', credentials: 'omit', cache: 'force-cache' })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('emoji_fetch_failed');
                        }
                        return response.blob();
                    })
                    .then(function (blob) {
                        return new Promise(function (resolve) {
                            var reader = new FileReader();
                            reader.onloadend = function () {
                                if (typeof reader.result === 'string') {
                                    img.src = reader.result;
                                }
                                resolve();
                            };
                            reader.onerror = resolve;
                            reader.readAsDataURL(blob);
                        });
                    })
                    .catch(function () {
                        return new Promise(function (resolve) {
                            var proxyImg = new Image();
                            proxyImg.crossOrigin = 'anonymous';
                            proxyImg.referrerPolicy = 'no-referrer';
                            proxyImg.onload = function () {
                                var canvas = document.createElement('canvas');
                                canvas.width = proxyImg.naturalWidth || 72;
                                canvas.height = proxyImg.naturalHeight || 72;
                                try {
                                    canvas.getContext('2d').drawImage(proxyImg, 0, 0);
                                    img.src = canvas.toDataURL('image/png');
                                } catch (e) {
                                    // Keep remote src if canvas is tainted.
                                }
                                resolve();
                            };
                            proxyImg.onerror = resolve;
                            proxyImg.src = src;
                        });
                    });
            }));
        }

        function addCanvasToPdf(pdf, canvas, marginX) {
            var imgData = canvas.toDataURL('image/png');
            var pdfWidth = pdf.internal.pageSize.getWidth();
            var pdfHeight = pdf.internal.pageSize.getHeight();
            var marginTop = marginX;
            var contentWidth = pdfWidth - marginX * 2;
            var contentHeight = pdfHeight - marginTop * 2;
            var imgWidth = contentWidth;
            var imgHeight = (canvas.height * imgWidth) / canvas.width;
            var heightLeft = imgHeight;
            var position = marginTop;

            pdf.addImage(imgData, 'PNG', marginX, position, imgWidth, imgHeight);
            heightLeft -= contentHeight;

            while (heightLeft > 0) {
                position = heightLeft - imgHeight + marginTop;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', marginX, position, imgWidth, imgHeight);
                heightLeft -= contentHeight;
            }
        }

        function downloadQuizPdf() {
            var pdfConfig = (window.giftRocketQuiz && giftRocketQuiz.pdf) || {};

            if (!lastResult) {
                window.alert(pdfConfig.completeFirst || 'Please complete the quiz before downloading your results.');
                return Promise.reject(new Error('quiz_incomplete'));
            }

            if (typeof html2canvas !== 'function' || !window.jspdf || !window.jspdf.jsPDF) {
                window.alert(pdfConfig.libraryError || 'PDF download is unavailable. Please refresh the page and try again.');
                return Promise.reject(new Error('pdf_libraries_missing'));
            }

            var resultsEl = document.getElementById('grt-quiz-results');
            if (!resultsEl) {
                return Promise.reject(new Error('results_element_missing'));
            }

            var captureWidth = resultsEl.offsetWidth || 720;
            var marginX = typeof pdfConfig.marginX === 'number' ? pdfConfig.marginX : 15;

            var wrapper = document.createElement('div');
            wrapper.setAttribute('aria-hidden', 'true');
            wrapper.style.cssText = 'position:fixed;left:-10000px;top:0;z-index:-1;width:' + captureWidth + 'px;background:#fff;';

            var clone = resultsEl.cloneNode(true);
            clone.classList.add('grt-quiz-results--pdf-capture');
            clone.style.display = 'block';

            var footer = clone.querySelector('.grt-result-footer-actions');
            if (footer) {
                footer.remove();
            }

            wrapper.appendChild(clone);
            document.body.appendChild(wrapper);
            applyPdfEmojiImages(clone);

            return waitForImages(clone)
                .then(function () {
                    return inlineImagesAsDataUrls(clone);
                })
                .then(function () {
                    return waitForImages(clone);
                })
                .then(function () {
                    return html2canvas(clone, {
                        scale: 2,
                        useCORS: true,
                        allowTaint: false,
                        logging: false,
                        backgroundColor: '#ffffff',
                        width: captureWidth,
                        windowWidth: captureWidth,
                        imageTimeout: 15000
                    });
                })
                .then(function (canvas) {
                    var JsPDF = window.jspdf.jsPDF;
                    var pdf = new JsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                    addCanvasToPdf(pdf, canvas, marginX);

                    var prefix = pdfConfig.filenamePrefix || 'gift-genius-quiz';
                    var filename = prefix + '-' + new Date().toISOString().slice(0, 10) + '.pdf';
                    pdf.save(filename);
                })
                .finally(function () {
                    if (wrapper.parentNode) {
                        wrapper.parentNode.removeChild(wrapper);
                    }
                });
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

        downloadBtn.addEventListener('click', function () {
            var originalLabel = downloadBtn.innerHTML;
            downloadBtn.disabled = true;
            downloadBtn.textContent = 'Preparing PDF…';

            downloadQuizPdf()
                .catch(function (err) {
                    if (err && err.message !== 'quiz_incomplete' && err.message !== 'pdf_libraries_missing') {
                        console.error(err);
                        window.alert('Could not generate the PDF. Please try again.');
                    }
                })
                .finally(function () {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalLabel;
                });
        });

        wrap.querySelectorAll('[data-share]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                shareResult(btn.dataset.share);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (!isQuizActive) return;

            var active = document.activeElement;
            if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable)) {
                return;
            }

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
                var option = optionsContainer.querySelectorAll('.grt-quiz-option')[num - 1];
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
