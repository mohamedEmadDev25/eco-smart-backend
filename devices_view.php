<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8" />
    <title>Active Devices</title>
    <style>
    body {
        font-family: Arial;
        background: #f4f4f4;
        margin: 20px
    }

    .row {
        display: flex;
        gap: 20px
    }

    .card {
        background: #fff;
        padding: 14px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08)
    }

    .left {
        width: 260px
    }

    .main {
        flex: 1
    }

    .device {
        margin: 8px 0;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px
    }

    input,
    select,
    button {
        padding: 8px;
        margin: 4px
    }

    .health {
        font-size: 22px;
        font-weight: bold;
        color: #5d2ea8
    }
    </style>
</head>

<body>
    <h2>Active Devices</h2>

    <div>
        <input id="home_id" value="1" placeholder="home_id">
        <input id="search" placeholder="search by name/location">
        <select id="status">
            <option value="">All Status</option>
            <option value="online">online</option>
            <option value="offline">offline</option>
            <option value="sleep">sleep</option>
        </select>
        <button onclick="loadAll()">Load</button>
    </div>

    <div class="row">
        <div class="card left">
            <h3>Categories</h3>
            <div id="cats"></div>
        </div>

        <div class="card main">
            <h3>Devices</h3>
            <div id="devices"></div>
        </div>

        <div class="card" style="width:220px">
            <h3>System Health</h3>
            <div class="health" id="health">--%</div>
            <div id="online">Online: --</div>
            <div id="warn">Warnings: --</div>
        </div>
    </div>

    <script>
    async function loadAll() {
        const homeId = document.getElementById('home_id').value;
        const search = document.getElementById('search').value;
        const status = document.getElementById('status').value;

        // devices
        const dRes = await fetch(
            `api_devices_list.php?home_id=${homeId}&search=${encodeURIComponent(search)}&status=${status}`);
        const dData = await dRes.json();
        const dBox = document.getElementById('devices');
        dBox.innerHTML = '';
        (dData.data || []).forEach(d => {
            dBox.innerHTML += `
      <div class="device">
        <b>${d.device_name}</b> - ${d.location_text || '-'}<br>
        Status: ${d.status} | ${d.metric_label || ''}: ${d.metric_value || ''}<br>
        Last seen: ${d.last_seen_at || '-'}
      </div>
    `;
        });

        // categories
        const cRes = await fetch(`api_categories_counts.php?home_id=${homeId}`);
        const cData = await cRes.json();
        const cBox = document.getElementById('cats');
        cBox.innerHTML = `<div><b>All Devices:</b> ${cData.all_devices_count || 0}</div>`;
        (cData.categories || []).forEach(c => {
            cBox.innerHTML += `<div>${c.category_name}: ${c.devices_count}</div>`;
        });

        // health
        const hRes = await fetch(`api_system_health.php?home_id=${homeId}`);
        const hData = await hRes.json();
        const h = hData.data || {};
        document.getElementById('health').textContent = `${h.health_percent ?? 0}%`;
        document.getElementById('online').textContent = `Online: ${h.online_devices ?? 0}`;
        document.getElementById('warn').textContent = `Warnings: ${h.active_warnings ?? 0}`;
    }
    loadAll();
    </script>
</body>

</html>