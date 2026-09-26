<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURITY THREAT INTERCEPTED // SAGOR LARAVEL SECURITY</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-void: #02040a;
            --bg-card: rgba(8, 14, 28, 0.92);
            --border-red: rgba(255, 0, 85, 0.4);
            --neon-red: #ff0055;
            --neon-cyan: #00f3ff;
            --neon-amber: #ffb700;
            --neon-green: #00ff66;
            --font-mono: 'Share Tech Mono', 'JetBrains Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-mono); }

        body {
            background-color: var(--bg-void);
            color: #d1d5db;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(255, 0, 85, 0.12) 0%, transparent 70%),
                linear-gradient(rgba(255, 0, 85, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 0, 85, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 35px 35px, 35px 35px;
            position: relative;
            overflow: hidden;
        }

        /* Matrix Code Canvas Background */
        canvas#matrixBg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            opacity: 0.15;
            z-index: 0;
        }

        /* CRT Scanline Overlay */
        body::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.3) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.04), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.04));
            z-index: 999;
            background-size: 100% 3px, 6px 100%;
            pointer-events: none;
            opacity: 0.6;
        }

        .blocked-container {
            width: 100%;
            max-width: 750px;
            background: var(--bg-card);
            border: 1px solid var(--border-red);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 0 40px rgba(255, 0, 85, 0.3), inset 0 0 15px rgba(255, 0, 85, 0.15);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(12px);
            animation: container-glow 3s infinite alternate;
        }

        @keyframes container-glow {
            0% { border-color: rgba(255, 0, 85, 0.3); box-shadow: 0 0 25px rgba(255, 0, 85, 0.25); }
            100% { border-color: rgba(255, 0, 85, 0.6); box-shadow: 0 0 50px rgba(255, 0, 85, 0.45); }
        }

        .blocked-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-red);
            padding-bottom: 1.25rem;
        }

        .alert-icon {
            width: 50px; height: 50px;
            background: rgba(255, 0, 85, 0.15);
            border: 2px solid var(--neon-red);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--neon-red);
            font-size: 1.8rem;
            font-weight: 800;
            box-shadow: 0 0 15px var(--neon-red);
            animation: pulse-icon 1s infinite alternate;
        }

        @keyframes pulse-icon {
            from { transform: scale(0.95); box-shadow: 0 0 10px var(--neon-red); }
            to { transform: scale(1.05); box-shadow: 0 0 25px var(--neon-red); }
        }

        .blocked-title h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--neon-red);
            text-shadow: 0 0 12px rgba(255, 0, 85, 0.8);
            letter-spacing: 2px;
        }

        .blocked-title p {
            color: #9ca3af;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-top: 0.2rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.75rem;
            background: rgba(0, 0, 0, 0.4);
            padding: 1.25rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .detail-item .label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }

        .detail-item .val {
            font-size: 0.95rem;
            color: var(--neon-cyan);
            font-weight: 600;
            word-break: break-all;
        }

        .reason-box {
            background: rgba(255, 0, 85, 0.1);
            border: 1px solid rgba(255, 0, 85, 0.3);
            border-left: 4px solid var(--neon-red);
            padding: 1rem 1.25rem;
            border-radius: 6px;
            margin-bottom: 1.75rem;
            color: #fecdd3;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .footer-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.8rem;
            color: #6b7280;
        }

        .btn-home {
            background: rgba(0, 243, 255, 0.15);
            color: var(--neon-cyan);
            border: 1px solid var(--neon-cyan);
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 1px;
            transition: all 0.25s ease;
            display: inline-block;
        }

        .btn-home:hover {
            background: var(--neon-cyan);
            color: #000;
            box-shadow: 0 0 15px var(--neon-cyan);
        }
    </style>
</head>
<body>

    <canvas id="matrixBg"></canvas>

    <div class="blocked-container">
        <div class="blocked-header">
            <div class="alert-icon">!</div>
            <div class="blocked-title">
                <h1>SECURITY THREAT INTERCEPTED</h1>
                <p>ACCESS DENIED BY SAGOR // LARAVEL SECURITY FIREWALL</p>
            </div>
        </div>

        <div class="reason-box">
            <strong>REASON:</strong> {{ $reason ?? 'Request blocked due to security policy violation.' }}
        </div>

        <div class="details-grid">
            <div class="detail-item">
                <div class="label">CLIENT IP ADDRESS</div>
                <div class="val">{{ $ip ?? request()->ip() }}</div>
            </div>
            <div class="detail-item">
                <div class="label">TARGET ROUTE</div>
                <div class="val">{{ request()->path() }}</div>
            </div>
            <div class="detail-item">
                <div class="label">HTTP METHOD</div>
                <div class="val">{{ request()->method() }}</div>
            </div>
            <div class="detail-item">
                <div class="label">TIMESTAMP</div>
                <div class="val" id="localTime">--:--:--</div>
            </div>
        </div>

        <div class="footer-bar">
            <span>PACKAGE: <strong>sagor/laravel-security</strong></span>
            <a href="/" class="btn-home">← RETURN TO HOME</a>
        </div>
    </div>

    <!-- Matrix Red Code Rain Canvas Script -->
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

            const chars = '010101ACCESS_DENIED_SECURITY_FIREWALL_SAGOR';
            const fontSize = 12;
            const columns = Math.floor(window.innerWidth / fontSize);
            const drops = Array(columns).fill(1);

            function drawMatrix() {
                mCtx.fillStyle = 'rgba(2, 4, 10, 0.1)';
                mCtx.fillRect(0, 0, mCanvas.width, mCanvas.height);

                mCtx.fillStyle = '#ff0055';
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

        document.getElementById('localTime').innerText = new Date().toLocaleTimeString();
    </script>

</body>
</html>
