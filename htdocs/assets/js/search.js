// Search page JavaScript
let isSwapped = false;
const collegeLocation = "Ramco Institute of Technology";

// Initialize search page
document.addEventListener('DOMContentLoaded', () => {
    initializeEventListeners();
    loadOrigins();
});

// Initialize event listeners
function initializeEventListeners() {
    const switchBtn = document.getElementById('switchBtn');
    const searchBtn = document.getElementById('searchBtn');
    const originSelect = document.getElementById('originSelect');
    
    if (switchBtn) {
        switchBtn.addEventListener('click', toggleLocations);
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', searchBuses);
    }
    
    if (originSelect) {
        originSelect.addEventListener('change', () => {
            if (!isSwapped) {
                updateFromLocation(originSelect.value);
            }
        });
    }
}

// Load origins from database
async function loadOrigins() {
    try {
        const response = await fetch('api/get_origins.php');
        const data = await response.json();
        
        const originSelect = document.getElementById('originSelect');
        if (originSelect && data.origins) {
            originSelect.innerHTML = '<option value="">Select departure location...</option>' +
                data.origins.map(origin => `<option value="${origin}">${origin}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading origins:', error);
    }
}

// Toggle between from/to locations
function toggleLocations() {
    isSwapped = !isSwapped;
    
    const fromBox = document.getElementById('fromBox');
    const toBox = document.getElementById('toBox');
    const fromLocation = document.getElementById('fromLocation');
    const toLocation = document.getElementById('toLocation');
    const originSelect = document.getElementById('originSelect');
    const searchBtn = document.getElementById('searchBtn');
    
    if (isSwapped) {
        // Swapped mode: College is FROM, other is TO
        fromLocation.innerHTML = collegeLocation;
        toLocation.innerHTML = '<span style="color:#999">Select location</span>';
        fromBox.classList.add('from');
        toBox.classList.remove('to');
        toBox.classList.add('to');
        originSelect.style.display = 'none';
        searchBtn.textContent = '🔍 Search Buses from College';
    } else {
        // Normal mode: Other is FROM, College is TO
        toLocation.innerHTML = collegeLocation;
        fromLocation.innerHTML = '<span style="color:#999">Select location</span>';
        fromBox.classList.remove('from');
        toBox.classList.add('to');
        originSelect.style.display = 'block';
        searchBtn.textContent = '🔍 Search Available Buses';
    }
    
    // Clear previous results
    clearResults();
}

// Update from location display
function updateFromLocation(location) {
    const fromLocation = document.getElementById('fromLocation');
    if (fromLocation && location) {
        fromLocation.innerHTML = location;
    }
}

// Search for buses
async function searchBuses() {
    let from, to;
    
    if (isSwapped) {
        from = collegeLocation;
        const originSelect = document.getElementById('originSelect');
        to = originSelect.options[originSelect.selectedIndex]?.text;
        
        if (!to || to === 'Select departure location...') {
            showError('Please select a destination location');
            return;
        }
    } else {
        const originSelect = document.getElementById('originSelect');
        from = originSelect.value;
        to = collegeLocation;
        
        if (!from) {
            showError('Please select a departure location');
            return;
        }
    }
    
    showLoading();
    
    try {
        const response = await fetch(`api/get_buses_by_route.php?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
        const data = await response.json();
        
        displayResults(data.buses, from, to);
    } catch (error) {
        console.error('Error searching buses:', error);
        showError('Failed to search buses. Please try again.');
    } finally {
        hideLoading();
    }
}

// Display search results
function displayResults(buses, from, to) {
    const resultsDiv = document.getElementById('busList');
    const resultsContainer = document.getElementById('results');
    
    if (!resultsDiv) return;
    
    if (buses && buses.length > 0) {
        resultsDiv.innerHTML = `
            <div style="margin-bottom: 1rem; padding: 1rem; background: #e3f2fd; border-radius: 8px;">
                <strong>Route:</strong> ${from} → ${to}
            </div>
            ${buses.map(bus => `
                <div class="bus-result">
                    <div>
                        <div class="bus-number">Bus ${bus.bus_number}</div>
                        <div style="color:#666; font-size: 0.9rem; margin-top: 0.5rem;">
                            ${bus.route_name || 'Regular Route'}
                        </div>
                    </div>
                    <div style="text-align: right">
                        <div style="color:#4CAF50; font-weight: bold">✓ Available</div>
                        <div style="font-size: 0.8rem; color:#999; margin-top: 0.5rem;">
                            ${bus.departure_time || 'On Schedule'}
                        </div>
                        <button class="btn-secondary" style="margin-top: 0.5rem; padding: 0.3rem 0.8rem; font-size: 0.8rem;" 
                                onclick="trackBus('${bus.bus_number}')">
                            Track Live
                        </button>
                    </div>
                </div>
            `).join('')}
        `;
        resultsContainer.style.display = 'block';
    } else {
        resultsDiv.innerHTML = `
            <div class="no-results">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🚌❌</div>
                <h3>No buses available on this route</h3>
                <p>Try selecting a different location or check back later.</p>
            </div>
        `;
    }
}

// Track specific bus
window.trackBus = function(busNumber) {
    window.location.href = `home.php?highlight=${busNumber}`;
};

// Clear search results
function clearResults() {
    const resultsDiv = document.getElementById('busList');
    if (resultsDiv) {
        resultsDiv.innerHTML = '<div class="spinner"></div>';
    }
}

// Show loading indicator
function showLoading() {
    const resultsDiv = document.getElementById('busList');
    if (resultsDiv) {
        resultsDiv.innerHTML = '<div class="spinner"></div>';
    }
}

// Hide loading indicator
function hideLoading() {
    // Loading will be replaced by results
}

// Show error message
function showError(message) {
    const resultsDiv = document.getElementById('busList');
    if (resultsDiv) {
        resultsDiv.innerHTML = `
            <div class="alert alert-error">
                ${message}
            </div>
        `;
    }
}