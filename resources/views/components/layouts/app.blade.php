<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Game Manager' }} — Game Data Manager</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="description" content="Dynamic game data manager for editing JSON game configurations">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        /* ═══════════════════════════════════════════════════
           DESIGN SYSTEM — Game Manager
           ═══════════════════════════════════════════════════ */

        :root {
            /* Colors — Dark Gaming Theme */
            --bg-primary: #0a0e1a;
            --bg-secondary: #111827;
            --bg-tertiary: #1a2035;
            --bg-card: #151c2e;
            --bg-card-hover: #1c2540;
            --bg-input: #0d1220;
            --bg-input-focus: #111827;

            --border-default: #1e293b;
            --border-hover: #334155;
            --border-focus: #6366f1;
            --border-accent: rgba(99, 102, 241, 0.3);

            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-tertiary: #64748b;
            --text-muted: #475569;

            --accent-primary: #6366f1;
            --accent-primary-hover: #818cf8;
            --accent-secondary: #8b5cf6;
            --accent-glow: rgba(99, 102, 241, 0.15);

            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.1);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.1);
            --info: #3b82f6;
            --info-bg: rgba(59, 130, 246, 0.1);

            --gradient-accent: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa);
            --gradient-dark: linear-gradient(180deg, #111827, #0a0e1a);
            --gradient-card: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.03));

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 20px rgba(99, 102, 241, 0.15);

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;

            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-mono: 'JetBrains Mono', 'Fira Code', monospace;

            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 400ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ═══ RESET & BASE ═══ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        /* Background pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(99, 102, 241, 0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        a { color: var(--accent-primary); text-decoration: none; transition: color var(--transition-fast); }
        a:hover { color: var(--accent-primary-hover); }

        /* ═══ LAYOUT ═══ */
        .app-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        .sidebar {
            width: 280px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-default);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-default);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-accent);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow-glow);
        }

        .sidebar-logo-text {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .sidebar-logo-sub {
            font-size: 11px;
            color: var(--text-tertiary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-tertiary);
            padding: 8px 12px 8px;
            margin-top: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            transition: all var(--transition-fast);
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: var(--accent-glow);
            color: var(--accent-primary-hover);
            border: 1px solid var(--border-accent);
        }

        .nav-item-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .nav-item-count {
            margin-left: auto;
            background: var(--bg-tertiary);
            color: var(--text-tertiary);
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
        }

        .page-header {
            padding: 28px 36px 20px;
            border-bottom: 1px solid var(--border-default);
            background: rgba(17, 24, 39, 0.5);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .page-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .page-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-tertiary);
            margin-bottom: 8px;
        }

        .page-breadcrumb a {
            color: var(--text-tertiary);
        }

        .page-breadcrumb a:hover {
            color: var(--accent-primary);
        }

        .page-breadcrumb .separator {
            opacity: 0.4;
        }

        .page-content {
            padding: 28px 36px;
        }

        /* ═══ CARDS ═══ */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: all var(--transition-normal);
        }

        .card:hover {
            border-color: var(--border-hover);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-default);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
        }

        /* ═══ BUTTONS ═══ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all var(--transition-fast);
            white-space: nowrap;
            line-height: 1;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-primary {
            background: var(--gradient-accent);
            color: white;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
            color: white;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            border-color: var(--border-default);
        }

        .btn-secondary:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
            border-color: var(--border-hover);
        }

        .btn-danger {
            background: var(--danger-bg);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .btn-success {
            background: var(--success-bg);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }

        .btn-icon {
            padding: 8px;
            width: 34px;
            height: 34px;
            justify-content: center;
        }

        .btn-group {
            display: flex;
            gap: 8px;
        }

        /* ═══ FORMS ═══ */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 14px;
            font-family: var(--font-sans);
            color: var(--text-primary);
            transition: all var(--transition-fast);
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            background: var(--bg-input-focus);
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 20px;
            padding-right: 36px;
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
            font-family: var(--font-mono);
            font-size: 13px;
            line-height: 1.5;
        }

        .form-checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-primary);
            cursor: pointer;
        }

        .form-checkbox-label {
            font-size: 14px;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .form-help {
            font-size: 12px;
            color: var(--text-tertiary);
            margin-top: 4px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .form-inline {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .form-inline .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        /* ═══ TABLES ═══ */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background: var(--bg-tertiary);
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-tertiary);
            border-bottom: 1px solid var(--border-default);
            text-align: left;
            position: sticky;
            top: 0;
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }

        .data-table th:hover {
            color: var(--text-secondary);
        }

        .data-table th:first-child { border-radius: var(--radius-sm) 0 0 0; }
        .data-table th:last-child { border-radius: 0 var(--radius-sm) 0 0; }

        .data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-default);
            font-size: 14px;
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .data-table tr {
            transition: background var(--transition-fast);
        }

        .data-table tbody tr:hover {
            background: var(--bg-card-hover);
        }

        .data-table tbody tr:hover td {
            color: var(--text-primary);
        }

        .sort-indicator {
            margin-left: 4px;
            opacity: 0.5;
        }

        /* ═══ TAGS ═══ */
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: var(--accent-glow);
            border: 1px solid var(--border-accent);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: var(--accent-primary-hover);
        }

        .tag-remove {
            background: none;
            border: none;
            color: var(--text-tertiary);
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            line-height: 1;
            transition: color var(--transition-fast);
        }

        .tag-remove:hover {
            color: var(--danger);
        }

        /* ═══ COLLECTION GRID ═══ */
        .collection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .collection-card {
            background: var(--bg-card);
            background-image: var(--gradient-card);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: all var(--transition-normal);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .collection-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-accent);
            opacity: 0;
            transition: opacity var(--transition-normal);
        }

        .collection-card:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }

        .collection-card:hover::before {
            opacity: 1;
        }

        .collection-card-icon {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .collection-card-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .collection-card-desc {
            font-size: 13px;
            color: var(--text-tertiary);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .collection-card-stats {
            display: flex;
            gap: 20px;
        }

        .collection-card-stat {
            display: flex;
            flex-direction: column;
        }

        .collection-card-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent-primary-hover);
        }

        .collection-card-stat-label {
            font-size: 11px;
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .collection-card-actions {
            position: absolute;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .collection-card:hover .collection-card-actions {
            opacity: 1;
        }

        /* ═══ FIELD EDITOR ═══ */
        .field-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .field-item:hover {
            border-color: var(--border-hover);
            background: var(--bg-card-hover);
        }

        .field-item.nested {
            margin-left: 32px;
            border-left: 3px solid var(--accent-primary);
        }

        .field-item-key {
            font-family: var(--font-mono);
            font-size: 13px;
            font-weight: 600;
            color: var(--accent-primary-hover);
            min-width: 120px;
        }

        .field-item-type {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .field-type-string { background: var(--info-bg); color: var(--info); }
        .field-type-number { background: var(--success-bg); color: var(--success); }
        .field-type-boolean { background: var(--warning-bg); color: var(--warning); }
        .field-type-array { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
        .field-type-object { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
        .field-type-relation { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .field-type-array_of_objects { background: rgba(20, 184, 166, 0.1); color: #14b8a6; }
        .field-type-color { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
        .field-type-image_url { background: rgba(6, 182, 212, 0.1); color: #06b6d4; }

        .field-item-label {
            font-size: 13px;
            color: var(--text-secondary);
            flex: 1;
        }

        .field-item-relation {
            font-size: 12px;
            color: var(--warning);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field-item-actions {
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .field-item:hover .field-item-actions {
            opacity: 1;
        }

        /* ═══ NESTED OBJECT EDITOR ═══ */
        .object-group {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 16px;
        }

        .object-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-default);
        }

        .object-group-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--accent-primary-hover);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .array-objects-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .array-object-item {
            background: var(--bg-card);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: 16px;
            position: relative;
        }

        .array-object-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-tertiary);
        }

        /* ═══ MODAL ═══ */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn var(--transition-fast);
        }

        .modal-content {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 28px;
            width: 100%;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: slideUp var(--transition-normal);
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--border-default);
        }

        /* ═══ NOTIFICATIONS ═══ */
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .notification {
            padding: 12px 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-left: 3px solid var(--success);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 14px;
            box-shadow: var(--shadow-lg);
            animation: slideInRight var(--transition-normal);
        }

        /* ═══ EMPTY STATE ═══ */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-tertiary);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state-text {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }

        .empty-state-sub {
            font-size: 14px;
            margin-bottom: 24px;
        }

        /* ═══ BADGE ═══ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success { background: var(--success-bg); color: var(--success); }
        .badge-warning { background: var(--warning-bg); color: var(--warning); }
        .badge-info { background: var(--info-bg); color: var(--info); }

        /* ═══ JSON PREVIEW ═══ */
        .json-preview {
            background: var(--bg-input);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: 16px;
            font-family: var(--font-mono);
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }

        .json-key { color: #818cf8; }
        .json-string { color: #34d399; }
        .json-number { color: #fbbf24; }
        .json-boolean { color: #f472b6; }
        .json-null { color: #64748b; }

        /* ═══ DELETE CONFIRMATION ═══ */
        .confirm-delete {
            background: var(--danger-bg);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .confirm-delete-text {
            flex: 1;
            font-size: 14px;
            color: var(--danger);
        }

        /* ═══ SEARCH ═══ */
        .search-box {
            position: relative;
        }

        .search-box .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
            font-size: 14px;
        }

        .search-box .form-input {
            padding-left: 40px;
        }

        /* ═══ PAGINATION ═══ */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }

        .pagination-wrapper nav span {
            display: flex;
            gap: 4px;
        }

        .pagination-wrapper .page-link {
            padding: 8px 14px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-size: 14px;
            text-decoration: none;
            transition: all var(--transition-fast);
        }

        .pagination-wrapper .page-link:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
        }

        .pagination-wrapper span[aria-current="page"] .page-link,
        .pagination-wrapper .page-link.active {
            background: var(--accent-primary);
            color: white;
            border-color: var(--accent-primary);
        }

        /* ═══ TOGGLE ═══ */
        .toggle {
            position: relative;
            width: 44px;
            height: 24px;
            cursor: pointer;
        }

        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-input);
            border: 1px solid var(--border-default);
            border-radius: 12px;
            transition: all var(--transition-fast);
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 2px;
            bottom: 2px;
            background: var(--text-tertiary);
            border-radius: 50%;
            transition: all var(--transition-fast);
        }

        .toggle input:checked + .toggle-slider {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
        }

        .toggle input:checked + .toggle-slider::before {
            background: white;
            transform: translateX(20px);
        }

        /* ═══ ANIMATIONS ═══ */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ═══ SCROLLBAR ═══ */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-primary); }
        ::-webkit-scrollbar-thumb { background: var(--border-hover); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* ═══ RESPONSIVE ═══ */
        .mobile-only { display: none; }
        
        @media (max-width: 768px) {
            .mobile-only { display: block; }
            
            .sidebar { width: 64px; transition: width var(--transition-fast) ease-out; overflow-x: hidden; bottom: 0; }
            .sidebar.expanded { width: 280px; box-shadow: var(--shadow-lg); }
            
            .sidebar-logo-text, .sidebar-logo-sub, .sidebar-section-title,
            .nav-item span:not(.nav-item-icon), .nav-item-count,
            .hide-on-collapse { display: none; opacity: 0; }
            
            .sidebar.expanded .sidebar-logo-text, 
            .sidebar.expanded .sidebar-logo-sub, 
            .sidebar.expanded .sidebar-section-title,
            .sidebar.expanded .nav-item span:not(.nav-item-icon), 
            .sidebar.expanded .nav-item-count,
            .sidebar.expanded .hide-on-collapse { display: block; opacity: 1; animation: fadeIn var(--transition-normal); }
            
            .nav-item { justify-content: center; padding: 12px; }
            .sidebar.expanded .nav-item { justify-content: flex-start; padding: 10px 14px; }
            
            .main-content { margin-left: 64px; }
            .page-content { padding: 16px; }
            .collection-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            
            .sidebar-logo { cursor: pointer; }
        }

        /* ═══ UTILITY ═══ */
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-8 { gap: 8px; }
        .gap-12 { gap: 12px; }
        .gap-16 { gap: 16px; }
        .gap-20 { gap: 20px; }
        .mt-8 { margin-top: 8px; }
        .mt-12 { margin-top: 12px; }
        .mt-16 { margin-top: 16px; }
        .mt-20 { margin-top: 20px; }
        .mb-8 { margin-bottom: 8px; }
        .mb-16 { margin-bottom: 16px; }
        .mb-20 { margin-bottom: 20px; }
        .text-center { text-align: center; }
        .text-secondary { color: var(--text-secondary); }
        .text-tertiary { color: var(--text-tertiary); }
        .text-sm { font-size: 13px; }
        .text-xs { font-size: 12px; }
        .font-mono { font-family: var(--font-mono); }
        .w-full { width: 100%; }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px; }
    </style>
</head>
<body>
    <div class="app-layout">
        {{-- Sidebar --}}
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo" onclick="document.getElementById('appSidebar').classList.toggle('expanded')" title="Toggle Menu">
                    <div class="sidebar-logo-icon">🎮</div>
                    <div class="hide-on-collapse">
                        <div class="sidebar-logo-text">Game Manager</div>
                        <div class="sidebar-logo-sub">Data Editor</div>
                    </div>
                </div>

                @php
                    $user = auth()->user();
                    $activeGameId = session('active_game_id');
                    $userGames = $user->role === 'super_admin' ? \App\Models\Game::all() : $user->games;
                    $currentGame = $userGames->firstWhere('id', $activeGameId) ?? $userGames->first();
                @endphp
                
                @if($currentGame)
                <div class="hide-on-collapse" style="position: relative; margin-top: 20px;">
                    <div id="gameSwitcherBtn" onclick="document.getElementById('gameSwitcherMenu').style.display = document.getElementById('gameSwitcherMenu').style.display === 'none' ? 'block' : 'none'" style="padding: 10px 14px; background: var(--bg-input); border-radius: var(--radius-sm); border: 1px solid var(--border-default); display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-default)'">
                        <div>
                            <div style="font-size: 10px; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2px;">Current Game</div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--accent-primary-hover);">{{ $currentGame->name }}</div>
                        </div>
                        <span style="font-size: 10px; color: var(--text-tertiary);">▾</span>
                    </div>

                    <div id="gameSwitcherMenu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; width: 100%; background: var(--bg-card); border: 1px solid var(--border-default); border-radius: var(--radius-sm); box-shadow: var(--shadow-md); z-index: 50; overflow: hidden; max-height: 200px; overflow-y: auto;">
                        @foreach($userGames as $gameOption)
                            <a href="{{ route('switch-game', $gameOption->id) }}" style="display: block; padding: 10px 14px; font-size: 13px; font-weight: 500; color: {{ $gameOption->id === $currentGame->id ? 'var(--text-primary)' : 'var(--text-secondary)' }}; background: {{ $gameOption->id === $currentGame->id ? 'var(--bg-tertiary)' : 'transparent' }}; border-bottom: 1px solid var(--border-default); text-decoration: none;">
                                {{ $gameOption->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                
                <script>
                    document.addEventListener('click', function(e) {
                        const btn = document.getElementById('gameSwitcherBtn');
                        const menu = document.getElementById('gameSwitcherMenu');
                        if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                            menu.style.display = 'none';
                        }
                    });
                </script>
                @endif
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-section-title">Navigation</div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-item-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('collections.index') }}" class="nav-item {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                    <span class="nav-item-icon">⚙️</span>
                    <span>Collections</span>
                </a>

                @php
                    $navCollections = $currentGame 
                        ? \App\Models\GameCollection::where('game_id', $currentGame->id)->withCount('entries')->orderBy('sort_order')->get()
                        : collect();
                @endphp

                @if($navCollections->count())
                    <div class="sidebar-section-title">Game Data</div>
                    @foreach($navCollections as $navCol)
                        @php
                            $navUrl = $navCol->type === 'static' 
                                ? route('static.editor', $navCol->slug) 
                                : route('entries.index', $navCol->slug);
                            $isActive = request()->is('entries/' . $navCol->slug . '*') 
                                || request()->is('static/' . $navCol->slug . '*');
                        @endphp
                        <a href="{{ $navUrl }}"
                           class="nav-item {{ $isActive ? 'active' : '' }}">
                            <span class="nav-item-icon">{{ $navCol->icon }}</span>
                            <span>{{ $navCol->display_name }}</span>
                            @if($navCol->type === 'static')
                                <span class="nav-item-count" style="background: var(--warning-bg); color: var(--warning);">JSON</span>
                            @elseif($navCol->entries_count)
                                <span class="nav-item-count">{{ $navCol->entries_count }}</span>
                            @endif
                        </a>
                    @endforeach
                @endif
                
                <div class="sidebar-section-title">System</div>
                <a href="{{ route('settings.games') }}" class="nav-item {{ request()->routeIs('settings.games') ? 'active' : '' }}">
                    <span class="nav-item-icon">🕹️</span>
                    <span>Manage Games</span>
                </a>
                @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('settings.users') }}" class="nav-item {{ request()->routeIs('settings.users') ? 'active' : '' }}">
                    <span class="nav-item-icon">👥</span>
                    <span>Manage Users</span>
                </a>
                @endif
            </nav>

            <div style="padding: 16px; border-top: 1px solid var(--border-default); background: rgba(17, 24, 39, 0.5);">
                <div class="hide-on-collapse">
                    <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px;">{{ auth()->user()->name ?? 'Guest' }}</div>
                    <div style="font-size: 11px; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">{{ auth()->user()->role ?? '' }}</div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-full" style="justify-content: center; font-size: 11px; padding-left: 0; padding-right: 0;">
                        <span style="font-size: 16px;">🚪</span> <span class="hide-on-collapse">Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            {{ $slot }}
        </main>
    </div>

    {{-- Notification Container --}}
    <div class="notification-container" id="notification-container"></div>

    @livewireScripts

    <script>
        // Notification system
        window.addEventListener('livewire:load', function () {
            Livewire.on('notify', function (message) {
                showNotification(message);
            });
        });

        function showNotification(message) {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            container.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(40px)';
                notification.style.transition = 'all 300ms';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
