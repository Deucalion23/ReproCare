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
            // Flexible match: Name - Barangay, EDD Date (Risk); To do: todo
            const m = text.match(/^(.+?)\s*[-–]\s*([^,]+?),\s*EDD\s*([^;]*?)\s*\(([^)]+)\)\s*;\s*To do:\s*(.+)$/i);
            if (!m) return null;
            return {
                name: m[1].trim(),
                barangay: m[2].trim(),
                edd: m[3].trim(),
                risk: m[4].trim(),
                todo: m[5].trim()
            };
        };
        };

        const renderPatientCards = (text) => {
            // Split by bullet and try to parse each patient item
            const chunks = text.split(/•\s*/).filter(s => s.trim().length);
            const items = chunks.map(parsePatientItem).filter(Boolean);
            if (!items.length) return null;

            const grid = document.createElement('div');
            grid.className = 'an-patient-list';

            items.forEach(item => {
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
                grid.appendChild(card);
            });

            return grid;
        };

        /** Safely render untrusted AI text into structured DOM (no innerHTML). */
        const renderAnswer = (container, htmlSafeText) => {
            container.innerHTML = '';
            const wrap = document.createElement('div');
            wrap.className = 'an-answer-wrap';

            const header = document.createElement('div');
            header.className = 'an-answer-header';
            header.innerHTML = '<i class="bi bi-robot" aria-hidden="true"></i>AI Answer';
            wrap.appendChild(header);

            const body = document.createElement('div');
            body.className = 'an-answer-body';

            // Try patient-card rendering first (local listing data with names)
            const cards = renderPatientCards(htmlSafeText);
            if (cards) {
                body.appendChild(cards);
                wrap.appendChild(body);
                container.appendChild(wrap);
                return;
            }

            // Fallback: paragraph / bullet list rendering
            const paras = htmlSafeText.split(/\n\n+/).filter(p => p.trim().length);
            let currentList = null;
            paras.forEach(para => {
                const lines = para.split(/\n+/).map(l => l.trim()).filter(l => l.length);
                const isList = lines.every(l => l.startsWith('- ') || l.startsWith('• ') || l.startsWith('– '));
                if (isList) {
                    if (!currentList) {
                        currentList = document.createElement('ul');
                        body.appendChild(currentList);
                    }
                    lines.forEach(l => {
                        const li = document.createElement('li');
                        li.textContent = l.replace(/^[–\-•]\s*/, '');
                        currentList.appendChild(li);
                    });
                } else {
                    currentList = null;
                    const p = document.createElement('p');
                    const parts = para.split(/\*\*([^*]+)\*\*/);
                    parts.forEach((part, i) => {
                        if (i % 2 === 1) {
                            const b = document.createElement('strong');
                            b.textContent = part;
                            p.appendChild(b);
                        } else if (part.length) {
                            p.append(document.createTextNode(part));
                        }
                    });
                    body.appendChild(p);
                }
            });
            wrap.appendChild(body);
            container.appendChild(wrap);
        };

        const sourceBadge = (source, model, cached) => {
            const label = { groq: 'Groq', openrouter: 'OpenRouter', ollama: 'Ollama', rules: 'Local rules' }[source] || 'Assistant';
            const span = document.createElement('span');
            span.className = `an-answer-source ${source}${cached ? ' cached' : ''}`;
            span.innerHTML = `<span class="dot"></span>${label}${model ? ' · ' + model : ''}${cached ? ' · cached' : ''}`;
            return span;
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
                notice.textContent = '';
                renderAnswer(answer, data.answer);
                answer.appendChild(sourceBadge(data.source, data.model, data.cached));
                const actions = document.createElement('div');
                actions.className = 'an-answer-actions';
                const copyBtn = document.createElement('button');
                copyBtn.id = 'analytics-copy-btn';
                copyBtn.className = 'an-action-btn';
                copyBtn.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i> Copy';
                copyBtn.addEventListener('click', () => copyAnswer(data.answer));
                actions.appendChild(copyBtn);
                const verified = document.createElement('span');
                verified.className = 'an-answer-verified';
                verified.innerHTML = '<i class="bi bi-check2-circle" aria-hidden="true"></i> Verified counts';
                actions.appendChild(verified);
                const regenBtn = document.createElement('button');
                regenBtn.className = 'an-action-btn';
                regenBtn.innerHTML = '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Regenerate';
                regenBtn.addEventListener('click', () => {
                    question.value = question.value.trim();
                    form.dispatchEvent(new Event('submit'));
                });
                actions.appendChild(regenBtn);
                answer.appendChild(actions);
                const ts = document.createElement('div');
                ts.className = 'an-answer-timestamp';
                answer.appendChild(ts);
                if (data.source === 'groq' || data.source === 'openrouter') {
                    const lines = [data.topic || 'Report summary'];
                    if (Array.isArray(data.area_legend) && data.area_legend.length) {
                        lines.push(data.area_legend.map(area => `${area.alias}: ${area.label}`).join('; '));
                    }
                    lines.push(`Counts shown are exact recorded numbers for the applied filters.`);
                    context.textContent = lines.join(' ');
                }
            } catch (error) {
                notice.textContent = 'Answer unavailable';
                answer.innerHTML = '';
                answer.appendChild(sourceBadge('rules', null, false));
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
