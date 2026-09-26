<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAGOR / LARAVEL SECURITY FIREWALL COMMAND CENTER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-void: #02040a;
            --bg-card: rgba(6, 12, 24, 0.90);
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
                radial-gradient(circle at 50% 0%, rgba(0, 243, 255, 0.12) 0%, transparent 70%),
                linear-gradient(rgba(0, 243, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 243, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 35px 35px, 35px 35px;
            position: relative;
            overflow-x: hidden;
        }

        /* Matrix Code Canvas Background */
        canvas#matrixBg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            opacity: 0.18;
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

        /* Scanline CRT Overlay Effect */
        body::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 999;
            background-size: 100% 3px, 6px 100%;
            pointer-events: none;
            opacity: 0.55;
        }

        .relative-z { position: relative; z-index: 10; }

        /* Cyber Glitch Header */
        .hud-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.75rem;
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 243, 255, 0.25);
            margin-bottom: 1.5rem;
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
            font-size: 1.75rem;
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
            background-color: var(--neon-green);
            border-radius: 50%;
            box-shadow: 0 0 15px var(--neon-green);
            animation: pulse-ring 1.5s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(0, 255, 102, 0.8); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 12px rgba(0, 255, 102, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(0, 255, 102, 0); }
        }

        .hud-badges { display: flex; gap: 1rem; align-items: center; }
        
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

        /* Live Clock Banner Bar */
        .live-bar {
            display: flex;
            justify-content: space-between;
            background: rgba(0, 243, 255, 0.05);
            border: 1px solid rgba(0, 243, 255, 0.15);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--neon-cyan);
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 25px rgba(0, 243, 255, 0.4);
            transform: translateY(-4px) scale(1.01);
        }

        .stat-card .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 0.5rem;
        }

        .stat-card .val {
            font-size: 2.3rem;
            font-weight: 800;
            color: #ffffff;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.7);
        }

        .stat-card .corner-accent {
            position: absolute;
            top: 0; right: 0;
            width: 18px; height: 18px;
            border-top: 2px solid var(--neon-cyan);
            border-right: 2px solid var(--neon-cyan);
        }

        /* Interactive Graphics Grid */
        .graphics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 900px) {
            .graphics-grid { grid-template-columns: 1fr; }
        }

        .chart-box {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-header h3 {
            font-size: 1rem;
            color: var(--neon-cyan);
            letter-spacing: 1.5px;
        }

        canvas#threatChart {
            width: 100%;
            height: 220px;
            display: block;
        }

        /* Threat Breakdown Progress Bars */
        .breakdown-box {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            backdrop-filter: blur(10px);
        }

        .progress-item { margin-bottom: 1rem; }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 0.35rem;
        }

        .progress-bar-bg {
            width: 100%;
            height: 8px;
            background: rgba(255,255,255,0.08);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            box-shadow: 0 0 12px currentColor;
            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: bar-shimmer 2s infinite;
        }

        @keyframes bar-shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Radar Box */
        .radar-box {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .radar-container {
            width: 170px; height: 170px;
            border-radius: 50%;
            border: 1px solid var(--border-cyan);
            position: relative;
            background: radial-gradient(circle, rgba(0, 243, 255, 0.12) 0%, transparent 70%);
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.2);
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .radar-sweep {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, rgba(0, 243, 255, 0.5) 0deg, transparent 60deg, transparent 360deg);
            animation: radar-spin 2.5s linear infinite;
        }

        @keyframes radar-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .radar-grid-line {
            position: absolute;
            top: 50%; left: 0; width: 100%; height: 1px;
            background: var(--border-cyan);
        }
        .radar-grid-line.v {
            top: 0; left: 50%; width: 1px; height: 100%;
        }

        .radar-blip {
            position: absolute;
            width: 7px; height: 7px;
            background: var(--neon-red);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--neon-red);
            animation: blip-flash 1.2s infinite alternate;
        }

        @keyframes blip-flash {
            0% { opacity: 0.2; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1.5); }
        }

        /* Terminal Table */
        .terminal-box {
            background: var(--bg-card);
            border: 1px solid var(--border-cyan);
            border-radius: 8px;
            padding: 1.25rem;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .terminal-box h3 {
            font-size: 1.1rem;
            color: var(--neon-cyan);
            margin-bottom: 1rem;
            letter-spacing: 1.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.85rem;
        }

        th {
            padding: 0.75rem 1rem;
            color: var(--neon-cyan);
            border-bottom: 1px solid var(--border-cyan);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 243, 255, 0.08);
            font-family: var(--font-mono);
        }

        tr:hover { background: rgba(0, 243, 255, 0.07); }

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
                <div class="cyber-badge package">PACKAGE: sagor/laravel-security</div>
                <div class="cyber-badge">MODE: {{ strtoupper(config('security.mode', 'balanced')) }}</div>
            </div>
        </div>

        <!-- Cyber Navigation Bar -->
        <div class="cyber-nav">
            <div class="nav-links">
                <a href="{{ route('security.dashboard') }}" class="nav-link active">⚡ COMMAND CENTER OVERVIEW</a>
                <a href="{{ route('security.attempts') }}" class="nav-link">🖥️ CYBER DESK: ALL ATTEMPTS</a>
            </div>
            <div style="font-size: 0.8rem; color: var(--neon-cyan);">
                ALL INTRUSION ATTEMPTS: <a href="{{ route('security.attempts') }}" style="color: var(--neon-green); text-decoration: underline; font-weight:700;">VIEW WORKSTATION →</a>
            </div>
        </div>

        <!-- Live HUD Status Bar -->
        <div class="live-bar">
            <span>SYS_TIME: <strong id="liveClock" style="color: var(--neon-green);">--:--:--</strong></span>
            <span>LATENCY: <strong style="color: var(--neon-cyan);">0.7ms</strong></span>
            <span>FIREWALL: <strong style="color: var(--neon-green);">ARMED & OPERATIONAL</strong></span>
            <span>CYBER RADAR: <strong style="color: var(--neon-amber);">SCANNING SUBNETS</strong></span>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="corner-accent"></div>
                <div class="label">Total Intercepted Attacks</div>
                <div class="val counter" data-target="{{ $stats['total_events'] ?? 0 }}" style="color: var(--neon-cyan);">0</div>
            </div>
            <div class="stat-card">
                <div class="corner-accent" style="border-color: var(--neon-red);"></div>
                <div class="label">Blocked Threats</div>
                <div class="val counter" data-target="{{ $stats['total_blocked'] ?? 0 }}" style="color: var(--neon-red);">0</div>
            </div>
            <div class="stat-card">
                <div class="corner-accent" style="border-color: var(--neon-amber);"></div>
                <div class="label">Throttled Requests</div>
                <div class="val counter" data-target="{{ $stats['total_throttled'] ?? 0 }}" style="color: var(--neon-amber);">0</div>
            </div>
            <div class="stat-card">
                <div class="corner-accent" style="border-color: var(--neon-purple);"></div>
                <div class="label">Active Blocked IPs</div>
                <div class="val counter" data-target="{{ $stats['blocked_ips_count'] ?? 0 }}" style="color: var(--neon-purple);">0</div>
            </div>
        </div>

        <!-- Interactive Real Database Time-Series Graphics Grid -->
        <div class="graphics-grid">
            <!-- Real 24-Hour Time-Series Canvas Chart -->
            <div class="chart-box">
                <div class="chart-header">
                    <h3>REAL 24-HOUR THREAT TIMELINE (MYSQL DATA)</h3>
                    <span style="font-size: 0.75rem; color: var(--neon-green);">● LIVE DATABASE TIME-SERIES</span>
                </div>
                <canvas id="threatChart"></canvas>
            </div>

            <!-- Radar Sweep Box -->
            <div class="radar-box">
                <div class="chart-header" style="width: 100%;">
                    <h3>CYBER RADAR</h3>
                    <span style="font-size: 0.75rem; color: var(--neon-cyan);">360° SWEEP</span>
                </div>
                <div class="radar-container">
                    <div class="radar-grid-line"></div>
                    <div class="radar-grid-line v"></div>
                    <div class="radar-sweep"></div>
                    <!-- Simulated threat blips -->
                    <div class="radar-blip" style="top: 30%; left: 60%;"></div>
                    <div class="radar-blip" style="top: 70%; left: 40%;"></div>
                    <div class="radar-blip" style="top: 45%; left: 25%;"></div>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.75rem;">SCANNING ACTIVE SUBNETS</div>
            </div>
        </div>

        <!-- Threat Type Distribution Breakdown -->
        <div class="breakdown-box" style="margin-bottom: 1.5rem;">
            <div class="chart-header">
                <h3>ATTACK VECTOR BREAKDOWN PERCENTAGE</h3>
            </div>
            <div>
                @php
                    $dist = $stats['threat_distribution'] ?? [];
                    $totalDist = array_sum($dist) ?: 1;
                @endphp

                @forelse($dist as $type => $cnt)
                    @php $pct = round(($cnt / $totalDist) * 100); @endphp
                    <div class="progress-item">
                        <div class="progress-label">
                            <span style="color: var(--neon-cyan);">{{ strtoupper(str_replace(['.detector', '_'], ['', ' '], $type)) }}</span>
                            <span>{{ $cnt }} ({{ $pct }}%)</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: {{ $pct }}%; background: var(--neon-cyan); color: var(--neon-cyan);"></div>
                        </div>
                    </div>
                @empty
                    <div class="progress-item">
                        <div class="progress-label"><span>SQL INJECTION</span><span>0%</span></div>
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 0%;"></div></div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-label"><span>XSS ATTACK</span><span>0%</span></div>
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 0%;"></div></div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-label"><span>PATH TRAVERSAL</span><span>0%</span></div>
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 0%;"></div></div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Terminal Threat Log Table -->
        <div class="terminal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin-bottom:0;">REAL-TIME MYSQL THREAT AUDIT LOG STREAM</h3>
                <a href="{{ route('security.attempts') }}" style="color: var(--neon-cyan); text-decoration: underline; font-size: 0.8rem; font-weight:700;">🖥️ VIEW ALL ATTEMPTS WORKSTATION →</a>
            </div>
            @if(isset($events) && count($events) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Rule ID</th>
                            <th>Severity</th>
                            <th>Risk Score</th>
                            <th>Target Route</th>
                            <th>Method</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                            <tr>
                                <td>{{ $event->created_at }}</td>
                                <td><code style="color: var(--neon-cyan);">{{ $event->type }}</code></td>
                                <td class="severity-{{ strtolower($event->severity) }}">{{ strtoupper($event->severity) }}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap: 0.5rem;">
                                        <span>{{ $event->risk_score }}/100</span>
                                        <div style="width: 60px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
                                            <div style="width: {{ $event->risk_score }}%; height: 100%; background: {{ $event->risk_score >= 80 ? 'var(--neon-red)' : ($event->risk_score >= 50 ? 'var(--neon-amber)' : 'var(--neon-green)') }};"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $event->route }}</td>
                                <td><strong>{{ $event->method }}</strong></td>
                                <td><span class="action-tag action-{{ strtolower($event->action) }}">{{ strtoupper($event->action) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                    [!] NO THREAT EVENT LOGS DETECTED IN DATABASE. APPLICATION SECURE.
                </div>
            @endif
        </div>

        <!-- HUD Footer Credit -->
        <div class="hud-footer">
            <strong>sagor/laravel-security v1.0.0</strong> | Developed by <strong>Moh Sagor</strong> | Defense-in-Depth Application Security Firewall
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

    <!-- Stat Counter Number Roll-Up Animation Script -->
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

    <!-- Matrix Digital Rain Canvas Script -->
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

            const chars = '010101010101SHIELD_SECURITY_SAGOR_FIREWALL';
            const fontSize = 12;
            const columns = Math.floor(window.innerWidth / fontSize);
            const drops = Array(columns).fill(1);

            function drawMatrix() {
                mCtx.fillStyle = 'rgba(2, 4, 10, 0.1)';
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

    <!-- Real Database Time-Series Canvas Chart Script -->
    <script>
        (function() {
            const canvas = document.getElementById('threatChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            function resizeCanvas() {
                canvas.width = canvas.parentElement.clientWidth - 40;
                canvas.height = 200;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            // REAL DATABASE TIME-SERIES DATA FROM LARAVEL / MYSQL
            const labels = @json($stats['hourly_labels'] ?? []);
            const counts = @json($stats['hourly_counts'] ?? []);
            const blocked = @json($stats['hourly_blocked'] ?? []);

            function drawChart() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (labels.length === 0) return;

                const maxVal = Math.max(...counts, ...blocked, 10);
                const stepX = canvas.width / (labels.length - 1);

                // Grid Lines & Hourly Labels
                ctx.strokeStyle = 'rgba(0, 243, 255, 0.08)';
                ctx.lineWidth = 1;
                ctx.font = '10px "Share Tech Mono"';
                ctx.fillStyle = '#5b708b';

                for (let i = 0; i < labels.length; i += 3) {
                    const x = i * stepX;
                    ctx.beginPath();
                    ctx.moveTo(x, 0);
                    ctx.lineTo(x, canvas.height - 20);
                    ctx.stroke();
                    ctx.fillText(labels[i], x, canvas.height - 5);
                }

                // 1. Plot Total Threats Waveform Line (Cyan Glow)
                ctx.beginPath();
                for (let i = 0; i < counts.length; i++) {
                    const x = i * stepX;
                    const y = (canvas.height - 30) - ((counts[i] / maxVal) * (canvas.height - 50));
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.strokeStyle = '#00f3ff';
                ctx.lineWidth = 2.5;
                ctx.shadowColor = '#00f3ff';
                ctx.shadowBlur = 10;
                ctx.stroke();

                // 2. Plot Blocked Threats Line (Red Glow)
                ctx.beginPath();
                for (let i = 0; i < blocked.length; i++) {
                    const x = i * stepX;
                    const y = (canvas.height - 30) - ((blocked[i] / maxVal) * (canvas.height - 50));
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.strokeStyle = '#ff0055';
                ctx.lineWidth = 2;
                ctx.shadowColor = '#ff0055';
                ctx.shadowBlur = 8;
                ctx.stroke();

                // Reset shadow blur
                ctx.shadowBlur = 0;
            }

            drawChart();
        })();
    </script>

</body>
</html>
