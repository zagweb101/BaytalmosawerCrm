<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="CRM">
    <title>@yield('title', 'CRM Laravel')</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap');

        @keyframes fadeLift {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        :root {
            color-scheme: light;
            --font-ui: 'IBM Plex Sans Arabic', 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif;
            --font-display: 'Cairo', 'IBM Plex Sans Arabic', 'Tajawal', Tahoma, Arial, sans-serif;
            --ink: #18202f;
            --muted: #667085;
            --line: #dde3eb;
            --soft-line: #edf1f5;
            --page: #f5f7fb;
            --panel: #ffffff;
            --brand: #0f766e;
            --brand-strong: #115e59;
            --accent: #b42318;
            --gold: #b7791f;
            --shadow-soft: 0 10px 28px rgba(16, 24, 40, .06);
            --ease: cubic-bezier(.2, .8, .2, 1);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: var(--font-ui);
            background: var(--page);
            color: var(--ink);
            font-size: 14px;
            font-weight: 400;
            line-height: 1.65;
        }

        a { color: inherit; text-decoration: none; }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid rgba(15, 118, 110, .28);
            outline-offset: 2px;
        }

        .shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar {
            background: var(--panel);
            border-bottom: 1px solid var(--line);
        }

        .topbar-inner {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .mobile-nav-toggle,
        .mobile-nav-close,
        .mobile-nav-backdrop,
        .mobile-nav-head {
            display: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sidebar-section {
            display: grid;
            gap: 4px;
        }

        .sidebar-label {
            margin: 8px 8px 2px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 32px;
            padding: 5px 9px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: var(--ink);
            font-size: 13px;
            font-weight: 500;
            transition: background-color .16s var(--ease), border-color .16s var(--ease), color .16s var(--ease), transform .16s var(--ease);
        }

        .nav-item:hover,
        .nav-item.active {
            border-color: #c8e7df;
            background: #eef9f6;
            color: var(--brand-strong);
            transform: translateX(-2px);
        }

        .nav-item.primary {
            justify-content: center;
            background: var(--brand);
            color: white;
            min-height: 36px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 18px;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: var(--brand);
            color: white;
            font-weight: 600;
            font-size: 13px;
        }

        main { padding: 24px 0 38px; }

        .hero {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-family: var(--font-display);
            font-size: clamp(24px, 3vw, 34px);
            line-height: 1.2;
            letter-spacing: 0;
            font-weight: 600;
        }

        .subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            max-width: 680px;
            font-size: 13px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 7px 12px;
            border: 1px solid var(--brand);
            border-radius: 8px;
            background: var(--brand);
            color: white;
            cursor: pointer;
            font: inherit;
            font-weight: 500;
            font-size: 13px;
            white-space: nowrap;
            transition: transform .16s var(--ease), background-color .16s var(--ease), border-color .16s var(--ease), box-shadow .16s var(--ease);
        }

        .button:hover {
            background: var(--brand-strong);
            box-shadow: var(--shadow-soft);
            transform: translateY(-1px);
        }
        .button.secondary { background: white; color: var(--brand); }
        .button.secondary:hover { background: #eef9f6; }
        .button.danger { background: var(--accent); border-color: var(--accent); }
        .button.disabled,
        .button[aria-disabled="true"] {
            opacity: .5;
            pointer-events: none;
            cursor: not-allowed;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .company-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .stat, .metric-card, .company-card, .panel, .form-panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .025);
            animation: fadeLift .22s var(--ease) both;
        }

        .stat { padding: 14px; }
        .stat span { display: block; color: var(--muted); font-size: 12px; }
        .stat strong { display: block; margin-top: 3px; font-size: 24px; font-weight: 600; }

        .metric-card {
            padding: 14px;
        }

        .metric-card span {
            display: block;
            color: var(--muted);
            font-size: 12px;
        }

        .metric-card strong {
            display: block;
            margin-top: 3px;
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .two-column-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .pipeline-board {
            display: grid;
            grid-template-columns: repeat(5, minmax(220px, 1fr));
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .pipeline-column {
            min-height: 420px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fafc;
        }

        .pipeline-column h2 {
            margin: 0 0 12px;
            font-family: var(--font-display);
            font-size: 18px;
            letter-spacing: 0;
        }

        .pipeline-card {
            display: grid;
            gap: 8px;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
        }

        .pipeline-card strong {
            font-family: var(--font-display);
            letter-spacing: 0;
        }

        .status-list {
            display: grid;
            gap: 12px;
            padding: 18px;
        }

        .status-row {
            display: grid;
            grid-template-columns: minmax(120px, .7fr) 1fr auto;
            align-items: center;
            gap: 12px;
        }

        .bar {
            height: 10px;
            overflow: hidden;
            border-radius: 999px;
            background: #edf2f7;
        }

        .bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--brand);
        }

        .company-card {
            padding: 14px;
            display: grid;
            gap: 6px;
        }

        .company-card h2 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .company-card p {
            margin: 0;
            color: var(--muted);
        }

        .company-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .panel { overflow: hidden; }
        .padded-panel { padding: 16px; }
        .mt-panel { margin-top: 14px; }

        .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
            gap: 14px;
        }

        .detail-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 14px 0 0;
        }

        .detail-list dt {
            color: var(--muted);
            font-size: 12px;
        }

        .detail-list dd {
            margin: 3px 0 0;
            font-weight: 500;
        }

        .note-box {
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fafc;
            color: var(--ink);
        }

        .recommendation-card {
            border-color: #b7ded5;
            background: #f5fbf9;
        }

        .recommendation-card.high-priority {
            border-color: #fecdca;
            background: #fff7f6;
        }

        .daily-summary-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .daily-summary-modal.is-open {
            display: flex;
        }

        .daily-summary-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .42);
        }

        .daily-summary-panel {
            position: relative;
            width: min(860px, 100%);
            max-height: calc(100vh - 32px);
            overflow-y: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: 0 22px 60px rgba(15, 23, 42, .22);
        }

        .daily-summary-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        body.daily-summary-open {
            overflow: hidden;
        }

        .section-head {
            padding: 13px 16px;
            border-bottom: 1px solid var(--line);
        }

        .section-head h2,
        .padded-panel h2,
        .form-panel h2 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .avatar-row {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .avatar-preview,
        .avatar-placeholder {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .avatar-preview {
            object-fit: cover;
            border: 2px solid var(--line);
        }

        .avatar-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--soft-line);
            color: var(--muted);
            font-size: 2rem;
            font-weight: 700;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1fr minmax(150px, 210px) auto;
            gap: 8px;
            padding: 12px;
            border-bottom: 1px solid var(--line);
        }

        .toolbar-wide {
            grid-template-columns: 1fr repeat(6, minmax(120px, 1fr)) auto;
        }

        input, select, textarea {
            width: 100%;
            min-height: 38px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 7px 10px;
            font: inherit;
            background: white;
            color: var(--ink);
            font-size: 13px;
            transition: border-color .16s var(--ease), box-shadow .16s var(--ease);
        }

        input:hover, select:hover, textarea:hover { border-color: #c7d3df; }
        textarea { min-height: 104px; resize: vertical; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500; }

        .check-row {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .check-row input {
            width: auto;
            min-height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 11px 12px;
            text-align: right;
            border-bottom: 1px solid var(--soft-line);
            vertical-align: middle;
        }

        th { color: var(--muted); font-size: 12px; font-weight: 600; }

        .customer-name { font-weight: 600; }
        .muted { color: var(--muted); }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e6f4f1;
            color: var(--brand-strong);
            font-size: 12px;
            font-weight: 500;
        }

        .badge.inactive { background: #f2f4f7; color: #475467; }
        .badge.prospect { background: #fff4df; color: var(--gold); }
        .badge.contacted { background: #e0f2fe; color: #026aa2; }
        .company-badge { background: #eef2ff; color: #3538cd; }
        .danger-badge { background: #fee4e2; color: var(--accent); }

        .tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            padding: 6px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
        }

        .tab-button {
            min-height: 34px;
            padding: 6px 10px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            font: inherit;
            font-size: 13px;
            font-weight: 500;
        }

        .tab-button.active {
            border-color: #c8e7df;
            background: #eef9f6;
            color: var(--brand-strong);
        }

        [data-customer-tab][hidden] {
            display: none !important;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .flash {
            margin-bottom: 12px;
            padding: 10px 12px;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            background: #ecfdf5;
            color: #065f46;
            font-weight: 500;
        }

        .form-panel { padding: 16px; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .one-column { grid-template-columns: 1fr; }

        .full { grid-column: 1 / -1; }
        .error { margin-top: 6px; color: var(--accent); font-size: 13px; }
        .form-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .empty { padding: 26px 14px; text-align: center; color: var(--muted); font-size: 13px; }

        .timeline {
            display: grid;
            gap: 10px;
            padding: 14px;
        }

        .timeline-item {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcfe;
        }

        .timeline-time {
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
        }

        .timeline-body strong {
            display: block;
            margin-bottom: 4px;
        }

        .timeline-body p {
            margin: 0;
            color: var(--muted);
        }

        .pagination { padding: 14px; }
        .pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            color: var(--muted);
        }

        .pager-actions {
            display: flex;
            gap: 8px;
        }

        .pager .disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        @media (min-width: 981px) {
            .topbar {
                position: fixed;
                inset: 0 0 0 auto;
                width: 248px;
                overflow: hidden;
                border-bottom: 0;
                border-left: 1px solid var(--line);
            }

            .topbar-inner {
                width: 100%;
                min-height: 100%;
                padding: 10px 11px;
                align-items: stretch;
                justify-content: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            .topbar .brand {
                gap: 8px;
                font-size: 15px;
            }

            .topbar .brand-mark {
                width: 30px;
                height: 30px;
                font-size: 11px;
            }

            .nav-links {
                align-items: stretch;
                flex-direction: column;
                justify-content: space-between;
                gap: 3px;
                flex: 1;
            }

            .sidebar-section {
                gap: 2px;
            }

            .sidebar-label {
                margin: 3px 7px 0;
                font-size: 9px;
                line-height: 1.2;
            }

            .nav-item {
                min-height: 25px;
                padding: 2px 8px;
                font-size: 13px;
            }

            .nav-item.primary {
                min-height: 30px;
            }

            .nav-links form {
                margin: 2px 0 0;
            }

            .nav-links form .button {
                width: 100%;
                min-height: 30px;
                padding: 4px 9px;
                font-size: 13px;
            }

            main {
                margin-right: 248px;
                padding-top: 28px;
            }

            main .shell {
                width: min(1240px, calc(100% - 38px));
            }
        }

        @media (min-width: 981px) and (max-height: 780px) {
            body { font-size: 13px; }
            .topbar-inner { padding: 8px 10px; gap: 6px; }
            .topbar .brand { font-size: 14px; }
            .topbar .brand-mark { width: 28px; height: 28px; }
            .nav-links { gap: 2px; }
            .sidebar-label { margin: 2px 7px 0; font-size: 8px; }
            .nav-item { min-height: 23px; padding: 1px 8px; font-size: 11px; }
            .nav-item.primary { min-height: 28px; }
            .button { min-height: 34px; padding: 5px 10px; font-size: 12px; }
            .nav-links form .button { min-height: 28px; padding: 3px 8px; }
        }

        @media (min-width: 981px) and (max-height: 680px) {
            .sidebar-label {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
            }
        }

        @media (max-width: 980px) {
            .shell {
                width: min(100% - 24px, 920px);
            }

            body.mobile-nav-open {
                overflow: hidden;
            }

            .topbar {
                position: sticky;
                top: 0;
                z-index: 900;
            }

            .topbar-inner {
                min-height: 64px;
                align-items: center;
                flex-direction: row;
                padding: 10px 0;
            }

            .mobile-nav-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                min-width: 44px;
                height: 44px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: white;
                color: var(--ink);
                cursor: pointer;
                font: inherit;
                font-size: 24px;
                font-weight: 500;
            }

            .mobile-nav-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                min-width: 42px;
                height: 42px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: white;
                color: var(--ink);
                cursor: pointer;
                font: inherit;
                font-size: 24px;
                font-weight: 500;
            }

            .mobile-nav-backdrop {
                position: fixed;
                inset: 0;
                z-index: 998;
                display: none;
                background: rgba(15, 23, 42, .42);
            }

            .mobile-nav-backdrop.is-open {
                display: block;
            }

            .nav-links {
                position: fixed;
                inset: 0 0 0 auto;
                z-index: 999;
                width: min(86vw, 340px);
                max-width: 100%;
                height: 100vh;
                padding: 14px;
                overflow: hidden;
                display: grid;
                grid-template-columns: 1fr;
                align-items: stretch;
                align-content: start;
                gap: 7px;
                background: var(--panel);
                border-left: 1px solid var(--line);
                box-shadow: -18px 0 48px rgba(15, 23, 42, .18);
                transform: translateX(105%);
                transition: transform .22s ease;
            }

            .nav-links.is-open {
                transform: translateX(0);
            }

            .mobile-nav-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
            }

            .sidebar-section {
                gap: 5px;
            }

            .hero {
                align-items: stretch;
            }

            .dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .detail-grid,
            .two-column-grid,
            .company-grid {
                grid-template-columns: 1fr;
            }

            .toolbar,
            .toolbar-wide {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pipeline-board {
                grid-template-columns: repeat(5, minmax(240px, 1fr));
            }
        }

        @media (max-width: 980px) and (max-height: 720px) {
            .nav-links {
                width: min(88vw, 320px);
                padding: 10px;
                gap: 4px;
            }

            .mobile-nav-head {
                margin-bottom: 2px;
            }

            .mobile-nav-close {
                width: 34px;
                min-width: 34px;
                height: 34px;
                font-size: 20px;
            }

            .nav-item {
                min-height: 29px;
                padding: 3px 8px;
                font-size: 12px;
            }

            .nav-item.primary {
                min-height: 31px;
            }

            .sidebar-section {
                gap: 3px;
            }

            .sidebar-label {
                position: absolute;
                width: 1px;
                height: 1px;
                margin: 0;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
            }
        }

        @media (max-width: 980px) and (max-height: 620px) {
            .nav-links {
                padding: 8px;
                gap: 3px;
            }

            .brand {
                font-size: 15px;
            }

            .brand-mark {
                width: 30px;
                height: 30px;
            }

            .nav-item {
                min-height: 25px;
                padding: 2px 7px;
                font-size: 11px;
            }

            .nav-item.primary {
                min-height: 28px;
            }
        }

        @media (max-width: 760px) {
            .hero { align-items: stretch; flex-direction: column; }
            .stats, .dashboard-grid, .company-grid, .two-column-grid, .detail-grid, .detail-list, .form-grid, .toolbar, .timeline-item { grid-template-columns: 1fr; }
            .daily-summary-head { align-items: stretch; flex-direction: column; }
            .panel { overflow-x: visible; }
            .button { width: 100%; }
            .status-row { grid-template-columns: 1fr; }
            .pager { align-items: stretch; flex-direction: column; }
            .pager-actions { flex-direction: column; }
        }

        @media (max-width: 640px) {
            main { padding: 18px 0 30px; }
            .shell { width: calc(100% - 18px); }
            h1 { font-size: 28px; }
            .brand { font-size: 18px; }
            .brand-mark { width: 38px; height: 38px; }
            .sidebar-label { margin-top: 10px; }
            .metric-card, .stat, .company-card, .padded-panel, .form-panel { padding: 14px; }
            .section-head { padding: 14px; }
            .tabs {
                position: sticky;
                top: 0;
                z-index: 5;
                overflow-x: auto;
                flex-wrap: nowrap;
            }
            .tab-button { white-space: nowrap; }
            .actions,
            .form-actions { align-items: stretch; flex-direction: column; }
            .actions .button,
            .form-actions .button,
            .actions form,
            .form-actions form { width: 100%; }

            table.responsive-card-table,
            table.responsive-card-table thead,
            table.responsive-card-table tbody,
            table.responsive-card-table tr,
            table.responsive-card-table th,
            table.responsive-card-table td {
                display: block;
                width: 100%;
            }

            table.responsive-card-table {
                border-collapse: separate;
                border-spacing: 0 10px;
                background: transparent;
            }

            table.responsive-card-table thead {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
            }

            table.responsive-card-table tr {
                border: 1px solid var(--line);
                border-radius: 8px;
                background: white;
                overflow: hidden;
            }

            table.responsive-card-table td {
                display: grid;
                grid-template-columns: minmax(92px, .42fr) minmax(0, 1fr);
                gap: 10px;
                padding: 11px 12px;
                border-bottom: 1px solid #eef1f5;
                text-align: right;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            table.responsive-card-table td:last-child {
                border-bottom: 0;
            }

            table.responsive-card-table td::before {
                content: attr(data-label);
                color: var(--muted);
                font-size: 12px;
                font-weight: 500;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="shell topbar-inner">
            <a class="brand" href="{{ route('customers.index') }}">
                <span class="brand-mark">CRM</span>
                <span>إدارة العملاء</span>
            </a>
            <button class="mobile-nav-toggle" type="button" aria-label="فتح القائمة" aria-controls="main-navigation" aria-expanded="false">☰</button>
            <div class="mobile-nav-backdrop" data-close-mobile-nav></div>
            <nav id="main-navigation" class="nav-links" aria-label="التنقل الرئيسي">
                <div class="mobile-nav-head">
                    <div class="brand">
                        <span class="brand-mark">CRM</span>
                        <span>القائمة</span>
                    </div>
                    <button class="mobile-nav-close" type="button" aria-label="إغلاق القائمة" data-close-mobile-nav>×</button>
                </div>

                @if (auth()->user()?->canDo('customers.create'))
                    <div class="sidebar-section">
                        <a class="nav-item primary" href="{{ route('customers.create') }}">عميل جديد</a>
                    </div>
                @endif

                <div class="sidebar-section">
                    <div class="sidebar-label">العمل اليومي</div>
                    @if (auth()->user()?->canDo('dashboard.view'))
                        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">الرئيسية</a>
                    @endif
                    @if (auth()->user()?->canDo('alerts.view'))
                        <a class="nav-item {{ request()->routeIs('alerts.index') ? 'active' : '' }}" href="{{ route('alerts.index') }}">
                            <span>التنبيهات</span>
                            @if (($globalAlertCount ?? 0) > 0)
                                <span class="badge danger-badge">{{ $globalAlertCount }}</span>
                            @endif
                        </a>
                    @endif
                    @if (auth()->user()?->canDo('tasks.view'))
                        <a class="nav-item {{ request()->routeIs('tasks.today') ? 'active' : '' }}" href="{{ route('tasks.today') }}">المهام</a>
                    @endif
                    @if (auth()->user()?->canDo('followups.view'))
                        <a class="nav-item {{ request()->routeIs('follow-ups.today') ? 'active' : '' }}" href="{{ route('follow-ups.today') }}">المتابعات</a>
                    @endif
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-label">العملاء والمبيعات</div>
                    @if (auth()->user()?->canDo('customers.view'))
                        <a class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">العملاء</a>
                        <a class="nav-item {{ request()->routeIs('pipeline.*') ? 'active' : '' }}" href="{{ route('pipeline.index') }}">المسار</a>
                    @endif
                    @if (auth()->user()?->canDo('deals.view'))
                        <a class="nav-item {{ request()->routeIs('deals.*') ? 'active' : '' }}" href="{{ route('deals.index') }}">الصفقات</a>
                    @endif
                </div>

                @if (auth()->user()?->canDo('reports.view'))
                    <div class="sidebar-section">
                        <div class="sidebar-label">التقارير</div>
                        <a class="nav-item {{ request()->routeIs('reports.sources') ? 'active' : '' }}" href="{{ route('reports.sources') }}">المصادر</a>
                        <a class="nav-item {{ request()->routeIs('reports.interests') ? 'active' : '' }}" href="{{ route('reports.interests') }}">الرغبات</a>
                        <a class="nav-item {{ request()->routeIs('reports.campaigns') ? 'active' : '' }}" href="{{ route('reports.campaigns') }}">الحملات</a>
                        <a class="nav-item {{ request()->routeIs('reports.team') ? 'active' : '' }}" href="{{ route('reports.team') }}">أداء الفريق</a>
                        <a class="nav-item {{ request()->routeIs('reports.duplicates') ? 'active' : '' }}" href="{{ route('reports.duplicates') }}">المكررين</a>
                    </div>
                @endif

                @if (auth()->user()?->canDo('campaigns.manage') || auth()->user()?->canDo('team.manage') || auth()->user()?->canDo('users.manage') || auth()->user()?->canDo('offerings.manage') || auth()->user()?->canDo('customers.import') || auth()->user()?->canDo('roles.manage'))
                    <div class="sidebar-section">
                        <div class="sidebar-label">الإدارة</div>
                        @if (auth()->user()?->canDo('campaigns.manage'))
                            <a class="nav-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.index') }}">الحملات</a>
                        @endif
                        @if (auth()->user()?->canDo('team.manage'))
                            <a class="nav-item {{ request()->routeIs('team-members.*') ? 'active' : '' }}" href="{{ route('team-members.index') }}">الفريق</a>
                        @endif
                        @if (auth()->user()?->canDo('users.manage'))
                            <a class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">المستخدمون</a>
                        @endif
                        @if (auth()->user()?->canDo('roles.manage'))
                            <a class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">الأدوار</a>
                        @endif
                        @if (auth()->user()?->canDo('offerings.manage'))
                            <a class="nav-item {{ request()->routeIs('offerings.*') ? 'active' : '' }}" href="{{ route('offerings.index') }}">العروض</a>
                        @endif
                        @if (auth()->user()?->canDo('customers.import'))
                            <a class="nav-item {{ request()->routeIs('customers.import.*') ? 'active' : '' }}" href="{{ route('customers.import.create') }}">استيراد</a>
                        @endif
                    </div>
                @endif

                <div class="sidebar-section">
                    <div class="sidebar-label">الحساب</div>
                    <a class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">البروفايل</a>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button danger" type="submit">خروج</button>
                </form>
            </nav>
        </div>
    </header>

    <main>
        <div class="shell">
            @yield('content')
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.getElementById('main-navigation');
            const navToggle = document.querySelector('.mobile-nav-toggle');
            const navBackdrop = document.querySelector('.mobile-nav-backdrop');
            const navClosers = document.querySelectorAll('[data-close-mobile-nav]');

            const closeMobileNav = () => {
                nav?.classList.remove('is-open');
                navBackdrop?.classList.remove('is-open');
                document.body.classList.remove('mobile-nav-open');
                navToggle?.setAttribute('aria-expanded', 'false');
            };

            const openMobileNav = () => {
                nav?.classList.add('is-open');
                navBackdrop?.classList.add('is-open');
                document.body.classList.add('mobile-nav-open');
                navToggle?.setAttribute('aria-expanded', 'true');
                nav?.querySelector('a, button')?.focus();
            };

            navToggle?.addEventListener('click', openMobileNav);
            navClosers.forEach((closer) => closer.addEventListener('click', closeMobileNav));
            nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMobileNav));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMobileNav();
                }
            });

            document.querySelectorAll('table').forEach((table) => {
                const headers = Array.from(table.querySelectorAll('thead th')).map((header) => header.textContent.trim());

                if (!headers.length) {
                    return;
                }

                table.classList.add('responsive-card-table');
                table.querySelectorAll('tbody tr').forEach((row) => {
                    Array.from(row.children).forEach((cell, index) => {
                        if (!cell.hasAttribute('data-label')) {
                            cell.setAttribute('data-label', headers[index] || '');
                        }
                    });
                });
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/service-worker.js').catch(() => {});
                });
            }
        });
    </script>
</body>
</html>
