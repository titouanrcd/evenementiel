// NOVA ÉVÉNEMENTS - API JavaScript

// API Configuration
const API_CONFIG = {
    BASE_URL: 'https://api.openagenda.com/v2/agendas/11074358/events',
    API_KEY: 'a50726ecf42f4c59be4a5867a08231e8',
    get URL() {
        return `${this.BASE_URL}?key=${this.API_KEY}`;
    }
};

// DOM Elements
let eventsContainer, loadingSpinner, filtersForm;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeElements();
    loadEvents();
    setupFilters();
});

// Initialize DOM elements
function initializeElements() {
    eventsContainer = document.getElementById('eventsContainer');
    loadingSpinner = document.getElementById('loadingSpinner');
    filtersForm = document.getElementById('filtersForm');
}

// Store all events for client-side filtering
let allEvents = [];

// Load events from API
async function loadEvents(applyFilters = true) {
    try {
        showLoading(true);
        
        const response = await fetch(API_CONFIG.URL);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        allEvents = data.events || [];
        
        showLoading(false);
        
        if (applyFilters) {
            applyClientSideFilters();
        } else {
            displayEvents(allEvents);
        }
        
    } catch (error) {
        console.error('Erreur lors du chargement des événements:', error);
        showLoading(false);
        showError('Erreur lors du chargement des événements. Veuillez réessayer.');
    }
}

// Apply filters on client side
function applyClientSideFilters() {
    const filters = getCurrentFilters();
    let filteredEvents = [...allEvents];
    
    // Filter by search term
    if (filters.search) {
        const searchTerm = normalizeText(filters.search);
        filteredEvents = filteredEvents.filter(event => {
            const title = normalizeText(getEventTitle(event));
            const description = normalizeText(event.description?.fr || event.description || '');
            const location = normalizeText(getEventLocation(event));
            
            return title.includes(searchTerm) || 
                   description.includes(searchTerm) || 
                   location.includes(searchTerm);
        });
    }
    
    // Filter by location
    if (filters.location) {
        const locationTerm = normalizeText(filters.location);
        filteredEvents = filteredEvents.filter(event => {
            const eventLocation = normalizeText(getEventLocation(event));
            return eventLocation.includes(locationTerm);
        });
    }
    
    // Filter by date range
    if (filters.dateFrom || filters.dateTo) {
        filteredEvents = filteredEvents.filter(event => {
            const eventDates = getEventDateRange(event);
            if (!eventDates.start) return false;
            
            const eventStart = new Date(eventDates.start);
            const eventEnd = eventDates.end ? new Date(eventDates.end) : eventStart;
            
            if (filters.dateFrom) {
                const filterStart = new Date(filters.dateFrom);
                if (eventEnd < filterStart) return false;
            }
            
            if (filters.dateTo) {
                const filterEnd = new Date(filters.dateTo);
                if (eventStart > filterEnd) return false;
            }
            
            return true;
        });
    }
    
    // Filter by categories
    if (filters.categories && filters.categories.length > 0) {
        filteredEvents = filteredEvents.filter(event => {
            const eventCategories = getEventCategories(event);
            return filters.categories.some(filterCat => 
                eventCategories.some(eventCat => 
                    normalizeText(eventCat).includes(normalizeText(filterCat))
                )
            );
        });
    }
    
    // Filter by price range
    if (filters.maxPrice !== undefined) {
        filteredEvents = filteredEvents.filter(event => {
            const eventPrice = getEventPriceNumber(event);
            return eventPrice <= filters.maxPrice;
        });
    }
    
    displayEvents(filteredEvents);
}

// Display events in the grid
function displayEvents(events) {
    if (!eventsContainer) return;
    
    if (events.length === 0) {
        eventsContainer.innerHTML = '<div class="no-events"><p>Aucun événement trouvé pour ces critères.</p></div>';
        return;
    }

    eventsContainer.innerHTML = events.map((event, index) => {
        const eventImage = getEventImage(event);
        const eventTitle = getEventTitle(event);
        const eventLocation = getEventLocation(event);
        const eventPrice = getEventPrice(event);
        const eventDate = getEventDate(event);
        const featuredClass = index === 0 ? 'featured' : '';
        
        return `
            <article class="event-card ${featuredClass}">
                <div class="event-image">
                    <img src="${eventImage}" alt="${eventTitle}" onerror="handleImageError(this)">
                    ${event.conditions?.free ? '<div class="event-status">Gratuit</div>' : ''}
                    ${eventDate ? `<div class="event-date-badge">${eventDate}</div>` : ''}
                </div>
                <div class="event-info">
                    <h3 class="event-title">${eventTitle}</h3>
                    <p class="event-location">${eventLocation}</p>
                    ${event.description ? `<p class="event-description">${truncateText(event.description.fr || event.description, 100)}</p>` : ''}
                    <div class="event-details">
                        <span class="event-price">${eventPrice}</span>
                        <button class="event-btn" onclick="openEventDetails('${event.url || '#'}')">Voir détails</button>
                    </div>
                </div>
            </article>
        `;
    }).join('');
}

