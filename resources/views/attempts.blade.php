<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAGOR / LARAVEL SECURITY FIREWALL // CYBER DESK ATTEMPTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-void: #02040a;
            --bg-card: rgba(6, 12, 24, 0.92);
            --border-cyan: rgba(0, 243, 255, 0.35);
            --neon-cyan: #00f3ff;
            --neon-green: #00ff66;
            --neon-red: #ff0055;
            --neon-amber: #ffb700;
            --neon-purple: #bd00ff;
            --text-muted: #5b708b;
            --font-mono: 'Share Tech Mono', 'JetBrains Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-mono); }
        
        body {
            background-color: var(--bg-void);
            color: #d1d5db;
            padding: 1.5rem;
            min-height: 100vh;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(0, 243, 255, 0.14) 0%, transparent 70%),
                linear-gradient(rgba(0, 243, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 243, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 35px 35px, 35px 35px;
            position: relative;
            overflow-x: hidden;
        }

        /* Matrix Rain Background Canvas */
        canvas#matrixBg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            opacity: 0.16;
            z-index: 0;
        }

        /* Hologram Laser Scanning Sweep Line */
        .laser-scanner {
            position: fixed;
            top: -10px; left: 0; width: 100%; height: 4px;
            background: linear-gradient(90deg, transparent, var(--neon-cyan), var(--neon-purple), var(--neon-cyan), transparent);
            box-shadow: 0 0 15px var(--neon-cyan), 0 0 25px var(--neon-purple);
            z-index: 998;
            animation: laser-sweep 8s linear infinite;
            pointer-events: none;
        }

        @keyframes laser-sweep {
            0% { top: -10px; opacity: 0; }
            5% { opacity: 1; }
            50% { top: 100vh; opacity: 1; }
            55% { opacity: 0; }
            100% { top: -10px; opacity: 0; }
        }

        /* CRT Overlay Effect */
        body::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 999;
            background-size: 100% 3px, 6px 100%;
            pointer-events: none;
            opacity: 0.5;
        }

        .relative-z { position: relative; z-index: 10; }

        /* HUD Header */
        .hud-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.75rem;
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 243, 255, 0.22);
            margin-bottom: 1.25rem;
            position: relative;
            backdrop-filter: blur(12px);
        }

        .hud-header::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 5%; right: 5%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--neon-cyan), var(--neon-purple), var(--neon-cyan), transparent);
            animation: glow-pulse 2s ease-in-out infinite alternate;
        }

        @keyframes glow-pulse {
            from { opacity: 0.4; }
            to { opacity: 1; }
        }

        .title-box { display: flex; align-items: center; gap: 1.25rem; }
        
        .glitch-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--neon-cyan);
            text-shadow: 0 0 15px rgba(0, 243, 255, 0.9);
            letter-spacing: 2px;
            position: relative;
            animation: glitch-anim 3.5s infinite;
        }

        @keyframes glitch-anim {
            0%, 93%, 100% { transform: translate(0); }
            94% { transform: translate(-3px, 1px); }
            95% { transform: translate(3px, -1px); filter: hue-rotate(90deg); }
            96% { transform: translate(-2px, -2px); }
        }

        .author-tag {
            font-size: 0.8rem;
            color: var(--neon-green);
            background: rgba(0, 255, 102, 0.12);
            border: 1px solid rgba(0, 255, 102, 0.4);
            padding: 0.25rem 0.65rem;
            border-radius: 4px;
            letter-spacing: 1.5px;
            margin-top: 0.3rem;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0, 255, 102, 0.2);
        }

        .pulse-beacon {
            width: 14px; height: 14px;
            background-color: var(--neon-red);
            border-radius: 50%;
            box-shadow: 0 0 15px var(--neon-red);
            animation: pulse-ring 1.5s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 0, 85, 0.8); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 12px rgba(255, 0, 85, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 0, 85, 0); }
        }

        .hud-badges { display: flex; gap: 0.75rem; align-items: center; }
        
        .cyber-badge {
            background: rgba(0, 243, 255, 0.08);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 0.45rem 0.9rem;
            border-radius: 4px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: inset 0 0 10px rgba(0, 243, 255, 0.2);
        }

        .cyber-badge.package {
            border-color: var(--neon-purple);
            color: var(--neon-purple);
            background: rgba(189, 0, 255, 0.12);
            box-shadow: 0 0 12px rgba(189, 0, 255, 0.3), inset 0 0 10px rgba(189, 0, 255, 0.3);
            animation: border-flash 4s infinite alternate;
        }

        @keyframes border-flash {
            0% { border-color: var(--neon-purple); box-shadow: 0 0 8px var(--neon-purple); }
            50% { border-color: var(--neon-cyan); box-shadow: 0 0 12px var(--neon-cyan); }
            100% { border-color: var(--neon-purple); box-shadow: 0 0 8px var(--neon-purple); }
        }

        /* Cyber Nav Bar */
        .cyber-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(6, 12, 24, 0.85);
            border: 1px solid var(--border-cyan);
            border-radius: 6px;
            padding: 0.6rem 1.25rem;
            margin-bottom: 1.25rem;
            backdrop-filter: blur(8px);
        }

        .nav-links { display: flex; gap: 1rem; }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            transition: all 0.25s ease;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            color: var(--neon-cyan);
            border-color: rgba(0, 243, 255, 0.4);
            background: rgba(0, 243, 255, 0.08);
            box-shadow: 0 0 12px rgba(0, 243, 255, 0.2);
        }

        .nav-link.active {
            color: var(--neon-green);
            border-color: var(--neon-green);
            background: rgba(0, 255, 102, 0.12);
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
        }

        /* Live Clock & Audio Bar */
        .live-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 243, 255, 0.05);
            border: 1px solid rgba(0, 243, 255, 0.18);
            padding: 0.55rem 1.25rem;
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--neon-cyan);
            margin-bottom: 1.25rem;
            letter-spacing: 1px;
        }

        /* Cyber Desk Workstation Control Panel (Filter Form) */
        .desk-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .desk-panel h3 {
            font-size: 1rem;
            color: var(--neon-cyan);
            letter-spacing: 1.5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
            gap: 0.85rem;
            align-items: end;
        }

        @media (max-width: 1024px) {
            .filter-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .filter-grid { grid-template-columns: 1fr; }
        }

        .form-group { display: flex; flex-direction: column; gap: 0.35rem; }
        .form-group label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cyber-input, .cyber-select {
            background: rgba(2, 4, 10, 0.8);
            border: 1px solid var(--border-cyan);
            color: #ffffff;
            padding: 0.55rem 0.85rem;
            border-radius: 4px;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            outline: none;
            transition: all 0.25s ease;
        }

        .cyber-input:focus, .cyber-select:focus {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 12px rgba(0, 243, 255, 0.4);
        }

        .cyber-select option {
            background: #060c18;
            color: #ffffff;
        }

        .cyber-btn {
            background: rgba(0, 243, 255, 0.12);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 0.55rem 1.1rem;
            border-radius: 4px;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .cyber-btn:hover {
            background: var(--neon-cyan);
            color: #02040a;
            box-shadow: 0 0 18px rgba(0, 243, 255, 0.7);
            transform: translateY(-2px);
        }

        .cyber-btn.secondary {
            border-color: var(--text-muted);
            color: var(--text-muted);
            background: transparent;
        }

        .cyber-btn.secondary:hover {
            background: var(--text-muted);
            color: #02040a;
            box-shadow: 0 0 12px var(--text-muted);
        }

        /* Stats Bar Desk Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .sm-card {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 6px;
            padding: 1rem;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(8px);
        }

        .sm-card .label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .sm-card .val { font-size: 1.8rem; font-weight: 800; color: #fff; margin-top: 0.2rem; }

        /* Cyber Table Workstation */
        .terminal-box {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            position: relative;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 25px rgba(0, 243, 255, 0.15);
            margin-bottom: 1.5rem;
        }

        .terminal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 243, 255, 0.15);
            padding-bottom: 0.75rem;
        }

        .terminal-header h3 {
            font-size: 1.1rem;
            color: var(--neon-cyan);
            letter-spacing: 1.5px;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.82rem;
        }

        th {
            padding: 0.8rem 1rem;
            color: var(--neon-cyan);
            border-bottom: 1px solid var(--border-cyan);
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(0, 243, 255, 0.05);
            white-space: nowrap;
        }

        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 243, 255, 0.08);
            font-family: var(--font-mono);
            vertical-align: middle;
            white-space: nowrap;
        }

        tr:hover { background: rgba(0, 243, 255, 0.08); }

        .type-badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            background: rgba(0, 243, 255, 0.12);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
        }

        .severity-critical { color: var(--neon-red); font-weight: 700; text-shadow: 0 0 8px var(--neon-red); }
        .severity-high { color: var(--neon-amber); font-weight: 700; }
        .severity-medium { color: var(--neon-cyan); }
        .severity-low { color: var(--neon-green); }

        .action-tag {
            padding: 0.25rem 0.6rem;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .action-block { background: rgba(255, 0, 85, 0.25); color: var(--neon-red); border: 1px solid var(--neon-red); box-shadow: 0 0 8px rgba(255,0,85,0.4); }
        .action-throttle { background: rgba(255, 183, 0, 0.25); color: var(--neon-amber); border: 1px solid var(--neon-amber); }
        .action-allow { background: rgba(0, 255, 102, 0.25); color: var(--neon-green); border: 1px solid var(--neon-green); }

        .inspect-btn {
            background: rgba(0, 255, 102, 0.1);
            border: 1px solid var(--neon-green);
            color: var(--neon-green);
            padding: 0.3rem 0.65rem;
            border-radius: 3px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: var(--font-mono);
        }

        .inspect-btn:hover {
            background: var(--neon-green);
            color: #02040a;
            box-shadow: 0 0 10px var(--neon-green);
        }

        /* Holographic Modal Inspector Overlay */
        .cyber-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(2, 4, 10, 0.88);
            backdrop-filter: blur(12px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .cyber-modal-overlay.open { display: flex; }

        .cyber-modal {
            background: var(--bg-card);
            border: 1px solid var(--neon-cyan);
            border-radius: 10px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 0 40px rgba(0, 243, 255, 0.4);
            position: relative;
            padding: 1.75rem;
            animation: modal-pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes modal-pop {
            from { transform: scale(0.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-cyan);
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            color: var(--neon-cyan);
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .close-modal {
            background: transparent;
            border: 1px solid var(--neon-red);
            color: var(--neon-red);
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .close-modal:hover {
            background: var(--neon-red);
            color: #ffffff;
            box-shadow: 0 0 12px var(--neon-red);
        }

        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 640px) { .modal-grid { grid-template-columns: 1fr; } }

        .modal-field {
            background: rgba(2, 4, 10, 0.6);
            border: 1px solid rgba(0, 243, 255, 0.15);
            padding: 0.85rem;
            border-radius: 6px;
        }

        .modal-field .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.35rem;
        }

        .modal-field .val {
            font-size: 0.95rem;
            color: #ffffff;
            word-break: break-all;
        }

        .code-block {
            background: #02040a;
            border: 1px solid var(--border-cyan);
            border-radius: 6px;
            padding: 1rem;
            color: var(--neon-green);
            font-size: 0.85rem;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 250px;
            overflow-y: auto;
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.8);
        }

        /* Cyber Pagination Styling */
        .pagination-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 243, 255, 0.15);
        }

        /* Footer Credit */
        .hud-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-cyan);
            padding-top: 1.25rem;
            letter-spacing: 1.5px;
        }
    </style>
