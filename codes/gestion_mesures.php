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

ini_set('display_errors', 0);

// Connexion à la base de données
$host = "localhost";
$dbname = "empoct_app_medecin";
$username = "root";
$password = "";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion : " . $e->getMessage()]);
    exit();
}

// Vérifier si les données sont bien envoyées
$data = json_decode(file_get_contents("php://input"), true);
if (!empty($data['id_patient']) && !empty($data['date']) && !empty($data['description'])) {
    $id_patient = $data['id_patient'];
    $date = $data['date'];
    $description = $data['description'];

    // Insérer dans la table `rapport`
    $stmt = $pdo->prepare("INSERT INTO rapport (id_patient, date, description) VALUES (:id_patient, :date, :description)");
    $stmt->bindParam(':id_patient', $id_patient, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Rapport enregistré avec succès"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur lors de l'enregistrement"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Données manquantes"]);
}

// 🔍 Récupération des données envoyées


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> gestion_mesures </title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #EFEFEF;
        }

        /* Header */
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

        /* Layout */
        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background-color: #B0D8F8;
            width: 25%;
            padding: 20px;
        }

        .sidebar h2 {
            color: #0073E6;
        }

        .patient-info, .patients-list {
            margin-bottom: 20px;
        }

        .patients-list button {
            display: block;
            background-color: #EFEFEF;
            border: none;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            text-align: left;
            cursor: pointer;
        }

        .patients-list button:hover {
            background-color: #D1E9FF;
        }

        /* Main content */
        .main-content {
            background-color: #FFFFFF;
            width: 75%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .main-content h1 {
            color: #0073E6;
            font-size: 24px;
        }

        /* Boutons */
        button {
            padding: 10px 20px;
            border: none;
            background-color: #0073E6;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #005bb5;
            transform: translateY(-2px);
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.3);
        }

        button.disabled {
            background-color: #ccc; /* Couleur grise */
            color: #666; /* Texte grisé */
            cursor: not-allowed;
        }

        button.active {
            background-color: #28a745; /* Couleur verte */
            color: white;
            cursor: pointer;
        }


        /* Zone de logs */
        .log {
            background-color: rgba(245, 245, 245, 0.9);
            font-size: 14px;
            padding: 30px;
            border: 1px solid #CCC;
            border-radius: 10px;
            height: 300px;
            overflow-y: scroll;
            margin: 20px auto;
            width: 90%;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Graphique */
        .chart {
            background-color: #FFFFFF;
            border: 1px solid #DDD;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Statistiques */
        .stats div {
            background-color: #f7f9fc;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin: 10px;
            flex: 1;
        }

        

        .stats div:nth-child(1) {
            color: #0073E6; /* Moyenne en bleu */
        }

        .stats div:nth-child(2) {
            color: #28a745; /* Minimum en vert */
        }

        .stats div:nth-child(3) {
            color: #dc3545; /* Maximum en rouge */
        }

    </style>
</head>
<body>
    <header>
        <a href="profil.php">Mon tableau de bord</a>
        <a href="gestion_mesures.html">Mes patients</a>
        <a href="configuration.html">Configuration</a>
        <li class="nav-item">
            <a class="nav-link" href="deconnexion.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">Déconnexion</a>
        </li>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Patient actif</h2>
            <div class="patient-info">
                <p><strong>Nom :</strong></p>
                <p><strong>Prénom :</strong></p>
                <p><strong>Date dernière mesure :</strong></p>
            </div>

            <div class="reports">
                <h2>Rapports</h2>
                <ul id="reportList">
                    <!-- Les rapports du patient seront affichés ici -->
                    <div>

                    </div>
                </ul>
                <button id="addReportBtn">Rédiger un rapport</button>
            </div>
            
            <!-- Fenêtre modale pour la rédaction du rapport -->
            <div id="reportModal" class="modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center;">
                <div class="modal-content" style="width: 50%; max-width: 600px; max-height: 80vh; padding: 20px; border-radius: 10px; background-color: #f0f8ff; display: flex; flex-direction: column; overflow: auto;">
                    
                    <!-- Titre de la fenêtre modale avec icône -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h2>Rédiger un rapport</h2>
                    </div>
                    
                    <!-- Contenu principal de la modale -->
                    <div class="container mt-4" style="background: white; padding: 15px; border-radius: 10px; text-align: center; flex-grow: 1; display: flex; flex-direction: column;">
                        
                        <!-- Champ de sélection de la date -->
                        <div class="row" style="margin-bottom: 15px; text-align: center;">
                            <label for="reportDate">Date :</label>
                            <input type="date" id="reportDate" required style="width: 80%; padding: 5px; margin-top: 5px;">
                        </div>
                        
                        <!-- Zone de saisie du texte du rapport -->
                        <div class="row" style="flex-grow: 1;">
                            <textarea id="reportText" rows="10" required style="width: 100%; padding: 10px; resize: vertical; border: 1px solid #ccc; border-radius: 5px;"></textarea>
                        </div>
                    </div>
                    
                    <!-- Boutons de validation et d'annulation -->
                    <div class="row" style="margin-top: 15px; text-align: center; padding: 15px; background: white; border-radius: 10px;">
                        <button id="saveReportBtn" style="padding: 10px 15px; margin-right: 10px; border-radius: 5px; background-color: #28a745; color: white; border: none;">Enregistrer</button>
                        <button id="closeReportModal" style="padding: 10px 15px; border-radius: 5px; background-color: #dc3545; color: white; border: none;">Annuler</button>
                    </div>
                </div>
            </div>
            
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Récupération des éléments de la modale et des rapports
                const reportList = document.getElementById("reportList");
                const reportModal = document.getElementById("reportModal");
                const addReportBtn = document.getElementById("addReportBtn");
                const closeReportModal = document.getElementById("closeReportModal");
                const saveReportBtn = document.getElementById("saveReportBtn");
                const reportDate = document.getElementById("reportDate");
                const reportText = document.getElementById("reportText");
                const patientList = document.getElementById("patientList"); // Liste des patients
                
                var currentPatientId = null;  // var permet une portée globale

                patientList.addEventListener("click", function(event) {
                    const selectedPatient = event.target;
                    if (selectedPatient.dataset.id) {
                        currentPatientId = selectedPatient.dataset.id;
                        console.log("Patient sélectionné ID:", currentPatientId);
                    }
                });
            
                // Mettre à jour currentPatientId lorsqu'un patient est sélectionné
                if (patientList) {
                    patientList.addEventListener("click", function(event) {
                        const selectedPatient = event.target;
                        if (selectedPatient.dataset.id) {
                            currentPatientId = selectedPatient.dataset.id;
                            console.log("Patient sélectionné ID:", currentPatientId);
                        }
                    });
                }
            
                // Chargement des rapports du patient sélectionné
                function loadReports(patientId) {
                    currentPatientId = patientId;
                    console.log(currentPatientId);
                    fetch(`./get_reports.php?id_patient=${patientId}`)
                        .then(response => response.json())
                        .then(data => {
                            reportList.innerHTML = ""; 
                            data.forEach(report => {
                                const li = document.createElement("li");
                                li.textContent = `${report.date}`;
                                li.dataset.id = report.id_rapport;
                                li.addEventListener("click", function() {
                                    window.open(`view_report.php?id_rapport=${report.id_rapport}`, '_blank');
                                });
                                reportList.appendChild(li);
                            });
                        })
                        .catch(error => console.error("Erreur lors du chargement des rapports:", error));
                }
            
                // Vérifier si un patient est sélectionné avant d'ouvrir la fenêtre modale
                addReportBtn.addEventListener("click", function() {
                    if (!currentPatientId) {
                        alert("Veuillez sélectionner un patient avant de rédiger un rapport.");
                        return;
                    }
                    reportModal.style.display = "flex";
                    console.log("Patient actuel : " + currentPatientId);
                });
            
                // Fermer la fenêtre modale lorsqu'on clique sur "Annuler"
                closeReportModal.addEventListener("click", function() {
                    reportModal.style.display = "none";
                });
            
                // Sauvegarde d'un rapport
                saveReportBtn.addEventListener("click", function() {
                    if (!currentPatientId) {
                        alert("Sélectionnez d'abord un patient.");
                        return;
                    }
                    
                    const date = reportDate.value;
                    const description = reportText.value;
                    if (!date || !description) {
                        alert("Veuillez remplir tous les champs.");
                        return;
                    }
                    
                    console.log(currentPatientId);
                    fetch("./save_report.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_patient: currentPatientId,
                            date: reportDate.value,
                            description: reportText.value
                        })
                        
                    })

                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Rapport enregistré avec succès.");
                            reportModal.style.display = "none";
                            loadReports(currentPatientId);
                        } else {
                            alert("Erreur lors de l'enregistrement du rapport.");
                        }
                    })
                    .catch(error => console.error("Erreur lors de la sauvegarde du rapport:", error));
                });
            });

                // Vérifie les données envoyées
                    console.log("Données envoyées :", { id_patient: currentPatientId, date, description });

                    console.log(currentPatientId);
                    fetch("save_report.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id_patient: currentPatientId, date, description })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Réponse du serveur :", data);
                        if (data.success) {
                            alert("Rapport enregistré avec succès.");
                            reportModal.style.display = "none";
                            loadReports(currentPatientId);
                        } else {
                            alert("Erreur lors de l'enregistrement du rapport : " + data.message);
                        }
                    })
                    .catch(error => console.error("Erreur lors de la sauvegarde du rapport:", error));
            </script>
            

        </div>

        <!-- Main content -->
        <div class="main-content">
            <div class="buttons">
                <button id="connect-btn">Connecter</button>
                <button id="send-info-btn"> Infos Dispositif</button>
                <button id="display-data-btn" disabled class="disabled">Afficher données</button>

                <div class="device-info">
                    <h2>Informations du Dispositif</h2>
                    <p><strong>Espace restant :</strong> <span id="device-space">--</span> %</p>
                    <p><strong>Batterie :</strong> <span id="device-battery">--</span> %</p>
                </div>

            </div>
            <div class="log" id="log-output">
                <!-- Logs apparaîtront ici -->
            </div>
            <h1>Mesures</h1>
            <div class="chart">
                <canvas id="dataChart" width="600" height="300"></canvas>
            </div>
            <div class="stats">
                <div>Moyenne : Na</div>
                <div>Minimum : Na</div>
                <div>Maximum : Na</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("patientModal");
            const patientList = document.getElementById("patientList");
        
            // Ouverture automatique du modal
            modal.style.display = "flex";
        
            // Requête AJAX pour récupérer les patients
            fetch("get_patients.php")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error("Erreur:", data.error);
                        return;
                    }
        
                    data.forEach(patient => {
                        const li = document.createElement("li");
                        li.textContent = `${patient.nom_patient} ${patient.prenom_patient}`;
                        li.dataset.id = patient.id_patient;
                        li.dataset.nom = patient.nom_patient;
                        li.dataset.prenom = patient.prenom_patient;
                        li.dataset.date = patient.date_debut;
        
                        li.addEventListener("click", function() {
                            // Met à jour les infos du patient sélectionné
                            document.querySelector(".patient-info p:nth-child(1)").innerHTML = `<strong>Nom :</strong> ${this.dataset.nom}`;
                            document.querySelector(".patient-info p:nth-child(2)").innerHTML = `<strong>Prénom :</strong> ${this.dataset.prenom}`;
                            document.querySelector(".patient-info p:nth-child(3)").innerHTML = `<strong>Date dernière mesure :</strong> ${this.dataset.date}`;
        
                            // Ferme la fenêtre modale
                            modal.style.display = "none";
                        });
        
                        patientList.appendChild(li);
                    });
                })
                .catch(error => console.error("Erreur AJAX:", error));
        });
    </script>
        

    <script>
        const connectBtn = document.getElementById('connect-btn');
        const sendInfoBtn = document.getElementById('send-info-btn');
        const displayDataBtn = document.getElementById('display-data-btn');

        const logOutput = document.getElementById('log-output');
        const ppbValues = []; // Stocke les valeurs de ppb

        // Tableau pour stocker temporairement les données reçues
        const storedData = [];


        // Ajout un événement 'click' pour appeler la fonction 'displayStoredData'
        displayDataBtn.addEventListener('click', displayStoredData);
        

        /*let intervalId = null; // Pour stocker l'ID de l'intervalle
        let frequency = 2000; // Fréquence d'envoi par défaut (en millisecondes)*/

        let device, server, txCharacteristic, rxCharacteristic;

        // Tableau pour stocker les données reçues
        const dataPoints = [];

        // Initialiser le graphique avec Chart.js
        const ctx = document.getElementById('dataChart').getContext('2d');
        const dataChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // Les labels seront ajoutés dynamiquement
                datasets: [{
                    label: 'Données reçues',
                    data: dataPoints,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Temps'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'PPM'
                        }
                    }
                }
            }
        });

        function log(message) {
            logOutput.textContent += `${message}\n`;
        }

        /**
         * Fonction pour stocker les données reçues
         * @param {Object} data - Un objet JSON contenant les clés 'timestamp' et 'ppb'
         */
        function storeData(data) {
            if ('timestamp' in data && 'ppb' in data) {
                // Ajouter les données au tableau stocké
                storedData.push({
                    timestamp: data.timestamp,
                    ppb: data.ppb
                });

                // Ajouter la valeur au tableau pour updateStats
                ppbValues.push(data.ppb/1000);

                log(`Data stored: Timestamp = ${data.timestamp}, PPB = ${data.ppb}`);
            } else {
                log('Error: Keys "timestamp" or "ppb" not found in data.');
            }
        }

        /**
         * Fonction pour afficher les données stockées sur le graphique
         */
        function displayStoredData() {
            /*for (const item of storedData) {
                updateChart(item.timestamp, item.ppb);
            }
            log('All stored data displayed on the chart.');*/

            if (storedData.length === 0) {
                log('No data to display.');
                return;
            }
            storedData.forEach(item => updateChart(item.timestamp, item.ppb));
            log('All stored data displayed on the chart.');

            // Mettre à jour les statistiques après affichage
            updateStats();

            // Réinitialiser le tableau après affichage
            storedData.length = 0;
    
        }



        // Connexion à un dispositif Bluetooth
        connectBtn.addEventListener('click', async () => {
            try {
                log('Searching for Bluetooth devices...');
                device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: ['6e400001-b5a3-f393-e0a9-e50e24dcca9e'] // UUID du service Nordic UART
                });

                log(`Device selected: ${device.name}`);
                server = await device.gatt.connect();
                log('Connected to device.');

                // Gestion de la déconnexion
                device.addEventListener('gattserverdisconnected', () => {
                    log('Device disconnected.');
                    //stopSendingData();
                    sendInfoBtn.disabled = true; // Désactiver le bouton d'envoi manuel
                });

                // Accéder au service Nordic UART
                const service = await server.getPrimaryService('6e400001-b5a3-f393-e0a9-e50e24dcca9e');
                log('Nordic UART service found.');

                // Récupérer les caractéristiques TX et RX
                txCharacteristic = await service.getCharacteristic('6e400002-b5a3-f393-e0a9-e50e24dcca9e'); // TX UUID
                rxCharacteristic = await service.getCharacteristic('6e400003-b5a3-f393-e0a9-e50e24dcca9e'); // RX UUID
                log('TX and RX characteristics found.');

                // Activer les notifications pour RX
                try {
                    await rxCharacteristic.startNotifications();
                    log('Notifications started for RX characteristic.');
                } catch (error) {
                    log(`Error starting notifications: ${error.message}`);
                    return;
                }

                // Gérer les données reçues via RX
                rxCharacteristic.addEventListener('characteristicvaluechanged', (event) => {
                    const decoder = new TextDecoder();
                    const receivedData = decoder.decode(event.target.value);

                    // Journaliser les données reçues
                    log(`Data received: ${receivedData}`);

                    // Diviser les données en utilisant '\n' comme séparateur
                    const dataParts = receivedData.split('\n');
                    
                    for (const dataPart of dataParts){
                        try {
                            // Ignorer les segments vides
                            if (!dataPart.trim()) continue;

                            // Activer le bouton si on reçoit "END"
                            if (dataPart.trim() === "END") {
                                toggleButtonState(displayDataBtn, true);
                                log('Received "END". Button activated.');
                                continue;
                            }

                            // Convertir la chaîne JSON en objet
                            const data = JSON.parse(dataPart);

                            // Appeler la fonction pour stocker les données
                            storeData(data);

                            // Vérifier si les données correspondent aux informations du dispositif
                            if (data.space !== undefined && data.battery !== undefined) {
                                document.getElementById('device-space').textContent = data.space;
                                document.getElementById('device-battery').textContent = data.battery;

                                log(`Espace restant : ${data.space} %`);
                                log(`Batterie : ${data.battery} %`);
                            }


                        } catch (error) {
                            log(`Error parsing received data: ${error.message}`);
                        }
                    }
                });
                
                // Envoyer l'heure actuelle une fois au début
                await sendCurrentTime();

                // 🔹 Envoyer une seule fois 'MEASURE' après connexion 🔹
                await sendDataCommand();

                // Démarrer l'envoi périodique de commandes
                //startSendingData();

            } catch (error) {
                log(`Error: ${error.message}`);
            }
        });

        // Associer le bouton "Envoyer Commande" à l'envoi de la commande "measure"
        sendInfoBtn.addEventListener('click', async () => {
            try {
                

                // Envoyer l'heure actuelle une fois au début
                await sendCurrentTime();

                // Appeler la fonction sendInfoCommand pour envoyer la commande "info"
                await sendInfoCommand();

                // Journaliser le succès
                log('Command sent: INFO');
            } catch (error) {
                log(`Error sending command: ${error.message}`);
            }
        });

        // Ajouter un événement pour afficher les données stockées sur le graphique
        displayDataBtn.addEventListener('click', () => {
            displayStoredData(); // Appeler la fonction existante pour afficher les données

            // Désactiver à nouveau le bouton après l'affichage
            toggleButtonState(displayDataBtn, false);
            log('Data displayed on chart and button deactivated.');;
        });

        
        // Fonction pour envoyer une commande périodiquement
        async function sendDataCommand() {
        try {
            if (!txCharacteristic) {
                log('TX characteristic not found. Connect to the device first.');
                return;
            }

            const encoder = new TextEncoder();
            const command = encoder.encode('_MEASURE\n'); // Commande à envoyer
            await txCharacteristic.writeValue(command);
            log('Command sent: MEASURE');
        } catch (error) {
            log(`Error sending command: ${error.message}`);
        }
        }

        // Fonction pour envoyer une commande pour recuperer les donneés dispositif
        async function sendInfoCommand() {
        try {
            if (!txCharacteristic) {
                log('TX characteristic not found. Connect to the device first.');
                return;
            }

            const encoder = new TextEncoder();
            const command = encoder.encode('_INFO\n'); // Commande à envoyer
            await txCharacteristic.writeValue(command);
            log('Command sent: INFO');
        } catch (error) {
            log(`Error sending command: ${error.message}`);
        }
        }

        // Démarrer l'envoi périodique de commandes
        /*function startSendingData() {
            if (intervalId) {
                clearInterval(intervalId); // Nettoyer tout intervalle précédent
            }
            intervalId = setInterval(sendDataCommand, frequency); // Envoyer à la fréquence définie
            log(`Started sending "send data" every ${frequency / 1000} seconds.`);
        }*/

        // Arrêter l'envoi périodique
        /*function stopSendingData() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
                log('Stopped sending "send data".');
            }
        }*/
        

        // Fonction pour envoyer l'heure actuelle une seule fois
        async function sendCurrentTime() {
            try {
                if (!txCharacteristic) {
                    log('TX characteristic not found. Connect to the device first.');
                    return;
                }

                // Obtenir le timestamp actuel (nombre de millisecondes depuis 1970, divisé par 1000 pour avoir des secondes)
                const timestamp = Math.floor(Date.now() / 1000);

                // Créer le message à envoyer
                const message = `${timestamp}`;
                const encoder = new TextEncoder();
                const command = encoder.encode(message);

                // Envoyer le timestamp au dispositif Bluetooth
                await txCharacteristic.writeValue(command);

                // Ajouter aux logs
                log(`Current timestamp sent: ${message}`);
            } catch (error) {
                log(`Error sending current timestamp: ${error.message}`);
            }
        }


       // Fonction pour mettre à jour le graphique
        function updateChart(timestamp, value) {
            // Convertir le timestamp UNIX en date lisible
            const date = new Date(timestamp * 1000); // Convertit le timestamp en millisecondes

            // Formater la date en "JJ/MM/AAAA HH:MM"
            const formattedDate = date.toLocaleDateString("fr-FR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
            }) + " " + date.toLocaleTimeString("fr-FR", {
                hour: "2-digit",
                minute: "2-digit"
            });

            // Ajouter la date formatée comme label
            dataChart.data.labels.push(formattedDate);
            dataChart.data.datasets[0].data.push(value/1000); // divison par 1000 car valeur recu en PPB et non en PPM

            // Garder uniquement les 50 dernières entrées pour éviter la surcharge du graphique
            if (dataChart.data.labels.length > 50) {
                dataChart.data.labels.shift();
                dataChart.data.datasets[0].data.shift();
            }

            dataChart.update();
        }



        

        function updateStats() {
            if (ppbValues.length === 0) return;

            const sum = ppbValues.reduce((a, b) => a + b, 0);
            const average = (sum / ppbValues.length).toFixed(2); // Moyenne arrondie à 2 décimales
            const min = Math.min(...ppbValues);
            const max = Math.max(...ppbValues);

            // Mettre à jour les éléments HTML
            document.querySelector('.stats div:nth-child(1)').textContent = `Moyenne : ${average}`;
            document.querySelector('.stats div:nth-child(2)').textContent = `Minimum : ${min}`;
            document.querySelector('.stats div:nth-child(3)').textContent = `Maximum : ${max}`;
        }

        function toggleButtonState(button, enable) {
            if (enable) {
                button.disabled = false;
                button.classList.remove('disabled');
                button.classList.add('active');
            } else {
                button.disabled = true;
                button.classList.remove('active');
                button.classList.add('disabled');
            }
        }

        


    </script>

    <!-- Fenêtre modale -->
    <div id="patientModal" class="modal">
        <div class="modal-content">
            <h2>Choisissez un patient</h2>
            <ul id="patientList"></ul>
        </div>
    </div>

    <style>
    /* Style pour la fenêtre modale */
        .modal {
            display: flex;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            text-align: center;
        }

        .modal-content ul {
            list-style: none;
            padding: 0;
        }

        .modal-content li {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ccc;
        }

        .modal-content li:hover {
            background-color: #0073E6;
            color: white;
        }
    </style>

</body>
</html>
