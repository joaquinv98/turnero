<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafe Pelotero</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="flex-center">
        <div class="card login-card">
            <div class="login-logo">🎈</div>
            <h2>Cafe Pelotero</h2>
            <p style="margin-bottom: 20px; color: var(--text-light);">Sistema de Gestión</p>
            
            <form id="loginForm">
                <div class="form-group">
                    <input type="text" id="username" placeholder="Usuario" required>
                </div>
                <div class="form-group">
                    <input type="password" id="password" placeholder="Contraseña" required>
                </div>
                <button type="submit">Ingresar</button>
            </form>
            <div id="errorMsg" style="color: var(--danger); margin-top: 10px; display: none;"></div>
        </div>
    </div>

    <script src="assets/js/main.js?v=1"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            const result = await apiRequest('api/auth.php?action=login', 'POST', { username, password });
            
            if (result.success) {
                window.location.href = result.redirect || 'admin.php';
            } else {
                const errorDiv = document.getElementById('errorMsg');
                errorDiv.textContent = result.message;
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>
