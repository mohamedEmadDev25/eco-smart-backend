<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alerts Management</title>
    <style>
    :root {
        --bg: #f6f3fb;
        --card1: #4b1496;
        --card2: #39107b;
        --accent: #ff8f3a;
        --text: #fff;
        --muted: #d8c9ff;
    }

    * {
        box-sizing: border-box
    }

    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        background: var(--bg);
        padding: 22px;
        color: #1f1140;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        max-width: 1300px;
        margin: 0 auto 14px;
    }

    .header h1 {
        margin: 0;
        color: #2e1163
    }

    .header p {
        margin: 6px 0 0;
        color: #7f66a8
    }

    .btn {
        border: none;
        background: #5a1ab3;
        color: #fff;
        padding: 10px 14px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 700;
    }

    .btn:hover {
        opacity: .92
    }

    .controls {
        max-width: 1300px;
        margin: 0 auto 12px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    input,
    select {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 9px;
        background: #fff;
    }

    .stats {
        max-width: 1300px;
        margin: 0 auto 14px;
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: 12px;
    }

    .stat-card {
        color: var(--text);
        background: linear-gradient(180deg, var(--card1), var(--card2));
        border-radius: 12px;
        padding: 14px;
        min-height: 88px;
    }

    .stat-card .label {
        font-size: 13px;
        color: var(--muted)
    }

    .stat-card .num {
        font-size: 34px;
        font-weight: 800;
        margin-top: 4px
    }

    .tabs {
        max-width: 1300px;
        margin: 0 auto 12px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .tab {
        border: none;
        padding: 9px 14px;
        border-radius: 999px;
        background: #e7ddff;
        color: #3f1f76;
        cursor: pointer;
        font-weight: 700;
    }

    .tab.active {
        background: #5a1ab3;
        color: #fff
    }

    .layout {
        max-width: 1300px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 14px;
    }

    .list-card,
    .trend-card {
        background: linear-gradient(180deg, var(--card1), var(--card2));
        border-radius: 14px;
        color: #fff;
        padding: 12px;
        box-shadow: 0 8px 20px rgba(45, 14, 94, .2);
    }

    .alert-item {
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
    }

    .row1 {
        display: flex;
        justify-content: space-between;
        gap: 10px
    }

    .title {
        font-weight: 800
    }

    .ago {
        color: var(--muted);
        font-size: 13px;
        white-space: nowrap
    }

    .msg {
        color: #f0e6ff;
        font-size: 14px;
        margin-top: 6px
    }

    .meta {
        font-size: 12px;
        color: #d8c9ff;
        margin-top: 8px
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        margin-right: 5px;
        background: rgba(255, 255, 255, .18);
    }

    .sev-critical {
        background: #ff5c5c
    }

    .sev-high {
        background: #ff8f3a
    }

    .sev-medium {
        background: #f0b429;
        color: #221
    }

    .sev-low {
        background: #59b8ff
    }

    .sev-info {
        background: #a58bff
    }

    .status-active {
        background: #f54a7a
    }

    .status-acknowledged {
        background: #2ca58d
    }

    .status-resolved {
        background: #7f8c8d
    }

    .trend-title {
        margin: 0 0 8px
    }

    canvas {
        width: 100%;
        height: 240px
    }

    .small-msg {
        color: #7f66a8;
        text-align: center;
        margin: 8px 0 12px;
        font-weight: 700
    }
    </style>
</head>

<body>

    <div class="header">
        <div>
            <h1>Alerts Management</h1>
            <p>Monitor and respond to system notifications in real-time</p>
        </div>
        <button class="btn" onclick="ackAll()">Acknowledge All</button>
    </div>

    <div class="controls">
        <input id="home_id" type="number" value="1" min="1" />
        <input id="user_id" type="number" value="1" min="1" />
        <input id="search" placeholder="Filter by service, status..." />
        <select id="status">
            <option value="">All Status</option>
            <option value="active">active</option>
            <option value="acknowledged">acknowledged</option>
            <option value="resolved">resolved</option>
        </select>
        <button class="btn" onclick="loadAll()">Load</button>
    </div>

    <div id="msg" class="small-msg"></div>

    <section class="stats">
        <article class="stat-card">
            <div class="label">Critical</div>
            <div class="num" id="critical_count">0</div>
        </article>
        <article class="stat-card">
            <div class="label">High</div>
            <div class="num" id="high_count">0</div>
        </article>
        <article class="stat-card">
            <div class="label">Medium</div>
            <div class="num" id="medium_count">0</div>
        </article>
        <article class="stat-card">
            <div class="label">Low</div>
            <div class="num" id="low_count">0</div>
        </article>
        <article class="stat-card">
            <div class="label">Info</div>
            <div class="num" id="info_count">0</div>
        </article>
    </section>

    <section class="tabs">
        <button class="tab active" data-tab="all" onclick="setTab(this)">All Alerts</button>
        <button class="tab" data-tab="critical" onclick="setTab(this)">Critical</button>
        <button class="tab" data-tab="high" onclick="setTab(this)">High</button>
        <button class="tab" data-tab="medium" onclick="setTab(this)">Medium</button>
        <button class="tab" data-tab="resolved" onclick="setTab(this)">Resolved</button>
    </section>

    <section class="layout">
        <div class="list-card">
            <div id="alerts_list"></div>
        </div>

        <div class="trend-card">
            <h3 class="trend-title">Alert Volume Trend</h3>
            <canvas id="trendCanvas" width="420" height="260"></canvas>
        </div>
    </section>

    <script>
    let currentTab = "all";

    function setMsg(t) {
        document.getElementById("msg").textContent = t;
    }

    function n(v) {
        const x = Number(v);
        return Number.isFinite(x) ? x : 0;
    }

    function setTab(btn) {
        currentTab = btn.dataset.tab;
        document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
        btn.classList.add("active");
        loadAlerts();
    }

    function agoText(dt) {
        const d = new Date(dt);
        const diff = Math.floor((Date.now() - d.getTime()) / 1000);
        if (diff < 60) return `${diff}s ago`;
        if (diff < 3600) return `${Math.floor(diff/60)} mins ago`;
        if (diff < 86400) return `${Math.floor(diff/3600)} hours ago`;
        return `${Math.floor(diff/86400)} days ago`;
    }

    function sevClass(sev) {
        return `sev-${sev}`;
    }

    function statusClass(s) {
        return `status-${s}`;
    }

    async function loadCounts() {
        const homeId = document.getElementById("home_id").value;
        const res = await fetch(`api_alerts_counts.php?home_id=${encodeURIComponent(homeId)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || "Counts API error");
        const d = json.data || {};
        document.getElementById("critical_count").textContent = n(d.critical_count);
        document.getElementById("high_count").textContent = n(d.high_count);
        document.getElementById("medium_count").textContent = n(d.medium_count);
        document.getElementById("low_count").textContent = n(d.low_count);
        document.getElementById("info_count").textContent = n(d.info_count);
    }

    async function loadAlerts() {
        const homeId = document.getElementById("home_id").value;
        const search = document.getElementById("search").value;
        const status = document.getElementById("status").value;

        const url =
            `api_alerts_list.php?home_id=${encodeURIComponent(homeId)}&tab=${encodeURIComponent(currentTab)}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;
        const res = await fetch(url);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || "List API error");

        const box = document.getElementById("alerts_list");
        box.innerHTML = "";

        const rows = json.data || [];
        if (rows.length === 0) {
            box.innerHTML = `<div class="alert-item">No alerts found</div>`;
            return;
        }

        rows.forEach(a => {
            box.innerHTML += `
      <div class="alert-item">
        <div class="row1">
          <div class="title">${a.title || "(No title)"}</div>
          <div class="ago">${a.created_at ? agoText(a.created_at) : ""}</div>
        </div>
        <div class="msg">${a.message || ""}</div>
        <div class="meta">
          <span class="badge ${sevClass(a.severity)}">${a.severity}</span>
          <span class="badge ${statusClass(a.status)}">${a.status}</span>
          <span class="badge">${a.alert_type || "other"}</span>
          <span class="badge">${a.source_component || "-"}</span>
          <span class="badge">${a.service_name || "-"}</span>
        </div>
      </div>
    `;
        });
    }

    function drawTrend(rows) {
        const c = document.getElementById("trendCanvas");
        const ctx = c.getContext("2d");
        const w = c.width,
            h = c.height,
            pad = 34;
        ctx.clearRect(0, 0, w, h);

        // grid
        ctx.strokeStyle = "rgba(255,255,255,.14)";
        for (let i = 0; i < 4; i++) {
            const y = pad + i * ((h - pad * 2) / 3);
            ctx.beginPath();
            ctx.moveTo(pad, y);
            ctx.lineTo(w - pad, y);
            ctx.stroke();
        }

        const days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
        const map = {};
        rows.forEach(r => {
            const d = new Date(r.day_date);
            const day = d.toLocaleDateString('en-US', {
                weekday: 'short'
            });
            map[day] = n(r.total_alerts);
        });

        const values = days.map(d => map[d] || 0);
        const maxV = Math.max(...values, 1);
        const barW = ((w - pad * 2) / days.length) - 8;

        values.forEach((v, i) => {
            const x = pad + i * ((w - pad * 2) / days.length) + 4;
            const bh = ((h - pad * 2) * (v / maxV));
            const y = h - pad - bh;

            ctx.fillStyle = (i === 5) ? "#ff5577" : "#ff9a4a";
            ctx.fillRect(x, y, barW, bh);

            ctx.fillStyle = "rgba(255,255,255,.85)";
            ctx.font = "11px Arial";
            ctx.fillText(days[i], x + 2, h - 10);
        });
    }

    async function loadTrend() {
        const homeId = document.getElementById("home_id").value;
        const res = await fetch(`api_alerts_trend.php?home_id=${encodeURIComponent(homeId)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || "Trend API error");
        drawTrend(json.data || []);
    }

    async function ackAll() {
        try {
            const homeId = document.getElementById("home_id").value;
            const userId = document.getElementById("user_id").value;
            const body = new URLSearchParams();
            body.append("home_id", homeId);
            body.append("user_id", userId);

            const res = await fetch("api_alert_acknowledge_all.php", {
                method: "POST",
                body
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message || "Acknowledge failed");
            setMsg(`Acknowledged: ${json.affected_rows || 0} alert(s)`);
            await loadAll();
        } catch (e) {
            setMsg(e.message);
        }
    }

    async function loadAll() {
        setMsg("Loading...");
        try {
            await loadCounts();
            await loadAlerts();
            await loadTrend();
            setMsg("Loaded successfully");
        } catch (e) {
            setMsg(e.message || "Load error");
        }
    }

    window.addEventListener("DOMContentLoaded", loadAll);
    </script>
</body>

</html>