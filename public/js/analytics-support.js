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
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (button.disabled || !question.value.trim()) return;
            button.disabled = true;
            button.textContent = 'Checking report…';
            result.hidden = false;
            result.setAttribute('aria-busy', 'true');
            notice.textContent = 'Preparing an answer for the applied filters…';
            answer.textContent = '';
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
                // Model output is untrusted text, never HTML or executable Markdown.
                notice.textContent = data.notice || ({ groq: 'Online AI draft (Groq)', ollama: 'Local AI draft' }[data.source] || 'Free local rules');
                answer.textContent = data.answer;
                if (data.source === 'groq') {
                    const lines = [data.topic || 'Report summary'];
                    if (Array.isArray(data.area_legend) && data.area_legend.length) {
                        lines.push(data.area_legend.map(area => `${area.alias}: ${area.label}`).join('; '));
                    }
                    lines.push(`Month 1 starts in ${filters.from.slice(0, 7)}. Exact counts remain in the local charts.`);
                    context.textContent = lines.join(' ');
                }
            } catch (error) {
                notice.textContent = 'Answer unavailable';
                answer.textContent = error.name === 'AbortError'
                    ? 'The request took too long. Try again; the suggested next steps remain available above.'
                    : error.message || 'Unable to connect. Please try again.';
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
