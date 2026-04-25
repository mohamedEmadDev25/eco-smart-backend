<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Today's Usage</title>
    <style>
    :root {
        --bg: #f6f3fb;
        --card: #3f0f87;
        --card2: #4a1495;
        --accent: #ff8f3a;
        --text: #fff;
        --muted: #d9c8ff;
    }

    * {
        box-sizing: border-box
    }

    body {
        margin: 0;
        font-family: Arial;
        background: var(--bg);
        padding: 30px
    }

    .title-wrap {
        text-align: center;
        margin-bottom: 24px
    }

    .title-wrap small {
        color: #9a7fbe;
        font-weight: 700;
        letter-spacing: 2px
    }

    .title-wrap h1 {
        margin: 8px 0 0;
        color: #2f1066;
        font-size: 44px
    }

    .top-controls {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 20px
    }

    .top-controls input,
    .top-controls button {
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #ddd
    }

    .top-controls button {
        background: var(--accent);
        color: #fff;
        border: none;
        font-weight: 700;
        cursor: pointer
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(240px, 1fr));
        gap: 18px;
        max-width: 1200px;
        margin: 0 auto 20px
    }

    .card {
        background: linear-gradient(180deg, var(--card2), var(--card));
        color: #fff;
        border-radius: 16px;
        padding: 18px;
        min-height: 180px
    }

    .card h3 {
        margin: 0 0 10px
    }

    .kwh {
        font-size: 40px;
        font-weight: 800;
        margin: 6px 0
    }

    .kwh span {
        font-size: 18px;
        color: var(--muted)
    }

    .meta {
        color: var(--muted);
        font-size: 14px;
        margin-top: 8px
    }

    .progress {
        margin-top: 12px;
        height: 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .2);
        overflow: hidden
    }

    .bar {
        height: 100%;
        background: var(--accent);
        width: 0%
    }

    .details {
        margin-top: 12px;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 10px;
        padding: 9px;
        text-align: center
    }

    .trend-wrap {
        max-width: 1200px;
        margin: 0 auto;
        background: linear-gradient(180deg, var(--card2), var(--card));
        border-radius: 16px;
        padding: 16px;
        color: #fff
    }

    .chart-box {
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 12px;
        padding: 12px;
        overflow-x: auto
    }

    canvas {
        width: 100%;
        height: 260px
    }

    .msg {
        max-width: 1200px;
        margin: 0 auto 16px;
        color: #7b5f9f;
        text-align: center;
        font-weight: 700
    }
    </style>
</head>

