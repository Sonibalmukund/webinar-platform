@extends('layouts.portal')
@section('title', $webinar->exists ? 'Edit Webinar' : 'Add Webinar')
@section('content')
    <style>
        .dynamic-field-switches {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding-bottom: 8px
        }

        .dynamic-field-toggle {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 0;
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
            cursor: pointer
        }

        .dynamic-field-toggle .form-switch {
            display: flex;
            align-items: center;
            min-height: 0;
            margin: 0;
            padding-left: 0
        }

        .dynamic-field-toggle .form-check-input {
            float: none;
            margin: 0;
            width: 2.55rem;
            height: 1.3rem;
            cursor: pointer;
            border-color: #cbd5e1;
            box-shadow: none
        }

        .dynamic-field-toggle .form-check-input:checked {
            background-color: #16a34a;
            border-color: #16a34a
        }

        .dynamic-field-toggle .form-check-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 .2rem rgba(34, 197, 94, .13)
        }

        .setting-toggle {
            cursor: pointer;
            transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease
        }

        .setting-toggle:hover {
            border-color: #cbd5e1;
            box-shadow: 0 5px 16px rgba(15, 23, 42, .05)
        }

        .setting-toggle:has(.setting-switch .form-check-input:checked) {
            border-color: #bbf7d0;
            background: #f0fdf4
        }

        .setting-toggle .setting-switch {
            display: flex;
            flex-direction: row;
            align-items: center;
            min-height: 0;
            margin: 0;
            padding: 0;
            flex: none
        }

        .setting-toggle .setting-switch .form-check-input {
            float: none;
            margin: 0;
            width: 2.75rem;
            height: 1.4rem;
            cursor: pointer;
            border-color: #cbd5e1;
            box-shadow: none;
            accent-color: initial
        }

        .setting-toggle .setting-switch .form-check-input:checked {
            background-color: #16a34a;
            border-color: #16a34a
        }

        .setting-toggle .setting-switch .form-check-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 .2rem rgba(34, 197, 94, .13)
        }

        .certificate-visibility-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px !important
        }

        .certificate-visibility-option {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between;
            gap: 14px;
            padding: 13px 14px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            background: #f8fafc !important;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease, box-shadow .18s ease
        }

        .certificate-visibility-option:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 5px 16px rgba(15, 23, 42, .05)
        }

        .certificate-visibility-option:has(.form-check-input:checked) {
            border-color: #bbf7d0 !important;
            background: #f0fdf4 !important
        }

        .certificate-switch-copy {
            display: flex !important;
            flex-direction: column;
            gap: 2px;
            min-width: 0
        }

        .certificate-visibility-option strong {
            display: block;
            font-size: .75rem;
            color: #1e293b
        }

        .certificate-visibility-option small {
            display: block;
            font-size: .62rem;
            line-height: 1.35;
            color: #64748b;
            font-weight: 500
        }

        .certificate-show-switch {
            appearance: none !important;
            -webkit-appearance: none !important;
            display: inline-block !important;
            position: relative !important;
            flex: 0 0 42px !important;
            width: 42px !important;
            min-width: 42px !important;
            max-width: 42px !important;
            height: 23px !important;
            min-height: 23px !important;
            max-height: 23px !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 999px !important;
            background: #e2e8f0 !important;
            cursor: pointer;
            box-sizing: border-box !important;
            transition: .18s ease
        }

        .certificate-show-switch::after {
            content: '';
            position: absolute;
            left: 2px;
            top: 2px;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, .3);
            transition: transform .18s ease
        }

        .certificate-show-switch:checked {
            border-color: #16a34a !important;
            background: #16a34a !important
        }

        .certificate-show-switch:checked::after {
            transform: translateX(19px)
        }

        .certificate-show-switch:focus-visible {
            outline: 3px solid rgba(34, 197, 94, .2);
            outline-offset: 2px
        }

        .builder-card-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px
        }

        .builder-remove {
            margin-left: auto;
            white-space: nowrap;
            height: auto !important;
            min-height: 36px;
            padding: 7px 12px !important
        }

        .inline-file-error {
            display: none;
            margin-top: 6px;
            color: #dc2626;
            font-size: .72rem;
            font-weight: 700
        }

        .inline-file-error.show {
            display: block
        }

        .certificate-advanced-positioning>summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            color: #334155;
            font-size: .76rem;
            font-weight: 800;
            cursor: pointer;
            list-style: none
        }

        .certificate-advanced-positioning>summary::-webkit-details-marker {
            display: none
        }

        .certificate-advanced-positioning>summary small {
            padding: 3px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: .6rem
        }

        .certificate-advanced-positioning[open]>summary {
            border-radius: 12px 12px 0 0;
            border-color: #cbd5e1;
            background: #f8fafc
        }

        .certificate-editor-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(420px, 1.05fr);
            gap: 28px;
            align-items: start
        }

        .certificate-editor-controls {
            min-width: 0
        }

        .certificate-editor-controls>.form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }

        .certificate-editor-preview {
            min-width: 0;
            position: sticky;
            top: 20px
        }

        .certificate-editor-preview .certificate-preview {
            padding: 12px !important
        }

        .certificate-editor-preview .cert-inner {
            min-height: 300px !important
        }

        .certificate-editor-controls .certificate-visibility-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }

        @media(max-width:1199.98px) {
            .certificate-editor-layout {
                grid-template-columns: minmax(0, 1fr) minmax(360px, .9fr);
                gap: 20px
            }

            .certificate-editor-controls>.form-grid {
                grid-template-columns: 1fr
            }

            .certificate-editor-controls .form-field {
                grid-column: 1/-1
            }
        }

        @media(max-width:991.98px) {
            .certificate-editor-layout {
                grid-template-columns: 1fr
            }

            .certificate-editor-preview {
                position: static
            }

            .certificate-visibility-grid {
                grid-template-columns: 1fr 1fr
            }
        }

        @media(max-width:575.98px) {
            .certificate-visibility-grid {
                grid-template-columns: 1fr
            }
        }

        /* Modern Glassmorphic / Card Wizard Container */
        .webinar-stepper-wrap {
            position: relative;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 10px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .webinar-stepper {
            display: flex;
            align-items: center;
            justify-content: stretch;
            gap: 4px;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
            padding: 1px;
        }

        .webinar-stepper::-webkit-scrollbar {
            height: 5px;
        }

        .webinar-stepper::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: #cbd5e1;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px;
            border-radius: 14px;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            text-align: left;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            flex: 1 1 0;
            min-width: 138px;
            overflow: hidden;
        }

        .step-item:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .step-item.active {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            box-shadow: 0 8px 22px rgba(124, 58, 237, 0.28);
            border-color: transparent;
        }

        .step-item.completed {
            border-color: #bbf7d0;
            background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
        }

        .step-badge {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 800;
            font-size: 0.9rem;
            flex: none;
            transition: all 0.2s ease;
        }

        .step-item.active .step-badge {
            background: #ffffff;
            color: #7c3aed;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .step-item.completed .step-badge {
            background: #10b981;
            color: #ffffff;
        }

        .step-meta {
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
        }

        .step-title {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.79rem;
            font-weight: 700;
            color: #334155;
            line-height: 1.25;
        }

        .step-item.completed .step-title {
            color: #166534;
        }

        .step-item {
            position: relative;
        }

        .step-item.ready::after,
        .step-item.needs-attention::after {
            content: '';
            position: absolute;
            right: 7px;
            top: 7px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            box-shadow: 0 0 0 3px #fff
        }

        .step-item.ready::after {
            background: #22c55e
        }

        .step-item.needs-attention::after {
            background: #f59e0b
        }

        .step-item.active::after {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .3)
        }

        .step-item.active .step-title {
            color: #ffffff;
        }

        .step-sub {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.64rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .step-item.active .step-sub {
            color: rgba(255, 255, 255, 0.85);
        }

        .step-arrow {
            color: #cbd5e1;
            font-size: 0.9rem;
            flex: 0 0 14px;
            width: 14px;
            display: grid;
            place-items: center;
        }

        .wizard-completion {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 10px 2px
        }

        .wizard-completion strong {
            font-size: .72rem;
            color: #334155;
            white-space: nowrap
        }

        .wizard-completion-track {
            height: 6px;
            flex: 1;
            overflow: hidden;
            border-radius: 999px;
            background: #e9eef5
        }

        .wizard-completion-track>i {
            display: block;
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transition: width .25s ease
        }

        .wizard-missing-summary {
            max-width: 48%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #b45309;
            font-size: .68rem
        }

        @media (max-width: 1380px) {
            .step-item {
                min-width: 148px;
                flex: 0 0 148px;
            }
        }

        @media (max-width: 820px) {
            .step-arrow {
                display: none;
            }

            .webinar-stepper-wrap {
                padding: 8px;
            }

            .step-item {
                min-width: 154px;
                flex-basis: 154px;
                padding: 9px 10px;
            }

            .step-badge {
                width: 32px;
                height: 32px;
                border-radius: 10px;
            }

            .step-sub {
                font-size: .61rem;
            }
        }

        /* Step Panes */
        .wizard-step-pane {
            display: none;
        }

        .wizard-step-pane.active {
            display: block;
            animation: wizardStepFade 0.22s ease forwards;
        }

        @keyframes wizardStepFade {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wizard-poll-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .wizard-poll-row:focus-within {
            border-color: #7c3aed;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.08);
        }

        /* Form Styles & Inline Left-Aligned Labels */
        .form-field {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            text-align: left !important;
            justify-content: flex-start !important;
            gap: 6px !important;
            width: 100% !important;
        }

        .form-field.full {
            grid-column: 1 / -1 !important;
        }

        .form-field>.form-control,
        .form-field>.form-select,
        .form-field>.input-group,
        .form-field>textarea {
            width: 100% !important;
            border-radius: 10px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            font-size: 0.93rem !important;
            color: #0f172a !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease !important;
        }

        .form-field>.form-control:focus,
        .form-field>.form-select:focus,
        .form-field>.input-group:focus-within,
        .form-field>textarea:focus {
            border-color: #7c3aed !important;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.14) !important;
            outline: none !important;
        }

        .form-grid label.form-label-custom,
        .form-field label.form-label-custom,
        .form-label-custom {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            text-align: left !important;
            width: fit-content !important;
            gap: 6px !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin: 0 0 4px 0 !important;
        }

        .form-label-custom .req {
            color: #ef4444 !important;
            font-weight: 800 !important;
            font-size: 1rem !important;
            line-height: 1 !important;
            display: inline-block !important;
        }

        .wizard-field-error {
            color: #ef4444 !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            margin-top: 4px !important;
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* Schedule date/time controls */
        .schedule-picker-shell {
            position: relative;
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) 44px;
            align-items: center;
            width: 100%;
            min-height: 58px;
            overflow: hidden;
            border: 1.5px solid #cbd5e1;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .schedule-picker-shell:focus-within {
            border-color: #ee1f2d;
            box-shadow: 0 0 0 4px rgba(238, 31, 45, .12), 0 12px 28px rgba(185, 21, 34, .1);
            transform: translateY(-1px);
        }

        .schedule-picker-shell.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, .15) !important;
        }

        .schedule-picker-icon {
            align-self: stretch;
            display: grid;
            place-items: center;
            color: #ee1f2d;
            background: linear-gradient(145deg, #fff5f5, #ffe4e6);
            border-right: 1px solid #fecdd3;
            font-size: 1.15rem;
        }

        .schedule-picker-shell input[type="datetime-local"],
        .schedule-picker-shell .flatpickr-input {
            width: 100%;
            min-width: 0;
            height: 56px;
            padding: 8px 12px;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            color: #0f172a;
            font-weight: 700;
            color-scheme: light;
            accent-color: #ee1f2d;
        }

        .schedule-picker-shell>input[type="hidden"] {
            display: none;
        }

        .flatpickr-calendar {
            overflow: hidden;
            border: 1px solid #fecdd3 !important;
            border-radius: 16px !important;
            box-shadow: 0 24px 55px rgba(15, 23, 42, .2) !important;
            font-family: inherit;
        }

        .flatpickr-months {
            padding: 7px 4px;
            background: linear-gradient(135deg, #ff3341, #ee1f2d);
        }

        .flatpickr-months .flatpickr-month,
        .flatpickr-current-month,
        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            color: #fff !important;
            fill: #fff !important;
        }

        .flatpickr-current-month input.cur-year {
            color: #fff !important;
            -webkit-text-fill-color: #fff !important;
            background: transparent !important;
            font-family: inherit !important;
            font-size: 16px !important;
            font-weight: 800 !important;
        }

        .flatpickr-current-month {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .flatpickr-current-month .flatpickr-monthDropdown-months {
            appearance: auto;
            pointer-events: auto;
            color: #fff !important;
            background: #ee1f2d !important;
            font-family: inherit !important;
            font-size: 16px !important;
            font-weight: 800 !important;
            line-height: 1.4;
            cursor: pointer;
        }

        .flatpickr-current-month .flatpickr-monthDropdown-months option {
            color: #0f172a !important;
            background: #fff !important;
            font-family: inherit !important;
            font-size: 15px !important;
            font-weight: 600 !important;
        }

        .flatpickr-current-month .flatpickr-yearDropdown {
            min-width: 82px;
            padding: 2px 24px 2px 8px;
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 7px;
            color: #fff !important;
            background: #ee1f2d !important;
            font-family: inherit !important;
            font-size: 16px !important;
            font-weight: 800 !important;
            line-height: 1.4;
            cursor: pointer;
        }

        .flatpickr-current-month .flatpickr-yearDropdown option {
            color: #0f172a !important;
            background: #fff !important;
            font-family: inherit !important;
            font-size: 15px !important;
            font-weight: 600 !important;
        }

        .flatpickr-current-month .numInputWrapper {
            width: 68px;
        }

        .flatpickr-current-month .numInputWrapper span {
            display: none;
        }

        [data-media-preview] {
            cursor: zoom-in;
            position: relative;
        }

        [data-media-preview]:not([data-preview-ready="1"]) {
            cursor: default;
        }

        [data-media-preview][data-preview-ready="1"]::after {
            content: 'Click to enlarge';
            position: absolute;
            right: 10px;
            bottom: 10px;
            z-index: 3;
            padding: 5px 9px;
            border-radius: 999px;
            background: rgba(15, 23, 42, .78);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            opacity: 0;
            transition: opacity .2s ease;
            pointer-events: none;
        }

        [data-media-preview][data-preview-ready="1"]:hover::after {
            opacity: 1;
        }

        .flatpickr-weekdays {
            background: #fff1f2;
        }

        span.flatpickr-weekday {
            color: #9f1239 !important;
            background: #fff1f2 !important;
        }

        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected:hover,
        .flatpickr-day.selected:focus {
            border-color: #ee1f2d !important;
            background: #ee1f2d !important;
        }

        .flatpickr-day.today {
            border-color: #fb7185 !important;
            color: #be123c;
        }

        .flatpickr-day:hover {
            border-color: #ffe4e6 !important;
            background: #ffe4e6 !important;
        }

        .flatpickr-time input:hover,
        .flatpickr-time input:focus,
        .flatpickr-time .flatpickr-am-pm:hover,
        .flatpickr-time .flatpickr-am-pm:focus {
            background: #fff1f2 !important;
        }

        .flatpickr-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 10px 12px;
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        .flatpickr-actions button {
            border: 0;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
        }

        .flatpickr-cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .flatpickr-apply {
            background: #ef233c;
            color: #fff;
        }

        .flatpickr-actions button:hover {
            filter: brightness(.96);
        }

        .schedule-picker-shell input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            width: 0;
            padding: 0;
        }

        .schedule-picker-open {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 10px;
            color: #ffffff;
            background: linear-gradient(135deg, #ff3341, #ee1f2d);
            box-shadow: 0 5px 12px rgba(238, 31, 45, .25);
        }

        .schedule-picker-open:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .schedule-field-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
        }

        .schedule-timezone-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 999px;
            color: #be123c;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            font-size: .7rem;
            font-weight: 800;
        }

        /* Section Dividers inside Cards */
        .form-subhead {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 0 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            grid-column: 1 / -1;
        }

        .form-subhead i {
            font-size: 1.1rem;
            color: #7c3aed;
        }

        .form-subhead strong {
            font-size: 0.95rem;
            color: #0f172a;
        }

        .form-subhead small {
            color: #64748b;
            margin-left: auto;
            font-size: 0.75rem;
        }

        /* Branding media editor */
        .media-config-card {
            width: 100%;
            padding: 18px;
            border: 1px solid #dce3ec;
            border-radius: 16px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 8px 24px rgba(15, 23, 42, .045);
        }

        .media-config-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(420px, 1fr);
            gap: 22px;
            align-items: stretch;
        }

        .media-config-fields {
            display: flex;
            flex-direction: column;
            gap: 15px;
            min-width: 0;
        }

        .media-config-fields .row {
            --bs-gutter-y: 15px;
        }

        .media-preview-pane {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .media-preview-canvas {
            flex: 1;
            min-height: 300px;
            aspect-ratio: 16 / 9;
            border: 1.5px dashed #cbd5e1 !important;
            border-radius: 14px !important;
            background: radial-gradient(circle at 50% 30%, #ffffff, #f4f7fb) !important;
        }

        .media-preview-canvas[data-preview-ready="1"] {
            border-style: solid !important;
            background: #0b1220 !important;
        }

        .media-preview-empty {
            display: grid;
            justify-items: center;
            gap: 6px;
            padding: 24px;
            text-align: center;
            color: #64748b;
        }

        .media-preview-empty i {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            color: #ee1f2d;
            background: #fff1f2;
            font-size: 1.25rem;
        }

        .media-preview-empty strong {
            color: #334155;
            font-size: .86rem;
        }

        .media-preview-empty small {
            max-width: 280px;
            line-height: 1.5;
        }

        .media-config-help {
            margin: 0;
            padding: 11px 13px;
            border-radius: 11px;
            background: #f8fafc;
            color: #64748b;
            font-size: .78rem;
            line-height: 1.55;
        }

        .sortable-card {
            position: relative
        }

        .sort-handle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 0;
            border-radius: 9px;
            padding: 6px 9px;
            color: #64748b;
            background: #f1f5f9;
            font-size: .72rem;
            font-weight: 800;
            cursor: grab;
            user-select: none
        }

        .sort-handle:active {
            cursor: grabbing
        }

        .sortable-card.is-dragging {
            opacity: .45;
            transform: scale(.99)
        }

        .sortable-card.drag-over {
            outline: 2px dashed #ef233c;
            outline-offset: 3px
        }

        .brand-style-options {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px
        }

        .brand-style-option {
            position: relative;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            width: 100% !important;
            padding: 12px !important;
            margin: 0 !important;
            border: 1px solid #dfe5ee;
            border-radius: 13px;
            background: #fff;
            cursor: pointer
        }

        .brand-style-option:has(input:checked) {
            border-color: #ef233c;
            background: #fff1f2;
            box-shadow: 0 0 0 3px #ffe4e6
        }

        .brand-style-option input {
            accent-color: #ef233c
        }

        .brand-style-option span {
            display: grid
        }

        .brand-style-option strong {
            font-size: .8rem
        }

        .brand-style-option small {
            color: #64748b;
            font-size: .68rem
        }

        .duplicate-alert {
            display: none;
            align-items: center;
            gap: 7px;
            margin-top: 8px;
            padding: 8px 10px;
            border: 1px solid #fde68a;
            border-radius: 10px;
            color: #92400e;
            background: #fffbeb;
            font-size: .73rem;
            font-weight: 700
        }

        .duplicate-alert.show {
            display: flex
        }

        .experience-preview-modal .modal-dialog {
            max-width: 1040px
        }

        .experience-preview-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px
        }

        .experience-preview-stage {
            min-height: 590px;
            padding: 26px;
            background: #eef2f7
        }

        .experience-preview-shell {
            overflow: hidden;
            max-width: 920px;
            margin: auto;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .2)
        }

        .experience-preview-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb
        }

        .experience-preview-logo {
            max-width: 145px;
            max-height: 38px;
            object-fit: contain
        }

        .experience-preview-hero {
            display: grid;
            place-items: center;
            min-height: 300px;
            padding: 28px;
            color: #fff;
            text-align: center;
            background: linear-gradient(135deg, var(--preview-primary, #6d28d9), var(--preview-secondary, #2563eb));
            background-size: cover;
            background-position: center
        }

        .experience-preview-hero h2 {
            max-width: 680px;
            margin: 0;
            font-size: 2rem
        }

        .experience-preview-body {
            display: grid;
            gap: 20px;
            padding: 24px
        }

        .experience-preview-brand-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px
        }

        .experience-preview-brand {
            display: grid;
            place-items: center;
            min-width: 130px;
            height: 70px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff
        }

        .experience-preview-brand img {
            max-width: 110px;
            max-height: 45px;
            object-fit: contain
        }

        .experience-preview-agenda {
            display: grid;
            gap: 7px
        }

        .experience-preview-agenda>div {
            display: flex;
            gap: 14px;
            padding: 11px 13px;
            border-radius: 10px;
            background: #f8fafc
        }

        .experience-preview-live .experience-preview-hero {
            min-height: 380px;
            background: #080d1a
        }

        .experience-preview-live .experience-preview-hero::before {
            content: '▶';
            display: grid;
            place-items: center;
            width: 64px;
            height: 64px;
            border: 2px solid var(--preview-primary, #6d28d9);
            border-radius: 18px;
            color: var(--preview-primary, #6d28d9);
            font-size: 1.5rem
        }

        .preview-mode-title {
            font-size: .75rem;
            font-weight: 800;
            color: #64748b
        }

        @media (max-width: 960px) {
            .media-config-grid {
                grid-template-columns: 1fr;
            }

            .media-preview-canvas {
                min-height: 240px;
            }

            .brand-style-options {
                grid-template-columns: 1fr
            }
        }

        /* Segmented Choice Buttons (Registration Type, Players, Layouts) */
        .choice-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            grid-column: 1 / -1;
        }

        .choice-card {
            position: relative;
            cursor: pointer;
            margin: 0;
        }

        .choice-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .choice-card-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .choice-card:hover .choice-card-box {
            border-color: #c4b5fd;
            background: #faf5ff;
        }

        .choice-card input[type="radio"]:checked+.choice-card-box {
            border-color: #7c3aed;
            background: #f5f3ff;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.15);
        }

        .choice-card-box i {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 1.25rem;
            flex: none;
            transition: all 0.2s ease;
        }

        .choice-card input[type="radio"]:checked+.choice-card-box i {
            background: #7c3aed;
            color: #ffffff;
        }

        .choice-card-text {
            display: flex;
            flex-direction: column;
        }

        .choice-card-text strong {
            font-size: 0.88rem;
            color: #1e293b;
        }

        .choice-card-text small {
            font-size: 0.72rem;
            color: #64748b;
        }

        /* Floating / Sticky Navigation Footer */
        .wizard-footer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 24px;
            padding: 16px 22px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(15, 23, 42, 0.05);
        }

        .wizard-step-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
        }

        .wizard-step-info-badge i {
            color: #7c3aed;
        }

        /* Live Player & Embed Studio */
        .live-embed-preview {
            min-height: 270px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            background: #090e1a;
            color: #94a3b8;
        }

        .stream-source-studio {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
            gap: 18px;
            grid-column: 1/-1;
            align-items: stretch;
        }

        .stream-source-studio .live-embed-preview {
            min-height: 360px;
        }

        .stream-source-controls {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .stream-source-studio .stream-source-controls {
            grid-column: 1;
            grid-row: 1;
        }

        .stream-source-studio .live-embed-preview {
            grid-column: 2;
            grid-row: 1;
        }

        .stream-provider-buttons {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .stream-provider-buttons .choice-card-box {
            height: 100%;
            padding: 12px;
        }

        @media (max-width:900px) {
            .stream-source-studio {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width:900px) {

            .stream-source-studio .stream-source-controls,
            .stream-source-studio .live-embed-preview {
                grid-column: 1;
                grid-row: auto;
            }
        }

        .live-embed-preview iframe {
            width: 100%;
            aspect-ratio: 16/9;
            border: 0;
        }

        .live-embed-preview>div {
            display: grid;
            justify-items: center;
            gap: 10px;
        }

        .live-embed-preview i {
            font-size: 3rem;
            color: #8b5cf6;
        }

        /* Photoshop-inspired brand color workspace */
        .brand-studio-grid {
            display: grid;
            grid-template-columns: minmax(320px, .78fr) minmax(420px, 1.22fr);
            gap: 18px;
            grid-column: 1/-1;
            align-items: stretch
        }

        .ps-color-panel {
            overflow: hidden;
            border: 1px solid #263244;
            border-radius: 18px;
            background: #111827;
            color: #e5e7eb;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .16)
        }

        .brand-color-launcher {
            padding: 18px;
            border: 1px solid #dfe5ee;
            border-radius: 18px;
            background: linear-gradient(145deg, #fff, #f8fafc);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .08)
        }

        .brand-color-launcher>small {
            display: block;
            margin: 5px 0 16px;
            color: #64748b
        }

        .brand-color-buttons {
            display: grid;
            gap: 10px
        }

        .brand-color-open {
            display: grid;
            grid-template-columns: 44px 1fr auto;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #dfe5ee;
            border-radius: 13px;
            background: #fff;
            text-align: left;
            transition: .2s
        }

        .brand-color-open:hover {
            border-color: #fb7185;
            box-shadow: 0 0 0 3px #ffe4e6
        }

        .brand-color-open>i {
            width: 44px;
            height: 44px;
            border: 4px solid #fff;
            border-radius: 12px;
            box-shadow: 0 0 0 1px #cbd5e1
        }

        .brand-color-open span {
            display: grid
        }

        .brand-color-open strong {
            font-size: .8rem
        }

        .brand-color-open code {
            color: #64748b;
            font-size: .72rem
        }

        .brand-color-open>.bi {
            width: auto;
            height: auto;
            border: 0;
            box-shadow: none;
            color: #94a3b8
        }

        .theme-picker-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            background: #111827;
            box-shadow: 0 30px 90px #0008
        }

        .theme-picker-modal .modal-header {
            border-bottom-color: #2b3648;
            background: #0b1220;
            color: #fff
        }

        .theme-picker-modal .btn-close {
            filter: invert(1)
        }

        .ps-color-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid #2b3648;
            background: #0b1220
        }

        .ps-color-head strong {
            font-size: .88rem
        }

        .ps-color-head small {
            color: #8290a5;
            font-size: .67rem;
            letter-spacing: .08em
        }

        .ps-target-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 12px
        }

        .ps-target-tab {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px;
            border: 1px solid #303b4d;
            border-radius: 11px;
            background: #172033;
            color: #aeb9ca;
            font-size: .73rem;
            font-weight: 800
        }

        .ps-target-tab.active {
            border-color: #fb7185;
            background: #2a1720;
            color: #fff
        }

        .ps-target-tab i {
            width: 22px;
            height: 22px;
            border: 3px solid #fff;
            border-radius: 7px;
            box-shadow: 0 0 0 1px #475569
        }

        .ps-picker-body {
            padding: 0 12px 12px
        }

        .ps-sv-field {
            position: relative;
            height: 220px;
            overflow: hidden;
            border: 1px solid #475569;
            border-radius: 10px;
            cursor: crosshair;
            background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, hsl(var(--picker-hue, 260), 100%, 50%));
            touch-action: none
        }

        .ps-picker-marker {
            position: absolute;
            left: 75%;
            top: 35%;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 1px #000, 0 2px 7px #0008;
            transform: translate(-50%, -50%);
            pointer-events: none
        }

        .ps-hue-row {
            display: grid;
            grid-template-columns: 1fr 34px;
            gap: 10px;
            align-items: center;
            margin-top: 12px
        }

        .ps-hue-slider {
            width: 100%;
            height: 14px;
            padding: 0;
            border: 0;
            border-radius: 999px;
            appearance: none;
            background: linear-gradient(90deg, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00)
        }

        .ps-hue-slider::-webkit-slider-thumb {
            width: 17px;
            height: 22px;
            appearance: none;
            border: 2px solid #fff;
            border-radius: 4px;
            background: transparent;
            box-shadow: 0 0 0 1px #111;
            cursor: ew-resize
        }

        .ps-native-color {
            width: 34px;
            height: 30px;
            padding: 2px;
            border: 1px solid #475569;
            border-radius: 7px;
            background: #0b1220;
            cursor: pointer
        }

        .ps-value-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
            margin-top: 12px
        }

        .ps-value-grid label {
            color: #8290a5;
            font-size: .65rem
        }

        .ps-value-grid .professional-color {
            margin-top: 5px;
            border-color: #344054;
            background: #0b1220
        }

        .ps-value-grid .professional-color .color-hex-input {
            color: #f8fafc !important
        }

        .ps-rgb-readout {
            padding: 8px 10px;
            border: 1px solid #344054;
            border-radius: 10px;
            background: #0b1220;
            color: #9ba8ba;
            font-family: monospace;
            font-size: .72rem
        }

        .brand-live-preview {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 430px;
            border: 1px solid #dfe5ee;
            border-radius: 18px;
            background: #f8fafc;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .08)
        }

        .brand-live-preview header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff
        }

        .brand-live-preview header strong {
            font-size: .78rem
        }

        .brand-live-preview header span {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #16a34a;
            font-size: .66rem;
            font-weight: 800
        }

        .brand-live-preview header span:before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e
        }

        .brand-preview-frame {
            width: 100%;
            min-height: 390px;
            flex: 1;
            border: 0;
            background: #fff
        }

        @media(max-width:1000px) {
            .brand-studio-grid {
                grid-template-columns: 1fr
            }

            .brand-live-preview {
                min-height: 360px
            }

            .brand-preview-frame {
                min-height: 320px
            }
        }

        .wizard-footer-bar>div:last-child {
            flex-wrap: wrap;
            justify-content: flex-end
        }

        @media (max-width: 768px) {
            .wizard-footer-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .wizard-footer-bar>div:last-child,
            .wizard-footer-bar #wizardPreviewActions {
                width: 100%;
                justify-content: stretch
            }

            .wizard-footer-bar .btn {
                flex: 1;
                white-space: nowrap
            }

            .choice-cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-heading">
        <div>
            <h1>{{ $webinar->exists ? 'Edit Webinar' : 'Add Webinar' }}</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($webinar->exists)
                <a class="btn btn-danger" href="{{ route('admin.webinars.live', $webinar) }}"><i
                        class="bi bi-broadcast me-1"></i> Live control</a>
            @endif
            <a class="btn btn-light" href="{{ route('admin.webinars.index') }}"><i class="bi bi-arrow-left me-1"></i> Back
                to webinars</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- High-End Stepper Navigation -->
    <div class="webinar-stepper-wrap">
        <div class="webinar-stepper" role="tablist">
            <button type="button" class="step-item active" data-step-nav="1">
                <span class="step-badge" data-badge="1">1</span>
                <div class="step-meta">
                    <span class="step-title">1. Essentials</span>
                    <span class="step-sub">Details, schedule &amp; stream</span>
                </div>
            </button>
            <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
            <button type="button" class="step-item" data-step-nav="2">
                <span class="step-badge" data-badge="2">2</span>
                <div class="step-meta">
                    <span class="step-title">2. Design &amp; Content</span>
                    <span class="step-sub">Branding, speakers &amp; agenda</span>
                </div>
            </button>
            <div class="step-arrow"><i class="bi bi-chevron-right"></i></div>
            <button type="button" class="step-item" data-step-nav="3">
                <span class="step-badge" data-badge="3">3</span>
                <div class="step-meta">
                    <span class="step-title">3. Registration &amp; Publish</span>
                    <span class="step-sub">Form, engagement &amp; certificate</span>
                </div>
            </button>
        </div>
        <div class="wizard-completion" aria-live="polite">
            <strong><i class="bi bi-clipboard-check me-1"></i><span id="wizardCompletionPercent">0%</span> complete</strong>
            <span class="wizard-completion-track"><i id="wizardCompletionBar"></i></span>
            <span class="wizard-missing-summary" id="wizardMissingSummary">Checking setup…</span>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" id="webinarForm"
        action="{{ $webinar->exists ? route('admin.webinars.update', $webinar) : route('admin.webinars.store') }}"
        novalidate data-no-validation>
        @csrf
        @if ($webinar->exists)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 rounded-4 mb-4 p-3 d-flex align-items-start gap-3"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0 mt-1"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading fw-bold mb-1 fs-6">Please resolve the following errors:</h5>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @php
            $experience = (array) old('experience', data_get($webinar->settings, 'experience', []));
            $selectedRoomLayout = old(
                'room_layout',
                in_array($experience['layout'] ?? 'presentation', ['theater', 'presentation'], true)
                    ? $experience['layout'] ?? 'presentation'
                    : 'presentation',
            );
        @endphp

        {{-- ======================================================== --}}
        {{-- STEP 1: BASIC INFORMATION                                --}}
        {{-- ======================================================== --}}
        <div class="wizard-step-pane active" data-step-pane="1">
            <section class="panel-card">
                <div class="panel-title">
                    <div>
                        <h3>Primary Details & Schedule</h3>
                        <p>Core metadata and timing parameters for this event.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field full">
                        <label class="form-label-custom" for="webinarTitle">
                            <span>Webinar Title</span>
                            <span class="req">*</span>
                        </label>
                        <input class="form-control @error('title') is-invalid @enderror" id="webinarTitle" name="title"
                            value="{{ old('title', $webinar->title) }}"
                            placeholder="e.g. Strategic Global Leadership Summit 2026" required>
                        @error('title')
                            <div class="wizard-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="webinarSlug">
                            <span>Public URL Slug</span>
                            <span class="req">*</span>
                        </label>
                        <input class="form-control @error('slug') is-invalid @enderror" id="webinarSlug" name="slug"
                            value="{{ old('slug', $webinar->slug) }}" placeholder="slug-auto-generates">
                        <small class="text-muted">The slug is generated automatically as you type the title. You can also
                            edit it manually.</small>
                    </div>

                    <div class="form-field">
                        <label class="form-label-custom" for="webinarStatus">
                            <span>Event Status</span>
                        </label>
                        <select class="form-select" name="status" id="webinarStatus">
                            @foreach (['draft' => 'Draft (Private)', 'scheduled' => 'Scheduled', 'live' => 'Live Now', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $status => $label)
                                <option value="{{ $status }}" @selected(old('status', $webinar->status ?: 'draft') === $status)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="form-label-custom" for="webinarLanguage">
                            <span>Language</span>
                            <span class="req">*</span>
                        </label>
                        <select class="form-select" id="webinarLanguage" name="language">
                            @foreach ($languages as $code => $label)
                                <option value="{{ $code }}" @selected(old('language', $webinar->language ?: 'en') === $code)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Stored as webinar metadata. This selection does not automatically
                            translate page content.</small>
                    </div>

                    <div class="form-field">
                        <label class="form-label-custom" for="webinarTimezone">
                            <span>Timezone</span>
                            <span class="req">*</span>
                        </label>
                        <select class="form-select" id="webinarTimezone" name="timezone">
                            @foreach ($timezones as $timezone => $label)
                                <option value="{{ $timezone }}" @selected(old('timezone', $webinar->timezone ?: 'Asia/Kolkata') === $timezone)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Schedule fields and attendee dates use this timezone.</small>
                    </div>

                    <div class="form-subhead">
                        <i class="bi bi-calendar-range"></i>
                        <strong>Event Timing & Access Capacity</strong>
                    </div>

                    <div class="form-field">
                        <div class="schedule-field-meta">
                            <label class="form-label-custom mb-0" for="webinarStartsAt"><span>Starts At</span></label>
                            <span class="schedule-timezone-chip" data-schedule-timezone><i class="bi bi-globe2"></i>
                                {{ old('timezone', $webinar->timezone ?: 'Asia/Kolkata') }}</span>
                        </div>
                        <div class="schedule-picker-shell">
                            <span class="schedule-picker-icon"><i class="bi bi-calendar-event"></i></span>
                            <input class="form-control" type="datetime-local" id="webinarStartsAt" name="starts_at"
                                data-webinar-datetime-picker
                                data-min-date="{{ $webinar->starts_at && $webinar->starts_at->isPast()? $webinar->starts_at->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d'): now($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d') }}"
                                value="{{ old('starts_at',$webinar->starts_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}">
                            <button class="schedule-picker-open" type="button" data-open-picker="webinarStartsAt"
                                aria-label="Open start date and time picker"><i class="bi bi-calendar3"></i></button>
                        </div>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>The selected date and time will be
                            saved in the webinar timezone.</small>
                    </div>

                    <div class="form-field">
                        <div class="schedule-field-meta">
                            <label class="form-label-custom mb-0" for="webinarEndsAt"><span>Ends At</span></label>
                            <span class="schedule-timezone-chip" data-schedule-timezone><i class="bi bi-globe2"></i>
                                {{ old('timezone', $webinar->timezone ?: 'Asia/Kolkata') }}</span>
                        </div>
                        <div class="schedule-picker-shell">
                            <span class="schedule-picker-icon"><i class="bi bi-calendar-check"></i></span>
                            <input class="form-control" type="datetime-local" id="webinarEndsAt" name="ends_at"
                                data-webinar-datetime-picker
                                value="{{ old('ends_at',$webinar->ends_at?->copy()->timezone($webinar->timezone ?: 'Asia/Kolkata')->format('Y-m-d\TH:i')) }}">
                            <button class="schedule-picker-open" type="button" data-open-picker="webinarEndsAt"
                                aria-label="Open end date and time picker"><i class="bi bi-calendar3"></i></button>
                        </div>
                    </div>

                    <div class="form-field full">
                        <div class="alert alert-light border mb-0" id="scheduleTimezonePreview" aria-live="polite">
                            Select a start time to preview the webinar schedule in the chosen timezone.
                        </div>
                    </div>

                    <div class="form-subhead">
                        <i class="bi bi-grid-1x2"></i>
                        <strong>Room Layout Design</strong>
                        <small>Controls the attendee live-room layout</small>
                    </div>

                    <div class="choice-cards-grid">
                        <label class="choice-card">
                            <input type="radio" name="room_layout_choice" value="theater" @checked($selectedRoomLayout === 'theater')>
                            <span class="choice-card-box">
                                <i class="bi bi-aspect-ratio"></i>
                                <span class="choice-card-text"><strong>Theater Mode</strong><small>Full-screen iframe,
                                        followed by all webinar content</small></span>
                            </span>
                        </label>
                        <label class="choice-card">
                            <input type="radio" name="room_layout_choice" value="presentation"
                                @checked($selectedRoomLayout === 'presentation')>
                            <span class="choice-card-box">
                                <i class="bi bi-layout-sidebar-reverse"></i>
                                <span class="choice-card-text"><strong>Interactive Mode</strong><small>Current video +
                                        right-side interaction panel</small></span>
                            </span>
                        </label>
                    </div>
                    <select class="form-select" name="room_layout" id="roomLayout" hidden>
                        <option value="theater" @selected($selectedRoomLayout === 'theater')>Theater</option>
                        <option value="presentation" @selected($selectedRoomLayout === 'presentation')>Interactive</option>
                    </select>

                    <div class="form-field">
                        <label class="form-label-custom" for="webinarMaxAttendees">
                            <span>Maximum Attendees</span>
                        </label>
                        <input class="form-control" type="number" min="1" id="webinarMaxAttendees"
                            name="max_attendees" value="{{ old('max_attendees', $webinar->max_attendees) }}"
                            placeholder="e.g. 500 (leave blank for unlimited)">
                    </div>

                    <div class="form-field">
                        <label class="form-label-custom" for="webinarContactMobile">
                            <span>Contact Mobile Number</span>
                        </label>
                        <input class="form-control" type="tel" id="webinarContactMobile" name="contact_mobile"
                            maxlength="25"
                            value="{{ old('contact_mobile', data_get($webinar->settings, 'contact_mobile')) }}"
                            placeholder="e.g. +91 98765 43210">
                        <small class="text-muted">Shown in this webinar's public Support section.</small>
                    </div>

                    <input type="hidden" name="registration_type" id="registrationType" value="free">
                    <input type="hidden" name="price" id="webinarPrice" value="0">

                    <div class="form-subhead">
                        <i class="bi bi-card-text"></i>
                        <strong>Overview & Description</strong>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="shortDescription">
                            <span>Short Teaser Summary</span>
                        </label>
                        <textarea class="form-control" id="shortDescription" name="short_description" rows="2"
                            placeholder="One or two compelling sentences displayed on landing cards">{{ old('short_description', $webinar->short_description) }}</textarea>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="fullDescription">
                            <span>Full Description & Key Takeaways</span>
                        </label>
                        <textarea class="form-control" id="fullDescription" name="description" rows="5"
                            placeholder="Detailed event breakdown, objectives, and prerequisites">{{ old('description', $webinar->description) }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 2: STREAM & ACCESS                                  --}}
        {{-- ======================================================== --}}
        <div class="wizard-step-pane" data-step-pane="1">
            <section class="panel-card">
                <div class="panel-title">
                    <div>
                        <h3>Live Video Player & Session Resources</h3>
                        <p>Choose your streaming provider, player source, and upload downloadable materials.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-subhead"><i class="bi bi-play-circle"></i><strong>Iframe / Video Source</strong>
                    </div>
                    <div class="stream-source-studio">
                        <div class="live-embed-preview" id="liveEmbedPreview">
                            <div><i class="bi bi-play-btn"></i><span>Select a player and paste the video source to preview
                                    it.</span></div>
                        </div>
                        <div class="stream-source-controls">
                            <div class="stream-provider-buttons">
                                <label class="choice-card">
                                    <input type="radio" name="live_provider_choice" value=""
                                        @checked(!old('live_provider', $webinar->live_provider))>
                                    <span class="choice-card-box">
                                        <i class="bi bi-dash-circle"></i>
                                        <span class="choice-card-text">
                                            <strong>No Player</strong>
                                            <small>Interactive room without embed</small>
                                        </span>
                                    </span>
                                </label>
                                <label class="choice-card">
                                    <input type="radio" name="live_provider_choice" value="youtube"
                                        @checked(old('live_provider', $webinar->live_provider) === 'youtube')>
                                    <span class="choice-card-box">
                                        <i class="bi bi-youtube text-danger"></i>
                                        <span class="choice-card-text">
                                            <strong>YouTube</strong>
                                            <small>Live stream, unlisted or video ID</small>
                                        </span>
                                    </span>
                                </label>
                                <label class="choice-card">
                                    <input type="radio" name="live_provider_choice" value="vimeo"
                                        @checked(old('live_provider', $webinar->live_provider) === 'vimeo')>
                                    <span class="choice-card-box">
                                        <i class="bi bi-vimeo text-primary"></i>
                                        <span class="choice-card-text">
                                            <strong>Vimeo</strong>
                                            <small>High bitrate video embed</small>
                                        </span>
                                    </span>
                                </label>
                                <label class="choice-card">
                                    <input type="radio" name="live_provider_choice" value="custom"
                                        @checked(old('live_provider', $webinar->live_provider) === 'custom')>
                                    <span class="choice-card-box">
                                        <i class="bi bi-code-slash text-success"></i>
                                        <span class="choice-card-text">
                                            <strong>Custom Iframe</strong>
                                            <small>External player or iframe source</small>
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <!-- Hidden select for compatibility with app.js event handlers -->
                            <select class="form-select" name="live_provider" id="liveProvider" hidden>
                                <option value="">No embedded player</option>
                                <option value="youtube" @selected(old('live_provider', $webinar->live_provider) === 'youtube')>YouTube</option>
                                <option value="vimeo" @selected(old('live_provider', $webinar->live_provider) === 'vimeo')>Vimeo</option>
                                <option value="custom" @selected(old('live_provider', $webinar->live_provider) === 'custom')>Custom iframe URL</option>
                            </select>

                            <div class="form-field" id="liveSourceField">
                                <label class="form-label-custom" for="liveSource">
                                    <span>Video URL, ID, or Iframe Code</span>
                                    <span class="req" id="liveSourceRequiredMark" hidden>*</span>
                                </label>
                                <textarea class="form-control @error('live_source') is-invalid @enderror" name="live_source" id="liveSource"
                                    rows="3" placeholder="Paste YouTube/Vimeo URL, video ID, or iframe code">{{ old('live_source', $webinar->live_url) }}</textarea>
                                @error('live_source')
                                    <div class="wizard-field-error"><i class="bi bi-exclamation-circle-fill"></i>
                                        {{ $message }}</div>
                                @enderror
                                <small class="text-muted" id="liveSourceHelp">A valid video source is required when a
                                    player is selected. A secure responsive embed will be configured automatically.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-subhead">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <strong>Downloadable Session Materials</strong>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="sessionResources">
                            <span>Resource Links <span class="badge bg-light text-secondary ms-1">Optional</span></span>
                        </label>
                        <textarea class="form-control" id="sessionResources" name="session_resources" rows="3"
                            placeholder="Session guide | https://example.com/guide.pdf&#10;Presentation slides | https://example.com/slides.pdf">{{ old('session_resources', $sessionResourcesText) }}</textarea>
                        <small class="text-muted">Links attendees can open or download inside the webinar, such as slides
                            or a guide. Add one per line as <strong>Title | URL</strong>.</small>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="resourcePdfs">
                            <span>Upload PDF Documents</span>
                        </label>
                        <input class="form-control" type="file" id="resourcePdfs" name="resource_pdfs[]"
                            accept="application/pdf,.pdf" multiple>
                        <small class="text-muted">Select one or more PDF files. Uploaded PDFs appear in the Session
                            Resources kit.</small>
                    </div>

                    <div class="form-subhead">
                        <i class="bi bi-chat-square-dots"></i>
                        <strong>Attendee Engagement Features</strong>
                    </div>

                    <label class="setting-toggle">
                        <span>
                            <strong>Live Chat</strong>
                            <small>Display real-time community chat room in session.</small>
                        </span>
                        <span class="form-check form-switch setting-switch"><input class="form-check-input"
                                type="checkbox" role="switch" name="chat_enabled" value="1"
                                @checked(old('chat_enabled', $webinar->chat_enabled ?? true)) aria-label="Toggle live chat"></span>
                    </label>

                    <label class="setting-toggle">
                        <span>
                            <strong>Live Q&amp;A</strong>
                            <small>Allow attendees to ask questions and vote on questions during the session.</small>
                        </span>
                        <input type="hidden" name="qa_enabled" value="0">
                        <span class="form-check form-switch setting-switch"><input class="form-check-input"
                                type="checkbox" role="switch" name="qa_enabled" value="1"
                                @checked(old('qa_enabled', $webinar->qa_enabled ?? true)) aria-label="Toggle live Q and A"></span>
                    </label>

                    <label class="setting-toggle">
                        <span>
                            <strong>Private Comments</strong>
                            <small>Allow attendees to send private messages directly to the host.</small>
                        </span>
                        <span class="form-check form-switch setting-switch"><input class="form-check-input"
                                type="checkbox" role="switch" name="comments_enabled" value="1"
                                @checked(old('comments_enabled', $webinar->comments_enabled ?? true)) aria-label="Toggle private comments"></span>
                    </label>

                    <label class="setting-toggle">
                        <span>
                            <strong>Attendee Feedback</strong>
                            <small>Collect star ratings and reviews in the Feedback module.</small>
                        </span>
                        <span class="form-check form-switch setting-switch"><input class="form-check-input"
                                type="checkbox" role="switch" name="feedback_enabled" value="1"
                                @checked(old('feedback_enabled', $webinar->feedback_enabled ?? false)) aria-label="Toggle attendee feedback"></span>
                    </label>

                    <label class="setting-toggle">
                        <span>
                            <strong>Live Polls & Quizzes</strong>
                            <small>Enable interactive audience polls & quizzes. Adds Step 6 for configuring
                                questions.</small>
                        </span>
                        <input type="hidden" name="polls_enabled" value="0">
                        <span class="form-check form-switch setting-switch"><input class="form-check-input"
                                type="checkbox" role="switch" name="polls_enabled" id="togglePollsEnabled"
                                value="1" @checked(old('polls_enabled', (bool) ($webinar->polls_enabled ?? false)))
                                aria-label="Toggle live polls and quizzes"></span>
                    </label>

                    <label class="setting-toggle">
                        <span>
                            <strong>Attendee Certificate</strong>
                            <small>Provide certificate upon meeting attendance rules. Adds dedicated Step for certificate
                                template & rules.</small>
                        </span>
                        <input type="hidden" name="certificate_enabled" value="0">
                        <span class="form-check form-switch setting-switch"><input class="form-check-input"
                                type="checkbox" role="switch" name="certificate_enabled" id="toggleCertificateEnabled"
                                value="1" @checked(old('certificate_enabled', ($webinar->certificate_enabled ?? 'no') === 'yes'))
                                aria-label="Toggle attendee certificate"></span>
                    </label>

                    <div class="form-subhead">
                        <i class="bi bi-chat-left-quote"></i>
                        <strong>Attendee &amp; Registration Messages</strong>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="waitingMessage"><span>Waiting Room Greeting
                                Message</span></label>
                        <textarea class="form-control" id="waitingMessage" name="waiting_message" rows="2">{{ old('waiting_message', $experience['waiting_message'] ?? 'The session will begin shortly. You are in the right place.') }}</textarea>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="postMessage"><span>Post-Webinar Farewell
                                Message</span></label>
                        <textarea class="form-control" id="postMessage" name="post_message" rows="2">{{ old('post_message', $experience['post_message'] ?? 'Thank you for attending. Please share your feedback.') }}</textarea>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="regSuccessTitle"><span>Registration Success
                                Title</span></label>
                        <input class="form-control" id="regSuccessTitle" name="registration_success_title"
                            value="{{ old('registration_success_title', $experience['registration_success_title'] ?? 'You are registered!') }}">
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom" for="regSuccessMessage"><span>Registration Confirmation
                                Message</span></label>
                        <textarea class="form-control" id="regSuccessMessage" name="registration_success_message" rows="2">{{ old('registration_success_message', $experience['registration_success_message'] ?? 'Your seat is confirmed. Add the webinar to your calendar and return when the room opens.') }}</textarea>
                    </div>

                </div>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 3: BRANDING & THEME                                 --}}
        {{-- ======================================================== --}}
        <div class="wizard-step-pane" data-step-pane="2">
            <section class="panel-card">
                <div class="panel-title">
                    <div>
                        <h3>Brand, Banners & Speakers</h3>
                        <p>Manage the client identity, landing banner, presenters, and attendee-room theme in one place.</p>
                    </div>
                    <span class="status-badge scheduled"><i class="bi bi-stars"></i> Live studio</span>
                </div>

                <div class="form-grid">
                    <input type="hidden" name="branding_assets_present" value="1">

                    <div class="form-subhead"><i class="bi bi-building"></i><strong>Client Identity</strong></div>
                    <div class="form-field full">
                        <div class="media-config-card">
                            <div class="media-config-grid">
                                <div class="media-config-fields">
                                    <label class="form-label-custom" for="brandLogoFile"><span>Client / Webinar
                                            Logo</span></label>
                                    <input class="form-control @error('brand_logo_file') is-invalid @enderror"
                                        type="file" id="brandLogoFile" name="brand_logo_file"
                                        accept="image/png,image/jpeg,image/webp,image/svg+xml">
                                    @error('brand_logo_file')
                                        <div class="wizard-field-error"><i class="bi bi-exclamation-circle-fill"></i>
                                            {{ $message }}</div>
                                    @enderror
                                    <p class="media-config-help"><i class="bi bi-info-circle me-1"></i>Shown on the
                                        landing page and attendee room. PNG, JPG, SVG or WebP, up to 5 MB.</p>
                                </div>
                                <div class="media-preview-pane">
                                    <label class="form-label-custom"><span>Logo Preview</span></label>
                                    <div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden"
                                        data-logo-preview data-media-preview
                                        @if (!empty($experience['logo_url'])) data-preview-ready="1" @endif>
                                        @if (!empty($experience['logo_url']))
                                            <img src="{{ $experience['logo_url'] }}" alt="Current client logo"
                                                style="width:100%;height:100%;object-fit:contain;padding:24px;background:#fff;">
                                        @else
                                            <div class="media-preview-empty"><i class="bi bi-building"></i><strong>No logo
                                                    selected</strong><small>Choose a client or webinar logo to preview it
                                                    here.</small></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-subhead"><i class="bi bi-patch-check"></i><strong>Landing Page Brands</strong></div>
                    @php
                        $brandRows = old('brands');
                        if ($brandRows === null) {
                            $brandRows = ($webinarBrands ?? collect())
                                ->map(
                                    fn($brand) => [
                                        'id' => $brand->id,
                                        'name' => $brand->name,
                                        'logo_url' => str_starts_with((string) $brand->logo_path, 'http')
                                            ? $brand->logo_path
                                            : '',
                                        'website_url' => $brand->website_url,
                                        'is_active' => $brand->is_active ? 1 : 0,
                                    ],
                                )
                                ->values()
                                ->all();
                            if (empty($brandRows)) {
                                $brandRows[] = [
                                    'id' => null,
                                    'name' => '',
                                    'logo_url' => '',
                                    'website_url' => '',
                                    'is_active' => 1,
                                ];
                            }
                        }
                    @endphp
                    <div class="form-field full">
                        <div id="brandBuilderRows" class="row g-3">
                            @foreach ($brandRows as $brandIndex => $brandRow)
                                @php
                                    $savedBrand = !empty($brandRow['id'])
                                        ? ($webinarBrands ?? collect())->firstWhere('id', (int) $brandRow['id'])
                                        : null;
                                    $brandLogoPath = $savedBrand?->logo_path;
                                @endphp
                                <div class="col-12 sortable-card" data-brand-row>
                                    <div class="media-config-card">
                                        <div class="media-config-grid">
                                            <div class="media-config-fields">
                                                <div class="row g-3">
                                                    <div class="col-12 builder-card-toolbar">
                                                        @if (!empty($brandRow['id']))
                                                            <input type="checkbox" class="visually-hidden"
                                                                name="remove_brand_ids[]" value="{{ $brandRow['id'] }}"
                                                                data-remove-brand-checkbox><button
                                                                class="btn btn-outline-danger btn-sm builder-remove"
                                                                type="button" data-remove-saved-brand><i
                                                                    class="bi bi-trash3 me-1"></i>Remove
                                                            brand</button>@else<button
                                                                class="btn btn-outline-danger btn-sm builder-remove"
                                                                type="button" data-remove-brand
                                                                @if ($loop->first && count($brandRows) === 1) hidden @endif><i
                                                                    class="bi bi-trash3 me-1"></i>Remove brand</button>
                                                        @endif
                                                    </div>
                                                    @if (!empty($brandRow['id']))
                                                        <input type="hidden" name="brands[{{ $brandIndex }}][id]"
                                                            value="{{ $brandRow['id'] }}">
                                                    @endif
                                                    <div class="col-12"><label class="form-label-custom"><span>Brand
                                                                name</span><span class="req">*</span></label><input
                                                            class="form-control @error('brands.' . $brandIndex . '.name') is-invalid @enderror"
                                                            name="brands[{{ $brandIndex }}][name]"
                                                            value="{{ $brandRow['name'] ?? '' }}" maxlength="255"
                                                            placeholder="e.g. Acme Corp" data-brand-name>
                                                        <div class="wizard-field-error @unless ($errors->has('brands.' . $brandIndex . '.name')) d-none @endunless"
                                                            data-brand-name-error><i
                                                                class="bi bi-exclamation-circle-fill"></i>
                                                            {{ $errors->first('brands.' . $brandIndex . '.name') ?: 'Brand name is required when adding a brand.' }}
                                                        </div>
                                                    </div>
                                                    <div class="col-12"><label
                                                            class="form-label-custom"><span>{{ $brandLogoPath ? 'Replace brand logo' : 'Upload brand logo' }}</span></label><input
                                                            class="form-control" type="file"
                                                            name="brand_logos[{{ $brandIndex }}]"
                                                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                                            data-brand-logo></div>
                                                    <div class="col-12"><label class="form-label-custom"><span>Or image
                                                                URL</span></label><input class="form-control"
                                                            type="url" name="brands[{{ $brandIndex }}][logo_url]"
                                                            value="{{ $brandRow['logo_url'] ?? '' }}"
                                                            placeholder="https://example.com/brand-logo.png"
                                                            data-brand-logo-url></div>
                                                    <div class="col-12"><label class="form-label-custom"><span>Website URL
                                                                (optional)
                                                            </span></label><input class="form-control" type="url"
                                                            name="brands[{{ $brandIndex }}][website_url]"
                                                            value="{{ $brandRow['website_url'] ?? '' }}"
                                                            placeholder="https://example.com"></div>
                                                </div>
                                            </div>
                                            <div class="media-preview-pane"><label class="form-label-custom"><span>Brand
                                                        Logo Preview</span></label>
                                                <div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden"
                                                    data-brand-preview data-media-preview
                                                    @if ($brandLogoPath) data-preview-ready="1" @endif>
                                                    @if ($brandLogoPath)
                                                        <img src="{{ $brandLogoPath }}"
                                                            alt="{{ $brandRow['name'] ?: 'Brand logo' }}"
                                                            style="width:100%;height:100%;object-fit:contain;padding:24px;background:#fff">
                                                    @else
                                                        <div class="media-preview-empty"><i
                                                                class="bi bi-patch-check"></i><strong>No brand logo
                                                                selected</strong><small>Upload the logo displayed in the
                                                                landing page Brands section.</small></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-outline-danger mt-3" type="button" id="addBrandRow"><i
                                class="bi bi-plus-lg me-1"></i>Add another brand</button>
                        <div class="duplicate-alert" id="brandDuplicateAlert"><i
                                class="bi bi-exclamation-triangle-fill"></i>A brand with the same name or logo source has
                            already been added.</div>
                        <small class="text-muted d-block mt-2">These partner or sponsor brands are displayed in the Brands
                            section on the webinar landing page.</small>
                    </div>

                    <div class="form-field full">
                        <label class="form-label-custom"><span>Brand display style</span></label>
                        <div class="brand-style-options w-100">
                            @php
                                $selectedBrandStyle = old(
                                    'brand_display_style',
                                    data_get($webinar->settings, 'experience.brand_display_style', 'cards'),
                                );
                                $selectedBrandStyle = in_array($selectedBrandStyle, ['cards', 'monochrome'], true)
                                    ? $selectedBrandStyle
                                    : 'cards';
                            @endphp
                            @foreach (['cards' => ['Cards', 'Premium individual logo cards'], 'monochrome' => ['Monochrome', 'Elegant grayscale logos with color on hover']] as $styleKey => $styleCopy)
                                <label class="brand-style-option"><input type="radio" name="brand_display_style"
                                        value="{{ $styleKey }}"
                                        @checked($selectedBrandStyle === $styleKey)><span><strong>{{ $styleCopy[0] }}</strong><small>{{ $styleCopy[1] }}</small></span></label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-subhead"><i class="bi bi-images"></i><strong>Landing Banners</strong></div>
                    @php
                        $newBannerRows = old('banners');
                        if ($newBannerRows === null) {
                            $newBannerRows = ($webinarBanners ?? collect())
                                ->map(
                                    fn($banner) => [
                                        'id' => $banner->id,
                                        'title' => $banner->title,
                                        'media_type' => $banner->media_type,
                                        'media_url' => $banner->media_url,
                                        'preview_src' => $banner->media_path ?: $banner->media_url,
                                    ],
                                )
                                ->values()
                                ->all();
                            if (empty($newBannerRows)) {
                                $newBannerRows[] = [
                                    'id' => null,
                                    'title' => '',
                                    'media_type' => 'image',
                                    'media_url' => '',
                                    'preview_src' => '',
                                ];
                            }
                        }
                    @endphp
                    <div class="form-field full">
                        <div id="bannerBuilderRows" class="row g-3">
                            @foreach ($newBannerRows as $bannerIndex => $newBanner)
                                <div class="col-12 sortable-card" data-banner-row>
                                    <div class="media-config-card">
                                        <div class="media-config-grid">
                                            <div class="media-config-fields">
                                                <div class="row g-3 align-items-end">
                                                    <div class="col-12 builder-card-toolbar">
                                                        @if (!empty($newBanner['id']))
                                                            <input type="checkbox" class="visually-hidden"
                                                                name="remove_banner_ids[]" value="{{ $newBanner['id'] }}"
                                                                data-remove-banner-checkbox><button
                                                                class="btn btn-outline-danger btn-sm builder-remove"
                                                                type="button" data-remove-saved-banner><i
                                                                    class="bi bi-trash3 me-1"></i>Remove
                                                            banner</button>@else<button
                                                                class="btn btn-outline-danger btn-sm builder-remove"
                                                                type="button" data-remove-banner
                                                                @if ($loop->first && count($newBannerRows) === 1) hidden @endif><i
                                                                    class="bi bi-trash3 me-1"></i>Remove banner</button>
                                                        @endif
                                                    </div>
                                                    @if (!empty($newBanner['id']))
                                                        <input type="hidden" name="banners[{{ $bannerIndex }}][id]"
                                                            value="{{ $newBanner['id'] }}">
                                                    @endif
                                                    <div class="col-md-6"><label class="form-label-custom"><span>Banner
                                                                title</span></label><input class="form-control"
                                                            name="banners[{{ $bannerIndex }}][title]"
                                                            value="{{ $newBanner['title'] ?? '' }}" maxlength="255"
                                                            placeholder="Landing banner"></div>
                                                    <div class="col-md-6"><label class="form-label-custom"><span>Media
                                                                type</span></label><select class="form-select"
                                                            name="banners[{{ $bannerIndex }}][media_type]"
                                                            data-banner-type>
                                                            <option value="image" @selected(($newBanner['media_type'] ?? 'image') === 'image')>Image
                                                            </option>
                                                            <option value="video" @selected(($newBanner['media_type'] ?? '') === 'video')>Video
                                                            </option>
                                                        </select></div>
                                                    <div class="col-12"><label class="form-label-custom"><span
                                                                data-banner-file-label>Upload image</span></label><input
                                                            class="form-control @error('banner_media.' . $bannerIndex) is-invalid @enderror"
                                                            type="file" name="banner_media[{{ $bannerIndex }}]"
                                                            data-banner-file>
                                                        <div class="inline-file-error" data-banner-file-error></div>
                                                        @error('banner_media.' . $bannerIndex)
                                                            <div class="wizard-field-error"><i
                                                                    class="bi bi-exclamation-circle-fill"></i>
                                                                {{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12"><label class="form-label-custom"><span
                                                                data-banner-url-label>Or image URL</span></label><input
                                                            class="form-control @error('banners.' . $bannerIndex . '.media_url') is-invalid @enderror"
                                                            type="url" name="banners[{{ $bannerIndex }}][media_url]"
                                                            value="{{ $newBanner['media_url'] ?? '' }}" data-banner-url
                                                            placeholder="https://example.com/banner.jpg">
                                                        @error('banners.' . $bannerIndex . '.media_url')
                                                            <div class="wizard-field-error"><i
                                                                    class="bi bi-exclamation-circle-fill"></i>
                                                                {{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="media-preview-pane"><label class="form-label-custom"><span>Banner
                                                        Preview</span></label>
                                                <div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden"
                                                    data-banner-preview data-media-preview
                                                    @if (!empty($newBanner['preview_src'])) data-initial-src="{{ $newBanner['preview_src'] }}" data-preview-ready="1" @endif>
                                                    <div class="media-preview-empty"><i class="bi bi-image"></i><strong>No
                                                            banner selected</strong><small>Upload an image or video to see
                                                            the preview.</small></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-outline-danger mt-3" type="button" id="addBannerRow"><i
                                class="bi bi-plus-lg me-1"></i> Add another banner</button>
                        <div class="duplicate-alert" id="bannerDuplicateAlert"><i
                                class="bi bi-exclamation-triangle-fill"></i>A banner with the same title or media URL has
                            already been added.</div>
                        <small class="text-muted d-block mt-2">Multiple image and video banners are supported. Images: JPG,
                            PNG, WebP. Videos: MP4, WebM, MOV. Maximum 10 MB each.</small>
                    </div>

                    <div class="form-subhead"><i class="bi bi-image"></i><strong>No Player / Waiting Room
                            Background</strong></div>
                    <div class="form-field full">
                        <div class="media-config-card">
                            <div class="media-config-grid">
                                <div class="media-config-fields">
                                    <label class="form-label-custom" for="waitingMediaFile"><span>Waiting Room Background
                                            Image</span></label>
                                    <input class="form-control" type="file" id="waitingMediaFile"
                                        name="waiting_media_file" accept="image/png,image/jpeg,image/webp">
                                    <p class="media-config-help"><i class="bi bi-info-circle me-1"></i>This image appears
                                        in the webinar room when No Player mode is selected. PNG, JPG or WebP, up to 8 MB.
                                    </p>
                                </div>
                                <div class="media-preview-pane">
                                    <label class="form-label-custom"><span>Background Preview</span></label>
                                    <div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden"
                                        data-waiting-preview data-media-preview
                                        @if (!empty($experience['waiting_media_url'])) data-preview-ready="1" @endif>
                                        @if (!empty($experience['waiting_media_url']))
                                            <img src="{{ $experience['waiting_media_url'] }}"
                                                alt="Current waiting room background"
                                            style="width:100%;height:100%;object-fit:contain">@else<div
                                                class="media-preview-empty"><i class="bi bi-image"></i><strong>No
                                                    background selected</strong><small>Choose a waiting-room image to
                                                    preview it here.</small></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-subhead"><i class="bi bi-mic"></i><strong>Speakers</strong></div>
                    @php
                        $speakerRows = old('speakers');
                        if ($speakerRows === null) {
                            $speakerRows = $webinar->speakers
                                ->map(
                                    fn($speaker) => [
                                        'id' => $speaker->id,
                                        'name' => $speaker->name,
                                        'headline' => $speaker->headline,
                                        'company' => $speaker->company,
                                        'preview_src' => $speaker->photo_path,
                                    ],
                                )
                                ->values()
                                ->all();
                            if (empty($speakerRows)) {
                                $speakerRows[] = [
                                    'id' => null,
                                    'name' => '',
                                    'headline' => '',
                                    'company' => '',
                                    'preview_src' => '',
                                ];
                            }
                        }
                    @endphp
                    <div class="form-field full">
                        <div id="speakerBuilderRows" class="row g-3">
                            @foreach ($speakerRows as $speakerIndex => $speakerRow)
                                <div class="col-12 sortable-card" data-speaker-row>
                                    <div class="media-config-card">
                                        <div class="media-config-grid">
                                            <div class="media-config-fields">
                                                <div class="row g-3">
                                                    <div class="col-12 builder-card-toolbar"><button
                                                            class="btn btn-outline-danger btn-sm builder-remove"
                                                            type="button" data-remove-speaker
                                                            @if ($loop->first && count($speakerRows) === 1) hidden @endif><i
                                                                class="bi bi-trash3 me-1"></i>Remove speaker</button></div>
                                                    @if (!empty($speakerRow['id']))
                                                        <input type="hidden" name="speakers[{{ $speakerIndex }}][id]"
                                                            value="{{ $speakerRow['id'] }}">
                                                    @endif
                                                    <div class="col-12"><label class="form-label-custom"><span>Speaker
                                                                name</span><span class="req">*</span></label><input
                                                            class="form-control"
                                                            name="speakers[{{ $speakerIndex }}][name]"
                                                            value="{{ $speakerRow['name'] ?? '' }}" maxlength="255"
                                                            placeholder="e.g. Dr. A. Sharma" required></div>
                                                    <div class="col-md-6"><label class="form-label-custom"><span>Headline
                                                                / Role</span></label><input class="form-control"
                                                            name="speakers[{{ $speakerIndex }}][headline]"
                                                            value="{{ $speakerRow['headline'] ?? '' }}" maxlength="255"
                                                            placeholder="Keynote speaker"></div>
                                                    <div class="col-md-6"><label
                                                            class="form-label-custom"><span>Company</span></label><input
                                                            class="form-control"
                                                            name="speakers[{{ $speakerIndex }}][company]"
                                                            value="{{ $speakerRow['company'] ?? '' }}" maxlength="255">
                                                    </div>
                                                    <div class="col-12"><label
                                                            class="form-label-custom"><span>{{ !empty($speakerRow['preview_src']) ? 'Replace speaker photo' : 'Upload speaker photo' }}</span></label><input
                                                            class="form-control" type="file"
                                                            name="speaker_photos[{{ $speakerIndex }}]"
                                                            accept="image/png,image/jpeg,image/webp" data-speaker-file>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="media-preview-pane"><label class="form-label-custom"><span>Speaker
                                                        Preview</span></label>
                                                <div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden"
                                                    data-speaker-preview data-media-preview
                                                    @if (!empty($speakerRow['preview_src'])) data-preview-ready="1" @endif>
                                                    @if (!empty($speakerRow['preview_src']))
                                                        <img src="{{ $speakerRow['preview_src'] }}"
                                                            alt="{{ $speakerRow['name'] ?: 'Speaker' }}"
                                                            style="width:100%;height:100%;object-fit:contain;background:#fff">
                                                    @else
                                                        <div class="media-preview-empty"><i
                                                                class="bi bi-person"></i><strong>No speaker photo
                                                                selected</strong><small>Upload a photo to preview it
                                                                here.</small></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-outline-danger mt-3" type="button" id="addSpeakerRow"><i
                                class="bi bi-plus-lg me-1"></i>Add another speaker</button>
                    </div>

                    <div class="form-subhead"><i class="bi bi-palette2"></i><strong>Theme Colors & Preview</strong></div>
                    <div class="brand-studio-grid">
                        <section class="brand-color-launcher" aria-label="Theme Color Studio">
                            <strong><i class="bi bi-palette2 me-2"></i>Theme Color Studio</strong>
                            <small>Click a color to open the Photoshop-style picker.</small>
                            <input type="hidden" name="brand_primary" id="brandPrimary"
                                value="{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}">
                            <input type="hidden" name="brand_secondary" id="brandSecondary"
                                value="{{ old('brand_secondary', $experience['secondary'] ?? '#2563eb') }}">
                            <div class="brand-color-buttons">
                                <button type="button" class="brand-color-open" data-color-open="primary"><i
                                        data-color-swatch="primary"></i><span><strong>Primary Brand</strong><code
                                            data-color-code="primary">{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}</code></span><i
                                        class="bi bi-chevron-right"></i></button>
                                <button type="button" class="brand-color-open" data-color-open="secondary"><i
                                        data-color-swatch="secondary"></i><span><strong>Accent Glow</strong><code
                                            data-color-code="secondary">{{ old('brand_secondary', $experience['secondary'] ?? '#2563eb') }}</code></span><i
                                        class="bi bi-chevron-right"></i></button>
                            </div>
                        </section>
                        <section class="brand-live-preview">
                            <header><strong><i class="bi bi-window me-2"></i>Attendee Room Preview</strong><span>LIVE
                                    PREVIEW</span></header>
                            <iframe class="brand-preview-frame" id="experiencePreviewFrame"
                                title="Attendee room color preview"
                                data-preview-title="{{ $webinar->title ?: 'Your webinar title' }}"></iframe>
                        </section>
                    </div>

                </div>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 4: AGENDA & CONTENT                                 --}}
        {{-- ======================================================== --}}
        <div class="wizard-step-pane" data-step-pane="2">
            <section class="panel-card mb-4">
                <div class="panel-title">
                    <div>
                        <h3>Session Timeline & Agenda</h3>
                        <p>Schedule your keynote, topics, guest sessions, and breaks.</p>
                    </div>
                    <button class="btn btn-sm btn-light" type="button" id="addAgendaItem"><i
                            class="bi bi-plus-lg me-1"></i> Add Agenda Session</button>
                </div>

                <div class="full agenda-builder">
                    <input type="hidden" name="agenda_present" value="1">
                    <?php
                    $agendaRows = collect(old('agenda', ($agendaItems ?? collect())->map(fn($item) => ['starts_at' => !empty($item->starts_at) ? substr($item->starts_at, 0, 5) : '', 'title' => $item->title ?? '', 'duration_minutes' => $item->duration_minutes ?? 30])->all()));
                    if ($agendaRows->isEmpty()) {
                        $agendaRows = collect([['starts_at' => '', 'title' => '', 'duration_minutes' => 30]]);
                    }
                    ?>
                    <div class="agenda-builder-rows" id="agendaBuilderRows">
                        @foreach ($agendaRows as $index => $item)
                            <div class="agenda-builder-row p-3 mb-2 bg-light border rounded-3 d-flex align-items-center gap-3"
                                data-agenda-row>
                                <div style="width:130px;">
                                    <label class="form-label-custom small mb-1"><span>Start Time</span></label>
                                    <input class="form-control form-control-sm" type="time"
                                        name="agenda[{{ $index }}][starts_at]"
                                        value="{{ $item['starts_at'] ?? '' }}">
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label-custom small mb-1"><span>Session Title</span></label>
                                    <input class="form-control form-control-sm" name="agenda[{{ $index }}][title]"
                                        value="{{ $item['title'] ?? '' }}" maxlength="255"
                                        placeholder="e.g. Welcome & Keynote Speech">
                                </div>
                                <div style="width:110px;">
                                    <label class="form-label-custom small mb-1"><span>Minutes</span></label>
                                    <input class="form-control form-control-sm" type="number" min="1"
                                        max="1440" name="agenda[{{ $index }}][duration_minutes]"
                                        value="{{ $item['duration_minutes'] ?? '' }}" placeholder="30">
                                </div>
                                <div class="pt-4">
                                    <button class="btn btn-sm btn-outline-danger" type="button" data-delete-agenda
                                        title="Delete agenda item"><i class="bi bi-trash3"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="panel-card">
                <div class="panel-title">
                    <div>
                        <h3>Session Agenda Details</h3>
                        <p>Add free-text agenda details that attendees will see on the frontend.</p>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field full">
                        <label class="form-label-custom" for="agendaNotes">
                            <span>Agenda Details</span>
                        </label>
                        <textarea class="form-control" id="agendaNotes" name="agenda_notes" rows="5"
                            placeholder="Add session highlights, topics, speaker notes, breaks, or any other agenda information.">{{ old('agenda_notes',$experience['agenda_notes'] ??collect($experience['chapters'] ?? [])->map(fn($item) => trim(($item['time'] ?? '') . ' ' . ($item['title'] ?? '')))->filter()->join("\n")) }}</textarea>
                        <small class="text-muted">Free text is allowed. Line breaks will be preserved on the
                            frontend.</small>
                    </div>
                </div>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 5: DYNAMIC REGISTRATION FIELDS                      --}}
        {{-- ======================================================== --}}
        <div class="wizard-step-pane" data-step-pane="3">
            <section class="panel-card">
                <div class="panel-title">
                    <div>
                        <h3>Dynamic Registration Fields</h3>
                        <p>Build the questions shown on this webinar's registration page.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light" id="addDynamicField"><i
                            class="bi bi-plus-lg me-1"></i> Add Field</button>
                </div>
                <div class="form-grid mb-4">
                    <input type="hidden" name="registration_enabled" value="0">
                    <label class="setting-toggle full"><span><strong>Enable Registration Form</strong><small>Collect these
                                fields before giving the attendee access.</small></span><input type="checkbox"
                            name="registration_enabled" value="1" @checked(old('registration_enabled', $webinar->registrationForm?->is_active ?? true))></label>
                    <label class="form-field"><span class="form-label-custom">Form Title</span><input
                            class="form-control" name="form_title"
                            value="{{ old('form_title', $webinar->registrationForm?->title ?? 'Register for this webinar') }}"></label>
                    <label class="form-field"><span class="form-label-custom">Success Message</span><input
                            class="form-control" name="success_message"
                            value="{{ old('success_message', $webinar->registrationForm?->success_message ?? 'Your registration is confirmed.') }}"></label>
                </div>
                @php
                    $dynamicRows = collect(
                        old(
                            'fields',
                            $webinar->registrationForm?->fields
                                ?->map(
                                    fn($field) => [
                                        'id' => $field->id,
                                        'label' => $field->label,
                                        'field_type' => $field->field_type,
                                        'placeholder' => $field->placeholder,
                                        'is_required' => $field->is_required ? '1' : null,
                                        'is_enabled' => $field->is_enabled ? '1' : null,
                                        'options' => $field->options->pluck('label')->join("\n"),
                                    ],
                                )
                                ->all() ?? [],
                        ),
                    );
                    if ($dynamicRows->isEmpty()) {
                        $dynamicRows = collect([
                            [
                                'label' => 'Full name',
                                'field_type' => 'text',
                                'placeholder' => 'Enter your full name',
                                'is_required' => '1',
                                'is_enabled' => '1',
                            ],
                            [
                                'label' => 'Email address',
                                'field_type' => 'text',
                                'placeholder' => 'you@example.com',
                                'is_required' => '1',
                                'is_enabled' => '1',
                            ],
                            [
                                'label' => 'Organization',
                                'field_type' => 'text',
                                'placeholder' => 'Company or institution',
                                'is_enabled' => '1',
                            ],
                        ]);
                    }
                @endphp
                <div id="dynamicFieldsList" class="d-flex flex-column gap-3">
                    @foreach ($dynamicRows as $index => $field)
                        <div class="p-3 border rounded-3 bg-light" data-dynamic-field>
                            @if (!empty($field['id']))
                                <input type="hidden" data-field-name="id" name="fields[{{ $index }}][id]"
                                    value="{{ $field['id'] }}">
                            @endif
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-3"><label class="form-label">Field label</label><input
                                        class="form-control" data-field-name="label"
                                        name="fields[{{ $index }}][label]" value="{{ $field['label'] ?? '' }}"
                                        placeholder="e.g. Department"></div>
                                <div class="col-lg-2"><label class="form-label">Type</label><select class="form-select"
                                        data-field-name="field_type" name="fields[{{ $index }}][field_type]">
                                        @foreach (['text' => 'Text', 'dropdown' => 'Dropdown', 'radio' => 'Radio', 'checkbox' => 'Checkboxes', 'country' => 'Country', 'state' => 'State', 'city' => 'City'] as $value => $label)
                                            <option value="{{ $value }}" @selected(($field['field_type'] ?? 'text') === $value)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3"><label class="form-label">Placeholder</label><input
                                        class="form-control" data-field-name="placeholder"
                                        name="fields[{{ $index }}][placeholder]"
                                        value="{{ $field['placeholder'] ?? '' }}"></div>
                                <div class="col-lg-3 dynamic-field-switches"><label
                                        class="dynamic-field-toggle"><span>Required</span><span
                                            class="form-check form-switch"><input class="form-check-input"
                                                type="checkbox" role="switch" data-field-name="is_required"
                                                name="fields[{{ $index }}][is_required]" value="1"
                                                @checked(!empty($field['is_required']))
                                                aria-label="Toggle required field"></span></label><label
                                        class="dynamic-field-toggle"><span>Active</span><span
                                            class="form-check form-switch"><input class="form-check-input"
                                                type="checkbox" role="switch" data-field-name="is_enabled"
                                                name="fields[{{ $index }}][is_enabled]" value="1"
                                                @checked(!empty($field['is_enabled']))
                                                aria-label="Toggle active field"></span></label></div>
                                <div class="col-lg-1 text-end"><button type="button" class="btn btn-outline-danger"
                                        data-remove-dynamic-field title="Remove field"><i
                                            class="bi bi-trash3"></i></button></div>
                                <div class="col-12" data-field-options @if (!in_array($field['field_type'] ?? 'text', ['dropdown', 'radio', 'checkbox'], true)) hidden @endif>
                                    <label class="form-label">Options <small class="text-muted">(one per line; used by
                                            dropdown, radio and checkbox)</small></label>
                                    <textarea class="form-control" rows="2" data-field-name="options"
                                        name="fields[{{ $index }}][options]">{{ $field['options'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 5 / POLLS & QUIZZES                                 --}}
        {{-- ======================================================== --}}
        <div class="wizard-step-pane" data-step-pane="3" data-feature-pane="poll">
            @if (isset($existingPolls) && $existingPolls->isNotEmpty())
                <section class="panel-card mb-4">
                    <div class="panel-title">
                        <div>
                            <h3>Configured Polls for this Webinar</h3>
                            <p>Polls already linked to this webinar room.</p>
                        </div>
                        <span
                            class="badge bg-purple-subtle text-purple fs-6 px-3 py-2 rounded-pill">{{ $existingPolls->count() }}
                            Active/Saved</span>
                    </div>
                    <div class="row g-3">
                        @foreach ($existingPolls as $p)
                            <div class="col-md-6">
                                <div
                                    class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span
                                                class="badge {{ $p->status === 'active' ? 'bg-success' : 'bg-secondary' }} text-uppercase">{{ $p->status }}</span>
                                            <small class="text-muted">{{ $p->options->count() }} options</small>
                                        </div>
                                        <strong class="d-block text-dark fs-6 mb-2">{{ $p->question }}</strong>
                                        <ul class="list-unstyled mb-0 small text-muted">
                                            @foreach ($p->options as $opt)
                                                <li class="d-flex align-items-center gap-1 mb-1">
                                                    <i
                                                        class="bi {{ $opt->is_correct ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                                                    <span>{{ $opt->label }}</span>
                                                    @if ($opt->is_correct)
                                                        <span
                                                            class="badge bg-success-subtle text-success ms-auto small">Correct</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="mt-3 pt-2 border-top d-flex justify-content-end">
                                        <a href="{{ route('admin.polls.edit', $p) }}"
                                            class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit
                                            in Polls Manager</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="panel-card">
                <div class="panel-title">
                    <div>
                        <h3>Add Live Polls &amp; Quizzes</h3>
                        <p>Create one or more questions for this webinar. Blank poll cards are ignored.</p>
                    </div>
                </div>
                @php($newPollRows = old('new_polls', [['question' => '', 'answers' => ['', '', '', ''], 'correct_index' => '', 'answer_reveal' => 'after_webinar', 'status' => 'draft', 'allow_multiple' => '0', 'started_at' => '', 'ended_at' => '']]))
                <div id="wizardPollsList" class="d-flex flex-column gap-3">
                    @foreach ($newPollRows as $pollIndex => $pollRow)
                        @php($wizardAnswers = $pollRow['answers'] ?? ['', '', '', ''])
                        <div class="wizard-poll-card border rounded-4 p-3 p-lg-4 bg-light" data-wizard-poll>
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div><span class="badge bg-danger-subtle text-danger mb-1" data-poll-number>Poll
                                        {{ $pollIndex + 1 }}</span><strong class="d-block">Question and answers</strong>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-wizard-poll><i
                                        class="bi bi-trash3 me-1"></i> Remove poll</button>
                            </div>
                            <div class="form-field mb-3"><label class="form-label-custom"><span>Poll / Quiz
                                        Question</span></label><input class="form-control form-control-lg"
                                    data-poll-field="question" name="new_polls[{{ $pollIndex }}][question]"
                                    value="{{ $pollRow['question'] ?? '' }}"
                                    placeholder="e.g. Which technology stack does your team primarily use?"></div>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <div><strong>Answer options</strong><small class="d-block text-muted">Enter at least two
                                        options.</small></div><button type="button" class="btn btn-sm btn-light"
                                    data-add-poll-option><i class="bi bi-plus-lg me-1"></i> Add option</button>
                            </div>
                            <div class="d-flex flex-column gap-2" data-poll-options>
                                @foreach ($wizardAnswers as $idx => $answer)
                                    <div class="wizard-poll-row" data-option-row><span
                                            class="badge bg-secondary-subtle text-secondary fw-bold flex-none"
                                            style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;">{{ chr(65 + $idx) }}</span><input
                                            class="form-control" data-poll-answer
                                            name="new_polls[{{ $pollIndex }}][answers][]"
                                            value="{{ $answer }}"
                                            placeholder="Option {{ chr(65 + $idx) }} text"><button type="button"
                                            class="btn btn-sm btn-outline-danger" data-remove-poll-option
                                            title="Delete option"><i class="bi bi-trash3"></i></button></div>
                                @endforeach
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6"><label class="form-label-custom"><span>Correct
                                            answer</span></label><select class="form-select"
                                        data-poll-field="correct_index" data-poll-correct
                                        name="new_polls[{{ $pollIndex }}][correct_index]">
                                        <option value="">No correct answer (standard poll)</option>
                                        @foreach ($wizardAnswers as $idx => $answer)
                                            <option value="{{ $idx }}" @selected((string) ($pollRow['correct_index'] ?? '') === (string) $idx)>Option
                                                {{ chr(65 + $idx) }}{{ filled($answer) ? ' — ' . $answer : '' }}
                                            </option>
                                        @endforeach
                                    </select></div>
                                <div class="col-md-6"><label class="form-label-custom"><span>Correct answer
                                            visibility</span></label><select class="form-select"
                                        data-poll-field="answer_reveal"
                                        name="new_polls[{{ $pollIndex }}][answer_reveal]">
                                        <option value="immediate" @selected(($pollRow['answer_reveal'] ?? '') === 'immediate')>Immediately after answering
                                        </option>
                                        <option value="after_webinar" @selected(($pollRow['answer_reveal'] ?? 'after_webinar') === 'after_webinar')>After webinar finishes
                                        </option>
                                        <option value="never" @selected(($pollRow['answer_reveal'] ?? '') === 'never')>Never show</option>
                                    </select></div>
                                <div class="col-md-6"><label class="form-label-custom"><span>Initial
                                            status</span></label><select class="form-select" data-poll-field="status"
                                        name="new_polls[{{ $pollIndex }}][status]">
                                        <option value="draft" @selected(($pollRow['status'] ?? 'draft') === 'draft')>Draft (launch later)</option>
                                        <option value="active" @selected(($pollRow['status'] ?? '') === 'active')>Active now</option>
                                    </select></div>
                                <div class="col-md-6"><label class="form-label-custom"><span>Selection
                                            mode</span></label><select class="form-select"
                                        data-poll-field="allow_multiple"
                                        name="new_polls[{{ $pollIndex }}][allow_multiple]">
                                        <option value="0" @selected(($pollRow['allow_multiple'] ?? '0') !== '1')>Single choice</option>
                                        <option value="1" @selected(($pollRow['allow_multiple'] ?? '0') === '1')>Multiple choice</option>
                                    </select></div>
                                <div class="col-md-6"><label class="form-label-custom"><span>Start time
                                            (optional)</span></label><input class="form-control" type="datetime-local"
                                        data-poll-field="started_at" name="new_polls[{{ $pollIndex }}][started_at]"
                                        value="{{ $pollRow['started_at'] ?? '' }}"></div>
                                <div class="col-md-6"><label class="form-label-custom"><span>Auto-end time
                                            (optional)</span></label><input class="form-control" type="datetime-local"
                                        data-poll-field="ended_at" name="new_polls[{{ $pollIndex }}][ended_at]"
                                        value="{{ $pollRow['ended_at'] ?? '' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-danger mt-3" id="addAnotherWizardPoll"><i
                        class="bi bi-plus-lg me-1"></i> Add another poll</button>
            </section>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 6 / CERTIFICATE SETTINGS                            --}}
        {{-- ======================================================== --}}
        @php($selectedTemplateId = old('certificate_template_id', data_get($webinar->settings, 'certificate_template_id')))
        @php($activeTemplateDesign = $activeCertificateTemplate?->design ?? [])
        @php($certDefaults = ['headline' => ['x' => 50, 'y' => 15, 'width' => 70, 'scale' => 100, 'bold' => 1], 'recipient' => ['x' => 50, 'y' => 44, 'width' => 55, 'scale' => 100, 'bold' => 1], 'webinar' => ['x' => 50, 'y' => 61, 'width' => 55, 'scale' => 100, 'bold' => 0], 'date' => ['x' => 20, 'y' => 84, 'width' => 25, 'scale' => 100, 'bold' => 0], 'signature' => ['x' => 80, 'y' => 76, 'width' => 22, 'scale' => 100, 'bold' => 0], 'signatory' => ['x' => 80, 'y' => 86, 'width' => 30, 'scale' => 100, 'bold' => 0]])
        @php($certPositions = old('positions', data_get($activeTemplateDesign, 'positions', $certDefaults)))
        @php($certImage = data_get($activeTemplateDesign, 'template_image'))
        @php($certSignature = data_get($activeTemplateDesign, 'signature_image'))
        @php($certAspect = \App\Support\WebinarCertificateTemplate::aspectRatio($activeCertificateTemplate))
        @php($certVisibleElements = old('certificate_visible_elements', \App\Support\WebinarCertificateTemplate::visibleElements($activeTemplateDesign)))
        @php($certHasPositionableElements = collect(['headline', 'recipient', 'date', 'signature', 'signatory'])->contains(fn($key) => (bool) data_get($certVisibleElements, $key, true)))
        <div class="wizard-step-pane" data-step-pane="3" data-feature-pane="certificate">
            <section class="panel-card mb-4">
                <div class="panel-title">
                    <div>
                        <h3>Certificate Design &amp; Rules</h3>
                        <p>Choose the certificate appearance, visible details and attendee eligibility.</p>
                    </div>
                    <a href="{{ route('admin.certificates.index') }}" target="_blank" class="btn btn-sm btn-light">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Full Certificate Studio
                    </a>
                </div>

                <input type="hidden" name="certificate_template_id" value="{{ $selectedTemplateId ?: 'custom' }}">
                <div class="certificate-editor-layout">
                    <div class="certificate-editor-controls">
                        <div class="form-grid">

                            <div class="full" id="customCertFieldsSection">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-field">
                                            <label class="form-label-custom" for="certificateName">
                                                <span>Internal Template Name</span>
                                                <span class="req">*</span>
                                            </label>
                                            <input class="form-control" id="certificateName" name="certificate_name"
                                                value="{{ old('certificate_name', $activeCertificateTemplate?->name ?? ($webinar->title ? $webinar->title . ' Certificate' : 'Webinar Completion Certificate')) }}"
                                                placeholder="e.g. Masterclass Completion Certificate">
                                            <small class="text-muted">Admin identification only; this name is never printed
                                                on the certificate.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-field">
                                            <label class="form-label-custom" for="certificateImageInput">
                                                <span>Upload Certificate Template Background</span>
                                            </label>
                                            <input class="form-control" type="file" id="certificateImageInput"
                                                name="certificate_template_image" accept="image/png,image/jpeg">
                                            <small class="text-muted">PNG or JPG, up to 10 MB. Upload a background to
                                                unlock the certificate content and preview.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6" data-certificate-field="headline" data-certificate-designer-only
                                        @if (!$certImage || !data_get($certVisibleElements, 'headline', true)) hidden @endif>
                                        <div class="form-field">
                                            <label class="form-label-custom" for="certificateHeadline">
                                                <span>Certificate Headline</span>
                                                <span class="req">*</span>
                                            </label>
                                            <input class="form-control" id="certificateHeadline"
                                                name="certificate_headline"
                                                value="{{ old('certificate_headline', data_get($activeTemplateDesign, 'headline', 'Certificate of Completion')) }}"
                                                placeholder="e.g. Certificate of Completion">
                                        </div>
                                    </div>

                                    <div class="col-md-6" data-certificate-field="signatory"
                                        data-certificate-designer-only @if (!$certImage || !data_get($certVisibleElements, 'signatory', true)) hidden @endif>
                                        <div class="form-field">
                                            <label class="form-label-custom" for="certificateSignatory">
                                                <span>Signatory Name & Title</span>
                                            </label>
                                            <input class="form-control" id="certificateSignatory"
                                                name="certificate_signatory"
                                                value="{{ old('certificate_signatory', data_get($activeTemplateDesign, 'signatory', 'Authorized Director')) }}"
                                                placeholder="e.g. Dr. John Doe, Director">
                                        </div>
                                    </div>

                                    <input type="hidden" id="certificateOrientation" name="certificate_orientation"
                                        value="{{ old('certificate_orientation', $activeCertificateTemplate?->orientation ?? 'landscape') }}">

                                    <div class="col-md-6" data-certificate-field="signature"
                                        data-certificate-designer-only @if (!$certImage || !data_get($certVisibleElements, 'signature', true)) hidden @endif>
                                        <div class="form-field">
                                            <label class="form-label-custom" for="signatureImageInput">
                                                <span>Upload Signatory Signature Image</span>
                                            </label>
                                            <input class="form-control" type="file" id="signatureImageInput"
                                                name="certificate_signature_image" accept="image/png,image/jpeg">
                                            <small class="text-muted">Transparent PNG recommended, up to 5 MB.</small>
                                        </div>
                                    </div>

                                    <div class="col-12" data-certificate-background-hint
                                        @if ($certImage) hidden @endif>
                                        <div class="alert alert-light border mb-0 d-flex align-items-center gap-2">
                                            <i class="bi bi-image text-danger"></i>
                                            <span>Upload a certificate background to customize its content and open the live
                                                preview.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-field">
                                <label class="setting-toggle h-100" style="margin-top:28px;">
                                    <span>
                                        <strong>Require Poll Participation</strong>
                                        <small>Attendee must submit at least one poll answer to unlock certificate.</small>
                                    </span>
                                    <input type="checkbox" name="certificate_require_poll" id="wizardCertRequirePoll"
                                        value="1" @checked(old('certificate_require_poll', (bool) data_get($webinar->settings, 'experience.certificate_require_poll', false)))>
                                </label>
                            </div>

                            <div class="form-field">
                                <label class="form-label-custom" for="certificateMinAttendance"><span>Minimum Attendance
                                        / Watch Time (%)</span></label>
                                <input class="form-control" type="number" min="0" max="100"
                                    id="certificateMinAttendance" name="certificate_min_attendance"
                                    value="{{ old('certificate_min_attendance', data_get($webinar->settings, 'experience.certificate_min_attendance', 80)) }}">
                                <small class="text-muted">An attendee must watch this percentage of the webinar to unlock
                                    the certificate.</small>
                            </div>

                            <div class="form-field full" data-certificate-designer-only
                                @if (!$certImage) hidden @endif>
                                <label class="form-label-custom"><span>Show on certificate</span></label>
                                <div class="certificate-visibility-grid w-100">
                                    <input type="hidden" name="certificate_visible_elements[webinar]" value="0">
                                    @foreach (['headline' => 'Headline', 'recipient' => 'Attendee name', 'date' => 'Issue date', 'signature' => 'Signature image', 'signatory' => 'Signatory name'] as $key => $label)
                                        <label
                                            class="certificate-visibility-option d-flex align-items-center justify-content-between">
                                            <input type="hidden"
                                                name="certificate_visible_elements[{{ $key }}]"
                                                value="0">
                                            <span
                                                class="certificate-switch-copy"><strong>{{ $label }}</strong><small>{{ $key === 'recipient' ? "Uses each attendee's registered name automatically" : 'Show this detail on the issued certificate' }}</small></span>
                                            <input class="certificate-show-switch" type="checkbox" role="switch"
                                                name="certificate_visible_elements[{{ $key }}]"
                                                value="1" data-certificate-visibility="{{ $key }}"
                                                @checked((bool) data_get($certVisibleElements, $key, true))
                                                aria-label="Show {{ strtolower($label) }} on certificate">
                                        </label>
                                    @endforeach
                                </div>
                                <small class="text-muted">Turn a switch off to hide that detail from both the preview and
                                    attendee PDF.</small>
                            </div>

                            <!-- Element Position & Coordinates (X, Y) Control Panel -->
                            <details class="full mt-3 certificate-advanced-positioning" data-certificate-designer-only
                                @if (!$certImage || !$certHasPositionableElements) hidden @endif>
                                <summary><span><i class="bi bi-sliders me-2"></i>Advanced element
                                        positioning</span><small>Optional</small></summary>
                                <div class="p-3 border border-top-0 rounded-bottom-3 bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <div>
                                            <strong class="d-block text-dark">Precise position and size</strong>
                                            <small class="text-muted">Use these values only when you need exact placement.
                                                You can also move an element directly in the preview.</small>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary"
                                            id="resetCertCoordinatesBtn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
                                        </button>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label-custom small mb-1"
                                                for="certificateElementSelect"><span>Certificate detail</span></label>
                                            <select class="form-select form-select-sm" id="certificateElementSelect">
                                                <option value="headline">Headline</option>
                                                <option value="recipient">Attendee name (automatic)</option>
                                                <option value="date">Issue Date</option>
                                                <option value="signature">Signature Image</option>
                                                <option value="signatory">Signatory Name</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom small mb-1" for="certificateBold"><span>Bold
                                                    text</span></label>
                                            <select class="form-select form-select-sm" id="certificateBold">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label-custom small mb-1"
                                                for="certificateX"><span>Horizontal (X %)</span></label>
                                            <input class="form-control form-control-sm" id="certificateX"
                                                type="number" min="0" max="100" step="0.1"
                                                placeholder="50">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label-custom small mb-1"
                                                for="certificateY"><span>Vertical (Y %)</span></label>
                                            <input class="form-control form-control-sm" id="certificateY"
                                                type="number" min="0" max="100" step="0.1"
                                                placeholder="50">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label-custom small mb-1"
                                                for="certificateWidth"><span>Element width (%)</span></label>
                                            <input class="form-control form-control-sm" id="certificateWidth"
                                                type="number" min="5" max="90" step="1"
                                                placeholder="55">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label-custom small mb-1"
                                                for="certificateScale"><span>Size (%)</span></label>
                                            <input class="form-control form-control-sm" id="certificateScale"
                                                type="number" min="50" max="200" step="5"
                                                placeholder="100">
                                        </div>
                                        <div class="col-12" id="certificateRecipientPreviewField" hidden>
                                            <label class="form-label-custom small mb-1"
                                                for="certificateRecipientPreview"><span>Attendee preview
                                                    name</span></label>
                                            <input class="form-control form-control-sm" id="certificateRecipientPreview"
                                                value="{{ auth()->user()->name ?: 'Sample Attendee' }}"
                                                placeholder="Type a name to test the certificate">
                                            <small class="text-muted">Preview only. Issued certificates still use each
                                                attendee's registered name.</small>
                                        </div>
                                    </div>
                                </div>
                            </details>

                            @foreach ($certDefaults as $key => $default)
                                <input type="hidden" data-position-x="{{ $key }}"
                                    name="positions[{{ $key }}][x]"
                                    value="{{ data_get($certPositions, $key . '.x', $default['x']) }}">
                                <input type="hidden" data-position-y="{{ $key }}"
                                    name="positions[{{ $key }}][y]"
                                    value="{{ data_get($certPositions, $key . '.y', $default['y']) }}">
                                <input type="hidden" data-position-width="{{ $key }}"
                                    name="positions[{{ $key }}][width]"
                                    value="{{ data_get($certPositions, $key . '.width', $default['width']) }}">
                                <input type="hidden" data-position-scale="{{ $key }}"
                                    name="positions[{{ $key }}][scale]"
                                    value="{{ data_get($certPositions, $key . '.scale', $default['scale']) }}">
                                <input type="hidden" data-position-bold="{{ $key }}"
                                    name="positions[{{ $key }}][bold]"
                                    value="{{ (int) data_get($certPositions, $key . '.bold', $default['bold']) }}">
                            @endforeach

                        </div>
                    </div>
                    <!-- Visual Certificate Designer & Canvas -->
                    <aside class="certificate-editor-preview" data-certificate-designer-only
                        @if (!$certImage) hidden @endif>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label-custom mb-0"><span>Certificate preview</span></label>
                            <span class="badge bg-purple-subtle text-purple small"><i class="bi bi-hand-index me-1"></i>
                                Move any visible element to reposition it</span>
                        </div>
                        <div
                            class="certificate-preview designer-preview p-3 rounded-4 bg-light border d-flex justify-content-center overflow-hidden">
                            <div class="cert-inner draggable-certificate bg-white shadow-sm" id="certificateCanvas"
                                style="max-width:700px;width:100%;aspect-ratio:{{ $certAspect }};--cert-accent:#6d28d9;border:3px solid #ddd6fe;position:relative;background-size:contain;background-position:center;background-repeat:no-repeat;background-image:{{ $certImage ? "url('$certImage')" : 'none' }};min-height:360px;">

                                <div class="certificate-drag-item webinar {{ data_get($certVisibleElements, 'headline', true) ? '' : 'd-none' }}"
                                    data-certificate-element="headline"
                                    style="left:{{ data_get($certPositions, 'headline.x', 50) }}%;top:{{ data_get($certPositions, 'headline.y', 15) }}%;width:{{ data_get($certPositions, 'headline.width', 70) }}%;--element-scale:{{ data_get($certPositions, 'headline.scale', 100) / 100 }};font-weight:{{ data_get($certPositions, 'headline.bold', 1) ? 700 : 400 }};opacity:.35;text-transform:uppercase">
                                    <h3 class="text-uppercase m-0"
                                        style="font-weight:inherit;letter-spacing: 0.12em; font-size: 1.35rem; color: #475569;"
                                        id="certPreviewHeadlineWatermark">
                                        {{ old('certificate_headline', data_get($activeTemplateDesign, 'headline', 'Certificate of Completion')) }}
                                    </h3>
                                </div>

                                <div class="certificate-drag-item recipient {{ data_get($certVisibleElements, 'recipient', true) ? '' : 'd-none' }}"
                                    data-certificate-element="recipient"
                                    style="left:{{ data_get($certPositions, 'recipient.x', 50) }}%;top:{{ data_get($certPositions, 'recipient.y', 44) }}%;width:{{ data_get($certPositions, 'recipient.width', 55) }}%;--element-scale:{{ data_get($certPositions, 'recipient.scale', 100) / 100 }};font-weight:{{ data_get($certPositions, 'recipient.bold', 1) ? 700 : 400 }}">
                                    <span
                                        id="certPreviewRecipientName">{{ auth()->user()->name ?: 'Sample Attendee' }}</span>
                                </div>

                                <div class="certificate-drag-item meta {{ data_get($certVisibleElements, 'date', true) ? '' : 'd-none' }}"
                                    data-certificate-element="date"
                                    style="left:{{ data_get($certPositions, 'date.x', 20) }}%;top:{{ data_get($certPositions, 'date.y', 84) }}%;width:{{ data_get($certPositions, 'date.width', 25) }}%;--element-scale:{{ data_get($certPositions, 'date.scale', 100) / 100 }};font-weight:{{ data_get($certPositions, 'date.bold', 0) ? 700 : 400 }}">
                                    {{ now()->format('F d, Y') }}
                                </div>

                                <div class="certificate-drag-item signature-image {{ $certSignature && data_get($certVisibleElements, 'signature', true) ? '' : 'd-none' }}"
                                    data-certificate-element="signature"
                                    style="left:{{ data_get($certPositions, 'signature.x', 80) }}%;top:{{ data_get($certPositions, 'signature.y', 76) }}%;width:{{ data_get($certPositions, 'signature.width', 22) }}%;--element-scale:{{ data_get($certPositions, 'signature.scale', 100) / 100 }}">
                                    <img id="signaturePreview" src="{{ $certSignature ?: '' }}" alt="Signature"
                                        style="max-width:100%;max-height:50px;object-fit:contain;">
                                </div>

                                <div class="certificate-drag-item meta {{ data_get($certVisibleElements, 'signatory', true) ? '' : 'd-none' }}"
                                    data-certificate-element="signatory"
                                    style="left:{{ data_get($certPositions, 'signatory.x', 80) }}%;top:{{ data_get($certPositions, 'signatory.y', 86) }}%;width:{{ data_get($certPositions, 'signatory.width', 30) }}%;--element-scale:{{ data_get($certPositions, 'signatory.scale', 100) / 100 }};font-weight:{{ data_get($certPositions, 'signatory.bold', 0) ? 700 : 400 }}"
                                    id="certPreviewSignatoryText">
                                    {{ old('certificate_signatory', data_get($activeTemplateDesign, 'signatory', 'Authorized Director')) }}
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </div>

        <!-- Unified Floating Action Footer -->
        <div class="wizard-footer-bar">
            <div class="wizard-step-info-badge">
                <i class="bi bi-layers-fill"></i>
                <span id="footerStepIndicator">Step 1 of 3: Essentials</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-light" id="btnPrevStep" style="display:none;">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-gradient px-4" id="btnNextStep">
                    Next: Design &amp; Content <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <button type="submit" class="btn btn-gradient px-4" id="btnFinalSubmit" style="display:none;">
                    <i class="bi bi-check2-circle me-1"></i> {{ $webinar->exists ? 'Save Webinar' : 'Add Webinar' }}
                </button>
            </div>
        </div>
    </form>

    <div class="modal fade experience-preview-modal" id="experiencePreviewModal" tabindex="-1"
        aria-labelledby="experiencePreviewModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div><span class="preview-mode-title" id="experiencePreviewMode">LIVE LANDING PREVIEW</span>
                        <h2 class="modal-title fs-5 mt-1" id="experiencePreviewModalTitle">Webinar experience preview
                        </h2>
                    </div><button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="experience-preview-stage">
                        <div class="experience-preview-shell" id="experiencePreviewShell">
                            <div class="experience-preview-nav"><strong id="experiencePreviewNavTitle">Your
                                    webinar</strong><span class="badge text-bg-danger">PREVIEW</span></div>
                            <div class="experience-preview-hero">
                                <h2 id="experiencePreviewTitle">Your webinar title</h2>
                            </div>
                            <div class="experience-preview-body">
                                <div><strong>Partner brands</strong>
                                    <div class="experience-preview-brand-list mt-2" id="experiencePreviewBrands"></div>
                                </div>
                                <div><strong>Session agenda</strong>
                                    <div class="experience-preview-agenda mt-2" id="experiencePreviewAgenda"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade theme-picker-modal" id="themeColorPickerModal" tabindex="-1"
        aria-labelledby="themeColorPickerTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-6" id="themeColorPickerTitle"><i
                                class="bi bi-palette2 me-2"></i>Theme Color Picker</h2><small
                            class="text-white-50">Photoshop-style color lab</small>
                    </div><button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="ps-target-tabs"><button type="button" class="ps-target-tab active"
                            data-color-target="primary"><i data-color-swatch="primary"></i><span>Primary
                                Brand</span></button><button type="button" class="ps-target-tab"
                            data-color-target="secondary"><i data-color-swatch="secondary"></i><span>Accent
                                Glow</span></button></div>
                    <div class="ps-picker-body">
                        <div class="ps-sv-field" id="brandSvField" aria-label="Saturation and brightness picker"><span
                                class="ps-picker-marker" id="brandPickerMarker"></span></div>
                        <div class="ps-hue-row"><input class="ps-hue-slider" id="brandHueSlider" type="range"
                                min="0" max="360" value="260" aria-label="Hue"><input
                                class="ps-native-color" id="brandNativeColor" type="color"
                                value="{{ old('brand_primary', $experience['primary'] ?? '#6d28d9') }}"
                                title="Open system color picker"></div>
                        <div class="ps-value-grid"><input class="ps-rgb-readout" id="brandActiveHex" value="#6D28D9"
                                maxlength="7" spellcheck="false" aria-label="Enter HEX color manually">
                            <div class="ps-rgb-readout" id="brandRgbReadout">RGB 109 · 40 · 217</div>
                            <div class="ps-rgb-readout" id="brandHsvReadout">HSV 263° · 82% · 85%</div>
                            <div class="d-flex gap-2"><button type="button"
                                    class="btn btn-outline-light btn-sm flex-fill"
                                    data-bs-dismiss="modal">Cancel</button><button type="button"
                                    class="btn btn-light btn-sm flex-fill" id="applyThemeColor">Apply color</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // -------------------------------------------------------------
            // Simplified three-step webinar builder
            // -------------------------------------------------------------
            const stepTabs = document.querySelectorAll('.step-item');
            const stepPanes = document.querySelectorAll('.wizard-step-pane');
            const btnPrev = document.querySelector('#btnPrevStep');
            const btnNext = document.querySelector('#btnNextStep');
            const btnFinal = document.querySelector('#btnFinalSubmit');
            const footerIndicator = document.querySelector('#footerStepIndicator');

            const togglePolls = document.querySelector('#togglePollsEnabled');
            const toggleCert = document.querySelector('#toggleCertificateEnabled');
            let currentStepKey = '1';

            function getActiveSteps() {
                return [{
                        key: '1',
                        name: 'Essentials'
                    },
                    {
                        key: '2',
                        name: 'Design & Content'
                    },
                    {
                        key: '3',
                        name: 'Registration & Publish'
                    },
                ];
            }

            function renderStep(targetKey = null, shouldScroll = false) {
                const activeSteps = getActiveSteps();
                const pollsOn = Boolean(togglePolls && togglePolls.checked);
                const certOn = Boolean(toggleCert && toggleCert.checked);

                // Validate or fallback targetKey
                if (targetKey !== null && targetKey !== undefined) {
                    currentStepKey = String(targetKey);
                }
                let currentIndex = activeSteps.findIndex(s => s.key === currentStepKey);
                if (currentIndex === -1) {
                    currentIndex = activeSteps.length - 1;
                    currentStepKey = activeSteps[currentIndex].key;
                }

                // Update tabs active / completed states
                stepTabs.forEach(tab => {
                    const tabKey = tab.dataset.stepNav;
                    const badge = tab.querySelector('.step-badge');
                    const stepIndex = activeSteps.findIndex(s => s.key === tabKey);
                    tab.setAttribute('role', 'tab');

                    if (stepIndex === -1) {
                        tab.classList.remove('active', 'completed');
                        tab.setAttribute('aria-selected', 'false');
                        tab.tabIndex = -1;
                        return;
                    }

                    const stepDisplayNum = stepIndex + 1;

                    if (tabKey === currentStepKey) {
                        tab.classList.add('active');
                        tab.classList.remove('completed');
                        tab.setAttribute('aria-selected', 'true');
                        tab.setAttribute('aria-current', 'step');
                        tab.tabIndex = 0;
                        if (badge) badge.innerHTML = stepDisplayNum;
                    } else {
                        tab.classList.remove('active');
                        tab.setAttribute('aria-selected', 'false');
                        tab.removeAttribute('aria-current');
                        tab.tabIndex = -1;
                        if (stepIndex < currentIndex) {
                            tab.classList.add('completed');
                            if (badge) badge.innerHTML = '<i class="bi bi-check-lg"></i>';
                        } else {
                            tab.classList.remove('completed');
                            if (badge) badge.innerHTML = stepDisplayNum;
                        }
                    }
                });

                const stepper = document.querySelector('.webinar-stepper');
                const activeTab = document.querySelector('.step-item.active');
                if (stepper && activeTab && stepper.scrollWidth > stepper.clientWidth) {
                    requestAnimationFrame(() => activeTab.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    }));
                }

                // Update step panes visibility
                stepPanes.forEach(pane => {
                    const feature = pane.dataset.featurePane;
                    const featureEnabled = feature === 'poll' ? pollsOn : (feature === 'certificate' ?
                        certOn : true);
                    pane.classList.toggle('active', pane.dataset.stepPane === currentStepKey &&
                        featureEnabled);
                });

                // Update footer controls
                if (btnPrev) {
                    btnPrev.style.display = currentIndex > 0 ? 'inline-flex' : 'none';
                }

                const isLastStep = (currentIndex === activeSteps.length - 1);
                if (btnNext && btnFinal) {
                    if (isLastStep) {
                        btnNext.style.display = 'none';
                        btnFinal.style.display = 'inline-flex';
                    } else {
                        btnNext.style.display = 'inline-flex';
                        const nextStepObj = activeSteps[currentIndex + 1];
                        btnNext.innerHTML = `Next: ${nextStepObj.name} <i class="bi bi-arrow-right ms-1"></i>`;
                        btnFinal.style.display = 'none';
                    }
                }

                if (footerIndicator) {
                    const currentStepObj = activeSteps[currentIndex];
                    footerIndicator.textContent =
                        `Step ${currentIndex + 1} of ${activeSteps.length}: ${currentStepObj.name}`;
                }

                if (shouldScroll) {
                    document.querySelector('.webinar-stepper-wrap')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }

            // Step validation helper (clean inline validation without native browser popups)
            function validateStep(stepKey) {
                stepKey = String(stepKey);
                let isValid = true;
                const currentPanes = [...document.querySelectorAll(
                    `.wizard-step-pane[data-step-pane="${stepKey}"]`)].filter(pane => !pane.dataset
                    .featurePane || pane.classList.contains('active'));
                if (!currentPanes.length) return true;
                const findInStep = selector => currentPanes.map(pane => pane.querySelector(selector)).find(Boolean);

                // Clear existing custom errors in this step
                currentPanes.forEach(currentPane => {
                    currentPane.querySelectorAll('.wizard-field-error').forEach(el => el.remove());
                    currentPane.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                    currentPane.querySelectorAll('.schedule-picker-shell').forEach(el => el.classList
                        .remove('is-invalid'));
                });

                const markInvalid = (input, message) => {
                    isValid = false;
                    const visibleInput = input._flatpickr?.altInput || input;
                    visibleInput.classList.add('is-invalid');
                    const shell = visibleInput.closest('.schedule-picker-shell');
                    if (shell) shell.classList.add('is-invalid');
                    const err = document.createElement('div');
                    err.className = 'wizard-field-error text-danger small mt-1 fw-bold';
                    err.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${message}`;
                    const targetParent = shell || visibleInput.closest('.input-group') || visibleInput;
                    targetParent.parentNode.insertBefore(err, targetParent.nextSibling);

                    const clearErr = () => {
                        visibleInput.classList.remove('is-invalid');
                        if (shell) shell.classList.remove('is-invalid');
                        err.remove();
                    };
                    input.addEventListener('input', clearErr, {
                        once: true
                    });
                    input.addEventListener('change', clearErr, {
                        once: true
                    });
                };

                if (stepKey === '1') {
                    const title = findInStep('#webinarTitle');
                    if (title && !title.value.trim()) {
                        markInvalid(title, 'Webinar Title is required to proceed.');
                    }
                    const slug = findInStep('#webinarSlug');
                    if (slug && !slug.value.trim()) {
                        markInvalid(slug, 'Public URL Slug is required to proceed.');
                    }
                    const startsAt = findInStep('#webinarStartsAt');
                    const endsAt = findInStep('#webinarEndsAt');
                    const status = findInStep('#webinarStatus')?.value;
                    if (['scheduled', 'live'].includes(status)) {
                        if (startsAt && !startsAt.value) markInvalid(startsAt,
                            'Start date and time are required for a scheduled or live webinar.');
                        if (endsAt && !endsAt.value) markInvalid(endsAt,
                            'End date and time are required for a scheduled or live webinar.');
                    }
                    if (startsAt && endsAt && startsAt.value && endsAt.value) {
                        if (new Date(endsAt.value) <= new Date(startsAt.value)) {
                            markInvalid(endsAt, 'End date and time must be after the start date and time.');
                        }
                    }
                    const provider = findInStep('#liveProvider');
                    const source = findInStep('#liveSource');
                    if (provider?.value && source && !source.value.trim()) {
                        markInvalid(source, 'Video URL, ID, or iframe code is required for the selected player.');
                    }
                }

                if (!isValid) {
                    const firstErr = findInStep('.is-invalid');
                    if (firstErr) {
                        firstErr.focus();
                    }
                }

                return isValid;
            }

            // Tab buttons: allow clicking past steps freely, validate before jumping forward
            stepTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const activeSteps = getActiveSteps();
                    const targetKey = tab.dataset.stepNav;
                    const targetIdx = activeSteps.findIndex(s => s.key === targetKey);
                    const curIdx = activeSteps.findIndex(s => s.key === currentStepKey);

                    if (targetIdx === -1) return;
                    if (targetIdx > curIdx) {
                        if (!validateStep(currentStepKey)) {
                            return;
                        }
                    }
                    renderStep(targetKey, false);
                });
            });

            // Next button: strictly validate before advancing
            btnNext?.addEventListener('click', () => {
                if (!validateStep(currentStepKey)) {
                    return;
                }
                const activeSteps = getActiveSteps();
                const curIdx = activeSteps.findIndex(s => s.key === currentStepKey);
                if (curIdx < activeSteps.length - 1) {
                    renderStep(activeSteps[curIdx + 1].key, true);
                }
            });

            // Previous button
            btnPrev?.addEventListener('click', () => {
                const activeSteps = getActiveSteps();
                const curIdx = activeSteps.findIndex(s => s.key === currentStepKey);
                if (curIdx > 0) {
                    renderStep(activeSteps[curIdx - 1].key, true);
                }
            });

            // Dynamic step updates when toggling Polls or Certificate
            togglePolls?.addEventListener('change', () => renderStep(null, false));
            toggleCert?.addEventListener('change', () => renderStep(null, false));

            // Form submit listener: validate all active steps before submitting
            const form = document.getElementById('webinarForm');
            form?.addEventListener('submit', (e) => {
                const activeSteps = getActiveSteps();
                for (const step of activeSteps) {
                    if (!validateStep(step.key)) {
                        e.preventDefault();
                        e.stopPropagation();
                        renderStep(step.key, false);
                        const firstInvalid = document.querySelector(
                            `.wizard-step-pane[data-step-pane="${step.key}"] .is-invalid`);
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstInvalid.focus();
                        }
                        return false;
                    }
                }
            });

            @if ($errors->any())
                const firstErrField = document.querySelector('.wizard-step-pane .is-invalid');
                if (firstErrField) {
                    const parentPane = firstErrField.closest('.wizard-step-pane');
                    if (parentPane && parentPane.dataset.stepPane) {
                        renderStep(parentPane.dataset.stepPane, true);
                        firstErrField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            @endif

            // Repeatable poll builder
            const wizardPollsList = document.getElementById('wizardPollsList');
            const addAnotherWizardPoll = document.getElementById('addAnotherWizardPoll');

            function reindexPollOptions(card) {
                const pollList = card?.querySelector('[data-poll-options]');
                const pollCorrectSelect = card?.querySelector('[data-poll-correct]');
                if (!pollList) return;
                pollList.querySelectorAll('[data-option-row]').forEach((row, index) => {
                    const letter = String.fromCharCode(65 + index);
                    const badge = row.querySelector('.badge');
                    if (badge) badge.textContent = letter;
                    const input = row.querySelector('[data-poll-answer]');
                    if (input && !input.value) input.placeholder = `Option ${letter} text`;
                });
                if (pollCorrectSelect) {
                    const selected = pollCorrectSelect.value;
                    pollCorrectSelect.innerHTML = '<option value="">No correct answer (standard poll)</option>';
                    pollList.querySelectorAll('[data-poll-answer]').forEach((input, index) => {
                        const option = document.createElement('option');
                        option.value = String(index);
                        option.textContent =
                            `Option ${String.fromCharCode(65 + index)}${input.value.trim() ? ` — ${input.value.trim()}` : ''}`;
                        option.selected = selected === String(index);
                        pollCorrectSelect.appendChild(option);
                    });
                }
            }

            function reindexWizardPolls() {
                wizardPollsList?.querySelectorAll('[data-wizard-poll]').forEach((card, pollIndex) => {
                    const number = card.querySelector('[data-poll-number]');
                    if (number) number.textContent = `Poll ${pollIndex + 1}`;
                    card.querySelectorAll('[data-poll-field]').forEach(input => input.name =
                        `new_polls[${pollIndex}][${input.dataset.pollField}]`);
                    card.querySelectorAll('[data-poll-answer]').forEach(input => input.name =
                        `new_polls[${pollIndex}][answers][]`);
                    const remove = card.querySelector('[data-remove-wizard-poll]');
                    if (remove) remove.hidden = wizardPollsList.querySelectorAll('[data-wizard-poll]')
                        .length === 1;
                    reindexPollOptions(card);
                });
            }

            function addPollOption(card) {
                const pollList = card?.querySelector('[data-poll-options]');
                if (!pollList) return;
                const currentCount = pollList.querySelectorAll('[data-option-row]').length;
                const letter = String.fromCharCode(65 + currentCount);
                const newRow = document.createElement('div');
                newRow.className = 'wizard-poll-row';
                newRow.dataset.optionRow = '';
                newRow.innerHTML = `
            <span class="badge bg-secondary-subtle text-secondary fw-bold flex-none" style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;">${letter}</span>
            <input class="form-control" data-poll-answer placeholder="Option ${letter} text">
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-poll-option title="Delete option">
                <i class="bi bi-trash3"></i>
            </button>
        `;
                pollList.appendChild(newRow);
                reindexWizardPolls();
                newRow.querySelector('[data-poll-answer]')?.focus();
            }

            addAnotherWizardPoll?.addEventListener('click', () => {
                const source = wizardPollsList?.querySelector('[data-wizard-poll]');
                if (!source) return;
                const card = source.cloneNode(true);
                card.querySelectorAll('input').forEach(input => input.value = '');
                card.querySelectorAll('select').forEach(select => select.selectedIndex = select
                    .hasAttribute('data-poll-correct') ? 0 : 0);
                card.querySelector('[data-poll-field="answer_reveal"]')?.querySelector(
                    'option[value="after_webinar"]')?.setAttribute('selected', 'selected');
                wizardPollsList.appendChild(card);
                const reveal = card.querySelector('[data-poll-field="answer_reveal"]');
                if (reveal) reveal.value = 'after_webinar';
                reindexWizardPolls();
                card.querySelector('[data-poll-field="question"]')?.focus();
            });

            wizardPollsList?.addEventListener('click', event => {
                const card = event.target.closest('[data-wizard-poll]');
                if (!card) return;
                if (event.target.closest('[data-add-poll-option]')) addPollOption(card);
                const removeOption = event.target.closest('[data-remove-poll-option]');
                if (removeOption) {
                    const options = card.querySelectorAll('[data-option-row]');
                    if (options.length > 2) {
                        removeOption.closest('[data-option-row]')?.remove();
                        reindexWizardPolls();
                    } else window.showToast?.('A poll needs at least two answer options.', 'error');
                }
                if (event.target.closest('[data-remove-wizard-poll]') && wizardPollsList.querySelectorAll(
                        '[data-wizard-poll]').length > 1) {
                    card.remove();
                    reindexWizardPolls();
                }
            });
            wizardPollsList?.addEventListener('input', event => {
                const card = event.target.closest('[data-wizard-poll]');
                if (card && event.target.matches('[data-poll-answer]')) reindexPollOptions(card);
            });
            reindexWizardPolls();

            // Dynamic registration field builder
            const dynamicFieldsList = document.getElementById('dynamicFieldsList');
            const addDynamicField = document.getElementById('addDynamicField');
            const reindexDynamicFields = () => dynamicFieldsList?.querySelectorAll('[data-dynamic-field]').forEach((
                row, index) => {
                row.querySelectorAll('[data-field-name]').forEach(input => input.name =
                    `fields[${index}][${input.dataset.fieldName}]`);
            });
            const syncDynamicFieldOptions = row => {
                const type = row.querySelector('[data-field-name="field_type"]')?.value;
                const optionsContainer = row.querySelector('[data-field-options]');
                const optionsInput = optionsContainer?.querySelector('[data-field-name="options"]');
                const supportsOptions = ['dropdown', 'radio', 'checkbox'].includes(type);
                if (optionsContainer) optionsContainer.hidden = !supportsOptions;
                if (!supportsOptions && optionsInput) optionsInput.value = '';
            };
            dynamicFieldsList?.querySelectorAll('[data-dynamic-field]').forEach(syncDynamicFieldOptions);
            addDynamicField?.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'p-3 border rounded-3 bg-light';
                row.dataset.dynamicField = '';
                row.innerHTML = `<div class="row g-3 align-items-end">
            <div class="col-lg-3"><label class="form-label">Field label</label><input class="form-control" data-field-name="label" placeholder="e.g. Department"></div>
            <div class="col-lg-2"><label class="form-label">Type</label><select class="form-select" data-field-name="field_type"><option value="text">Text</option><option value="dropdown">Dropdown</option><option value="radio">Radio</option><option value="checkbox">Checkboxes</option><option value="country">Country</option><option value="state">State</option><option value="city">City</option></select></div>
            <div class="col-lg-3"><label class="form-label">Placeholder</label><input class="form-control" data-field-name="placeholder"></div>
            <div class="col-lg-3 dynamic-field-switches"><label class="dynamic-field-toggle"><span>Required</span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" data-field-name="is_required" value="1" aria-label="Toggle required field"></span></label><label class="dynamic-field-toggle"><span>Active</span><span class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" data-field-name="is_enabled" value="1" checked aria-label="Toggle active field"></span></label></div>
            <div class="col-lg-1 text-end"><button type="button" class="btn btn-outline-danger" data-remove-dynamic-field><i class="bi bi-trash3"></i></button></div>
            <div class="col-12" data-field-options hidden><label class="form-label">Options <small class="text-muted">(one per line)</small></label><textarea class="form-control" rows="2" data-field-name="options"></textarea></div>
        </div>`;
                dynamicFieldsList?.appendChild(row);
                reindexDynamicFields();
                syncDynamicFieldOptions(row);
                row.querySelector('[data-field-name="label"]')?.focus();
            });
            dynamicFieldsList?.addEventListener('change', event => {
                if (!event.target.matches('[data-field-name="field_type"]')) return;
                const row = event.target.closest('[data-dynamic-field]');
                if (row) syncDynamicFieldOptions(row);
            });
            dynamicFieldsList?.addEventListener('click', event => {
                const button = event.target.closest('[data-remove-dynamic-field]');
                if (!button) return;
                button.closest('[data-dynamic-field]')?.remove();
                reindexDynamicFields();
            });

            // Certificate preview live sync
            const certHeadlineInput = document.getElementById('certificateHeadline');
            const certSignatoryInput = document.getElementById('certificateSignatory');
            const certPreviewHeadlineWatermark = document.getElementById('certPreviewHeadlineWatermark');
            const certPreviewSignatory = document.getElementById('certPreviewSignatoryText');
            const certPreviewRecipientName = document.getElementById('certPreviewRecipientName');
            const certificateRecipientPreview = document.getElementById('certificateRecipientPreview');
            const certificateRecipientPreviewField = document.getElementById('certificateRecipientPreviewField');
            const certificateElementSelect = document.getElementById('certificateElementSelect');
            const certificateBold = document.getElementById('certificateBold');
            const certificateImageInput = document.getElementById('certificateImageInput');
            const certificateOrientationInput = document.getElementById('certificateOrientation');
            const certificateDesignerBlocks = document.querySelectorAll('[data-certificate-designer-only]');
            const certificateBackgroundHint = document.querySelector('[data-certificate-background-hint]');
            const certificateVisibilitySwitches = document.querySelectorAll('[data-certificate-visibility]');
            const certificatePositionPanel = document.querySelector('.certificate-advanced-positioning');
            let certificateHasBackground = @json((bool) $certImage);

            const syncCertificatePositionPanel = () => {
                if (!certificatePositionPanel) return;
                certificatePositionPanel.hidden = !certificateHasBackground || ![...
                    certificateVisibilitySwitches
                ].some(input => input.checked);
            };
            const syncCertificateFieldVisibility = input => {
                const field = document.querySelector(
                    `[data-certificate-field="${input.dataset.certificateVisibility}"]`);
                if (field) field.hidden = !certificateHasBackground || !input.checked;
            };
            const syncCertificateDesigner = hasBackground => {
                certificateHasBackground = Boolean(hasBackground);
                certificateDesignerBlocks.forEach(block => {
                    if (block.dataset.certificateField) return;
                    block.hidden = !certificateHasBackground;
                });
                if (certificateBackgroundHint) certificateBackgroundHint.hidden = certificateHasBackground;
                certificateVisibilitySwitches.forEach(syncCertificateFieldVisibility);
                syncCertificatePositionPanel();
            };

            certificateVisibilitySwitches.forEach(input => {
                input.addEventListener('change', () => {
                    syncCertificateFieldVisibility(input);
                    syncCertificatePositionPanel();
                    requestAnimationFrame(() => syncCertificateAdvancedSelection(
                        certificateElementSelect?.value));
                });
                syncCertificateFieldVisibility(input);
            });
            certificateImageInput?.addEventListener('change', () => {
                if (certificateImageInput.files?.[0]) syncCertificateDesigner(true);
            });
            syncCertificateDesigner(certificateHasBackground);

            certHeadlineInput?.addEventListener('input', () => {
                if (certPreviewHeadlineWatermark) certPreviewHeadlineWatermark.textContent =
                    certHeadlineInput.value.trim() || 'Certificate of Completion';
            });
            certSignatoryInput?.addEventListener('input', () => {
                if (certPreviewSignatory) certPreviewSignatory.textContent = certSignatoryInput.value
                    .trim() || 'Authorized Director';
            });
            certificateRecipientPreview?.addEventListener('input', () => {
                if (certPreviewRecipientName) certPreviewRecipientName.textContent =
                    certificateRecipientPreview.value.trim() || 'Attendee Name';
            });

            const setCertificateBold = (key, isBold) => {
                const enabled = Boolean(Number(isBold));
                const target = document.querySelector(`[data-certificate-element="${key}"]`);
                const hiddenInput = document.querySelector(`[data-position-bold="${key}"]`);
                if (target && key !== 'signature') target.style.fontWeight = enabled ? '700' : '400';
                if (hiddenInput) hiddenInput.value = enabled ? '1' : '0';
                if (certificateBold && certificateElementSelect?.value === key) certificateBold.value =
                    enabled ? '1' : '0';
            };
            const syncCertificateAdvancedSelection = key => {
                if (!key) return;
                if (certificateElementSelect) certificateElementSelect.value = key;
                const boldValue = document.querySelector(`[data-position-bold="${key}"]`)?.value || '0';
                if (certificateBold) {
                    certificateBold.value = boldValue === '1' ? '1' : '0';
                    certificateBold.disabled = key === 'signature';
                }
                if (certificateRecipientPreviewField) certificateRecipientPreviewField.hidden = key !==
                    'recipient';
            };
            document.querySelectorAll('[data-position-bold]').forEach(input => setCertificateBold(input.dataset
                .positionBold, input.value));
            syncCertificateAdvancedSelection(certificateElementSelect?.value);
            requestAnimationFrame(() => syncCertificateAdvancedSelection(certificateElementSelect?.value));
            certificateElementSelect?.addEventListener('change', () => syncCertificateAdvancedSelection(
                certificateElementSelect.value));
            certificateBold?.addEventListener('change', () => setCertificateBold(certificateElementSelect?.value,
                certificateBold.value));
            document.querySelectorAll('[data-certificate-element]').forEach(item => {
                item.addEventListener('pointerdown', () => syncCertificateAdvancedSelection(item.dataset
                    .certificateElement));
            });

            // When selecting saved template, populate headline, signatory, X/Y positions & images
            const certTplSelect = document.getElementById('certTemplateSelect');
            certTplSelect?.addEventListener('change', () => {
                const opt = certTplSelect.options[certTplSelect.selectedIndex];
                if (!opt) return;
                if (opt.value !== 'custom') {
                    if (opt.dataset.orientation && certificateOrientationInput) certificateOrientationInput
                        .value = opt.dataset.orientation;
                    if (opt.dataset.headline && certHeadlineInput) {
                        certHeadlineInput.value = opt.dataset.headline;
                        if (certPreviewHeadlineWatermark) certPreviewHeadlineWatermark.textContent = opt
                            .dataset.headline;
                    }
                    if (opt.dataset.signatory && certSignatoryInput) {
                        certSignatoryInput.value = opt.dataset.signatory;
                        if (certPreviewSignatory) certPreviewSignatory.textContent = opt.dataset.signatory;
                    }
                    if (opt.dataset.positions) {
                        try {
                            const pos = JSON.parse(opt.dataset.positions);
                            Object.entries(pos).forEach(([k, v]) => {
                                if (window.setCertificatePosition && v.x !== undefined && v.y !==
                                    undefined) {
                                    window.setCertificatePosition(k, v.x, v.y);
                                }
                                if (window.setCertificateSize && v.width !== undefined && v
                                    .scale !== undefined) {
                                    window.setCertificateSize(k, v.width, v.scale);
                                }
                            });
                            const curKey = document.querySelector('#certificateElementSelect')?.value;
                            if (curKey && window.selectCertificateElement) {
                                window.selectCertificateElement(curKey);
                            }
                        } catch (e) {}
                    }
                    const canvas = document.querySelector('#certificateCanvas');
                    if (canvas) {
                        canvas.style.backgroundImage = opt.dataset.image ? `url('${opt.dataset.image}')` :
                            'none';
                        if (opt.dataset.aspect) canvas.style.aspectRatio = opt.dataset.aspect;
                    }
                    syncCertificateDesigner(Boolean(opt.dataset.image));
                    if (opt.dataset.visibility) {
                        try {
                            const visibility = JSON.parse(opt.dataset.visibility);
                            Object.entries(visibility).forEach(([key, shown]) => {
                                const checkbox = document.querySelector(
                                    `[data-certificate-visibility="${key}"]`);
                                if (checkbox) {
                                    checkbox.checked = Boolean(shown);
                                    checkbox.dispatchEvent(new Event('change'));
                                }
                            });
                        } catch (e) {}
                    }
                    const sigEl = document.querySelector('[data-certificate-element="signature"]');
                    const sigImg = document.querySelector('#signaturePreview');
                    if (sigEl && sigImg) {
                        const signatureVisible = document.querySelector(
                            '[data-certificate-visibility="signature"]')?.checked ?? true;
                        if (opt.dataset.signature && signatureVisible) {
                            sigImg.src = opt.dataset.signature;
                            sigEl.classList.remove('d-none');
                        } else {
                            sigImg.src = opt.dataset.signature || '';
                            sigEl.classList.add('d-none');
                        }
                    }
                } else {
                    const canvas = document.querySelector('#certificateCanvas');
                    if (canvas) canvas.style.backgroundImage = 'none';
                    syncCertificateDesigner(Boolean(certificateImageInput?.files?.[0]));
                }
            });

            // Reset Coordinates to defaults
            document.getElementById('resetCertCoordinatesBtn')?.addEventListener('click', () => {
                const defs = {
                    headline: {
                        x: 50,
                        y: 15,
                        width: 70,
                        scale: 100,
                        bold: 1
                    },
                    recipient: {
                        x: 50,
                        y: 44,
                        width: 55,
                        scale: 100,
                        bold: 1
                    },
                    date: {
                        x: 20,
                        y: 84,
                        width: 25,
                        scale: 100,
                        bold: 0
                    },
                    signature: {
                        x: 80,
                        y: 76,
                        width: 22,
                        scale: 100,
                        bold: 0
                    },
                    signatory: {
                        x: 80,
                        y: 86,
                        width: 30,
                        scale: 100,
                        bold: 0
                    }
                };
                Object.entries(defs).forEach(([k, v]) => {
                    if (window.setCertificatePosition) window.setCertificatePosition(k, v.x, v.y);
                    if (window.setCertificateSize) window.setCertificateSize(k, v.width, v.scale);
                    setCertificateBold(k, v.bold);
                });
                const curKey = document.querySelector('#certificateElementSelect')?.value;
                if (curKey && window.selectCertificateElement) {
                    window.selectCertificateElement(curKey);
                }
                syncCertificateAdvancedSelection(curKey);
            });

            // Initial render on load: check if errors exist in any pane, else Step 1
            const firstInvalid = document.querySelector('.is-invalid, .invalid-feedback');
            if (firstInvalid) {
                const errorPane = firstInvalid.closest('.wizard-step-pane');
                if (errorPane && errorPane.dataset.stepPane) {
                    renderStep(errorPane.dataset.stepPane, false);
                } else {
                    renderStep('1', false);
                }
            } else {
                renderStep('1', false);
            }

            // -------------------------------------------------------------
            // Real-time Date Range Validation (ends_at must be after starts_at)
            // -------------------------------------------------------------
            const startsInput = document.querySelector('#webinarStartsAt');
            const endsInput = document.querySelector('#webinarEndsAt');

            function checkDateOrder(isStartsChange = false) {
                if (!startsInput || !endsInput) return;
                if (startsInput.value) {
                    if (isStartsChange && endsInput.value) {
                        endsInput._flatpickr?.clear(false);
                        endsInput.value = '';
                    }
                    endsInput.min = startsInput.value;
                    endsInput._flatpickr?.set('minDate', startsInput.value);
                } else {
                    endsInput.removeAttribute('min');
                    endsInput._flatpickr?.set('minDate', null);
                }
                const endsShell = endsInput.closest('.schedule-picker-shell') || endsInput;
                const visibleEnds = endsInput._flatpickr?.altInput || endsInput;
                const formField = endsInput.closest('.form-field') || endsShell.parentNode;
                let err = formField.querySelector('.wizard-field-error-date');

                if (startsInput.value && endsInput.value) {
                    const startDate = new Date(startsInput.value);
                    const endDate = new Date(endsInput.value);
                    if (endDate <= startDate) {
                        endsInput.classList.add('is-invalid');
                        visibleEnds.classList.add('is-invalid');
                        endsShell.classList.add('is-invalid');
                        if (!err) {
                            err = document.createElement('div');
                            err.className =
                                'wizard-field-error wizard-field-error-date text-danger small mt-1 fw-bold';
                            endsShell.parentNode.insertBefore(err, endsShell.nextSibling);
                        }
                        err.innerHTML =
                            '<i class="bi bi-exclamation-circle-fill me-1"></i> End date and time must be after the start date and time.';
                    } else {
                        endsInput.classList.remove('is-invalid');
                        visibleEnds.classList.remove('is-invalid');
                        endsShell.classList.remove('is-invalid');
                        if (err) err.remove();
                    }
                } else {
                    endsInput.classList.remove('is-invalid');
                    visibleEnds.classList.remove('is-invalid');
                    endsShell.classList.remove('is-invalid');
                    if (err) err.remove();
                }
            }

            startsInput?.addEventListener('change', event => {
                if (startsInput._flatpickr?.isOpen && event.detail?.previousValue === undefined) return;
                const previousValue = event.detail?.previousValue;
                checkDateOrder(previousValue === undefined || previousValue !== startsInput.value);
            });
            endsInput?.addEventListener('change', event => {
                if (endsInput._flatpickr?.isOpen && event.detail?.previousValue === undefined) return;
                checkDateOrder(false);
            });
            endsInput?.addEventListener('input', event => {
                if (endsInput._flatpickr?.isOpen && event.detail?.previousValue === undefined) return;
                checkDateOrder(false);
            });

            const timezoneInput = document.querySelector('#webinarTimezone');
            const schedulePreview = document.querySelector('#scheduleTimezonePreview');
            const languageInput = document.querySelector('#webinarLanguage');

            document.querySelectorAll('[data-open-picker]').forEach(button => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.openPicker);
                    if (!input) return;
                    if (input._flatpickr) input._flatpickr.open();
                    else if (typeof input.showPicker === 'function') input.showPicker();
                    else input.focus();
                });
            });

            function readableLocalDate(value) {
                if (!value) return null;
                const [date, time] = value.split('T');
                const [year, month, day] = date.split('-').map(Number);
                const [hour, minute] = time.split(':').map(Number);
                return new Intl.DateTimeFormat(document.documentElement.lang || 'en', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: 'numeric',
                    minute: '2-digit'
                }).format(new Date(year, month - 1, day, hour, minute));
            }

            function updateSchedulePreview() {
                if (!schedulePreview) return;
                const timezone = timezoneInput?.value || 'UTC';
                document.querySelectorAll('[data-schedule-timezone]').forEach(chip => {
                    chip.innerHTML = `<i class="bi bi-globe2"></i> ${timezone}`;
                });
                const start = readableLocalDate(startsInput?.value);
                const end = readableLocalDate(endsInput?.value);
                const language = languageInput?.selectedOptions?.[0]?.text || 'English';
                schedulePreview.innerHTML = start ?
                    `<i class="bi bi-clock-history me-1"></i> <strong>${start}${end ? ` – ${end}` : ''}</strong> in <strong>${timezone}</strong> · ${language}` :
                    `Select a start time to preview the webinar schedule in <strong>${timezone}</strong>.`;
            }

            [startsInput, endsInput, timezoneInput, languageInput].forEach(input => {
                input?.addEventListener('change', updateSchedulePreview);
                input?.addEventListener('input', updateSchedulePreview);
            });
            updateSchedulePreview();

            // -------------------------------------------------------------
            // Live Preview Heading Sync
            // -------------------------------------------------------------
            const titleInput = document.querySelector('#webinarTitle');
            const previewHeading = document.querySelector('#previewHeading');
            titleInput?.addEventListener('input', () => {
                if (previewHeading) {
                    previewHeading.textContent = titleInput.value.trim() || 'Your webinar title';
                }
            });

            // -------------------------------------------------------------
            // Video Player Provider Segmented Cards Sync
            // -------------------------------------------------------------
            const providerRadios = document.querySelectorAll('input[name="live_provider_choice"]');
            const liveProviderSelect = document.querySelector('#liveProvider');
            const liveSourceInput = document.querySelector('#liveSource');
            const liveSourceRequiredMark = document.querySelector('#liveSourceRequiredMark');
            const liveSourceHelp = document.querySelector('#liveSourceHelp');

            function syncLiveSourceRequirement() {
                const provider = liveProviderSelect?.value || '';
                const isRequired = provider !== '';
                if (liveSourceInput) {
                    liveSourceInput.required = isRequired;
                    liveSourceInput.setAttribute('aria-required', isRequired ? 'true' : 'false');
                    liveSourceInput.placeholder = isRequired ?
                        `Paste ${provider === 'custom' ? 'a secure iframe URL or iframe code' : `${provider[0].toUpperCase() + provider.slice(1)} URL or ID`}` :
                        'No player selected — video source is not required';
                }
                if (liveSourceRequiredMark) liveSourceRequiredMark.hidden = !isRequired;
                if (liveSourceHelp) {
                    liveSourceHelp.textContent = isRequired ?
                        'Required: a secure responsive player will be configured automatically after you enter a valid source.' :
                        'A video source is not required in No Player mode.';
                }
            }

            providerRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (liveProviderSelect) {
                        liveProviderSelect.value = radio.value;
                        liveProviderSelect.dispatchEvent(new Event('change'));
                    }
                    syncLiveSourceRequirement();
                });
            });
            syncLiveSourceRequirement();

            // -------------------------------------------------------------
            // Room Layout Segmented Cards Sync
            // -------------------------------------------------------------
            const layoutRadios = document.querySelectorAll('input[name="room_layout_choice"]');
            const roomLayoutSelect = document.querySelector('#roomLayout');

            layoutRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (roomLayoutSelect) {
                        roomLayoutSelect.value = radio.value;
                        roomLayoutSelect.dispatchEvent(new Event('input'));
                    }
                });
            });

            // -------------------------------------------------------------
            // Auto-generate Slug from Title
            // -------------------------------------------------------------
            const slug = document.querySelector('#webinarSlug');
            if (titleInput && slug) {
                let manuallyEdited = Boolean(slug.value && slug.value !== '');
                const makeSlug = value => value.toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g,
                    '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 180);
                titleInput.addEventListener('input', () => {
                    if (!manuallyEdited) slug.value = makeSlug(titleInput.value);
                });
                slug.addEventListener('input', event => {
                    manuallyEdited = event.isTrusted && Boolean(slug.value.trim());
                    slug.value = makeSlug(slug.value);
                });
            }

            // -------------------------------------------------------------
            // Dynamic Agenda Rows
            // -------------------------------------------------------------
            // -------------------------------------------------------------
            // Multiple banner media rows and instant image/video previews
            // -------------------------------------------------------------
            const brandRows = document.querySelector('#brandBuilderRows');
            const addBrandRow = document.querySelector('#addBrandRow');
            const syncBrandRemoveButtons = () => {
                const buttons = [...(brandRows?.querySelectorAll('[data-remove-brand]') || [])];
                const rowCount = brandRows?.querySelectorAll('[data-brand-row]:not(.d-none)').length || 0;
                buttons.forEach(button => {
                    button.hidden = rowCount <= 1;
                });
            };
            const validateBrandRow = row => {
                const nameInput = row.querySelector('[data-brand-name]');
                const error = row.querySelector('[data-brand-name-error]');
                if (!nameInput || !error) return true;
                const hasContent = Boolean(
                    row.querySelector('input[name*="[id]"]')?.value ||
                    row.querySelector('[data-brand-logo]')?.files?.length ||
                    row.querySelector('[data-brand-logo-url]')?.value.trim() ||
                    row.querySelector('input[name*="[website_url]"]')?.value.trim()
                );
                const invalid = hasContent && !nameInput.value.trim();
                nameInput.classList.toggle('is-invalid', invalid);
                error.classList.toggle('d-none', !invalid);
                return !invalid;
            };
            const bindBrandRow = row => {
                const input = row.querySelector('[data-brand-logo]');
                const urlInput = row.querySelector('[data-brand-logo-url]');
                const preview = row.querySelector('[data-brand-preview]');
                const renderBrandLogo = source => {
                    if (!source || !preview) return;
                    const image = document.createElement('img');
                    image.src = source;
                    image.alt = row.querySelector('input[name*="[name]"]')?.value || 'Brand logo preview';
                    image.style.width = '100%';
                    image.style.height = '100%';
                    image.style.objectFit = 'contain';
                    image.style.padding = '24px';
                    image.style.background = '#fff';
                    preview.dataset.previewReady = '1';
                    preview.replaceChildren(image);
                };
                input?.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) return;
                    if (urlInput) urlInput.value = '';
                    renderBrandLogo(URL.createObjectURL(file));
                });
                urlInput?.addEventListener('input', () => {
                    const source = urlInput.value.trim();
                    if (!source) return;
                    if (input) input.value = '';
                    renderBrandLogo(source.replace(/["<>]/g, ''));
                });
                if (urlInput?.value.trim() && !preview?.dataset.previewReady) renderBrandLogo(urlInput.value
                    .trim());
                row.querySelectorAll('input').forEach(field => field.addEventListener('input', () =>
                    validateBrandRow(row)));
                row.querySelector('[data-remove-brand]')?.addEventListener('click', () => {
                    row.remove();
                    syncBrandRemoveButtons();
                });
                row.querySelector('[data-remove-saved-brand]')?.addEventListener('click', () => {
                    const checkbox = row.querySelector('[data-remove-brand-checkbox]');
                    if (checkbox) checkbox.checked = true;
                    row.classList.add('d-none');
                    syncBrandRemoveButtons();
                });
            };
            brandRows?.querySelectorAll('[data-brand-row]').forEach(bindBrandRow);
            syncBrandRemoveButtons();
            document.querySelector('#webinarForm')?.addEventListener('submit', event => {
                const invalidRow = [...(brandRows?.querySelectorAll('[data-brand-row]:not(.d-none)') || [])]
                    .find(row => !validateBrandRow(row));
                if (!invalidRow) return;
                event.preventDefault();
                renderStep('2', false);
                invalidRow.querySelector('[data-brand-name]')?.focus();
                invalidRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });
            addBrandRow?.addEventListener('click', () => {
                const index = Date.now();
                const row = document.createElement('div');
                row.className = 'col-12 sortable-card';
                row.dataset.brandRow = '';
                row.innerHTML = `
            <div class="media-config-card"><div class="media-config-grid">
                <div class="media-config-fields"><div class="row g-3">
                    <div class="col-12 builder-card-toolbar"><button class="btn btn-outline-danger btn-sm builder-remove" type="button" data-remove-brand><i class="bi bi-trash3 me-1"></i>Remove brand</button></div>
                    <div class="col-12"><label class="form-label-custom"><span>Brand name</span><span class="req">*</span></label><input class="form-control" name="brands[${index}][name]" maxlength="255" placeholder="e.g. Acme Corp" data-brand-name><div class="wizard-field-error d-none" data-brand-name-error><i class="bi bi-exclamation-circle-fill"></i> Brand name is required when adding a brand.</div></div>
                    <div class="col-12"><label class="form-label-custom"><span>Upload brand logo</span></label><input class="form-control" type="file" name="brand_logos[${index}]" accept="image/png,image/jpeg,image/webp,image/svg+xml" data-brand-logo></div>
                    <div class="col-12"><label class="form-label-custom"><span>Or image URL</span></label><input class="form-control" type="url" name="brands[${index}][logo_url]" placeholder="https://example.com/brand-logo.png" data-brand-logo-url></div>
                    <div class="col-12"><label class="form-label-custom"><span>Website URL (optional)</span></label><input class="form-control" type="url" name="brands[${index}][website_url]" placeholder="https://example.com"></div>
                </div></div>
                <div class="media-preview-pane"><label class="form-label-custom"><span>Brand Logo Preview</span></label><div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden" data-brand-preview data-media-preview><div class="media-preview-empty"><i class="bi bi-patch-check"></i><strong>No brand logo selected</strong><small>Upload the logo displayed in the landing page Brands section.</small></div></div></div>
            </div></div>`;
                brandRows?.appendChild(row);
                bindBrandRow(row);
                syncBrandRemoveButtons();
                row.querySelector('input')?.focus();
            });

            const bannerRows = document.querySelector('#bannerBuilderRows');
            const addBannerRow = document.querySelector('#addBannerRow');

            const renderBannerPreview = (row, source = '') => {
                const preview = row?.querySelector('[data-banner-preview]');
                if (!preview) return;
                preview.replaceChildren();
                delete preview.dataset.previewSrc;
                delete preview.dataset.previewType;
                delete preview.dataset.previewReady;
                if (!source) {
                    const empty = document.createElement('div');
                    empty.className = 'media-preview-empty';
                    empty.innerHTML =
                        '<i class="bi bi-image"></i><strong>No banner selected</strong><small>Upload an image or video, or enter a media URL to see the preview.</small>';
                    preview.appendChild(empty);
                    return;
                }
                const type = row.querySelector('[data-banner-type]')?.value || 'image';
                const safeSource = String(source).trim().replace(/["<>]/g, '');
                const youtubeMatch = type === 'video' ? safeSource.match(
                    /(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/i
                ) : null;
                const vimeoMatch = type === 'video' ? safeSource.match(/vimeo\.com\/(?:video\/)?(\d{6,12})/i) :
                    null;
                if (youtubeMatch || vimeoMatch) {
                    const iframe = document.createElement('iframe');
                    iframe.src = youtubeMatch ?
                        `https://www.youtube-nocookie.com/embed/${youtubeMatch[1]}?rel=0` :
                        `https://player.vimeo.com/video/${vimeoMatch[1]}`;
                    iframe.allow = 'autoplay; fullscreen; picture-in-picture';
                    iframe.allowFullscreen = true;
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.minHeight = '300px';
                    iframe.style.border = '0';
                    preview.dataset.previewSrc = safeSource;
                    preview.dataset.previewType = youtubeMatch ? 'youtube' : 'vimeo';
                    preview.dataset.previewReady = '1';
                    preview.appendChild(iframe);
                    return;
                }
                const media = document.createElement(type === 'video' ? 'video' : 'img');
                media.src = safeSource;
                media.style.width = '100%';
                media.style.height = '100%';
                media.style.minHeight = '300px';
                media.style.objectFit = 'contain';
                if (type === 'video') {
                    media.controls = true;
                    media.muted = true;
                    media.playsInline = true;
                    media.className = 'bg-dark';
                } else {
                    media.alt = 'Banner preview';
                }
                preview.dataset.previewSrc = safeSource;
                preview.dataset.previewType = type;
                preview.dataset.previewReady = '1';
                preview.appendChild(media);
            };

            const syncBannerRemoveButtons = () => {
                const rowCount = bannerRows?.querySelectorAll('[data-banner-row]:not(.d-none)').length || 0;
                bannerRows?.querySelectorAll('[data-remove-banner]').forEach(button => {
                    button.hidden = rowCount <= 1;
                });
            };

            const bindBannerRow = row => {
                const typeInput = row.querySelector('[data-banner-type]');
                const fileInput = row.querySelector('[data-banner-file]');
                const urlInput = row.querySelector('[data-banner-url]');
                const fileLabel = row.querySelector('[data-banner-file-label]');
                const urlLabel = row.querySelector('[data-banner-url-label]');
                const fileError = row.querySelector('[data-banner-file-error]');
                const syncType = () => {
                    const isVideo = typeInput?.value === 'video';
                    if (fileInput) fileInput.accept = isVideo ? 'video/mp4,video/webm,video/quicktime' :
                        'image/png,image/jpeg,image/webp';
                    if (fileLabel) fileLabel.textContent = isVideo ? 'Upload video' : 'Upload image';
                    if (urlLabel) urlLabel.textContent = isVideo ? 'Or video URL' : 'Or image URL';
                    if (urlInput) urlInput.placeholder = isVideo ?
                        'https://example.com/video.mp4 or YouTube/Vimeo URL' :
                        'https://example.com/banner.jpg';
                    if (urlInput?.value.trim()) renderBannerPreview(row, urlInput.value.trim());
                    else if (fileInput?.files?.[0]) renderBannerPreview(row, URL.createObjectURL(fileInput
                        .files[0]));
                    else if (row.querySelector('[data-banner-preview]')?.dataset.initialSrc)
                        renderBannerPreview(row, row.querySelector('[data-banner-preview]').dataset
                            .initialSrc);
                    else renderBannerPreview(row);
                };
                typeInput?.addEventListener('change', syncType);
                fileInput?.addEventListener('change', () => {
                    const file = fileInput.files?.[0];
                    if (fileError) {
                        const tooLarge = Boolean(file && file.size > 10 * 1024 * 1024);
                        fileError.textContent = tooLarge ? 'Video or image must be 10 MB or smaller.' :
                            '';
                        fileError.classList.toggle('show', tooLarge);
                        fileInput.setCustomValidity(tooLarge ?
                            'Video or image must be 10 MB or smaller.' : '');
                        if (tooLarge) {
                            fileInput.value = '';
                            renderBannerPreview(row, row.querySelector('[data-banner-preview]')?.dataset
                                .initialSrc || '');
                            return;
                        }
                    }
                    if (file && urlInput) urlInput.value = '';
                    renderBannerPreview(row, file ? URL.createObjectURL(file) : '');
                });
                urlInput?.addEventListener('input', () => {
                    if (urlInput.value.trim() && fileInput) fileInput.value = '';
                    renderBannerPreview(row, urlInput.value.trim());
                });
                row.querySelector('[data-remove-banner]')?.addEventListener('click', () => {
                    row.remove();
                    syncBannerRemoveButtons();
                });
                row.querySelector('[data-remove-saved-banner]')?.addEventListener('click', () => {
                    const checkbox = row.querySelector('[data-remove-banner-checkbox]');
                    if (checkbox) checkbox.checked = true;
                    row.classList.add('d-none');
                    syncBannerRemoveButtons();
                });
                syncType();
            };

            bannerRows?.querySelectorAll('[data-banner-row]').forEach(bindBannerRow);
            syncBannerRemoveButtons();
            addBannerRow?.addEventListener('click', () => {
                const index = Date.now();
                const row = document.createElement('div');
                row.className = 'col-12 sortable-card';
                row.dataset.bannerRow = '';
                row.innerHTML = `
            <div class="media-config-card"><div class="media-config-grid">
                <div class="media-config-fields"><div class="row g-3 align-items-end">
                    <div class="col-12 builder-card-toolbar"><button class="btn btn-outline-danger btn-sm builder-remove" type="button" data-remove-banner><i class="bi bi-trash3 me-1"></i>Remove banner</button></div>
                    <div class="col-md-6"><label class="form-label-custom"><span>Banner title</span></label><input class="form-control" name="banners[${index}][title]" maxlength="255" placeholder="Landing banner"></div>
                    <div class="col-md-6"><label class="form-label-custom"><span>Media type</span></label><select class="form-select" name="banners[${index}][media_type]" data-banner-type><option value="image">Image</option><option value="video">Video</option></select></div>
                    <div class="col-12"><label class="form-label-custom"><span data-banner-file-label>Upload image</span></label><input class="form-control" type="file" name="banner_media[${index}]" data-banner-file><div class="inline-file-error" data-banner-file-error></div></div>
                    <div class="col-12"><label class="form-label-custom"><span data-banner-url-label>Or image URL</span></label><input class="form-control" type="url" name="banners[${index}][media_url]" data-banner-url placeholder="https://example.com/banner.jpg"></div>
                </div></div>
                <div class="media-preview-pane"><label class="form-label-custom"><span>Banner Preview</span></label><div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden" data-banner-preview data-media-preview><div class="media-preview-empty"><i class="bi bi-image"></i><strong>No banner selected</strong><small>Upload an image or video, or enter a media URL to see the preview.</small></div></div></div>
            </div></div>`;
                bannerRows?.appendChild(row);
                bindBannerRow(row);
                syncBannerRemoveButtons();
                row.querySelector('input')?.focus();
            });

            const speakerRows = document.querySelector('#speakerBuilderRows');
            const syncSpeakerRemoveButtons = () => {
                const buttons = [...(speakerRows?.querySelectorAll('[data-remove-speaker]') || [])];
                buttons.forEach(button => {
                    button.hidden = buttons.length === 1;
                });
            };
            const bindSpeakerRow = row => {
                const input = row.querySelector('[data-speaker-file]');
                const preview = row.querySelector('[data-speaker-preview]');
                input?.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file || !preview) return;
                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = 'Speaker preview';
                    image.style.cssText = 'width:100%;height:100%;object-fit:contain;background:#fff';
                    preview.dataset.previewReady = '1';
                    preview.replaceChildren(image);
                });
                row.querySelector('[data-remove-speaker]')?.addEventListener('click', () => {
                    row.remove();
                    syncSpeakerRemoveButtons();
                });
            };
            speakerRows?.querySelectorAll('[data-speaker-row]').forEach(bindSpeakerRow);
            syncSpeakerRemoveButtons();
            document.querySelector('#addSpeakerRow')?.addEventListener('click', () => {
                const index = Date.now();
                const row = document.createElement('div');
                row.className = 'col-12 sortable-card';
                row.dataset.speakerRow = '';
                row.innerHTML = `<div class="media-config-card"><div class="media-config-grid">
            <div class="media-config-fields"><div class="row g-3">
                <div class="col-12 builder-card-toolbar"><button class="btn btn-outline-danger btn-sm builder-remove" type="button" data-remove-speaker><i class="bi bi-trash3 me-1"></i>Remove speaker</button></div>
                <div class="col-12"><label class="form-label-custom"><span>Speaker name</span><span class="req">*</span></label><input class="form-control" name="speakers[${index}][name]" maxlength="255" placeholder="e.g. Dr. A. Sharma" required></div>
                <div class="col-md-6"><label class="form-label-custom"><span>Headline / Role</span></label><input class="form-control" name="speakers[${index}][headline]" maxlength="255" placeholder="Keynote speaker"></div>
                <div class="col-md-6"><label class="form-label-custom"><span>Company</span></label><input class="form-control" name="speakers[${index}][company]" maxlength="255"></div>
                <div class="col-12"><label class="form-label-custom"><span>Upload speaker photo</span></label><input class="form-control" type="file" name="speaker_photos[${index}]" accept="image/png,image/jpeg,image/webp" data-speaker-file></div>
            </div></div>
            <div class="media-preview-pane"><label class="form-label-custom"><span>Speaker Preview</span></label><div class="media-preview-canvas d-flex align-items-center justify-content-center overflow-hidden" data-speaker-preview data-media-preview><div class="media-preview-empty"><i class="bi bi-person"></i><strong>No speaker photo selected</strong><small>Upload a photo to preview it here.</small></div></div></div>
        </div></div>`;
                speakerRows?.appendChild(row);
                bindSpeakerRow(row);
                syncSpeakerRemoveButtons();
                row.querySelector('input')?.focus();
            });

            const initSortable = (container, itemSelector) => {
                if (!container) return;
                let dragged = null;
                container.addEventListener('pointerdown', event => {
                    const item = event.target.closest(itemSelector);
                    container.querySelectorAll(itemSelector).forEach(entry => {
                        entry.draggable = false;
                    });
                    if (item && event.target.closest('.sort-handle')) item.draggable = true;
                });
                container.addEventListener('dragstart', event => {
                    const item = event.target.closest(itemSelector);
                    if (!item) {
                        event.preventDefault();
                        return;
                    }
                    dragged = item;
                    item.classList.add('is-dragging');
                    event.dataTransfer.effectAllowed = 'move';
                });
                container.addEventListener('dragover', event => {
                    if (!dragged) return;
                    const target = event.target.closest(itemSelector);
                    if (!target || target === dragged) return;
                    event.preventDefault();
                    container.querySelectorAll('.drag-over').forEach(item => item.classList.remove(
                        'drag-over'));
                    target.classList.add('drag-over');
                    const rect = target.getBoundingClientRect();
                    const nearSameRow = Math.abs(event.clientY - (rect.top + rect.height / 2)) < rect
                        .height * .28;
                    const before = nearSameRow ? event.clientX < rect.left + rect.width / 2 : event
                        .clientY < rect.top + rect.height / 2;
                    container.insertBefore(dragged, before ? target : target.nextSibling);
                });
                container.addEventListener('drop', event => event.preventDefault());
                container.addEventListener('dragend', () => {
                    container.querySelectorAll('.is-dragging,.drag-over').forEach(item => item.classList
                        .remove('is-dragging', 'drag-over'));
                    container.querySelectorAll(itemSelector).forEach(item => {
                        item.draggable = false;
                    });
                    dragged = null;
                    document.dispatchEvent(new Event('webinar-builder-changed'));
                });
            };
            const normalizeDuplicateValue = value => String(value || '').trim().toLocaleLowerCase();
            const updateDuplicateWarnings = () => {
                const brandKeys = [...document.querySelectorAll('[data-brand-row]:not(.d-none)')].flatMap(
                    row => [
                        normalizeDuplicateValue(row.querySelector('input[name*="[name]"]')?.value),
                        normalizeDuplicateValue(row.querySelector('[data-brand-logo-url]')?.value),
                    ]).filter(Boolean);
                const bannerKeys = [...document.querySelectorAll('[data-banner-row]:not(.d-none)')].flatMap(
                    row => [
                        normalizeDuplicateValue(row.querySelector('input[name*="[title]"]')?.value),
                        normalizeDuplicateValue(row.querySelector('[data-banner-url]')?.value),
                    ]).filter(Boolean);
                const hasDuplicates = values => new Set(values).size !== values.length;
                document.querySelector('#brandDuplicateAlert')?.classList.toggle('show', hasDuplicates(
                    brandKeys));
                document.querySelector('#bannerDuplicateAlert')?.classList.toggle('show', hasDuplicates(
                    bannerKeys));
            };

            const waitingMediaInput = document.querySelector('#waitingMediaFile');
            const waitingPreview = document.querySelector('[data-waiting-preview]');
            waitingMediaInput?.addEventListener('change', () => {
                const file = waitingMediaInput.files?.[0];
                if (!file || !waitingPreview) return;
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = 'Waiting room background preview';
                image.style.width = '100%';
                image.style.height = '100%';
                image.style.minHeight = '300px';
                image.style.objectFit = 'contain';
                waitingPreview.dataset.previewReady = '1';
                waitingPreview.replaceChildren(image);
            });

            const bindImageUploadPreview = (inputSelector, previewSelector, alt) => {
                const input = document.querySelector(inputSelector);
                const preview = document.querySelector(previewSelector);
                input?.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file || !preview) return;
                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = alt;
                    image.style.width = '100%';
                    image.style.maxHeight = '220px';
                    image.style.objectFit = 'contain';
                    preview.classList.remove('d-none');
                    preview.classList.add('d-flex');
                    preview.dataset.previewReady = '1';
                    preview.replaceChildren(image);
                });
            };
            bindImageUploadPreview('#brandLogoFile', '[data-logo-preview]', 'Client logo preview');

            document.addEventListener('click', event => {
                const preview = event.target.closest('[data-media-preview]');
                if (!preview || preview.closest('#mediaPreviewModal')) return;
                const sourceMedia = preview.querySelector('video, img');
                const previewSrc = sourceMedia?.currentSrc || sourceMedia?.src || preview.dataset
                    .previewSrc;
                if (!previewSrc || typeof window.openMediaPreview !== 'function') return;
                event.preventDefault();
                const isVideo = sourceMedia?.tagName === 'VIDEO';
                if (isVideo) sourceMedia.pause();
                window.openMediaPreview({
                    src: previewSrc,
                    type: preview.dataset.previewType || (isVideo ? 'video' : 'image'),
                    title: sourceMedia?.alt || (isVideo ? 'Video Preview' : 'Image Preview'),
                });
            });

            const existingAssets = {
                logo: {{ !empty($experience['logo_url']) ? 'true' : 'false' }},
                banners: {{ ($webinarBanners ?? collect())->count() }},
                speakers: {{ $webinar->speakers->count() }},
                agenda: {{ ($agendaItems ?? collect())->count() }},
            };
            const updateWizardCompletion = () => {
                const activeSteps = getActiveSteps();
                const health = {};
                const titleReady = Boolean(document.querySelector('#webinarTitle')?.value.trim());
                const provider = document.querySelector('#liveProvider')?.value;
                const sourceReady = !provider || Boolean(document.querySelector('#liveSource')?.value.trim());
                const essentialsMissing = [];
                if (!titleReady) essentialsMissing.push('webinar title');
                if (!sourceReady) essentialsMissing.push('video source');
                health['1'] = {
                    ready: essentialsMissing.length === 0,
                    missing: essentialsMissing
                };

                const hasLogo = existingAssets.logo || Boolean(document.querySelector('#brandLogoFile')?.files
                    ?.length);
                const removedSavedBanners = document.querySelectorAll(
                    'input[name="remove_banner_ids[]"]:checked').length;
                const savedBannerCount = Math.max(0, existingAssets.banners - removedSavedBanners);
                const hasNewBanner = [...document.querySelectorAll('[data-banner-row]')].some(row => row
                    .querySelector('[data-banner-file]')?.files?.length || row.querySelector(
                        '[data-banner-url]')?.value.trim());
                const hasSpeaker = [...document.querySelectorAll('[data-speaker-row] input[name*="[name]"]')]
                    .some(input => input.value.trim());
                const brandingMissing = [];
                if (!hasLogo) brandingMissing.push('logo');
                if (!(savedBannerCount > 0 || hasNewBanner)) brandingMissing.push('banner');
                if (!hasSpeaker) brandingMissing.push('speaker');
                const hasAgenda = [...document.querySelectorAll('[data-agenda-row] input[name*="[title]"]')]
                    .some(input => input.value.trim()) || Boolean(document.querySelector(
                        '[name="agenda_notes"]')?.value.trim());
                if (!hasAgenda) brandingMissing.push('agenda');
                health['2'] = {
                    ready: brandingMissing.length === 0,
                    missing: brandingMissing
                };

                const pollReady = !togglePolls?.checked || [...document.querySelectorAll(
                    '[data-poll-field="question"]')].some(input => input.value.trim());
                const certificateReady = !toggleCert?.checked || Boolean(document.querySelector(
                    '[name="certificate_template_id"]')?.value || document.querySelector(
                    '[name="certificate_template_image"]')?.files?.length);
                const publishMissing = [];
                if (!pollReady) publishMissing.push('poll question');
                if (!certificateReady) publishMissing.push('certificate template');
                health['3'] = {
                    ready: publishMissing.length === 0,
                    missing: publishMissing
                };

                let readyCount = 0;
                const missing = [];
                activeSteps.forEach(step => {
                    const state = health[step.key] || {
                        ready: true,
                        missing: []
                    };
                    if (state.ready) readyCount++;
                    else missing.push(...state.missing);
                    const tab = document.querySelector(`[data-step-nav="${step.key}"]`);
                    tab?.classList.toggle('ready', state.ready);
                    tab?.classList.toggle('needs-attention', !state.ready);
                    if (tab) tab.title = state.ready ? `${step.name} is ready` :
                        `Missing: ${state.missing.join(', ')}`;
                });
                const requiredContentChecks = [titleReady, hasLogo, savedBannerCount > 0 || hasNewBanner,
                    hasSpeaker, hasAgenda
                ];
                const percent = Math.round((requiredContentChecks.filter(Boolean).length / requiredContentChecks
                    .length) * 100);
                const percentLabel = document.querySelector('#wizardCompletionPercent');
                const bar = document.querySelector('#wizardCompletionBar');
                const summary = document.querySelector('#wizardMissingSummary');
                if (percentLabel) percentLabel.textContent = `${percent}%`;
                if (bar) bar.style.width = `${percent}%`;
                if (summary) summary.textContent = missing.length ?
                    `Missing: ${[...new Set(missing)].join(', ')}` : 'All enabled steps are ready';
                updateDuplicateWarnings();
            };

            const mediaSourceFrom = selector => {
                const preview = document.querySelector(selector);
                const media = preview?.querySelector('img,video');
                return media?.currentSrc || media?.src || preview?.dataset.previewSrc || '';
            };
            const renderExperiencePreview = mode => {
                const shell = document.querySelector('#experiencePreviewShell');
                const hero = shell?.querySelector('.experience-preview-hero');
                const title = document.querySelector('#webinarTitle')?.value.trim() || 'Your webinar title';
                const primary = document.querySelector('#brandPrimary')?.value || '#6d28d9';
                const secondary = document.querySelector('#brandSecondary')?.value || '#2563eb';
                if (!shell || !hero) return;
                shell.style.setProperty('--preview-primary', primary);
                shell.style.setProperty('--preview-secondary', secondary);
                shell.classList.toggle('experience-preview-live', mode === 'live');
                document.querySelector('#experiencePreviewMode').textContent = mode === 'live' ?
                    'LIVE ROOM PREVIEW' : 'LIVE LANDING PREVIEW';
                document.querySelector('#experiencePreviewNavTitle').textContent = title;
                document.querySelector('#experiencePreviewTitle').textContent = title;
                const logo = mediaSourceFrom('[data-logo-preview]');
                const nav = shell.querySelector('.experience-preview-nav');
                nav.querySelector('.experience-preview-logo')?.remove();
                if (logo) {
                    const image = document.createElement('img');
                    image.className = 'experience-preview-logo';
                    image.src = logo;
                    image.alt = 'Webinar logo';
                    nav.prepend(image);
                }
                const banner = document.querySelector('#savedBannerRows [data-media-preview] img')?.src ||
                    mediaSourceFrom('[data-banner-preview]');
                hero.style.backgroundImage = mode === 'landing' && banner ?
                    `linear-gradient(rgba(15,23,42,.2),rgba(15,23,42,.48)),url("${banner.replace(/"/g, '')}")` :
                    '';

                const brandList = document.querySelector('#experiencePreviewBrands');
                brandList.replaceChildren();
                [...document.querySelectorAll('[data-brand-row]')].forEach(row => {
                    const name = row.querySelector('input[name*="[name]"]')?.value.trim();
                    if (!name) return;
                    const card = document.createElement('div');
                    card.className = 'experience-preview-brand';
                    const source = row.querySelector('[data-brand-preview] img')?.src;
                    if (source) {
                        const image = document.createElement('img');
                        image.src = source;
                        image.alt = name;
                        card.appendChild(image);
                    } else {
                        const text = document.createElement('strong');
                        text.textContent = name;
                        card.appendChild(text);
                    }
                    brandList.appendChild(card);
                });
                if (!brandList.children.length) brandList.innerHTML =
                    '<span class="text-muted small">Add brands to see them here.</span>';

                const agendaList = document.querySelector('#experiencePreviewAgenda');
                agendaList.replaceChildren();
                [...document.querySelectorAll('[data-agenda-row]')].forEach(row => {
                    const agendaTitle = row.querySelector('input[name*="[title]"]')?.value.trim();
                    if (!agendaTitle) return;
                    const item = document.createElement('div');
                    const time = document.createElement('strong');
                    time.textContent = row.querySelector('input[type="time"]')?.value || '—';
                    const text = document.createElement('span');
                    text.textContent = agendaTitle;
                    item.append(time, text);
                    agendaList.appendChild(item);
                });
                if (!agendaList.children.length) agendaList.innerHTML =
                    '<span class="text-muted small">Add agenda sessions to see them here.</span>';
                if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(document.querySelector(
                    '#experiencePreviewModal')).show();
            };
            document.querySelectorAll('[data-experience-preview]').forEach(button => button.addEventListener(
                'click', () => renderExperiencePreview(button.dataset.experiencePreview)));
            document.addEventListener('input', updateWizardCompletion);
            document.addEventListener('change', updateWizardCompletion);
            document.addEventListener('webinar-builder-changed', updateWizardCompletion);
            document.addEventListener('click', event => {
                if (event.target.closest('[data-remove-brand],[data-remove-banner],[data-delete-agenda]'))
                    setTimeout(updateWizardCompletion);
            });
            updateWizardCompletion();

            const agendaRows = document.querySelector('#agendaBuilderRows');
            const addAgenda = document.querySelector('#addAgendaItem');
            const minutesToTime = totalMinutes => {
                const normalized = ((totalMinutes % 1440) + 1440) % 1440;
                const hours = Math.floor(normalized / 60);
                const minutes = normalized % 60;
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
            };
            const nextAgendaTime = row => {
                const time = row?.querySelector('input[type="time"]')?.value;
                const duration = Number(row?.querySelector('input[type="number"]')?.value);
                if (!time || !Number.isFinite(duration) || duration <= 0) return '';
                const [hours, minutes] = time.split(':').map(Number);
                return minutesToTime((hours * 60) + minutes + duration);
            };
            const syncFollowingAgendaTimes = startRow => {
                let row = startRow;
                while (row) {
                    const nextRow = row.nextElementSibling;
                    if (!nextRow?.matches('[data-agenda-row]')) break;
                    const nextInput = nextRow.querySelector('input[type="time"]');
                    if (!nextInput || (nextInput.value && nextInput.dataset.autoTime !== '1')) break;
                    const suggestedTime = nextAgendaTime(row);
                    if (!suggestedTime) break;
                    nextInput.value = suggestedTime;
                    nextInput.dataset.autoTime = '1';
                    row = nextRow;
                }
            };
            const bindAgendaDelete = row => row.querySelector('[data-delete-agenda]')?.addEventListener('click',
                () => {
                    const allRows = agendaRows ? agendaRows.querySelectorAll('[data-agenda-row]') : [];
                    if (allRows.length > 1) {
                        const previousRow = row.previousElementSibling;
                        row.remove();
                        if (previousRow?.matches('[data-agenda-row]')) syncFollowingAgendaTimes(previousRow);
                    } else {
                        row.querySelectorAll('input').forEach(input => {
                            if (input.type === 'number') {
                                input.value = '30';
                            } else {
                                input.value = '';
                            }
                        });
                    }
                });
            agendaRows?.querySelectorAll('[data-agenda-row]').forEach(bindAgendaDelete);
            agendaRows?.addEventListener('input', event => {
                const row = event.target.closest('[data-agenda-row]');
                if (!row) return;
                if (event.target.matches('input[type="time"]') && event.isTrusted) {
                    event.target.dataset.autoTime = '0';
                }
                if (event.target.matches('input[type="time"], input[type="number"]')) {
                    syncFollowingAgendaTimes(row);
                }
            });

            addAgenda?.addEventListener('click', () => {
                const previousRow = agendaRows?.querySelector('[data-agenda-row]:last-child');
                const suggestedTime = nextAgendaTime(previousRow);
                const index = Date.now();
                const row = document.createElement('div');
                row.className =
                    'agenda-builder-row p-3 mb-2 bg-light border rounded-3 d-flex align-items-center gap-3';
                row.dataset.agendaRow = '';
                row.innerHTML = `
            <div style="width:130px;">
                <label class="form-label-custom small mb-1"><span>Start Time</span></label>
                <input class="form-control form-control-sm" type="time" name="agenda[${index}][starts_at]" value="${suggestedTime}" ${suggestedTime ? 'data-auto-time="1"' : ''}>
            </div>
            <div class="flex-grow-1">
                <label class="form-label-custom small mb-1"><span>Session Title</span></label>
                <input class="form-control form-control-sm" name="agenda[${index}][title]" maxlength="255" placeholder="e.g. Session Topic">
            </div>
            <div style="width:110px;">
                <label class="form-label-custom small mb-1"><span>Minutes</span></label>
                <input class="form-control form-control-sm" type="number" min="1" max="1440" name="agenda[${index}][duration_minutes]" placeholder="30">
            </div>
            <div class="pt-4">
                <button class="btn btn-sm btn-outline-danger" type="button" data-delete-agenda title="Delete agenda item"><i class="bi bi-trash3"></i></button>
            </div>
        `;
                agendaRows.appendChild(row);
                bindAgendaDelete(row);
                row.querySelector('input[type="time"]')?.focus();
            });
        });
    </script>
@endsection
