<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8" />
    <title>Sign Up</title>
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
        <h2>Sign Up</h2>
        <input id="full_name" placeholder="Full Name">
        <input id="email" placeholder="Email">
        <input id="password" type="password" placeholder="Password">
        <label><input id="accepted_terms" type="checkbox"> Accept Terms</label>
        <button onclick="signup()">Create Account</button>
        <pre id="out"></pre>
    </div>

    <script>
    async function signup() {
        const fullName = document.getElementById('full_name').value.trim();
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const acceptedTerms = document.getElementById('accepted_terms').checked ? '1' : '0';

        if (password !== confirmPassword) {
            document.getElementById('out').textContent = JSON.stringify({
                success: false,
                message: "Password and Confirm Password do not match"
            }, null, 2);
            return;
        }

        const form = new URLSearchParams();
        form.append('full_name', fullName);
        form.append('username', username);
        form.append('email', email);
        form.append('password', password);
        form.append('accepted_terms', acceptedTerms);

        const res = await fetch('api_signup.php', {
            method: 'POST',
            body: form
        });
        const data = await res.json();
        document.getElementById('out').textContent = JSON.stringify(data, null, 2);
    }
    </script>
</body>

</html>