// Helper functions for event data
function getEventImage(event) {
    if (event.image) {
        return event.image.base || event.image.url || event.image;
    }
    return 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';
}

function getEventTitle(event) {
    if (typeof event.title === 'object') {
        return event.title.fr || event.title.en || Object.values(event.title)[0] || 'Événement sans titre';
    }
    return event.title || 'Événement sans titre';
}

function getEventLocation(event) {
    if (event.location) {
        if (typeof event.location === 'string') return event.location;
        return event.location.name || event.location.address || event.location.city || 'Lieu non spécifié';
    }
    return 'Lieu non spécifié';
}

function getEventPrice(event) {
    if (event.conditions?.free) {
        return 'Gratuit';
    }
    if (event.conditions?.pricing) {
        if (typeof event.conditions.pricing === 'object') {
            return event.conditions.pricing.fr || event.conditions.pricing.en || 'Prix sur demande';
        }
        return event.conditions.pricing;
    }
    return 'Prix non spécifié';
}

function getEventDate(event) {
    if (event.timings && event.timings.length > 0) {
        const firstTiming = event.timings[0];
        if (firstTiming.begin) {
            const date = new Date(firstTiming.begin);
            return date.toLocaleDateString('fr-FR', { 
                day: 'numeric', 
                month: 'short' 
            });
        }
    }
    return null;
}

function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Normalize text for better search matching
function normalizeText(text) {
    if (!text) return '';
    return text.toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // Remove accents
        .replace(/[^\w\s]/g, ' ') // Replace special chars with spaces
        .replace(/\s+/g, ' ') // Replace multiple spaces with single space
        .trim();
}

// Get event categories
function getEventCategories(event) {
    const categories = [];
    
    // Add keywords as categories
    if (event.keywords) {
        if (Array.isArray(event.keywords)) {
            categories.push(...event.keywords);
        } else if (typeof event.keywords === 'object') {
            Object.values(event.keywords).forEach(keywordArray => {
                if (Array.isArray(keywordArray)) {
                    categories.push(...keywordArray);
                }
            });
        }
    }
    
    // Add category from event data
    if (event.category) {
        categories.push(event.category);
    }
    
    // Infer categories from title and description
    const title = getEventTitle(event).toLowerCase();
    const description = (event.description?.fr || event.description || '').toLowerCase();
    const combined = title + ' ' + description;
    
    // Category mapping
    const categoryMap = {
        'concert': ['concert', 'musique', 'spectacle', 'festival', 'live', 'show'],
        'conference': ['conference', 'seminaire', 'formation', 'workshop', 'atelier', 'colloque'],
        'festival': ['festival', 'fete', 'celebration', 'carnaval'],
        'exposition': ['exposition', 'expo', 'galerie', 'musee', 'art', 'vernissage'],
        'sport': ['sport', 'match', 'competition', 'tournoi', 'course', 'marathon', 'fitness']
    };
    
    Object.entries(categoryMap).forEach(([category, keywords]) => {
        if (keywords.some(keyword => combined.includes(keyword))) {
            categories.push(category);
        }
    });
    
    return categories;
}

// Get event date range
function getEventDateRange(event) {
    if (event.timings && event.timings.length > 0) {
        const sortedTimings = event.timings.sort((a, b) => new Date(a.begin) - new Date(b.begin));
        return {
            start: sortedTimings[0].begin,
            end: sortedTimings[sortedTimings.length - 1].end || sortedTimings[sortedTimings.length - 1].begin
        };
    }
    return { start: null, end: null };
}

