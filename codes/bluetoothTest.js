const connectBtn = document.getElementById('connect-btn');
const sendCommandBtn = document.getElementById('send-command-btn');
const logOutput = document.getElementById('log-output');

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
          text: 'Valeurs'
        }
      }
    }
  }
});

function log(message) {
  logOutput.textContent += `${message}\n`;
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

    // Accéder au service Nordic UART
    const service = await server.getPrimaryService('6e400001-b5a3-f393-e0a9-e50e24dcca9e');
    log('Nordic UART service found.');

    // Récupérer les caractéristiques TX et RX
    txCharacteristic = await service.getCharacteristic('6e400002-b5a3-f393-e0a9-e50e24dcca9e'); // TX UUID
    rxCharacteristic = await service.getCharacteristic('6e400003-b5a3-f393-e0a9-e50e24dcca9e'); // RX UUID
    log('TX and RX characteristics found.');

    // Activer les notifications pour RX
    rxCharacteristic.addEventListener('characteristicvaluechanged', (event) => {
      const decoder = new TextDecoder();
      const receivedData = decoder.decode(event.target.value);
      log(`Data received: ${receivedData}`);

      // Convertir en flottant et mettre à jour le graphique
      const floatData = parseFloat(receivedData);
      if (!isNaN(floatData)) {
        updateChart(floatData/1000); //On divise par 1000 car la valeur recupérée est en PPB(partie par billion ) or on veut du PPM
      } else {
        log('Error: Received data is not a valid float.');
      }
    });

    await rxCharacteristic.startNotifications();
    log('Notifications started for RX characteristic.');

    // Activer le bouton d'envoi de commande
    sendCommandBtn.disabled = false;
  } catch (error) {
    log(`Error: ${error.message}`);
  }
});

// Fonction pour mettre à jour le graphique
function updateChart(value) {
  const now = new Date().toLocaleTimeString(); // Timestamp pour l'axe des X
  dataChart.data.labels.push(now);
  dataChart.data.datasets[0].data.push(value);

  // Garde uniquement les 50 dernières données pour éviter de saturer le graphique
  if (dataChart.data.labels.length > 50) {
    dataChart.data.labels.shift();
    dataChart.data.datasets[0].data.shift();
  }

  dataChart.update(); // Mettre à jour le graphique
}

// Envoyer une commande
sendCommandBtn.addEventListener('click', async () => {
  try {
    if (!txCharacteristic) {
      log('TX characteristic not found. Connect to the device first.');
      return;
    }

    const encoder = new TextEncoder();
    const command = encoder.encode('Hello Feather'); // Exemple de commande à envoyer
    await txCharacteristic.writeValue(command);
    log('Command sent successfully: Hello Feather');
  } catch (error) {
    log(`Error sending command: ${error.message}`);
  }
});
