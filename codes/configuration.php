<?php
session_start();

// Empêche la mise en cache des pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #EFEFEF;
        }

        header {
            background-color: #0073E6;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        header a:hover {
            text-decoration: underline;
        }

        .container {
            text-align: center;
            margin-top: 50px;
        }

        button {
            padding: 10px 20px;
            border: none;
            background-color: #0073E6;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }

        button:hover {
            background-color: #005bb5;
            transform: translateY(-2px);
        }

        button.disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }

        .log {
            background-color: rgba(245, 245, 245, 0.9);
            font-size: 14px;
            padding: 20px;
            border: 1px solid #CCC;
            border-radius: 10px;
            height: 200px;
            overflow-y: auto;
            margin: 20px auto;
            width: 60%;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <a href="profil.php">Mon espace</a>
        <a href="gestion_mesures.php">Mes patients</a>
        <a href="">Configuration</a>
    </header>

    <div class="container">
        <h1>Configuration du Dispositif</h1>
        <button id="connect-btn">Effacer mémoire dispositif</button>
        <div class="log" id="log-output">
            <p><strong>Logs :</strong></p>
        </div>
    </div>

    <script>
        let device, server, txCharacteristic, rxCharacteristic;

        const connectBtn = document.getElementById('connect-btn');
        const logOutput = document.getElementById('log-output');

        // Fonction pour journaliser les actions
        function log(message, type = '') {
            const p = document.createElement('p');
            p.textContent = message;
            if (type === 'success') p.classList.add('success');
            if (type === 'error') p.classList.add('error');
            logOutput.appendChild(p);
            logOutput.scrollTop = logOutput.scrollHeight;
        }

        // Connexion au dispositif
        connectBtn.addEventListener('click', async () => {
            try {
                log("🔎 Recherche de périphériques Bluetooth...");
                device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: ['6e400001-b5a3-f393-e0a9-e50e24dcca9e'] // UUID du service Nordic UART
                });

                log(`✅ Périphérique sélectionné : ${device.name}`);
                server = await device.gatt.connect();
                log("🔗 Connexion réussie !", "success");

                device.addEventListener('gattserverdisconnected', () => {
                    log("❌ Périphérique déconnecté.", "error");
                });

                const service = await server.getPrimaryService('6e400001-b5a3-f393-e0a9-e50e24dcca9e');
                log("✅ Service Nordic UART trouvé.");

                txCharacteristic = await service.getCharacteristic('6e400002-b5a3-f393-e0a9-e50e24dcca9e'); // TX
                rxCharacteristic = await service.getCharacteristic('6e400003-b5a3-f393-e0a9-e50e24dcca9e'); // RX
                log("✅ Caractéristiques TX et RX trouvées.");

                // Activer la réception de notifications
                await rxCharacteristic.startNotifications();
                rxCharacteristic.addEventListener('characteristicvaluechanged', handleReceivedData);
                log("✅ Notifications activées sur RX.");


                // Envoyer le timestamp actuel après connexion
                await sendTimestamp();

            } catch (error) {
                log(`❌ Erreur de connexion : ${error.message}`, "error");
            }
        });

        // Fonction pour envoyer le timestamp actuel
        async function sendTimestamp() {
            try {
                if (!txCharacteristic) {
                    log("⚠️ TX characteristic non trouvée.", "error");
                    return;
                }

                const timestamp = Math.floor(Date.now() / 1000);
                const encoder = new TextEncoder();
                await txCharacteristic.writeValue(encoder.encode(`${timestamp}_RESET\n`));

                log(`🕒 Timestamp & RESET envoyé: ${timestamp}`);
            } catch (error) {
                log(`❌ Erreur lors de l'envoi du timestamp : ${error.message}`, "error");
            }
        }

        

        // Fonction pour gérer la réception des données
        function handleReceivedData(event) {
            const decoder = new TextDecoder();
            const receivedData = decoder.decode(event.target.value).trim();

            log(`📩 Réponse reçue : ${receivedData}`);

            if (receivedData.includes("RESET DONE")) {
                log("✅ Confirmation : Mémoire effacée avec succès !", "success");

                // Afficher la boîte de dialogue Bootstrap
                let resetModal = new bootstrap.Modal(document.getElementById('resetConfirmationModal'));
                resetModal.show();
            }
        }

    </script>

    <!-- Modal de confirmation -->
    <div id="resetConfirmationModal" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>✅ Mémoire du dispositif effacée avec succès !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap CDN (si ce n'est pas déjà inclus) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