// Get event price as number for filtering
function getEventPriceNumber(event) {
    if (event.conditions?.free) {
        return 0;
    }
    
    if (event.conditions?.pricing) {
        const pricing = typeof event.conditions.pricing === 'object' 
            ? event.conditions.pricing.fr || event.conditions.pricing.en || ''
            : event.conditions.pricing;
        
        // Extract number from pricing string
        const priceMatch = pricing.match(/(\d+(?:[.,]\d+)?)/);
        if (priceMatch) {
            return parseFloat(priceMatch[1].replace(',', '.'));
        }
    }
    
    // Default to high value if price not specified (will be filtered out by max price)
    return 999999;
}

// Setup filters functionality
function setupFilters() {
    if (!filtersForm) return;
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });
    }
    
    // Location filter
    const locationSelect = document.getElementById('locationSelect');
    if (locationSelect) {
        locationSelect.addEventListener('change', applyFilters);
    }
    
    // Date filters
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    
    if (dateFromInput) {
        dateFromInput.addEventListener('change', applyFilters);
    }
    if (dateToInput) {
        dateToInput.addEventListener('change', applyFilters);
    }
    
    // Price range slider
    const priceSlider = document.getElementById('priceRange');
    const priceDisplay = document.getElementById('priceDisplay');
    if (priceSlider) {
        priceSlider.addEventListener('input', function() {
            if (priceDisplay) {
                const value = parseInt(priceSlider.value);
                priceDisplay.textContent = value === 200 ? '200€+' : value + '€';
            }
        });
        
        priceSlider.addEventListener('change', applyFilters);
    }
    
    // Category checkboxes
    const categoryCheckboxes = document.querySelectorAll('input[name="category"]');
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
    
    // Reset button
    const resetButton = document.getElementById('resetFilters');
    if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
    }
}

// Apply current filters
function applyFilters() {
    if (allEvents.length === 0) {
        // If events not loaded yet, load them first
        loadEvents(true);
    } else {
        // Apply filters on already loaded events
        applyClientSideFilters();
    }
}

// Get current filter values
function getCurrentFilters() {
    const filters = {};
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value.trim()) {
        filters.search = searchInput.value.trim();
    }
    
    const locationSelect = document.getElementById('locationSelect');
    if (locationSelect && locationSelect.value) {
        filters.location = locationSelect.value;
    }
    
    const dateFromInput = document.getElementById('dateFrom');
    if (dateFromInput && dateFromInput.value) {
        filters.dateFrom = dateFromInput.value;
    }
    
    const dateToInput = document.getElementById('dateTo');
    if (dateToInput && dateToInput.value) {
        filters.dateTo = dateToInput.value;
    }
    
    // Get price range
    const priceSlider = document.getElementById('priceRange');
    if (priceSlider && priceSlider.value) {
        filters.maxPrice = parseFloat(priceSlider.value);
    }
    
    // Get selected categories
    const selectedCategories = [];
    const categoryCheckboxes = document.querySelectorAll('input[name="category"]:checked');
    categoryCheckboxes.forEach(checkbox => {
        selectedCategories.push(checkbox.value);
    });
    
    if (selectedCategories.length > 0) {
        filters.categories = selectedCategories;
    }
    
    return filters;
}

// Reset all filters
function resetFilters() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    
    const locationSelect = document.getElementById('locationSelect');
    if (locationSelect) locationSelect.value = '';
    
    const dateFromInput = document.getElementById('dateFrom');
    if (dateFromInput) dateFromInput.value = '';
    
    const dateToInput = document.getElementById('dateTo');
    if (dateToInput) dateToInput.value = '';
    
    const priceSlider = document.getElementById('priceRange');
    const priceDisplay = document.getElementById('priceDisplay');
    if (priceSlider) {
        priceSlider.value = 200;
        if (priceDisplay) priceDisplay.textContent = '200€+';
    }
    
    const categoryCheckboxes = document.querySelectorAll('input[name="category"]');
    categoryCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Display all events without filters
    displayEvents(allEvents);
}

// Utility functions
function showLoading(show) {
    if (!loadingSpinner) return;
    loadingSpinner.style.display = show ? 'block' : 'none';
}

function showError(message) {
    if (!eventsContainer) return;
    eventsContainer.innerHTML = `<div class="error-message"><p>${message}</p></div>`;
}

function handleImageError(img) {
    img.src = 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';
}

function openEventDetails(url) {
    if (url && url !== '#') {
        window.open(url, '_blank');
    }
}

// Export functions for global use
window.NOVA_EVENTS = {
    loadEvents,
    applyFilters,
    resetFilters,
    openEventDetails,
    handleImageError
};
