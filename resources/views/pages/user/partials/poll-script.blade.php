window.renderPollResults=(form,options)=>{
    if(form.dataset.answered!=='true'||form.dataset.isQuiz==='true')return;
    const votes=options.reduce((sum,option)=>sum+Number(option.count),0);
    options.forEach(option=>{
        const choice=form.querySelector(`[data-option-id="${option.id}"]`);
        if(!choice)return;
        const percent=Math.round(Number(option.count)/Math.max(1,votes)*100);
        choice.querySelector('b[data-poll-result]').textContent=`${percent}%`;
        choice.querySelector('.poll-track i').style.width=`${percent}%`;
    });
    form.querySelector('[data-poll-total]').textContent=`${votes} total votes`;
    form.querySelectorAll('[data-poll-result]').forEach(node=>node.hidden=false);
};
window.bindInstantPoll = (scope = document) => {
    const submitPoll = async form => {
        if (form.dataset.answered === 'true' || form.classList.contains('poll-submitting')) return;
        if (form.dataset.multiple === 'true' && !form.querySelector('input[type="checkbox"]:checked')) {
            showDashboardToast('Please select at least one answer.', 'warning');
            return;
        }
        form.classList.add('poll-submitting');
        const body = new FormData(form);
        const inputs = form.querySelectorAll('input[name="option_id"], input[name="option_ids[]"]');
        inputs.forEach(option => option.disabled = true);
        form.querySelector('[data-poll-submit]')?.setAttribute('disabled', 'disabled');
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 12000);
        try {
            const response = await fetch(form.action, {method:'POST', body, signal:controller.signal, headers:{'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json'}});
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to submit your vote.');
            const selectedIds = (data.selected_option_ids || [data.selected_option_id]).map(Number);
            form.dataset.answered = 'true';
            form.querySelectorAll('.poll-choice').forEach(choice => {
                const selected = selectedIds.includes(Number(choice.dataset.optionId));
                choice.classList.add('locked');
                choice.classList.toggle('selected', selected);
                choice.querySelector('input').checked = selected;
            });
            form.querySelector('[data-poll-submit]')?.remove();
            if (data.is_quiz && data.show_correct_answer) {
                (data.correct_option_ids || [data.correct_option_id]).filter(Boolean).forEach(id => form.querySelector(`[data-option-id="${id}"]`)?.classList.add('quiz-correct'));
                if (!data.is_correct) form.querySelectorAll('.selected').forEach(choice => choice.classList.add('quiz-incorrect'));
            }
            window.renderPollResults(form, data.options || []);
            if (form.previousElementSibling) {
                form.previousElementSibling.textContent = data.is_quiz
                    ? (data.show_correct_answer ? 'Answer submitted · The correct answer is highlighted.' : (data.answer_reveal === 'after_webinar' ? 'Answer submitted · Correct answer will appear after the webinar finishes.' : 'Answer submitted · Correct answer is hidden.'))
                    : 'Live results · Your selected option is highlighted.';
            }
            showDashboardToast(data.message, 'success');
        } catch (error) {
            if (form.dataset.answered !== 'true') inputs.forEach(option => { option.disabled = false; });
            form.querySelector('[data-poll-submit]')?.removeAttribute('disabled');
            showDashboardToast(error.name === 'AbortError' ? 'Connection is slow. Please try again. Your saved answer will be restored.' : (error.message || 'Unable to submit your vote.'), 'warning');
        } finally {
            clearTimeout(timeout);
            form.classList.remove('poll-submitting');
        }
    };

    scope.querySelectorAll('[data-instant-poll] input[type="radio"]').forEach(input => {
        if (input.dataset.pollBound === '1') return;
        input.dataset.pollBound = '1';
        input.addEventListener('change', () => submitPoll(input.form));
    });
    scope.querySelectorAll('[data-instant-poll][data-multiple="true"]').forEach(form => {
        if (form.dataset.pollSubmitBound === '1') return;
        form.dataset.pollSubmitBound = '1';
        form.addEventListener('submit', event => { event.preventDefault(); submitPoll(form); });
    });
};
window.bindInstantPoll();