<body>

    <div class="title-wrap">
        <small>REAL-TIME MONITORING</small>
        <h1>Today's Usage</h1>
    </div>

    <div class="top-controls">
        <input id="home_id" type="number" value="1" min="1">
        <button type="button" onclick="loadUsage()">Load Usage</button>
    </div>

    <div class="msg" id="msg"></div>

    <section class="cards">
        <article class="card">
            <h3>Daily Usage</h3>
            <div class="kwh" id="daily_kwh">0 <span>KWH</span></div>
            <div class="meta" id="daily_meta">vs baseline: --</div>
            <div class="details" id="daily_range">Period: --</div>
        </article>

        <article class="card">
            <h3>Weekly Usage</h3>
            <div class="kwh" id="weekly_kwh">0 <span>KWH</span></div>
            <div class="meta" id="weekly_meta">Average/day: --</div>
            <div class="details" id="weekly_range">Period: --</div>
        </article>

        <article class="card">
            <h3>Monthly Usage</h3>
            <div class="kwh" id="monthly_kwh">0 <span>KWH</span></div>
            <div class="meta" id="monthly_meta">Target: --</div>
            <div class="progress">
                <div class="bar" id="monthly_bar"></div>
            </div>
            <div class="details" id="monthly_percent">Achievement: --%</div>
        </article>
    </section>

    <section class="trend-wrap">
        <h3>Energy Consumption Trend</h3>
        <div class="chart-box">
            <canvas id="trendChart" width="1100" height="280"></canvas>
        </div>
    </section>

    <script>
    function num(v) {
        const n = Number(v);
        return Number.isFinite(n) ? n : 0;
    }

    function periodText(s, e) {
        if (!s || !e) return "Period: --";
        return `Period: ${s} to ${e}`;
    }

    function setMsg(t) {
        document.getElementById("msg").textContent = t;
    }

    function drawTrend(points) {
        const canvas = document.getElementById("trendChart");
        const ctx = canvas.getContext("2d");
        const w = canvas.width,
            h = canvas.height,
            pad = 45;

        ctx.clearRect(0, 0, w, h);

        // grid
        ctx.strokeStyle = "rgba(255,255,255,0.15)";
        for (let i = 0; i < 5; i++) {
            const y = pad + i * ((h - pad * 2) / 4);
            ctx.beginPath();
            ctx.moveTo(pad, y);
            ctx.lineTo(w - pad, y);
            ctx.stroke();
        }

        if (!Array.isArray(points) || points.length === 0) {
            ctx.fillStyle = "#fff";
            ctx.font = "18px Arial";
            ctx.fillText("No trend data for today", pad, h / 2);
            return;
        }

        const values = points.map(p => num(p.usage_kwh));
        const minV = Math.min(...values),
            maxV = Math.max(...values);
        const range = (maxV - minV) || 1;
        const stepX = (w - pad * 2) / Math.max(points.length - 1, 1);

        ctx.beginPath();
        points.forEach((p, i) => {
            const x = pad + i * stepX;
            const y = h - pad - ((num(p.usage_kwh) - minV) / range) * (h - pad * 2);
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.strokeStyle = "#ff9a4a";
        ctx.lineWidth = 3;
        ctx.stroke();

        points.forEach((p, i) => {
            const x = pad + i * stepX;
            const y = h - pad - ((num(p.usage_kwh) - minV) / range) * (h - pad * 2);
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, Math.PI * 2);
            ctx.fillStyle = "#ffd2a6";
            ctx.fill();

            const d = new Date(p.point_time);
            const hh = String(d.getHours()).padStart(2, "0");
            const mm = String(d.getMinutes()).padStart(2, "0");
            ctx.fillStyle = "rgba(255,255,255,.85)";
            ctx.font = "12px Arial";
            ctx.fillText(`${hh}:${mm}`, x - 16, h - 16);
        });
    }

    async function loadUsage() {
        const homeId = document.getElementById("home_id").value;
        if (!homeId) {
            setMsg("home_id مطلوب");
            return;
        }

        setMsg("Loading...");
        try {
            const overviewRes = await fetch(`api_usage_overview.php?home_id=${encodeURIComponent(homeId)}`);
            const trendRes = await fetch(`api_usage_trend.php?home_id=${encodeURIComponent(homeId)}`);

            const overview = await overviewRes.json();
            const trend = await trendRes.json();

            if (!overview.success) {
                setMsg(overview.message || "Overview API error");
                return;
            }
            if (!trend.success) {
                setMsg(trend.message || "Trend API error");
                return;
            }

            const d = overview.data?.daily || {};
            const w = overview.data?.weekly || {};
            const m = overview.data?.monthly || {};

            document.getElementById("daily_kwh").innerHTML = `${num(d.usage_kwh)} <span>KWH</span>`;
            document.getElementById("daily_meta").textContent = `vs baseline: ${num(d.baseline_kwh)} kWh`;
            document.getElementById("daily_range").textContent = periodText(d.period_start, d.period_end);

            const weeklyUsage = num(w.usage_kwh);
            const avgDay = (weeklyUsage / 7).toFixed(1);
            document.getElementById("weekly_kwh").innerHTML = `${weeklyUsage} <span>KWH</span>`;
            document.getElementById("weekly_meta").textContent = `Average: ${avgDay} kWh/day`;
            document.getElementById("weekly_range").textContent = periodText(w.period_start, w.period_end);

            const monthUsage = num(m.usage_kwh);
            const target = num(m.target_kwh);
            const percent = num(m.achievement_percent);
            document.getElementById("monthly_kwh").innerHTML = `${monthUsage} <span>KWH</span>`;
            document.getElementById("monthly_meta").textContent = `Target: ${target} kWh`;
            document.getElementById("monthly_percent").textContent = `Achievement: ${percent}%`;
            document.getElementById("monthly_bar").style.width = `${Math.min(percent,100)}%`;

            drawTrend(trend.data || []);
            setMsg("Loaded successfully");
        } catch (err) {
            setMsg("API error. تأكد إن ملفات api_usage_overview.php و api_usage_trend.php موجودين وشغالين.");
        }
    }

    window.addEventListener("DOMContentLoaded", loadUsage);
    </script>
</body>

</html>