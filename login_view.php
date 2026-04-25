<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
    body {
        font-family: Arial;
        background: #4b0c7d;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh
    }

    .box {
        background: #fff;
        color: #222;
        padding: 24px;
        border-radius: 14px;
        width: 360px
    }

    input,
    button {
        width: 100%;
        padding: 10px;
        margin: 6px 0
    }

    button {
        background: #ff8a00;
        color: #fff;
        border: none;
        border-radius: 8px
    }
    </style>
</head>

<body>
    <div class="box">
        <h2>Log In</h2>
        <input id="email_or_username" placeholder="Email or Username">
        <input id="password" type="password" placeholder="Password">
        <button onclick="login()">Log In</button>
        <pre id="out"></pre>
    </div>

    <script>
    async function login() {
        const form = new URLSearchParams();
        form.append('email_or_username', document.getElementById('email_or_username').value);
        form.append('password', document.getElementById('password').value);

        const res = await fetch('api_login.php', {
            method: 'POST',
            body: form
        });
        const data = await res.json();
        document.getElementById('out').textContent = JSON.stringify(data, null, 2);

        if (data.success && data.token) {
            localStorage.setItem('jwt_token', data.token);
            localStorage.setItem('user_data', JSON.stringify(data.user));
        }
    }
    </script>
</body>

</html>