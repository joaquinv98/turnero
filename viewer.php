<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiempos - Cafe Pelotero</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="viewer-body">
    <div style="padding: 20px; text-align: center;">
        <h1 style="color: white; font-size: 3rem;">🎈 Tiempos de Juego</h1>
        <div id="clock" style="color: var(--accent); font-size: 1.5rem; margin-top: 10px;"></div>
    </div>

    <div id="viewerGrid" class="viewer-grid">
        <!-- Cards will be injected here -->
    </div>

    <!-- Audio for alerts -->
    <audio id="alarmSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script src="assets/js/main.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);

        let previousOvertimeIds = new Set();

        async function loadViewer() {
            const result = await apiRequest('api/turns.php?action=list');
            if (result.success) {
                const container = document.getElementById('viewerGrid');
                container.innerHTML = '';
                
                const currentOvertimeIds = new Set();

                result.turns.forEach(turn => {
                    const card = document.createElement('div');
                    card.className = 'viewer-card';
                    
                    if (turn.status === 'finished') {
                        card.style.opacity = '0.5';
                        card.style.borderColor = '#7f8c8d';
                        card.style.background = 'rgba(127, 140, 141, 0.1)';
                    } else if (turn.is_overtime) {
                        card.classList.add('danger');
                        currentOvertimeIds.add(turn.id);
                    } else if (turn.remaining_seconds < 900) {
                        card.classList.add('warning');
                    }

                    const timeDisplay = turn.status === 'finished' ? 
                        'FIN' : 
                        (turn.is_overtime ? 'TIEMPO CUMPLIDO' : formatTime(turn.remaining_seconds));

                    card.innerHTML = `
                        <div class="viewer-name">${turn.child_names}</div>
                        <div class="viewer-timer">${timeDisplay}</div>
                    `;
                    container.appendChild(card);
                });

                // Check for new overtime turns to play sound
                let playSound = false;
                for (let id of currentOvertimeIds) {
                    if (!previousOvertimeIds.has(id)) {
                        playSound = true;
                    }
                }
                
                if (playSound) {
                    document.getElementById('alarmSound').play().catch(e => console.log("Audio play failed (user interaction needed first):", e));
                }

                previousOvertimeIds = currentOvertimeIds;
            }
        }

        // Initial Load
        loadViewer();
        
        // Refresh every 2 seconds for near-realtime updates
        setInterval(loadViewer, 2000);

    </script>
</body>
</html>
