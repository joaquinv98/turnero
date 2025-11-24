<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Cafe Pelotero</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Outfit', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .setup-card {
            background: var(--light);
            padding: 40px;
            border-radius: var(--radius);
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .step { display: none; }
        .step.active { display: block; }
        .branding {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8rem;
            color: #666;
        }
        .branding a {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1 style="text-align: center; margin-bottom: 30px;">🎈 Instalación</h1>
        
        <form id="setupForm" enctype="multipart/form-data">
            <!-- Step 1: Database -->
            <div class="step active" id="step1">
                <h3>1. Base de Datos</h3>
                <div class="form-group">
                    <label>Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Nombre de la BD</label>
                    <input type="text" name="db_name" value="cafe_pelotero" required>
                </div>
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="db_pass">
                </div>
                <button type="button" onclick="nextStep(2)" class="btn-primary full-width">Siguiente</button>
            </div>

            <!-- Step 2: Admin & Settings -->
            <div class="step" id="step2">
                <h3>2. Configuración Admin</h3>
                <div class="form-group">
                    <label>Usuario Admin</label>
                    <input type="text" name="admin_user" value="admin" required>
                </div>
                <div class="form-group">
                    <label>Contraseña Admin</label>
                    <input type="password" name="admin_pass" required>
                </div>
                
                <h3>3. Personalización</h3>
                <div class="form-group">
                    <label>Favicon (Opcional)</label>
                    <input type="file" name="favicon" accept="image/x-icon,image/png">
                </div>

                <div class="form-group">
                    <label>SMTP Host (Opcional)</label>
                    <input type="text" name="smtp_host" value="mail.neatech.ar">
                </div>
                <div class="form-group">
                    <label>SMTP User (Opcional)</label>
                    <input type="text" name="smtp_user" value="ejemplo@neatech.ar">
                </div>
                <div class="form-group">
                    <label>SMTP Pass (Opcional)</label>
                    <input type="password" name="smtp_pass" value="changeme">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="nextStep(1)" class="secondary">Atrás</button>
                    <button type="submit" class="btn-primary full-width">Instalar</button>
                </div>
            </div>
        </form>

        <div id="message" style="margin-top: 20px; text-align: center;"></div>

        <div class="branding">
            Desarrollado por <a href="https://neatech.ar" target="_blank">NEATECH.AR</a>
        </div>
    </div>

    <script>
        function nextStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
        }

        document.getElementById('setupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const msg = document.getElementById('message');
            
            btn.disabled = true;
            btn.textContent = 'Instalando...';
            msg.textContent = '';

            const formData = new FormData(e.target);

            try {
                const response = await fetch('api/setup_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    msg.innerHTML = '<span style="color: green;">¡Instalación completada! Redirigiendo...</span>';
                    setTimeout(() => window.location.href = 'index.php', 2000);
                } else {
                    msg.innerHTML = `<span style="color: red;">Error: ${result.message}</span>`;
                    btn.disabled = false;
                    btn.textContent = 'Instalar';
                }
            } catch (err) {
                msg.innerHTML = `<span style="color: red;">Error de conexión</span>`;
                btn.disabled = false;
                btn.textContent = 'Instalar';
            }
        });
    </script>
</body>
</html>
