<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$view = $_GET['view'] ?? 'active';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Cafe Pelotero</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-left">
                <h1>🎈 Panel de Control</h1>
                <nav class="main-nav">
                    <a href="?view=active" class="<?php echo $view === 'active' ? 'active' : ''; ?>">Turnos Activos</a>
                    <a href="?view=history" class="<?php echo $view === 'history' ? 'active' : ''; ?>">Historial</a>
                    <a href="?view=reports" class="<?php echo $view === 'reports' ? 'active' : ''; ?>">Reportes</a>
                    <a href="?view=settings" class="<?php echo $view === 'settings' ? 'active' : ''; ?>">Configuración</a>
                </nav>
            </div>
            <div class="header-right">
                <a href="viewer.php" target="_blank" class="btn-viewer">Pantalla Pública ↗</a>
                <button onclick="logout()" class="secondary small">Salir</button>
            </div>
        </header>

        <?php if ($view === 'active'): ?>
        <div class="dashboard-grid">
            <!-- Sidebar / New Turn -->
            <div class="sidebar">
                <h3>Nuevo Turno</h3>
                <form id="newTurnForm">
                    <div class="form-group">
                        <label>Email Cliente (Opcional)</label>
                        <input type="email" id="clientEmail" list="emailList" placeholder="buscar...">
                        <datalist id="emailList"></datalist>
                    </div>

                    <div class="form-group">
                        <label>Cantidad de Niños</label>
                        <select id="childCount" onchange="updateFormInputs()">
                            <!-- Populated by JS -->
                        </select>
                    </div>

                    <div id="namesContainer">
                        <div class="form-group">
                            <label>Nombre Niño 1</label>
                            <input type="text" class="child-name-input" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Duración</label>
                        <select id="duration" onchange="calculatePrice()">
                             <!-- Populated by JS -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Método de Pago</label>
                        <select id="paymentMethod">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="qr">QR</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Total a Cobrar</label>
                        <div id="priceDisplay" class="price-display">$1500.00</div>
                    </div>

                    <button type="submit" class="btn-primary full-width">Iniciar Turno</button>
                </form>

                <hr class="divider">

                <h3>Caja Diaria</h3>
                <div class="stat-card compact">
                    <div class="stat-label">Total Hoy</div>
                    <div class="stat-value" id="dailyIncome">$0</div>
                    <div class="stat-label" id="dailyTurns">0 Turnos</div>
                </div>
            </div>

            <!-- Active Turns List -->
            <div class="main-content">
                <h2>Turnos en Curso</h2>
                <div id="turnsList" class="turns-list">
                    <!-- Turns loaded via JS -->
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($view === 'history'): ?>
        <div class="full-width-view">
            <h2>Historial del Día</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Niño</th>
                            <th>Duración</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="historyList"></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($view === 'reports'): ?>
        <div class="full-width-view">
            <h2>Reportes</h2>
            <div class="reports-controls">
                <div class="form-group">
                    <label>Desde</label>
                    <input type="date" id="reportStart" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Hasta</label>
                    <input type="date" id="reportEnd" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group" style="align-self: flex-end; display: flex; gap: 10px;">
                    <button onclick="loadReportPreview()" class="btn-primary">Ver Reporte</button>
                    <button onclick="downloadCSV()" class="secondary">Exportar CSV</button>
                    <button onclick="printReport()" class="secondary">Imprimir / PDF</button>
                </div>
            </div>
            
            <div class="report-preview" id="reportPreview">
                <p class="text-muted" style="text-align: center; padding: 20px;">Seleccione un rango de fechas y haga clic en "Ver Reporte"</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($view === 'settings'): ?>
        <div class="full-width-view">
            <h2>Configuración</h2>
            
            <div class="settings-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Pricing -->
                <div>
                    <h3>Precios</h3>
                    <div id="pricingGrid" class="pricing-grid" style="margin-top: 10px;">
                        <!-- Pricing loaded via JS -->
                    </div>
                </div>

                <!-- SMTP & Password -->
                <div style="display: flex; flex-direction: column; gap: 30px;">
                    <!-- SMTP -->
                    <div class="card" style="background: #f8f9fa; padding: 20px; border-radius: var(--radius);">
                        <h3>Configuración de Correo (SMTP)</h3>
                        <form id="smtpForm" style="margin-top: 15px;">
                            <div class="form-group">
                                <label>Host</label>
                                <input type="text" id="smtpHost" placeholder="mail.example.com">
                            </div>
                            <div class="form-group">
                                <label>Puerto</label>
                                <input type="number" id="smtpPort" placeholder="587">
                            </div>
                            <div class="form-group">
                                <label>Usuario</label>
                                <input type="text" id="smtpUser" placeholder="user@example.com">
                            </div>
                            <div class="form-group">
                                <label>Contraseña</label>
                                <input type="password" id="smtpPass">
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" class="btn-primary">Guardar</button>
                                <button type="button" onclick="testEmail()" class="secondary">Probar Email</button>
                            </div>
                        </form>
                    </div>

                    <!-- Cleanup Setting -->
                    <div class="card" style="background: #f8f9fa; padding: 20px; border-radius: var(--radius);">
                        <h3>Configuración General</h3>
                        <form id="generalSettingsForm" style="margin-top: 15px;">
                            <div class="form-group">
                                <label>Tiempo visible tras finalizar (minutos)</label>
                                <input type="number" id="cleanupMinutes" placeholder="30">
                                <small class="text-muted">Tiempo que los turnos finalizados permanecen en pantalla.</small>
                            </div>
                            <button type="submit" class="btn-primary">Guardar</button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="card" style="background: #f8f9fa; padding: 20px; border-radius: var(--radius);">
                        <h3>Cambiar Contraseña Admin</h3>
                        <form id="passwordForm" style="margin-top: 15px;">
                            <div class="form-group">
                                <label>Nueva Contraseña</label>
                                <input type="password" id="newPassword" required>
                            </div>
                            <button type="submit" class="danger">Actualizar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <div class="branding" style="text-align: center; margin-top: 40px; color: #666; font-size: 0.8rem;">
        Desarrollado por <a href="https://neatech.ar" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: bold;">NEATECH.AR</a>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        let currentPricing = [];
        const currentView = '<?php echo $view; ?>';

        async function init() {
            await fetchPricing();
            
            if (currentView === 'active') {
                renderNewTurnOptions();
                loadTurns();
                loadStats();
                setInterval(loadTurns, 5000);
            } else if (currentView === 'history') {
                loadHistory();
            } else if (currentView === 'settings') {
                renderPricingSettings();
                loadSettings();
            } else if (currentView === 'reports') {
                // loadReportPreview();
            }
        }

        async function fetchPricing() {
            const result = await apiRequest('api/pricing.php');
            if (result.success) {
                currentPricing = result.pricing;
            }
        }

        async function loadSettings() {
            const result = await apiRequest('api/settings.php');
            if (result.success) {
                const s = result.settings;
                if(document.getElementById('smtpHost')) {
                    document.getElementById('smtpHost').value = s.smtp_host || '';
                    document.getElementById('smtpPort').value = s.smtp_port || '';
                    document.getElementById('smtpUser').value = s.smtp_user || '';
                    document.getElementById('smtpPass').value = s.smtp_pass || '';
                }
                if(document.getElementById('cleanupMinutes')) {
                    document.getElementById('cleanupMinutes').value = s.cleanup_minutes || '30';
                }
            }
        }

        if(document.getElementById('generalSettingsForm')) {
            document.getElementById('generalSettingsForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const data = {
                    cleanup_minutes: document.getElementById('cleanupMinutes').value
                };
                const result = await apiRequest('api/settings.php?action=update', 'POST', data);
                alert(result.message);
            });
        }

        if(document.getElementById('smtpForm')) {
            document.getElementById('smtpForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const data = {
                    smtp_host: document.getElementById('smtpHost').value,
                    smtp_port: document.getElementById('smtpPort').value,
                    smtp_user: document.getElementById('smtpUser').value,
                    smtp_pass: document.getElementById('smtpPass').value
                };
                const result = await apiRequest('api/settings.php?action=update', 'POST', data);
                alert(result.message);
            });
        }

        async function testEmail() {
            const email = prompt("Ingrese un email para enviar la prueba:");
            if (email) {
                const result = await apiRequest('api/settings.php?action=test_email', 'POST', { email });
                alert(result.message);
            }
        }

        if(document.getElementById('passwordForm')) {
            document.getElementById('passwordForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const pass = document.getElementById('newPassword').value;
                if(confirm('¿Seguro que desea cambiar la contraseña?')) {
                    const result = await apiRequest('api/auth.php?action=change_password', 'POST', { password: pass });
                    alert(result.message);
                    if(result.success) document.getElementById('passwordForm').reset();
                }
            });
        }

        function renderNewTurnOptions() {
            const childSelect = document.getElementById('childCount');
            const durationSelect = document.getElementById('duration');
            
            if (!childSelect || !durationSelect) return;

            // Extract unique child counts and durations
            const childCounts = [...new Set(currentPricing.map(p => p.child_count))].sort((a,b) => a-b);
            const durations = [...new Set(currentPricing.map(p => p.duration_minutes))].sort((a,b) => a-b);

            childSelect.innerHTML = '';
            childCounts.forEach(count => {
                const option = document.createElement('option');
                option.value = count;
                option.textContent = `${count} ${count == 1 ? 'Niño' : 'Niños'}`;
                childSelect.appendChild(option);
            });

            durationSelect.innerHTML = '';
            durations.forEach(min => {
                const option = document.createElement('option');
                option.value = min;
                option.textContent = `${min/60} ${min/60 == 1 ? 'Hora' : 'Horas'}`;
                durationSelect.appendChild(option);
            });
            
            // Trigger updates
            updateFormInputs();
            calculatePrice();
        }

        function renderPricingSettings() {
            const container = document.getElementById('pricingGrid');
            container.innerHTML = '';
            
            const grouped = {};
            currentPricing.forEach(p => {
                if (!grouped[p.child_count]) grouped[p.child_count] = [];
                grouped[p.child_count].push(p);
            });

            Object.keys(grouped).forEach(count => {
                const column = document.createElement('div');
                column.className = 'pricing-column';
                column.innerHTML = `<h3>${count} ${count == 1 ? 'Niño' : 'Niños'}</h3>`;
                
                grouped[count].forEach(p => {
                    const card = document.createElement('div');
                    card.className = 'pricing-card';
                    card.innerHTML = `
                        <label>${p.duration_minutes / 60} Horas</label>
                        <div class="price-input-group">
                            <span>$</span>
                            <input type="number" value="${p.price}" id="price-${p.id}">
                        </div>
                        <button onclick="updatePrice(${p.id})">Guardar</button>
                    `;
                    column.appendChild(card);
                });
                container.appendChild(column);
            });
        }

        async function updatePrice(id) {
            const price = document.getElementById(`price-${id}`).value;
            const result = await apiRequest('api/pricing.php?action=update', 'POST', { id, price });
            if (result.success) {
                alert('Precio actualizado');
            } else {
                alert(result.message);
            }
        }

        function updateFormInputs() {
            const count = parseInt(document.getElementById('childCount').value);
            const container = document.getElementById('namesContainer');
            container.innerHTML = '';

            for (let i = 1; i <= count; i++) {
                container.innerHTML += `
                    <div class="form-group">
                        <label>Nombre Niño ${i}</label>
                        <input type="text" class="child-name-input" required>
                    </div>
                `;
            }
            calculatePrice();
        }

        function calculatePrice() {
            const count = parseInt(document.getElementById('childCount').value);
            const duration = parseInt(document.getElementById('duration').value);
            
            const rule = currentPricing.find(p => p.child_count == count && p.duration_minutes == duration);
            const price = rule ? rule.price : 0;
            
            document.getElementById('priceDisplay').textContent = `$${price}`;
            return price;
        }

        async function searchClients(query) {
            if (query.length < 2) return;
            const result = await fetch(`api/clients.php?q=${query}`).then(r => r.json());
            const datalist = document.getElementById('emailList');
            datalist.innerHTML = '';
            result.forEach(client => {
                const option = document.createElement('option');
                option.value = client.client_email;
                // Store names in data attribute or just show in label?
                // Datalist only shows value. We can try to prefill names if exact match.
                datalist.appendChild(option);
            });
            
            // Auto-fill name if exact match found in result
            const match = result.find(c => c.client_email === query);
            if (match) {
                const names = match.child_names.split(', ');
                // Try to fill inputs
                // First set count
                const count = names.length;
                if (count <= 3) {
                    document.getElementById('childCount').value = count;
                    updateFormInputs();
                    const inputs = document.querySelectorAll('.child-name-input');
                    names.forEach((name, i) => {
                        if (inputs[i]) inputs[i].value = name;
                    });
                }
            }
        }

        async function loadTurns() {
            const result = await apiRequest('api/turns.php?action=list');
            if (result.success) {
                const container = document.getElementById('turnsList');
                container.innerHTML = '';
                
                result.turns.forEach(turn => {
                    if (turn.status === 'finished' && turn.remaining_seconds <= 0) return;
                    
                    const card = document.createElement('div');
                    card.className = 'turn-card';
                    
                    if (turn.status === 'finished') {
                        card.classList.add('finished');
                    } else if (turn.is_overtime) {
                        card.classList.add('danger');
                    } else if (turn.remaining_seconds < 900) {
                        card.classList.add('warning');
                    }

                    const timeDisplay = turn.status === 'finished' ? 'FINALIZADO' : (
                        turn.is_overtime ? `Vencido hace ${formatTime(Math.abs(turn.remaining_seconds))}` : formatTime(turn.remaining_seconds)
                    );

                    card.innerHTML = `
                        <div class="turn-header">
                            <span class="turn-names">${turn.child_names}</span>
                            ${turn.status === 'active' ? `<button onclick="finishTurn(${turn.id})" class="danger small">Finalizar</button>` : ''}
                        </div>
                        <div class="turn-time">${timeDisplay}</div>
                        <div class="turn-meta">
                            Ingreso: ${turn.start_time.split(' ')[1].substring(0, 5)}
                        </div>
                    `;
                    container.appendChild(card);
                });
            }
        }

        async function loadHistory() {
            const result = await apiRequest('api/turns.php?action=history');
            if (result.success) {
                const tbody = document.getElementById('historyList');
                tbody.innerHTML = '';
                result.turns.forEach(turn => {
                    const row = `
                        <tr>
                            <td>${turn.start_time.split(' ')[1].substring(0, 5)}</td>
                            <td>${turn.child_names}</td>
                            <td>${turn.duration_minutes} min</td>
                            <td><span class="badge ${turn.status}">${turn.status}</span></td>
                            <td>${turn.payment_method || '-'}</td>
                            <td>$${turn.total_price}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        }

        async function loadStats() {
            const result = await apiRequest('api/reports.php?action=daily');
            if (result.success) {
                document.getElementById('dailyIncome').textContent = `$${result.income}`;
                document.getElementById('dailyTurns').textContent = `${result.turns_count} Turnos`;
            }
        }

        async function finishTurn(id) {
            if(confirm('¿Finalizar este turno?')) {
                await apiRequest('api/turns.php?action=finish', 'POST', { id });
                loadTurns();
                loadStats();
            }
        }

        async function loadReportPreview() {
            const start = document.getElementById('reportStart').value;
            const end = document.getElementById('reportEnd').value;
            const container = document.getElementById('reportPreview');
            
            container.innerHTML = '<p style="text-align:center;">Cargando...</p>';
            
            const result = await apiRequest(`api/reports.php?action=preview&start=${start}&end=${end}`);
            
            if (result.success && result.data.length > 0) {
                let html = `
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Niños</th>
                                <th>Email</th>
                                <th>Duración</th>
                                <th>Total</th>
                                <th>Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                let total = 0;
                
                result.data.forEach(row => {
                    total += parseFloat(row.total_price);
                    html += `
                        <tr>
                            <td>${row.date}</td>
                            <td>${row.time.substring(0, 5)}</td>
                            <td>${row.child_names}</td>
                            <td>${row.client_email || '-'}</td>
                            <td>${row.duration_minutes} min</td>
                            <td>$${row.total_price}</td>
                            <td>${row.payment_method || '-'}</td>
                        </tr>
                    `;
                });
                
                html += `
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td colspan="5" style="text-align: right;">TOTAL</td>
                            <td>$${total.toFixed(2)}</td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                `;
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p style="text-align:center; padding:20px;">No hay datos para este rango de fechas.</p>';
            }
        }

        function downloadCSV() {
            const start = document.getElementById('reportStart').value;
            const end = document.getElementById('reportEnd').value;
            window.location.href = `api/export.php?start=${start}&end=${end}`;
        }

        function printReport() {
            window.print();
        }

        async function logout() {
            await apiRequest('api/auth.php?action=logout');
            window.location.href = 'index.php';
        }

        // Autocomplete Logic
        let clientCache = [];

        async function searchClients(query) {
            if (query.length < 3) return; // Trigger after 3 chars
            
            const result = await fetch(`api/clients.php?q=${query}`).then(r => r.json());
            clientCache = result;
            
            const datalist = document.getElementById('emailList');
            datalist.innerHTML = '';
            
            result.forEach(client => {
                const option = document.createElement('option');
                option.value = client.client_email;
                option.label = client.child_names; // Show names as hint
                datalist.appendChild(option);
            });
        }

        // Listen for input changes to detect selection
        if(document.getElementById('clientEmail')) {
            document.getElementById('clientEmail').addEventListener('input', function(e) {
                const val = this.value;
                // Try to find in cache
                const match = clientCache.find(c => c.client_email === val);
                
                if (match) {
                    console.log("Match found:", match);
                    // Prefill logic
                    const names = match.child_names.split(', ');
                    const count = names.length;
                    
                    if (count <= 3) {
                        const countSelect = document.getElementById('childCount');
                        countSelect.value = count;
                        
                        // Force update
                        updateFormInputs(); 
                        
                        // Fill names after inputs are rendered
                        setTimeout(() => {
                            const inputs = document.querySelectorAll('.child-name-input');
                            names.forEach((name, i) => {
                                if (inputs[i]) {
                                    inputs[i].value = name;
                                    console.log(`Filled child ${i+1}: ${name}`);
                                }
                            });
                        }, 50);
                    }
                } else {
                    searchClients(val);
                }
            });
        }

        if(document.getElementById('newTurnForm')) {
            document.getElementById('newTurnForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const nameInputs = document.querySelectorAll('.child-name-input');
                const names = Array.from(nameInputs).map(input => input.value.trim());
                const duration = document.getElementById('duration').value;
                const price = calculatePrice();
                const email = document.getElementById('clientEmail').value;
                const payment = document.getElementById('paymentMethod').value;

                const result = await apiRequest('api/turns.php?action=create', 'POST', {
                    child_names: names,
                    duration: duration,
                    price: price,
                    email: email,
                    payment_method: payment
                });

                if (result.success) {
                    document.getElementById('newTurnForm').reset();
                    updateFormInputs();
                    loadTurns();
                    loadStats();
                } else {
                    alert(result.message);
                }
            });
        }

        init();

    </script>
</body>
</html>
