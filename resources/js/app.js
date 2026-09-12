import './bootstrap';
import './live-chat';
import './realtime-experience';
import '../css/event-experience.css';
import $ from 'jquery';
import 'jquery-validation';
import Chart from 'chart.js/auto';

window.$ = window.jQuery = $;
window.Chart = Chart;

window.showToast = function(message, tone = 'success') {
    if (!message) return;
    let toastContainer = document.getElementById('simpleToastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'simpleToastContainer';
        toastContainer.className = 'simple-toast-container';
        document.body.appendChild(toastContainer);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `simple-toast simple-toast-${tone}`;

    let iconClass = 'bi-check-circle-fill';
    if (tone === 'warning') iconClass = 'bi-exclamation-circle-fill';
    else if (tone === 'danger' || tone === 'error') iconClass = 'bi-x-circle-fill';
    else if (tone === 'info') iconClass = 'bi-info-circle-fill';

    toastEl.innerHTML = `
        <i class="bi ${iconClass}"></i>
        <span class="simple-toast-msg">${message}</span>
        <button type="button" class="simple-toast-close" aria-label="Close">&times;</button>
    `;

    toastContainer.appendChild(toastEl);

    const dismiss = () => {
        toastEl.classList.add('simple-toast-leaving');
        setTimeout(() => toastEl.remove(), 250);
    };
    toastEl.querySelector('.simple-toast-close')?.addEventListener('click', dismiss);
    setTimeout(dismiss, 3200);

    const fallbackToast = document.querySelector('#appToast');
    if (fallbackToast) {
        const textSpan = fallbackToast.querySelector('.toast-body span') || fallbackToast.querySelector('span');
        if (textSpan) textSpan.textContent = message;
    }
};

document.addEventListener('click', event => {
    const sidebarGroupToggle = event.target.closest('[data-sidebar-group-toggle]');
    if (sidebarGroupToggle) {
        const menu = document.querySelector(`#${sidebarGroupToggle.dataset.sidebarGroupToggle}`);
        sidebarGroupToggle.classList.toggle('open');
        menu?.classList.toggle('open');
    }
    if (event.target.closest('#toggleAllPermissions')) {
        const boxes = [...document.querySelectorAll('.event-permission-matrix input[type="checkbox"]')];
        const shouldCheck = boxes.some(box => !box.checked);
        boxes.forEach(box => { box.checked = shouldCheck; });
        event.target.closest('#toggleAllPermissions').textContent = shouldCheck ? 'Clear all' : 'Select all';
    }
    const copyUrlButton = event.target.closest('[data-copy-url]');
    if (copyUrlButton) {
        const fallbackCopy=()=>{const area=document.createElement('textarea');area.value=copyUrlButton.dataset.copyUrl;area.style.position='fixed';area.style.opacity='0';document.body.appendChild(area);area.select();document.execCommand('copy');area.remove();};
        const task=navigator.clipboard?.writeText?navigator.clipboard.writeText(copyUrlButton.dataset.copyUrl):Promise.resolve(fallbackCopy());task.then(() => {
            const original = copyUrlButton.innerHTML;
            copyUrlButton.innerHTML = '<i class="bi bi-check2"></i> Copied';
            setTimeout(() => { copyUrlButton.innerHTML = original; }, 1600);
        }).catch(fallbackCopy);
    }
    if (event.target.closest('#addPollAnswer')) {
        const template = document.querySelector('#pollAnswerTemplate');
        if (template) document.querySelector('#pollAnswers')?.appendChild(template.content.cloneNode(true));
    }
    const removeAnswer = event.target.closest('.remove-poll-answer');
    if (removeAnswer && document.querySelectorAll('#pollAnswers .answer-row').length > 2) removeAnswer.closest('.answer-row')?.remove();
    if (event.target.closest('#addWebinarField')) {
        const container = document.querySelector('#webinarFields');
        const fragment = document.querySelector('#webinarFieldTemplate').content.cloneNode(true);
        const index = container.querySelectorAll('.field-editor').length;
        fragment.querySelectorAll('[data-name]').forEach(input => input.name = `fields[${index}][${input.dataset.name}]`);
        container.appendChild(fragment);
    }
    const remove = event.target.closest('.remove-webinar-field');
    if (remove) remove.closest('.field-editor').remove();
    if (event.target.closest('#addPoll')) {
        const container=document.querySelector('#pollBuilder'); const fragment=document.querySelector('#pollTemplate').content.cloneNode(true); const index=container.querySelectorAll('.poll-editor').length;
        fragment.querySelectorAll('[data-poll-name]').forEach(input=>input.name=`polls[${index}][${input.dataset.pollName}]`); container.appendChild(fragment);
    }
    const removePoll=event.target.closest('.remove-poll'); if(removePoll) removePoll.closest('.poll-editor').remove();
});

document.addEventListener('submit', event => {
    if (event.target.matches('form[action="/admin/registration-fields"]')) {
        const input=document.createElement('input'); input.type='hidden'; input.name='icon';
        input.value=document.querySelector('#signupFieldIcon')?.value || 'input-cursor-text';
        event.target.appendChild(input);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const authModals = { login: '#micrositeLoginModal', register: '#micrositeRegisterModal', forgot: '#frontendForgotModal' };
    const openAuthModal = mode => {
        const element = document.querySelector(authModals[mode] || '#missingAuthModal');
        if (element && window.bootstrap) bootstrap.Modal.getOrCreateInstance(element).show();
    };
    openAuthModal(document.querySelector('[data-auth-open]')?.dataset.authOpen);
    document.querySelectorAll('.frontend-auth-modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', () => modal.querySelector('input:not([type="hidden"])')?.focus({ preventScroll: true }));
        modal.addEventListener('hidden.bs.modal', () => {
            const url = new URL(window.location.href);
            if (url.searchParams.has('auth')) {
                url.searchParams.delete('auth');
                history.replaceState(null, '', url.pathname + url.search + url.hash);
            }
        });
    });
    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = new URL(link.href, window.location.href);
        const mode = { '/login': 'login', '/register': 'register', '/forgot-password': 'forgot' }[url.pathname];
        if (url.origin === location.origin && mode && document.querySelector(authModals[mode])) {
            event.preventDefault();
            openAuthModal(mode);
        }
    });
    // Keep location selectors usable after a validation error and while requests are in flight.
    const authCountry = document.querySelector('#micrositeCountry');
    const authState = document.querySelector('#micrositeState');
    const authCity = document.querySelector('#micrositeCity');
    const loadAuthLocations = async (select, endpoint, label) => {
        if (!select) return;
        select.replaceChildren(new Option(endpoint ? 'Loading...' : label, ''));
        select.disabled = Boolean(endpoint);
        if (!endpoint) return;
        try {
            const response = await fetch(endpoint);
            if (!response.ok) throw new Error('Location request failed');
            const items = await response.json();
            select.replaceChildren(new Option(label, ''), ...items.map(item => new Option(item.name, item.id)));
        } catch {
            select.replaceChildren(new Option('Unable to load. Please select the parent location again.', ''));
        } finally { select.disabled = false; }
    };
    authCountry?.addEventListener('change', () => {
        loadAuthLocations(authCity, null, 'Select city');
        loadAuthLocations(authState, authCountry.value ? `/locations/states?country_id=${encodeURIComponent(authCountry.value)}` : null, 'Select state');
    });
    authState?.addEventListener('change', () => loadAuthLocations(authCity, authState.value ? `/locations/cities?state_id=${encodeURIComponent(authState.value)}` : null, 'Select city'));
    $('.sidebar-help').remove();
    $('.premium-table').each(function () {
        const table = $(this);
        if (!table.find('thead th').filter(function () { return $(this).text().trim().toLowerCase() === 'index'; }).length) {
            table.find('thead tr').prepend('<th>Index</th>');
            table.find('tbody tr').each(function (index) {
                const row = $(this);
                if (row.children('td').length > 1) row.prepend(`<td data-auto-index>${index + 1}</td>`);
                else row.children('td').attr('colspan', Number(row.children('td').attr('colspan') || 1) + 1);
            });
        }
        const search = table.closest('.panel-card').find('[data-listing-search]');
        search.on('input', function () {
            const term = String($(this).val()).toLowerCase();
            table.find('tbody tr').each(function () { $(this).toggle($(this).text().toLowerCase().includes(term)); });
        });
        table.find('thead th').each(function (column) {
            const header=$(this);header.css('cursor','pointer').attr('title','Click to sort');
            header.on('click',function(){const rows=table.find('tbody tr').get();const ascending=header.data('sort-direction')!=='asc';table.find('thead th').removeData('sort-direction');header.data('sort-direction',ascending?'asc':'desc');rows.sort((a,b)=>$(a).children().eq(column).text().trim().localeCompare($(b).children().eq(column).text().trim(),undefined,{numeric:true})*(ascending?1:-1));$.each(rows,(_,row)=>table.children('tbody').append(row));});
        });
    });
    $('form[action*="general-settings"], form[action*="/admin/speakers"]').find('input[name="is_active"]').each(function () {
        $('<input>', { type: 'hidden', name: 'is_active', value: this.checked ? '1' : '0' }).insertAfter(this);
        $(this).closest('label').remove();
    });
    const dynamicRows = document.querySelector('#dynamicFieldRows');
    document.querySelector('#dynamicFieldSearch')?.addEventListener('input', event => {
        dynamicRows?.querySelectorAll('[data-dynamic-field-row]').forEach(row => row.hidden = !row.textContent.toLowerCase().includes(event.target.value.toLowerCase()));
    });
    let draggedFieldRow = null;
    dynamicRows?.querySelectorAll('[data-dynamic-field-row]').forEach(row => {
        row.addEventListener('dragstart', () => { draggedFieldRow = row; row.classList.add('dragging'); });
        row.addEventListener('dragend', () => { row.classList.remove('dragging'); draggedFieldRow = null; [...dynamicRows.querySelectorAll('[data-dynamic-field-row]')].forEach((item,index)=>{ item.querySelector('[data-field-index]').textContent=index+1; item.querySelector('[data-display-order]').value=index; }); });
        row.addEventListener('dragover', event => { event.preventDefault(); if(draggedFieldRow && draggedFieldRow!==row){ const bounds=row.getBoundingClientRect(); dynamicRows.insertBefore(draggedFieldRow,event.clientY<bounds.top+bounds.height/2?row:row.nextSibling); } });
    });
    const updateWebinarDropdown = () => {
        document.querySelectorAll('.webinar-multi-select').forEach(dropdown => {
            const selected = [...dropdown.querySelectorAll('input:checked')].map(input => input.closest('label')?.innerText.trim()).filter(Boolean);
            const label = dropdown.querySelector('[data-webinar-dropdown-label]');
            if (label) label.textContent = selected.length === 0 ? 'Select webinar(s)' : (selected.length === 1 ? selected[0] : `${selected.length} webinars selected`);
        });
    };
    document.querySelectorAll('.webinar-multi-select input').forEach(input => input.addEventListener('change', updateWebinarDropdown));
    updateWebinarDropdown();
    document.querySelector('#registrationWebinarSelect')?.addEventListener('change', event => {
        const url = new URL(window.location.href);
        if (event.target.value) url.searchParams.set('webinar_id', event.target.value); else url.searchParams.delete('webinar_id');
        window.location.href = url.toString();
    });
    document.querySelectorAll('[data-webinar-countdown]').forEach(countdown => {
        const target = new Date(countdown.dataset.webinarCountdown).getTime();
        const renderCountdown = () => {
            const distance = target - Date.now();
            if (distance <= 0) {
                countdown.querySelector('[data-countdown-status]').textContent = 'The webinar is starting now';
                ['days','hours','minutes','seconds'].forEach(unit => countdown.querySelector(`[data-countdown-${unit}]`).textContent = '00');
                return false;
            }
            const values = {
                days: Math.floor(distance / 86400000),
                hours: Math.floor((distance % 86400000) / 3600000),
                minutes: Math.floor((distance % 3600000) / 60000),
                seconds: Math.floor((distance % 60000) / 1000),
            };
            Object.entries(values).forEach(([unit, value]) => countdown.querySelector(`[data-countdown-${unit}]`).textContent = String(value).padStart(2, '0'));
            return true;
        };
        renderCountdown();
        const timer = setInterval(() => { if (!renderCountdown()) clearInterval(timer); }, 1000);
    });

    $.validator.setDefaults({
        errorElement: 'span',
        errorClass: 'field-error',
        ignore: ':hidden:not(select):not([type="file"])',
        errorPlacement(error, element) {
            const field = element.closest('label, .form-field, .answer-row');
            const toggle = element.closest('.setting-toggle');
            if (toggle.length) error.insertAfter(toggle);
            else if (field.length) error.appendTo(field);
            else error.insertAfter(element);
        },
        highlight(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        messages: {
            required: 'This field is required.',
            email: 'Please enter a valid email address.',
            number: 'Please enter a valid number.',
            minlength: $.validator.format('Please enter at least {0} characters.'),
            maxlength: $.validator.format('Please enter no more than {0} characters.'),
            min: $.validator.format('Value must be at least {0}.'),
            max: $.validator.format('Value must not exceed {0}.'),
        },
    });
    $('form').not('[data-no-validation]').each(function () {
        const form = $(this);
        form.attr('novalidate', 'novalidate');
        form.validate({
            focusInvalid: true,
            onkeyup: function (element) { $(element).valid(); },
            onchange: function (element) { $(element).valid(); },
        });
    });

    $('.portal-content table.premium-table').each(function (tableIndex) {
        const table=$(this); if(table.data('listingReady')) return; table.data('listingReady',true);
        if(document.body.classList.contains('sub-admin-portal'))table.find('thead th').each(function(index){const label=$(this).text().trim().toLowerCase();if(['webinar','event','event / webinar','webinar / client'].includes(label)){table.find('tr').each(function(){$(this).children().eq(index).hide()})}});
        const rows=table.find('tbody > tr').filter(function(){return !$(this).find('[colspan]').length;});
        if(!rows.length)return;
        const hasServerFilter = $('.filter-bar, .module-filter-bar').length > 0 || table.closest('.portal-content, .panel-card, .table-responsive').find('.filter-bar, .module-filter-bar, .pagination, .pagination-bar, .admin-pagination-bar').length > 0 || table.closest('.panel-card').parent().find('.pagination, .pagination-bar, .admin-pagination-bar').length > 0;
        let page=1, size=10, query='';
        let render = () => {};
        if (!hasServerFilter) {
            const shell=$('<div class="jquery-listing-tools"><label><i class="bi bi-search"></i><input type="search" placeholder="Search this listing…"></label><select aria-label="Rows per page"><option>10</option><option>25</option><option>50</option><option value="9999">All</option></select></div>');
            const bottomBar=$('<div class="jquery-listing-bottom mt-3 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 w-100"><span class="jquery-listing-info text-muted" style="font-size: 0.84rem; font-weight: 600;"></span><div class="jquery-listing-nav ms-auto d-flex align-items-center gap-2"></div></div>');
            table.closest('.table-responsive').before(shell);
            table.closest('.table-responsive').after(bottomBar);
            render=()=>{
                const matches=rows.filter(function(){return $(this).text().toLowerCase().includes(query)});
                const pages=Math.max(1,Math.ceil(matches.length/size));
                page=Math.min(page,pages);
                rows.hide();
                matches.slice((page-1)*size,page*size).show();
                bottomBar.find('.jquery-listing-info').text(`Showing ${matches.length ? (page-1)*size + 1 : 0} to ${Math.min(page*size, matches.length)} of ${matches.length} result${matches.length===1?'':'s'}`);
                const nav=bottomBar.find('.jquery-listing-nav').empty();
                if(pages > 1) {
                    $('<button type="button" class="btn btn-sm btn-light border" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>').prop('disabled',page===1).on('click',()=>{page--;render()}).appendTo(nav);
                    $('<span class="badge bg-light text-dark border px-2 py-1"></span>').text(`${page} / ${pages}`).appendTo(nav);
                    $('<button type="button" class="btn btn-sm btn-light border" aria-label="Next"><i class="bi bi-chevron-right"></i></button>').prop('disabled',page===pages).on('click',()=>{page++;render()}).appendTo(nav);
                }
            };
            shell.find('input').on('input',function(){query=this.value.toLowerCase().trim();page=1;render()});
            shell.find('select').on('change',function(){size=Number(this.value);page=1;render()});
            render();
        }
        table.find('thead th').each(function(index){const th=$(this);if(!th.text().trim())return;th.addClass('is-sortable').attr('tabindex','0').on('click keydown',function(event){if(event.type==='keydown'&&event.key!=='Enter')return;const ascending=th.attr('data-sort')!=='asc';table.find('th').removeAttr('data-sort');th.attr('data-sort',ascending?'asc':'desc');rows.sort((a,b)=>$(a).children().eq(index).text().trim().localeCompare($(b).children().eq(index).text().trim(),undefined,{numeric:true})*(ascending?1:-1)).appendTo(table.find('tbody'));if(!hasServerFilter){page=1;render()}})});
    });

    const registrationType = document.querySelector('#registrationType');
    const priceField = document.querySelector('#priceField');
    const webinarPrice = document.querySelector('#webinarPrice');
    const syncPriceField = () => {
        const isPaid = registrationType?.value === 'paid';
        if (priceField) priceField.hidden = !isPaid;
        if (webinarPrice) {
            webinarPrice.disabled = !isPaid;
            webinarPrice.required = isPaid;
            if (!isPaid) webinarPrice.value = '';
        }
    };
    registrationType?.addEventListener('change', syncPriceField);
    syncPriceField();

    const certificateCanvas = document.querySelector('#certificateCanvas');
    const elementSelect = document.querySelector('#certificateElementSelect');
    const coordinateX = document.querySelector('#certificateX');
    const coordinateY = document.querySelector('#certificateY');
    const elementWidth = document.querySelector('#certificateWidth');
    const elementScale = document.querySelector('#certificateScale');
    const certificateItems = [...document.querySelectorAll('[data-certificate-element]')];
    const selectCertificateElement = key => {
        elementSelect.value = key;
        certificateItems.forEach(item => item.classList.toggle('selected', item.dataset.certificateElement === key));
        coordinateX.value = document.querySelector(`[data-position-x="${key}"]`)?.value || 50;
        coordinateY.value = document.querySelector(`[data-position-y="${key}"]`)?.value || 50;
        elementWidth.value = document.querySelector(`[data-position-width="${key}"]`)?.value || 30;
        elementScale.value = document.querySelector(`[data-position-scale="${key}"]`)?.value || 100;
    };
    const setCertificateSize = (key, width, scale) => {
        width = Math.max(5, Math.min(90, Number(width)));
        scale = Math.max(50, Math.min(200, Number(scale)));
        const item = document.querySelector(`[data-certificate-element="${key}"]`);
        const widthInput = document.querySelector(`[data-position-width="${key}"]`);
        const scaleInput = document.querySelector(`[data-position-scale="${key}"]`);
        if (item) { item.style.width = `${width}%`; item.style.setProperty('--element-scale', scale / 100); }
        if (widthInput) widthInput.value = width;
        if (scaleInput) scaleInput.value = scale;
    };
    const setCertificatePosition = (key, x, y) => {
        x = Math.max(0, Math.min(100, Number(x)));
        y = Math.max(0, Math.min(100, Number(y)));
        const item = document.querySelector(`[data-certificate-element="${key}"]`);
        const xInput = document.querySelector(`[data-position-x="${key}"]`);
        const yInput = document.querySelector(`[data-position-y="${key}"]`);
        if (item) { item.style.left = `${x}%`; item.style.top = `${y}%`; }
        if (xInput) xInput.value = x.toFixed(1);
        if (yInput) yInput.value = y.toFixed(1);
        if (elementSelect?.value === key) { coordinateX.value = x.toFixed(1); coordinateY.value = y.toFixed(1); }
    };
    elementSelect?.addEventListener('change', () => selectCertificateElement(elementSelect.value));
    coordinateX?.addEventListener('input', () => setCertificatePosition(elementSelect.value, coordinateX.value, coordinateY.value));
    coordinateY?.addEventListener('input', () => setCertificatePosition(elementSelect.value, coordinateX.value, coordinateY.value));
    elementWidth?.addEventListener('input', () => setCertificateSize(elementSelect.value, elementWidth.value, elementScale.value));
    elementScale?.addEventListener('input', () => setCertificateSize(elementSelect.value, elementWidth.value, elementScale.value));
    certificateItems.forEach(item => {
        const resizeHandle = document.createElement('span');
        resizeHandle.className = 'certificate-resize-handle';
        resizeHandle.title = 'Drag to resize';
        resizeHandle.innerHTML = '<i class="bi bi-arrows-angle-expand"></i>';
        item.appendChild(resizeHandle);
        resizeHandle.addEventListener('pointerdown', event => {
            event.preventDefault();
            event.stopPropagation();
            const key = item.dataset.certificateElement;
            selectCertificateElement(key);
            resizeHandle.setPointerCapture(event.pointerId);
            const startX = event.clientX;
            const startY = event.clientY;
            const startWidth = Number(document.querySelector(`[data-position-width="${key}"]`)?.value || 30);
            const startScale = Number(document.querySelector(`[data-position-scale="${key}"]`)?.value || 100);
            const resize = moveEvent => {
                const widthChange = ((moveEvent.clientX - startX) / certificateCanvas.getBoundingClientRect().width) * 100;
                const scaleChange = ((moveEvent.clientX - startX) + (moveEvent.clientY - startY)) / 3;
                const width = Math.max(5, Math.min(90, startWidth + widthChange));
                const scale = Math.max(50, Math.min(200, startScale + scaleChange));
                elementWidth.value = width.toFixed(1);
                elementScale.value = scale.toFixed(0);
                setCertificateSize(key, width, scale);
            };
            resizeHandle.addEventListener('pointermove', resize);
            resizeHandle.addEventListener('pointerup', () => resizeHandle.removeEventListener('pointermove', resize), { once: true });
        });
        item.addEventListener('pointerdown', event => {
            if (event.target.closest('.certificate-resize-handle')) return;
            event.preventDefault();
            const key = item.dataset.certificateElement;
            selectCertificateElement(key);
            item.setPointerCapture(event.pointerId);
            const move = moveEvent => {
                const bounds = certificateCanvas.getBoundingClientRect();
                setCertificatePosition(key, ((moveEvent.clientX - bounds.left) / bounds.width) * 100, ((moveEvent.clientY - bounds.top) / bounds.height) * 100);
            };
            item.addEventListener('pointermove', move);
            item.addEventListener('pointerup', () => item.removeEventListener('pointermove', move), { once: true });
        });
    });
    if (elementSelect) selectCertificateElement(elementSelect.value);
    document.querySelector('#certificateImageInput')?.addEventListener('change', event => {
        const file = event.target.files?.[0];
        if (file && certificateCanvas) certificateCanvas.style.backgroundImage = `url('${URL.createObjectURL(file)}')`;
    });
    document.querySelector('#signatureImageInput')?.addEventListener('change', event => {
        const file = event.target.files?.[0];
        const preview = document.querySelector('#signaturePreview');
        const signatureElement = document.querySelector('[data-certificate-element="signature"]');
        if (file && preview && signatureElement) {
            preview.src = URL.createObjectURL(file);
            signatureElement.classList.remove('d-none');
            selectCertificateElement('signature');
        }
    });

    const sidebar = document.querySelector('#sidebar');
    document.querySelector('.sidebar-nav > a[href="/admin/profile"]')?.remove();
    const menuToggle = document.querySelector('#menuToggle');
    const sidebarBackdrop = document.querySelector('#sidebarBackdrop');
    const mobileNavigation = window.matchMedia('(max-width: 991px)');
    const setNavigationOpen = (open, restoreFocus = false) => {
        if (!sidebar) return;
        open = open && mobileNavigation.matches;
        sidebar.classList.toggle('open', open);
        sidebar.inert = mobileNavigation.matches && !open;
        document.body.classList.toggle('navigation-open', open);
        menuToggle?.setAttribute('aria-expanded', String(open));
        if (sidebarBackdrop) sidebarBackdrop.hidden = !open;
        const main = document.querySelector('.portal-main');
        if (main) main.inert = open;
        if (open) document.querySelector('#sidebarClose')?.focus();
        else if (restoreFocus) menuToggle?.focus();
    };
    menuToggle?.addEventListener('click', () => setNavigationOpen(!sidebar?.classList.contains('open')));
    document.querySelector('#sidebarClose')?.addEventListener('click', () => setNavigationOpen(false, true));
    sidebarBackdrop?.addEventListener('click', () => setNavigationOpen(false, true));
    mobileNavigation.addEventListener('change', () => setNavigationOpen(false));
    document.addEventListener('keydown', event => {
        if (!sidebar?.classList.contains('open')) return;
        if (event.key === 'Escape') setNavigationOpen(false, true);
        if (event.key === 'Tab') {
            const controls = [...sidebar.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')]
                .filter(control => !control.disabled && control.getClientRects().length);
            const first = controls[0], last = controls.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        }
    });
    sidebar?.querySelectorAll('a[href]').forEach(link => link.addEventListener('click', () => setNavigationOpen(false)));
    setNavigationOpen(false);

    // Keep wide data tables scrollable without widening the page.
    document.querySelectorAll('.portal-content table').forEach(table => {
        if (!table.closest('.table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.before(wrapper);
            wrapper.append(table);
        }
    });
    document.querySelectorAll('.table-responsive').forEach(wrapper => {
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', 'Data table, scroll horizontally to see all columns');
    });

    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        const current = window.location.pathname;
        if (link.getAttribute('href') === current || (current.startsWith(link.getAttribute('href')) && link.getAttribute('href').split('/').filter(Boolean).length > 1)) link.classList.add('active');
    });

    document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        input.type = input.type === 'password' ? 'text' : 'password';
        button.querySelector('i').className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }));

    document.querySelectorAll('.bookmark-btn').forEach(button => button.addEventListener('click', event => {
        event.preventDefault(); button.classList.toggle('active');
        button.querySelector('i').className = button.classList.contains('active') ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
    }));

    document.querySelectorAll('.event-option').forEach(option => option.addEventListener('click', () => {
        const label = document.querySelector('[data-event-label]'); if (label) label.textContent = option.textContent;
        document.querySelectorAll('[data-stat]').forEach((stat, index) => { if (option.textContent !== 'All Events') stat.textContent = ['1','2.8K','1.2K','86%','4.9','984'][index % 6]; });
    }));

    const flashEl = document.querySelector('[data-app-flash]');
    if (flashEl && (flashEl.dataset.appFlash || flashEl.textContent.trim())) {
        const msg = flashEl.dataset.appFlash || flashEl.textContent.trim();
        const tone = flashEl.dataset.appFlashTone || 'success';
        window.showToast(msg, tone);
    }

    document.querySelectorAll('[data-demo-toast]').forEach(button => button.addEventListener('click', event => {
        if (button.tagName === 'BUTTON' && button.closest('form')) event.preventDefault();
        window.showToast('Action completed successfully!', 'success');
    }));

    const search = document.querySelector('#webinarSearch');
    search?.addEventListener('input', () => document.querySelectorAll('.webinar-item').forEach(item => item.hidden = !item.textContent.toLowerCase().includes(search.value.toLowerCase())));
    const tableSearch = document.querySelector('[data-table-search]');
    tableSearch?.addEventListener('input', () => document.querySelectorAll('#resourceTable tbody tr').forEach(row => row.hidden = !row.textContent.toLowerCase().includes(tableSearch.value.toLowerCase())));
    const chatPersonSearch = document.querySelector('#chatPersonSearch');
    chatPersonSearch?.addEventListener('input', () => document.querySelectorAll('#chatPeopleList [data-search]').forEach(person => person.hidden = !person.dataset.search.includes(chatPersonSearch.value.toLowerCase())));
    const chatMessageStream = document.querySelector('#chatMessageStream');
    if (chatMessageStream) chatMessageStream.scrollTop = chatMessageStream.scrollHeight;

    document.querySelectorAll('.interaction-tabs button').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.interaction-tabs button,.interaction-content').forEach(el => el.classList.remove('active'));
        button.classList.add('active'); document.querySelector(`#${button.dataset.panel}`)?.classList.add('active');
    }));
    document.querySelector('.chat-form')?.addEventListener('submit', event => event.preventDefault());
    document.querySelector('.send-chat')?.addEventListener('click', event => {
        event.preventDefault(); const input = document.querySelector('.chat-form textarea'); if (!input?.value.trim()) return;
        const message = document.createElement('div'); message.innerHTML = `<span class="mini-avatar blue">JA</span><p><strong>John Anderson <small>Just now</small></strong>${input.value.replace(/[<>]/g, '')}</p>`;
        document.querySelector('.message-list')?.append(message); input.value = '';
    });

    let wizardStep = 0; const pages = [...document.querySelectorAll('.wizard-page')]; const steps = [...document.querySelectorAll('.wizard-step')];
    const showStep = step => { wizardStep = Math.max(0, Math.min(step, pages.length - 1)); pages.forEach((p,i)=>p.classList.toggle('active',i===wizardStep)); steps.forEach((s,i)=>s.classList.toggle('active',i===wizardStep)); const next=document.querySelector('#wizardNext'); if(next) next.innerHTML=wizardStep===pages.length-1?'Publish webinar <i class="bi bi-send"></i>':'Continue <i class="bi bi-arrow-right"></i>'; };
    steps.forEach(step => step.addEventListener('click', () => showStep(Number(step.dataset.step))));
    document.querySelector('#wizardNext')?.addEventListener('click', () => wizardStep === pages.length - 1 ? window.showToast('Webinar ready for publish!', 'success') : showStep(wizardStep + 1));
    document.querySelector('#wizardBack')?.addEventListener('click', () => showStep(wizardStep - 1));

    const fillSelect = (select, items, placeholder) => {
        if (!select) return;
        select.innerHTML = `<option value="">${placeholder}</option>` + items.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
    };
    const countrySelect = document.querySelector('#countrySelect');
    const stateSelect = document.querySelector('#stateSelect');
    const citySelect = document.querySelector('#citySelect');
    countrySelect?.addEventListener('change', async () => {
        const states = await fetch(`/locations/states?country_id=${countrySelect.value}`).then(response => response.json());
        fillSelect(stateSelect, states, 'Select state'); fillSelect(citySelect, [], 'Select city');
    });
    stateSelect?.addEventListener('change', async () => {
        const cities = await fetch(`/locations/cities?state_id=${stateSelect.value}`).then(response => response.json());
        fillSelect(citySelect, cities, 'Select city');
    });
    const adminCountry = document.querySelector('#adminCountry');
    const adminState = document.querySelector('#adminState');
    adminCountry?.addEventListener('change', async () => {
        const states = await fetch(`/locations/states?country_id=${adminCountry.value}`).then(response => response.json());
        fillSelect(adminState, states, 'Select default state');
    });
    const fieldType = document.querySelector('#fieldType');
    const optionBuilder = document.querySelector('#optionBuilder');
    fieldType?.addEventListener('change', () => optionBuilder?.classList.toggle('d-none', fieldType.value === 'text'));
    fieldType?.addEventListener('change', () => optionBuilder?.classList.toggle('d-none', !['dropdown','radio','checkbox'].includes(fieldType.value)));
    const webinarCountry=document.querySelector('.webinar-location-country'), webinarState=document.querySelector('.webinar-location-state'), webinarCity=document.querySelector('.webinar-location-city');
    webinarCountry?.addEventListener('change',async()=>{if(webinarState){webinarState.disabled=true;fillSelect(webinarState,[],'Loading states...');const states=webinarCountry.value?await fetch(`/locations/states?country_id=${encodeURIComponent(webinarCountry.value)}`).then(r=>r.json()):[];fillSelect(webinarState,states,'Select state');webinarState.disabled=false}if(webinarCity)fillSelect(webinarCity,[],'Select city')});
    webinarState?.addEventListener('change',async()=>{if(!webinarCity)return;webinarCity.disabled=true;fillSelect(webinarCity,[],'Loading cities...');const cities=webinarState.value?await fetch(`/locations/cities?state_id=${encodeURIComponent(webinarState.value)}`).then(r=>r.json()):[];fillSelect(webinarCity,cities,'Select city');webinarCity.disabled=false});
    const selectAll=document.querySelector('[data-select-all]'),rowSelectors=[...document.querySelectorAll('[data-row-select]')],bulkToolbar=document.querySelector('[data-bulk-toolbar]'),selectedCount=document.querySelector('[data-selected-count]');
    const syncBulkSelection=()=>{const count=rowSelectors.filter(input=>input.checked).length;if(selectedCount)selectedCount.textContent=count;bulkToolbar?.classList.toggle('visible',count>0);if(selectAll){selectAll.checked=count>0&&count===rowSelectors.length;selectAll.indeterminate=count>0&&count<rowSelectors.length;}};
    selectAll?.addEventListener('change',()=>{rowSelectors.forEach(input=>input.checked=selectAll.checked);syncBulkSelection();});rowSelectors.forEach(input=>input.addEventListener('change',syncBulkSelection));syncBulkSelection();
    [...document.querySelectorAll('form:not([data-live-chat-delete])')].filter(form=>form.dataset.confirmDelete!==undefined||form.querySelector('input[name="_method"][value="DELETE"]')).forEach(form=>{form.removeAttribute('onsubmit');form.addEventListener('submit',event=>{if(form.dataset.confirmed==='1')return;event.preventDefault();if(form.id==='bulkDeleteForm'&&!rowSelectors.some(input=>input.checked))return;const template=document.querySelector('#sweetConfirmTemplate');if(!template)return;const dialog=template.content.firstElementChild.cloneNode(true);dialog.querySelector('[data-confirm-heading]').textContent=form.dataset.confirmTitle||'Delete this item?';dialog.querySelector('[data-confirm-message]').textContent=form.dataset.confirmText||'This item will be permanently deleted.';dialog.querySelector('[data-confirm-cancel]').addEventListener('click',()=>dialog.remove());dialog.addEventListener('click',click=>{if(click.target===dialog)dialog.remove();});dialog.querySelector('[data-confirm-accept]').addEventListener('click',()=>{form.dataset.confirmed='1';dialog.remove();form.requestSubmit();});document.body.appendChild(dialog);});});
    document.querySelector('.chat-file-button input[type="file"]')?.addEventListener('change',event=>{const file=event.target.files?.[0],composer=event.target.closest('.chat-composer');composer?.querySelector('.chat-pending-file')?.remove();if(file){const preview=document.createElement('span');preview.className='chat-pending-file';const url=URL.createObjectURL(file);preview.innerHTML=file.type.startsWith('image/')?`<img src="${url}" alt="Attachment preview"><b>${file.name}</b>`:file.type.startsWith('video/')?`<video src="${url}" muted></video><b>${file.name}</b>`:`<i class="bi bi-paperclip"></i><b>${file.name}</b>`;composer?.appendChild(preview);}});

    const bannerType = document.querySelector('#bannerMediaType');
    const bannerImageFields = document.querySelector('#bannerImageFields');
    const bannerVideoFields = document.querySelector('#bannerVideoFields');
    const bannerVideoUpload = document.querySelector('#bannerVideoUpload');
    const bannerVideoUrl = document.querySelector('#bannerVideoUrl');
    const bannerImageUpload = document.querySelector('#bannerImageUpload');
    const bannerImageUrl = document.querySelector('#bannerImageUrl');
    const bannerImageUrlInput = document.querySelector('#bannerImageUrlInput');
    const bannerVideoUrlInput = document.querySelector('#bannerVideoUrlInput');
    const bannerPreview = document.querySelector('#bannerLivePreview');
    const syncBannerType = () => {
        const isVideo = bannerType?.value === 'video';
        if (bannerImageFields) bannerImageFields.hidden = isVideo;
        if (bannerVideoFields) bannerVideoFields.hidden = !isVideo;
        if (bannerImageUrlInput) bannerImageUrlInput.disabled = isVideo;
        if (bannerVideoUrlInput) bannerVideoUrlInput.disabled = !isVideo;
    };
    const syncBannerImageSource = () => {
        const useUrl = document.querySelector('input[name="image_source"]:checked')?.value === 'url';
        if (bannerImageUpload) bannerImageUpload.hidden = useUrl;
        if (bannerImageUrl) bannerImageUrl.hidden = !useUrl;
    };
    const syncBannerVideoSource = () => {
        const useUrl = document.querySelector('input[name="video_source"]:checked')?.value === 'url';
        if (bannerVideoUpload) bannerVideoUpload.hidden = useUrl;
        if (bannerVideoUrl) bannerVideoUrl.hidden = !useUrl;
    };
    const renderBannerPreview = (file, kind) => {
        if (!file || !bannerPreview) return;
        const url = URL.createObjectURL(file);
        bannerPreview.innerHTML = kind === 'video' ? `<video src="${url}" controls autoplay muted></video>` : `<img src="${url}" alt="Banner preview">`;
    };
    const renderBannerVideoUrlPreview = (val) => {
        if (!bannerPreview || !val) return;
        const url = String(val).trim();
        const ytMatch = url.match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/i) || url.match(/^([A-Za-z0-9_-]{11})$/);
        if (ytMatch) {
            bannerPreview.innerHTML = `<iframe src="https://www.youtube-nocookie.com/embed/${ytMatch[1]}?autoplay=1&mute=1&loop=1&playlist=${ytMatch[1]}&controls=0" allow="autoplay; encrypted-media" allowfullscreen style="width:100%;min-height:300px;border:0;"></iframe>`;
            return;
        }
        const vimeoMatch = url.match(/vimeo\.com\/(?:video\/)?(\d{6,12})/i);
        if (vimeoMatch) {
            bannerPreview.innerHTML = `<iframe src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&muted=1&loop=1&autopause=0&background=1" allow="autoplay; fullscreen" allowfullscreen style="width:100%;min-height:300px;border:0;"></iframe>`;
            return;
        }
        bannerPreview.innerHTML = `<video src="${url.replace(/["<>]/g, '')}" controls autoplay muted style="width:100%;max-height:430px;"></video>`;
    };
    bannerType?.addEventListener('change', syncBannerType);
    document.querySelectorAll('input[name="image_source"]').forEach(radio => radio.addEventListener('change', syncBannerImageSource));
    document.querySelectorAll('input[name="video_source"]').forEach(radio => radio.addEventListener('change', syncBannerVideoSource));
    document.querySelector('#bannerImageInput')?.addEventListener('change', event => renderBannerPreview(event.target.files?.[0], 'image'));
    document.querySelector('#bannerVideoInput')?.addEventListener('change', event => renderBannerPreview(event.target.files?.[0], 'video'));
    bannerImageUrlInput?.addEventListener('input', event => {
        if (bannerPreview && event.target.value) bannerPreview.innerHTML = `<img src="${event.target.value.replace(/["<>]/g, '')}" alt="Banner preview">`;
    });
    bannerVideoUrlInput?.addEventListener('input', event => renderBannerVideoUrlPreview(event.target.value));
    bannerVideoUrlInput?.addEventListener('change', event => renderBannerVideoUrlPreview(event.target.value));
    syncBannerType();
    syncBannerImageSource();
    syncBannerVideoSource();

    const liveProvider=document.querySelector('#liveProvider'),liveSource=document.querySelector('#liveSource'),livePreview=document.querySelector('#liveEmbedPreview');
    const getEmbedUrl=(provider,input)=>{input=String(input||'').trim();const iframe=input.match(/<iframe[^>]+src=["']([^"']+)["']/i);if(iframe)input=iframe[1];if(provider==='youtube'){const match=input.match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/)||input.match(/^([A-Za-z0-9_-]{11})$/);return match?`https://www.youtube-nocookie.com/embed/${match[1]}?rel=0`:'';}if(provider==='vimeo'){const match=input.match(/vimeo\.com\/(?:video\/)?(\d{6,12})/)||input.match(/^(\d{6,12})$/);return match?`https://player.vimeo.com/video/${match[1]}`:'';}if(provider==='custom'){try{const url=new URL(input);return ['http:','https:'].includes(url.protocol)?url.href:'';}catch{return '';}}return '';};
    const renderLivePreview=()=>{if(!livePreview)return;const url=getEmbedUrl(liveProvider?.value,liveSource?.value);livePreview.innerHTML=url?`<iframe src="${url.replace(/["<>]/g,'')}" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`:'<div><i class="bi bi-play-btn"></i><span>Enter a valid video URL, ID, or iframe code.</span></div>';};
    liveProvider?.addEventListener('change',renderLivePreview);liveSource?.addEventListener('input',renderLivePreview);if(liveProvider?.value&&liveSource?.value)renderLivePreview();

    const experiencePreview=document.querySelector('#experiencePreview'),roomLayout=document.querySelector('#roomLayout'),brandPrimary=document.querySelector('#brandPrimary'),brandSecondary=document.querySelector('#brandSecondary');
    const validHex=value=>/^#[0-9a-f]{6}$/i.test(value);const renderExperiencePreview=()=>{const primary=validHex(brandPrimary?.value)?brandPrimary.value:'#6d28d9',secondary=validHex(brandSecondary?.value)?brandSecondary.value:'#2563eb';const primarySwatch=document.querySelector('[data-color-swatch="primary"]'),secondarySwatch=document.querySelector('[data-color-swatch="secondary"]');if(primarySwatch)primarySwatch.style.background=primary;if(secondarySwatch)secondarySwatch.style.background=secondary;if(!experiencePreview)return;experiencePreview.style.setProperty('--preview-primary',primary);experiencePreview.style.setProperty('--preview-secondary',secondary);const label=roomLayout?.selectedOptions[0]?.textContent||'Presentation';const target=experiencePreview.querySelector('[data-layout-preview]');if(target)target.textContent=`${label} layout`;};
    [roomLayout,brandPrimary,brandSecondary].forEach(input=>input?.addEventListener('input',renderExperiencePreview));renderExperiencePreview();
    document.querySelectorAll('[data-theme-preset]').forEach(button=>button.addEventListener('click',()=>{const [primary,secondary]=button.dataset.themePreset.split('|');if(brandPrimary)brandPrimary.value=primary;if(brandSecondary)brandSecondary.value=secondary;document.querySelectorAll('[data-theme-preset]').forEach(item=>item.classList.remove('active'));button.classList.add('active');renderExperiencePreview();}));

    const conditionalFields=[...document.querySelectorAll('[data-conditional-field]')];
    const inputValue=id=>{const inputs=[...document.querySelectorAll(`[name="fields[${id}]"],[name="fields[${id}][]"]`)];const checked=inputs.filter(input=>input.checked).map(input=>input.value);return checked.length?checked.join(','):(inputs[0]?.value||'');};
    const syncConditionalFields=()=>conditionalFields.forEach(wrapper=>{const id=wrapper.dataset.conditionField;if(!id){wrapper.hidden=false;return;}const matches=String(inputValue(id)).toLowerCase()===String(wrapper.dataset.conditionValue||'').toLowerCase();const show=wrapper.dataset.conditionOperator==='not_equals'?!matches:matches;wrapper.hidden=!show;wrapper.querySelectorAll('input,select,textarea').forEach(input=>input.disabled=!show);});
    document.querySelectorAll('[name^="fields["]').forEach(input=>input.addEventListener('change',syncConditionalFields));syncConditionalFields();

    document.querySelectorAll('[data-site-preview-input]').forEach(input => input.addEventListener('change', event => {
        const file = event.target.files?.[0];
        const target = document.querySelector(`[data-site-preview="${input.dataset.sitePreviewInput}"]`);
        if (file && target) { target.src = URL.createObjectURL(file); target.hidden = false; }
    }));
});
