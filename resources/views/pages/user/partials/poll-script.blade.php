window.renderPollResults=(form,options)=>{
    if(form.dataset.answered!=='true')return;
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
    scope.querySelectorAll('[data-instant-poll] input[type="radio"]').forEach(input => {
        if (input.dataset.pollBound === '1') return;
        input.dataset.pollBound = '1';
        input.addEventListener('change', async () => {
            const form = input.form;
            if (form.dataset.answered === 'true' || form.classList.contains('poll-submitting')) return;
            form.classList.add('poll-submitting');
            const body = new FormData(form);
            form.querySelectorAll('input[type="radio"]').forEach(option => option.disabled = true);
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 12000);
            try {
                const response = await fetch(form.action, {method:'POST', body, signal:controller.signal, headers:{'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json'}});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Unable to submit your vote.');
                form.dataset.answered = 'true';
                form.querySelectorAll('.poll-choice').forEach(choice => {
                    const selected = Number(choice.dataset.optionId) === Number(data.selected_option_id);
                    choice.classList.add('locked'); choice.classList.toggle('selected', selected);
                    choice.querySelector('input').checked = selected;
                    choice.querySelector('[data-answer-state]').textContent = selected ? 'Your Answer' : '';
                });
                if (data.is_quiz) {
                    const correct = form.querySelector(`[data-option-id="${data.correct_option_id}"]`);
                    correct?.classList.add('quiz-correct');
                    if (correct) correct.querySelector('[data-answer-state]').textContent = data.is_correct ? 'Your Answer · Correct' : 'Correct Answer';
                    if (!data.is_correct) form.querySelector('.selected')?.classList.add('quiz-incorrect');
                }
                window.renderPollResults(form, data.options);
                if (form.previousElementSibling) form.previousElementSibling.textContent = 'Live results · Your answer is highlighted.';
                showDashboardToast(data.is_quiz ? (data.is_correct ? 'Correct Answer' : 'Incorrect. Correct answer highlighted.') : data.message, data.is_quiz && !data.is_correct ? 'warning' : 'success');
            } catch (error) {
                if (form.dataset.answered !== 'true') form.querySelectorAll('input[type="radio"]').forEach(option => { option.disabled = false; option.checked = false; });
                showDashboardToast(error.name === 'AbortError' ? 'Connection is slow. Please try again. Your saved answer will be restored.' : (error.message || 'Unable to submit your vote.'), 'warning');
            } finally {
                clearTimeout(timeout);
                form.classList.remove('poll-submitting');
            }
        });
    });
};
window.bindInstantPoll();
