// --- CONFIGURATION ---

// CRITICAL: Replace with the exact IP printed by your ESP32 Serial Monitor
const ESP32_IP = "http://192.168.68.250";
const READINGS_URL = ESP32_IP + '/reading              s';
const FETCH_INTERVAL_MS = 5000; // Fetch data every 5 seconds

// --- GAUGE UTILITIES ---

/**
 * Maps a value from one range to a rotation angle in another range.
 * @param {number} value - The sensor reading.
 * @param {number} inMin - Minimum sensor value (e.g., 0 for pH).
 * @param {number} inMax - Maximum sensor value (e.g., 14 for pH).
 * @param {number} outMin - Minimum rotation angle (e.g., -120 degrees).
 * @param {number} outMax - Maximum rotation angle (e.g., +120 degrees).
 * @returns {number} The calculated rotation angle in degrees.
 */
function mapValueToRotation(value, inMin, inMax, outMin, outMax) {
    const clampedValue = Math.max(inMin, Math.min(inMax, value));
    return (clampedValue - inMin) * (outMax - outMin) / (inMax - inMin) + outMin;
}

/**                                                                          
 * Updates a single sensor gauge with new data.
 * @param {string} idPrefix - The prefix ID of the gauge ('ph', 'tds', etc.).
 * @param {string|number} value - The sensor reading value.
 * @param {string} status - The status text ('Safe', 'Warning', 'Failed', etc.).
 */
function updateGauge(idPrefix, value, status) {
    const valueEl = document.getElementById(idPrefix + 'ValueDisplay');
    const statusEl = document.getElementById(idPrefix + 'StatusDisplay');
    const indicatorEl = document.getElementById(idPrefix + 'Indicator');

    if (valueEl) {
        // Display value with 2 decimal places for numeric sensors
        if (!isNaN(parseFloat(value)) && idPrefix !== 'color') {
            valueEl.textContent = parseFloat(value).toFixed(2);
        } else {
            // For Color (which is text) or error states
            valueEl.textContent = value;
        }
    }

    if (statusEl) {
        statusEl.textContent = status;
        // Update the status background color
        statusEl.className = 'gauge-status ' + status.toLowerCase().replace(' ', ''); // e.g., 'Safe' -> 'safe'
    }

    // --- Needle Rotation Logic ---
    let rotationDegrees = 0;
    const numericValue = parseFloat(value);

    // Check if the value is a valid number for rotation
    if (indicatorEl && !isNaN(numericValue) && idPrefix !== 'color') {

        // Define the target range for rotation (e.g., -120 degrees to +120 degrees = 240 degree arc)
        const ROT_MIN = -120;
        const ROT_MAX = 120;

        switch (idPrefix) {
            case 'ph':
                // pH Range: 0 to 14
                rotationDegrees = mapValueToRotation(numericValue, 0, 14, ROT_MIN, ROT_MAX);
                break;
            case 'tds':
                // TDS Range: 0 to 1000 mg/L (Adjust MAX to your expected limit)
                rotationDegrees = mapValueToRotation(numericValue, 0, 1000, ROT_MIN, ROT_MAX);
                break;
            case 'turbidity':
                // Turbidity Range: 0 to 50 NTU (Adjust MAX to your expected limit)
                rotationDegrees = mapValueToRotation(numericValue, 0, 50, ROT_MIN, ROT_MAX);
                break;
            case 'lead':
                // Lead Range: 0 to 0.02 mg/L (Adjust MAX to your expected limit)
                rotationDegrees = mapValueToRotation(numericValue, 0, 0.02, ROT_MIN, ROT_MAX);
                break;
            default:
                rotationDegrees = 0; // Default or error state
        }

    } else if (idPrefix === 'color') {
        // Color is categorical. Map status to a fixed angle if a needle is desired.
        switch (status.toLowerCase()) {
            case 'safe':
                rotationDegrees = 0; // Center
                break;
            case 'warning':
                rotationDegrees = 60;
                break;
            case 'failed':
                rotationDegrees = 100;
                break;
            default:
                rotationDegrees = -60;
        }
    }

    // Apply the rotation to the indicator needle
    if (indicatorEl) {
        indicatorEl.style.transform = rotate(${ rotationDegrees }deg);
    }
}

function updateGlobalStatus(message) {
    const updateEl = document.getElementById('last-update-display');
    if (updateEl) {
        updateEl.textContent = message;
    }
}


// --- DATA FETCHING ---

function fetchReadings() {
    // Set global status to indicate connection attempt
    const overallStatusEl = document.getElementById('overall-connection-status');
    overallStatusEl.textContent = "CONNECTING...";
    overallStatusEl.className = 'status-indicator connecting';

    // Attempt to fetch data from ESP32
    fetch(READINGS_URL)
        .then(response => {
            // Check for the 503 error (ESP32 is connected but waiting for first cycle)
            if (response.status === 503) {
                updateGlobalStatus("Service Unavailable (503): Waiting for ESP32 cycle to complete.");
                // Set all gauges to connecting state
                updateGauge('tds', '---', 'Connecting');
                updateGauge('ph', '---', 'Connecting');
                updateGauge('turbidity', '---', 'Connecting');
                updateGauge('lead', '---', 'Connecting');
                updateGauge('color', '---', 'Connecting');
                return Promise.reject("ESP32 Not Ready");
            }
            if (!response.ok) {
                updateGlobalStatus(Network Error: ${ response.status } ${ response.statusText });
                return Promise.reject(Network error: ${ response.status });
            }
            return response.json();
        })
        .then(data => {
            // --- Success: Update all gauges with new data ---

            updateGauge('tds', data.TDS_Value, data.TDS_Status);
            updateGauge('ph', data.PH_Value, data.PH_Status);
            updateGauge('turbidity', data.Turbidity_Value, data.Turbidity_Status);
            updateGauge('lead', data.Lead_Value, data.Lead_Status);
            updateGauge('color', data.Color_Result, data.Color_Status);

            // Update global status display
            updateGlobalStatus(Live Data - Last Update: ${ new Date().toLocaleTimeString() });

            // Set global connection status to ONLINE
            overallStatusEl.textContent = "ONLINE";
            overallStatusEl.className = 'status-indicator online';
        })
        .catch(error => {
            console.error('Fetch Error:', error);

            // Set global connection status to OFFLINE
            overallStatusEl.textContent = "OFFLINE";
            overallStatusEl.className = 'status-indicator offline';

            // Set all gauges to error state
            updateGauge('tds', '---', 'Failed');
            updateGauge('ph', '---', 'Failed');
            updateGauge('turbidity', '---', 'Failed');
            updateGauge('lead', '---', 'Failed');
            updateGauge('color', '---', 'Failed');
        });
}

// --- INITIALIZATION ---

document.addEventListener('DOMContentLoaded', () => {
    // Start fetching data immediately on load
    fetchReadings();

    // Set up interval to fetch data repeatedly
    setInterval(fetchReadings, FETCH_INTERVAL_MS);
});