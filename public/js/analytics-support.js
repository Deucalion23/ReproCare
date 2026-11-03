(() => {
    'use strict';
    const init = () => {
        const form = document.getElementById('analytics-chat');
        if (!form) return;
        const question = document.getElementById('analytics-question');
        const button = document.getElementById('analytics-ask');
        const answer = document.getElementById('analytics-chat-answer');
        const notice = document.getElementById('analytics-chat-notice');
        const context = document.getElementById('analytics-chat-context');
        const result = document.getElementById('analytics-chat-result');
        const filters = JSON.parse(document.getElementById('analytics-filter-data').textContent);
        document.getElementById('analytics-print').addEventListener('click', () => window.print());
        const topics = document.querySelectorAll('[data-analytics-question]');
        const updateTopics = () => topics.forEach(chip => {
            chip.setAttribute('aria-pressed', String(question.value.trim() === chip.dataset.analyticsQuestion));
        });
        topics.forEach(chip => {
            chip.addEventListener('click', () => {
                question.value = chip.dataset.analyticsQuestion;
                updateTopics();
                question.focus();
            });
        });
        question.addEventListener('input', updateTopics);

        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        const parsePatientItem = (text) => {
            // Flexible match: Name - Barangay, EDD Date, (Risk); To do: todo
            const m = text.match(/^([^-–]+?)\s*[-–]\s*([^,]+?),\s*EDD\s*([^;(]+?)\s*\(([^)]+)\)\s*;\s*To do:\s*(.+)$/i);
            if (!m) return null;
            return {
                name: m[1].trim(),
                barangay: m[2].trim(),
                edd: m[3].trim(),
                risk: m[4].trim(),
                todo: m[5].trim()
            };
        };

        const patientCard = (item) => {
            const card = document.createElement('article');
            card.className = 'an-patient-card';

            const riskClass = item.risk.toLowerCase();

            card.innerHTML = `
                <div class="an-patient-info">
                    <div class="an-patient-name">${escapeHtml(item.name)}</div>
                    <div class="an-patient-meta">
                        <span>${escapeHtml(item.barangay)}</span>
                        <span>•</span>
                        <span>EDD ${escapeHtml(item.edd)}</span>
                    </div>
                </div>
                <div class="an-patient-actions">
                    <span class="an-badge ${riskClass}">${escapeHtml(item.risk)}</span>
                    <div class="an-patient-todo">${escapeHtml(item.todo)}</div>
                </div>
            `;
            return card;
        };

        const BULLET_RE = /^[•\-*–—]\s+/;
        const NUMBERED_RE = /^\d{1,2}[.)]\s+/;

        /** Inline **bold** support, built from text nodes so no HTML is injected. */
        const renderInline = (el, text) => {
            text.split(/\*\*([^*]+)\*\*/).forEach((part, index) => {
                if (index % 2 === 1) {
                    const strong = document.createElement('strong');
                    strong.textContent = part;
                    el.appendChild(strong);
                } else if (part.length) {
                    el.append(document.createTextNode(part));
                }
            });
        };

        /**
         * Render the answer line by line so it never collapses into one block:
         * bullet or numbered lines become lists, short labels ending in a colon
         * (or bold/markdown headings) become section headings, and every other
         * line stands alone as its own paragraph.
         */
        const renderAnswerText = (body, text) => {
            let list = null;
            let listTag = null;
            text.split(/\r?\n/).forEach(rawLine => {
                const line = rawLine.trim();
                if (!line) {
                    list = null;
                    listTag = null;
                    return;
                }

                const bullet = BULLET_RE.test(line);
                if (bullet || NUMBERED_RE.test(line)) {
                    const tag = bullet ? 'UL' : 'OL';
                    if (!list || listTag !== tag) {
                        list = document.createElement(tag);
                        listTag = tag;
                        body.appendChild(list);
                    }
                    const li = document.createElement('li');
                    renderInline(li, line.replace(bullet ? BULLET_RE : NUMBERED_RE, '').trim());
                    list.appendChild(li);
                    return;
                }

                list = null;
                listTag = null;

                const heading = line.match(/^#{1,4}\s+(.+)$/)
                    || line.match(/^\*\*(.+?)\*\*:?$/)
                    || line.match(/^([A-Za-z][^:*]{1,59}):$/);
                if (heading) {
                    const label = document.createElement('p');
                    label.className = 'an-answer-heading';
                    renderInline(label, heading[1].trim().replace(/:$/, ''));
                    body.appendChild(label);
                    return;
                }

                const para = document.createElement('p');
                renderInline(para, line);
                body.appendChild(para);
            });
        };

        /** Safely render untrusted AI text into structured DOM (no innerHTML). */
        const renderAnswer = (container, htmlSafeText) => {
            container.innerHTML = '';
            const wrap = document.createElement('div');
            wrap.className = 'an-answer-wrap';

            const header = document.createElement('div');
            header.className = 'an-answer-header';
            header.innerHTML = '<i class="bi bi-stars" aria-hidden="true"></i>Answer';
            wrap.appendChild(header);

            const body = document.createElement('div');
            body.className = 'an-answer-body';

            // Named record lines become cards; every other line (scope, counts,
            // headings, notes) still renders as normal structured text.
            renderAnswerWithCards(body, htmlSafeText);
            wrap.appendChild(body);
            container.appendChild(wrap);
        };

        /** Mix patient cards for named record lines with structured text for the rest. */
        const renderAnswerWithCards = (body, text) => {
            let buffer = [];
            let grid = null;
            const flushText = () => {
                if (!buffer.length) return;
                renderAnswerText(body, buffer.join('\n'));
                buffer = [];
            };
            const flushCards = () => {
                if (!grid) return;
                body.appendChild(grid);
                grid = null;
            };

            text.split(/\r?\n/).forEach(rawLine => {
                const line = rawLine.trim();
                const item = line && BULLET_RE.test(line)
                    ? parsePatientItem(line.replace(BULLET_RE, '').trim())
                    : null;
                if (item) {
                    flushText();
                    if (!grid) {
                        grid = document.createElement('div');
                        grid.className = 'an-patient-list';
                    }
                    grid.appendChild(patientCard(item));
                    return;
                }
                flushCards();
                buffer.push(rawLine);
            });

            flushCards();
            flushText();
        };

        /**
         * The scope note under an AI answer, kept as separate lines instead of
         * one run-on sentence: what the answer focused on, which barangay each
         * Area alias stands for, and how to read the counts.
         */
        const renderContext = (container, data) => {
            container.innerHTML = '';
            const meta = document.createElement('div');
            meta.className = 'an-chat-meta';

            const topic = (data.topic || '').trim();
            if (/^focus area:/iu.test(topic)) {
                const row = document.createElement('div');
                row.className = 'an-chat-meta-row';
                row.innerHTML = '<i class="bi bi-bullseye" aria-hidden="true"></i>';
                const label = document.createElement('strong');
                label.textContent = 'Focus area';
                const value = document.createElement('span');
                value.textContent = topic.replace(/^focus area:\s*/iu, '').replace(/\.$/, '');
                row.append(label, value);
                meta.appendChild(row);
            }

            const areas = Array.isArray(data.area_legend) ? data.area_legend : [];
            if (areas.length) {
                const legend = document.createElement('div');
                legend.className = 'an-chat-legend';
                areas.forEach(area => {
                    const chip = document.createElement('span');
                    chip.className = 'an-chat-legend-item';
                    const alias = document.createElement('strong');
                    alias.textContent = area.alias;
                    const name = document.createElement('span');
                    name.textContent = area.label;
                    chip.append(alias, name);
                    legend.appendChild(chip);
                });
                meta.appendChild(legend);
            }

            const note = document.createElement('p');
            note.className = 'an-chat-note';
            note.textContent = 'Counts shown are exact recorded numbers for the applied filters.';
            meta.appendChild(note);

            container.appendChild(meta);
        };

        const copyAnswer = (text) => {
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.getElementById('analytics-copy-btn');
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2" aria-hidden="true"></i> Copied';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('copied');
                }, 1500);
            });
        };

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (button.disabled || !question.value.trim()) return;
            button.disabled = true;
            button.textContent = 'Checking report…';
            result.hidden = false;
            result.setAttribute('aria-busy', 'true');
            notice.textContent = 'Preparing an answer for the applied filters…';
            answer.innerHTML = '';
            context.textContent = '';
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 30000);
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    },
                    body: JSON.stringify({ ...filters, question: question.value.trim() }),
                    signal: controller.signal,
                });
                if ([401, 403, 419].includes(response.status)) throw new Error('Your session expired or access changed. Refresh the page and sign in again.');
                if (response.status === 429) throw new Error('Please wait a minute before asking another question.');
                const data = await response.json();
                if (!response.ok) throw new Error(response.status === 422 ? Object.values(data.errors || {}).flat()[0] || 'Check your question and filters.' : 'The report could not be loaded. Please try again.');
                if (typeof data.answer !== 'string') throw new Error('The assistant returned an incomplete response. Please try again.');
                notice.textContent = data.error_code && typeof data.notice === 'string'
                    ? data.notice
                    : '';
                renderAnswer(answer, data.answer);
                const actions = document.createElement('div');
                actions.className = 'an-answer-actions';
                const copyBtn = document.createElement('button');
                copyBtn.id = 'analytics-copy-btn';
                copyBtn.className = 'an-action-btn';
                copyBtn.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i> Copy';
                copyBtn.addEventListener('click', () => copyAnswer(data.answer));
                actions.appendChild(copyBtn);
                if (data.combined) {
                    const verified = document.createElement('span');
                    verified.className = 'an-answer-verified';
                    verified.innerHTML = '<i class="bi bi-check2-circle" aria-hidden="true"></i> Includes verified local data';
                    actions.appendChild(verified);
                }
                const regenBtn = document.createElement('button');
                regenBtn.className = 'an-action-btn';
                regenBtn.innerHTML = '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Regenerate';
                regenBtn.addEventListener('click', () => {
                    question.value = question.value.trim();
                    form.dispatchEvent(new Event('submit'));
                });
                actions.appendChild(regenBtn);
                answer.appendChild(actions);
                if (data.combined && (data.source === 'groq' || data.source === 'openrouter')) {
                    renderContext(context, data);
                }
            } catch (error) {
                notice.textContent = 'Answer unavailable';
                answer.innerHTML = '';
                const p = document.createElement('p');
                p.textContent = error.name === 'AbortError'
                    ? 'The request took too long. Try again; the suggested next steps remain available above.'
                    : error.message || 'Unable to connect. Please try again.';
                answer.appendChild(p);
            } finally {
                clearTimeout(timer);
                button.disabled = false;
                button.textContent = 'Ask assistant';
                result.setAttribute('aria-busy', 'false');
            }
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
