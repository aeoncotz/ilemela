const BANNER = document.getElementById('cookie-banner');
const MODAL = document.getElementById('cookie-modal-overlay');
const ANALYTICS_BOX = document.getElementById('analytics-check');

// --- ON LOAD: Check if consent exists ---
window.onload = function() {
    const consent = localStorage.getItem('user_cookie_consent');
    if (!consent) {
        BANNER.classList.remove('hidden'); // Show banner for first-time users
    } else {
        const data = JSON.parse(consent);
        applyScripts(data.analytics);
    }
};

// --- TOGGLE MODAL ---
function toggleModal(show) {
    if (show) {
        MODAL.classList.remove('hidden');
        // Sync checkbox with current saved state
        const consent = JSON.parse(localStorage.getItem('user_cookie_consent'));
        if (consent) ANALYTICS_BOX.checked = consent.analytics;
    } else {
        MODAL.classList.add('hidden');
    }
}

// --- SAVE ACTIONS ---
function saveConsent(all) {
    const preferences = {
        analytics: all ? true : false,
        date: new Date().toISOString()
    };
    localStorage.setItem('user_cookie_consent', JSON.stringify(preferences));
    BANNER.classList.add('hidden');
    location.reload(); // Reload to activate/deactivate scripts
}

function saveFromModal() {
    const preferences = {
        analytics: ANALYTICS_BOX.checked,
        date: new Date().toISOString()
    };
    localStorage.setItem('user_cookie_consent', JSON.stringify(preferences));
    toggleModal(false);
    BANNER.classList.add('hidden');
    location.reload();
}

// --- SCRIPT CONTROLLER ---
function applyScripts(allowAnalytics) {
    if (allowAnalytics) {
        console.log("Cookie Logic: Loading Google Analytics...");
        /* PASTE YOUR GOOGLE ANALYTICS CODE HERE 
        */
    } else {
        console.log("Cookie Logic: Analytics Blocked.");
    }
}