</head>
<body>

    <!-- Hologram Laser Scanning Beam -->
    <div class="laser-scanner"></div>

    <!-- Matrix Code Rain Canvas -->
    <canvas id="matrixBg"></canvas>

    <div class="relative-z">
        <!-- HUD Header -->
        <div class="hud-header">
            <div class="title-box">
                <div class="pulse-beacon"></div>
                <div>
                    <div class="glitch-title">SAGOR / LARAVEL SECURITY FIREWALL</div>
                    <div class="author-tag">DEVELOPED BY MOH SAGOR</div>
                </div>
            </div>
            <div class="hud-badges">
                <div class="cyber-badge package">sagor/laravel-security</div>
                <div class="cyber-badge">MODE: CYBER DESK AUDIT WORKSTATION</div>
            </div>
        </div>

        <!-- Cyber Navigation Bar -->
        <div class="cyber-nav">
            <div class="nav-links">
                <a href="{{ route('security.dashboard') }}" class="nav-link">⚡ COMMAND CENTER OVERVIEW</a>
                <a href="{{ route('security.attempts') }}" class="nav-link active">🖥️ CYBER DESK: ALL ATTEMPTS</a>
            </div>
            <div style="font-size: 0.8rem; color: var(--neon-cyan);">
                ATTEMPTS RECORDED: <strong style="color: var(--neon-green);">{{ $stats['total_events'] ?? 0 }}</strong>
            </div>
        </div>

        <!-- Live Status Ticker Bar -->
        <div class="live-bar">
            <span>LOCAL_TIME: <strong id="liveClock" style="color: var(--neon-green);">--:--:--</strong></span>
            <span>DESK SYNC: <strong style="color: var(--neon-cyan);">CONNECTED TO MYSQL LOG STREAM</strong></span>
            <span>AUDIO FX: <button id="soundToggle" onclick="toggleCyberAudio()" class="inspect-btn" style="padding: 0.15rem 0.5rem;">[ 🔊 SOUND: ON ]</button></span>
            <span>STATUS: <strong style="color: var(--neon-green);">FIREWALL INTRUSION MONITORED</strong></span>
        </div>

        <!-- Summary Telemetry Cards -->
        <div class="summary-grid">
            <div class="sm-card">
                <div class="label">Total Security Events</div>
                <div class="val counter" data-target="{{ $stats['total_events'] ?? 0 }}" style="color: var(--neon-cyan);">0</div>
            </div>
            <div class="sm-card">
                <div class="label">Blocked Attempts</div>
                <div class="val counter" data-target="{{ $stats['total_blocked'] ?? 0 }}" style="color: var(--neon-red);">0</div>
            </div>
            <div class="sm-card">
                <div class="label">Rate Throttled</div>
                <div class="val counter" data-target="{{ $stats['total_throttled'] ?? 0 }}" style="color: var(--neon-amber);">0</div>
            </div>
            <div class="sm-card">
                <div class="label">Active Blocked IPs</div>
                <div class="val counter" data-target="{{ $stats['blocked_ips_count'] ?? 0 }}" style="color: var(--neon-purple);">0</div>
            </div>
        </div>

        <!-- Cyber Desk Filter Controls Console -->
        <div class="desk-panel">
            <h3>🖥️ CYBER DESK SEARCH & THREAT INTRUSION FILTER CONSOLE</h3>
            <form method="GET" action="{{ url()->current() }}">
                <div class="filter-grid">
                    <div class="form-group">
                        <label>Search Keyword / IP Hash / Route / UUID</label>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="cyber-input" placeholder="e.g. sqli, /api/products, 127.0.0.1...">
                    </div>
                    <div class="form-group">
                        <label>Threat Vector</label>
                        <select name="type" class="cyber-select">
                            <option value="">-- ALL THREAT TYPES --</option>
                            <option value="sqli.detector" {{ ($type ?? '') === 'sqli.detector' ? 'selected' : '' }}>SQL Injection</option>
                            <option value="xss.detector" {{ ($type ?? '') === 'xss.detector' ? 'selected' : '' }}>XSS Attack</option>
                            <option value="path_traversal.detector" {{ ($type ?? '') === 'path_traversal.detector' ? 'selected' : '' }}>Path Traversal</option>
                            <option value="command_injection.detector" {{ ($type ?? '') === 'command_injection.detector' ? 'selected' : '' }}>Command Injection</option>
                            <option value="ssrf.detector" {{ ($type ?? '') === 'ssrf.detector' ? 'selected' : '' }}>SSRF Attack</option>
                            <option value="scanner.detector" {{ ($type ?? '') === 'scanner.detector' ? 'selected' : '' }}>Scanner / Bot</option>
                            <option value="upload.security" {{ ($type ?? '') === 'upload.security' ? 'selected' : '' }}>Upload Threat</option>
                            <option value="rate_limit" {{ ($type ?? '') === 'rate_limit' ? 'selected' : '' }}>Rate Limit Exceeded</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Severity Level</label>
                        <select name="severity" class="cyber-select">
                            <option value="">-- ALL SEVERITIES --</option>
                            <option value="critical" {{ ($severity ?? '') === 'critical' ? 'selected' : '' }}>CRITICAL</option>
                            <option value="high" {{ ($severity ?? '') === 'high' ? 'selected' : '' }}>HIGH</option>
                            <option value="medium" {{ ($severity ?? '') === 'medium' ? 'selected' : '' }}>MEDIUM</option>
                            <option value="low" {{ ($severity ?? '') === 'low' ? 'selected' : '' }}>LOW</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Action Taken</label>
                        <select name="action" class="cyber-select">
                            <option value="">-- ALL ACTIONS --</option>
                            <option value="block" {{ ($action ?? '') === 'block' ? 'selected' : '' }}>BLOCKED</option>
                            <option value="throttle" {{ ($action ?? '') === 'throttle' ? 'selected' : '' }}>THROTTLED</option>
                            <option value="allow" {{ ($action ?? '') === 'allow' ? 'selected' : '' }}>ALLOWED</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Per Page</label>
                        <select name="per_page" class="cyber-select">
                            <option value="10" {{ ($perPage ?? 20) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="cyber-btn primary">⚡ FILTER ATTEMPTS</button>
                        <a href="{{ route('security.attempts') }}" class="cyber-btn secondary">↻ RESET</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Terminal Workstation Log Stream Table -->
        <div class="terminal-box">
            <div class="terminal-header">
                <h3>ALL INTRUSION ATTEMPTS AUDIT FEED</h3>
                <span style="font-size: 0.8rem; color: var(--neon-cyan);">
                    DISPLAYING PAGE {{ method_exists($events, 'currentPage') ? $events->currentPage() : 1 }} OF {{ method_exists($events, 'lastPage') ? $events->lastPage() : 1 }}
                </span>
            </div>

            <div class="table-responsive">
                @if(isset($events) && count($events) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event UUID</th>
                                <th>Threat Vector</th>
                                <th>Severity</th>
                                <th>Risk Score</th>
                                <th>Target Route</th>
                                <th>Method</th>
                                <th>Action Taken</th>
                                <th>Cyber Inspection</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td style="color: #9ca3af;">{{ $event->created_at }}</td>
                                    <td><code style="color: var(--neon-purple); font-size: 0.78rem;">{{ substr($event->event_id ?? $event->id, 0, 16) }}...</code></td>
                                    <td><span class="type-badge">{{ strtoupper(str_replace(['.detector', '_'], ['', ' '], $event->type)) }}</span></td>
                                    <td class="severity-{{ strtolower($event->severity) }}">{{ strtoupper($event->severity) }}</td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap: 0.5rem;">
                                            <span style="font-weight:700;">{{ $event->risk_score }}/100</span>
                                            <div style="width: 50px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
                                                <div style="width: {{ $event->risk_score }}%; height: 100%; background: {{ $event->risk_score >= 80 ? 'var(--neon-red)' : ($event->risk_score >= 50 ? 'var(--neon-amber)' : 'var(--neon-green)') }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">{{ $event->route }}</td>
                                    <td><strong style="color: var(--neon-cyan);">{{ $event->method }}</strong></td>
                                    <td><span class="action-tag action-{{ strtolower($event->action) }}">{{ strtoupper($event->action) }}</span></td>
                                    <td>
                                        <button class="inspect-btn" data-event="{{ base64_encode(json_encode($event)) }}" onclick="openCyberModalFromBtn(this)">🔍 INSPECT ATTEMPT</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="pagination-box">
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{ $events->total() ?? 0 }} attempts
                        </div>
                        <div>
                            @if(method_exists($events, 'links'))
                                {{ $events->links() }}
                            @endif
                        </div>
                    </div>
                @else
                    <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                        [!] NO SECURITY INTRUSION ATTEMPTS MATCH THE APPLIED DESK FILTERS.
                    </div>
                @endif
            </div>
        </div>

        <!-- HUD Footer Credit -->
        <div class="hud-footer">
            <strong>sagor/laravel-security v1.0.0</strong> | Cyber Desk Security Workstation | Developed by <strong>Moh Sagor</strong>
        </div>
    </div>

    <!-- Holographic Modal Overlay -->
    <div class="cyber-modal-overlay" id="cyberModal" onclick="closeModalOnBg(event)">
        <div class="cyber-modal">
            <div class="modal-header">
                <h3><span>⚡</span> CYBER HOLOGRAM // INTRUSION PAYLOAD INSPECTOR</h3>
                <button class="close-modal" onclick="closeCyberModal()">✕ CLOSE</button>
            </div>
            
            <div class="modal-grid">
                <div class="modal-field">
                    <div class="label">Event UUID</div>
                    <div class="val" id="mEventId" style="color: var(--neon-purple);">--</div>
                </div>
                <div class="modal-field">
                    <div class="label">Detection Rule Vector</div>
                    <div class="val" id="mType" style="color: var(--neon-cyan);">--</div>
                </div>
                <div class="modal-field">
                    <div class="label">Timestamp</div>
                    <div class="val" id="mTime">--</div>
                </div>
                <div class="modal-field">
                    <div class="label">Severity & Risk Score</div>
                    <div class="val" id="mRisk">--</div>
                </div>
                <div class="modal-field">
                    <div class="label">Target Route & Method</div>
                    <div class="val" id="mRoute">--</div>
                </div>
                <div class="modal-field">
                    <div class="label">Action Taken</div>
                    <div class="val" id="mAction">--</div>
                </div>
            </div>

            <div style="margin-bottom: 0.5rem; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                ATTEMPT METADATA & PAYLOAD SNAPSHOT:
            </div>
            <pre class="code-block" id="mMetadata">// Inspecting hologram stream...</pre>
        </div>
    </div>

    <!-- Live Ticking Digital Clock Script -->
    <script>
        function updateClock() {
            const el = document.getElementById('liveClock');
            if (el) {
                const now = new Date();
                el.innerText = now.toLocaleTimeString();
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <!-- Stat Counter Roll-Up Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(c => {
                const target = +c.getAttribute('data-target');
                let count = 0;
                const inc = Math.max(1, Math.ceil(target / 40));
                
                function update() {
                    count += inc;
                    if (count < target) {
                        c.innerText = count.toLocaleString();
                        setTimeout(update, 25);
                    } else {
                        c.innerText = target.toLocaleString();
                    }
                }
                update();
            });
        });
    </script>

    <!-- Cyber Audio Synthesizer Script -->
    <script>
        let soundEnabled = true;
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        function playCyberBeep(freq = 800, duration = 0.08) {
            if (!soundEnabled) return;
            try {
                if (audioCtx.state === 'suspended') { audioCtx.resume(); }
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.04, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + duration);
            } catch (e) {}
        }

        function toggleCyberAudio() {
            soundEnabled = !soundEnabled;
            const btn = document.getElementById('soundToggle');
            if (btn) {
                btn.innerText = soundEnabled ? '[ 🔊 SOUND: ON ]' : '[ 🔇 SOUND: OFF ]';
            }
            if (soundEnabled) playCyberBeep(1200, 0.1);
        }
    </script>

    <!-- Cyber Hologram Modal Inspector Handler -->
    <script>
        function openCyberModalFromBtn(btn) {
            if (!btn) return;
            const b64 = btn.getAttribute('data-event');
            if (!b64) return;
            try {
                const jsonStr = decodeURIComponent(escape(atob(b64)));
                const data = JSON.parse(jsonStr);
                openCyberModal(data);
            } catch(e) {
                try {
                    const data = JSON.parse(atob(b64));
                    openCyberModal(data);
                } catch(err) {
                    console.error('Cyber modal decode error:', err);
                }
            }
        }

        function openCyberModal(rawJson) {
            playCyberBeep(900, 0.1);
            try {
                const data = typeof rawJson === 'string' ? JSON.parse(rawJson) : rawJson;
                document.getElementById('mEventId').innerText = data.event_id || data.id || 'N/A';
                document.getElementById('mType').innerText = (data.type || 'UNKNOWN').toUpperCase();
                document.getElementById('mTime').innerText = data.created_at || 'N/A';
                document.getElementById('mRisk').innerText = (data.severity || 'UNKNOWN').toUpperCase() + ' (' + (data.risk_score || 0) + '/100)';
                document.getElementById('mRoute').innerText = (data.method || 'GET') + ' ' + (data.route || '/');
                document.getElementById('mAction').innerText = (data.action || 'LOGGED').toUpperCase();

                let metaStr = '';
                if (data.metadata) {
                    try {
                        const metaObj = typeof data.metadata === 'string' ? JSON.parse(data.metadata) : data.metadata;
                        metaStr = JSON.stringify(metaObj, null, 2);
                    } catch(e) {
                        metaStr = String(data.metadata);
                    }
                } else {
                    metaStr = JSON.stringify(data, null, 2);
                }

                document.getElementById('mMetadata').innerText = metaStr || '// No extra payload recorded';
                document.getElementById('cyberModal').classList.add('open');
            } catch(e) {
                console.error('Modal parse error', e);
            }
        }

        function closeCyberModal() {
            playCyberBeep(400, 0.08);
            document.getElementById('cyberModal').classList.remove('open');
        }

        function closeModalOnBg(e) {
            if (e.target.id === 'cyberModal') {
                closeCyberModal();
            }
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCyberModal();
            }
        });
    </script>

    <!-- Matrix Code Rain Canvas Animation -->
    <script>
        (function() {
            const mCanvas = document.getElementById('matrixBg');
            if (!mCanvas) return;
            const mCtx = mCanvas.getContext('2d');

            function resizeMatrix() {
                mCanvas.width = window.innerWidth;
                mCanvas.height = window.innerHeight;
            }
            resizeMatrix();
            window.addEventListener('resize', resizeMatrix);

            const chars = '0101010101CYBER_DESK_SAGOR_INTRUSION_LOGS';
            const fontSize = 12;
            const columns = Math.floor(window.innerWidth / fontSize);
            const drops = Array(columns).fill(1);

            function drawMatrix() {
                mCtx.fillStyle = 'rgba(2, 4, 10, 0.12)';
                mCtx.fillRect(0, 0, mCanvas.width, mCanvas.height);

                mCtx.fillStyle = '#00f3ff';
                mCtx.font = fontSize + 'px "Share Tech Mono"';

                for (let i = 0; i < drops.length; i++) {
                    const text = chars.charAt(Math.floor(Math.random() * chars.length));
                    mCtx.fillText(text, i * fontSize, drops[i] * fontSize);

                    if (drops[i] * fontSize > mCanvas.height && Math.random() > 0.975) {
                        drops[i] = 0;
                    }
                    drops[i]++;
                }
            }

            setInterval(drawMatrix, 45);
        })();
    </script>

</body>
</html>
