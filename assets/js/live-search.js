// Live Search Functionality for TemanKosan
class LiveSearch {
    constructor(searchInput, resultsContainer, options = {}) {
        this.searchInput = document.querySelector(searchInput);
        this.resultsContainer = document.querySelector(resultsContainer);
        this.options = {
            minLength: 2,
            debounceTime: 300,
            maxResults: 10,
            apiUrl: 'api/live-search.php',
            ...options
        };
        
        this.debounceTimer = null;
        this.isSearching = false;
        this.currentRequest = null;
        
        this.init();
    }
    
    init() {
        if (!this.searchInput || !this.resultsContainer) {
            console.error('Live search: Required elements not found');
            return;
        }
        
        this.createSearchStructure();
        this.bindEvents();
    }
    
    createSearchStructure() {
        // Wrap search input if not already wrapped
        if (!this.searchInput.parentElement.classList.contains('live-search-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'live-search-wrapper';
            this.searchInput.parentElement.insertBefore(wrapper, this.searchInput);
            wrapper.appendChild(this.searchInput);
            
            // Add search icon
            const searchIcon = document.createElement('i');
            searchIcon.className = 'search-icon';
            searchIcon.innerHTML = '🔍';
            wrapper.appendChild(searchIcon);
            
            // Add loading spinner
            const spinner = document.createElement('div');
            spinner.className = 'search-spinner';
            spinner.innerHTML = '⏳';
            wrapper.appendChild(spinner);
        }
        
        // Ensure results container exists
        if (!this.resultsContainer) {
            this.resultsContainer = document.createElement('div');
            this.resultsContainer.className = 'live-search-results';
            this.searchInput.parentElement.appendChild(this.resultsContainer);
        }
        
        this.resultsContainer.style.display = 'none';
    }
    
    bindEvents() {
        // Search input events
        this.searchInput.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });
        
        this.searchInput.addEventListener('focus', () => {
            if (this.searchInput.value.length >= this.options.minLength) {
                this.showResults();
            }
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.searchInput.parentElement.contains(e.target)) {
                this.hideResults();
            }
        });
        
        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });
    }
    
    handleInput(query) {
        clearTimeout(this.debounceTimer);
        
        if (query.length < this.options.minLength) {
            this.hideResults();
            return;
        }
        
        this.debounceTimer = setTimeout(() => {
            this.performSearch(query);
        }, this.options.debounceTime);
    }
    
    async performSearch(query) {
        if (this.isSearching) {
            if (this.currentRequest) {
                this.currentRequest.abort();
            }
        }
        
        this.setSearching(true);
        
        try {
            const controller = new AbortController();
            this.currentRequest = controller;
            
            const url = new URL(this.options.apiUrl, window.location.origin);
            url.searchParams.append('q', query);
            url.searchParams.append('limit', this.options.maxResults);
            
            const response = await fetch(url, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.displayResults(data.data, query);
            } else {
                this.displayError(data.message || 'Search failed');
            }
            
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                this.displayError('Search failed. Please try again.');
            }
        } finally {
            this.setSearching(false);
            this.currentRequest = null;
        }
    }
    
    displayResults(results, query) {
        this.resultsContainer.innerHTML = '';
        
        if (results.length === 0) {
            this.displayNoResults(query);
            return;
        }
        
        // Create results header
        const header = document.createElement('div');
        header.className = 'search-results-header';
        header.innerHTML = `
            <span class="results-count">Ditemukan ${results.length} hasil</span>
            <button class="view-all-btn" onclick="this.closest('.live-search-wrapper').querySelector('form').submit()">
                Lihat Semua
            </button>
        `;
        this.resultsContainer.appendChild(header);
        
        // Create results list
        const resultsList = document.createElement('div');
        resultsList.className = 'search-results-list';
        
        results.forEach((item, index) => {
            const resultItem = this.createResultItem(item, index);
            resultsList.appendChild(resultItem);
        });
        
        this.resultsContainer.appendChild(resultsList);
        this.showResults();
    }
    
    createResultItem(item, index) {
        const resultItem = document.createElement('div');
        resultItem.className = 'search-result-item';
        resultItem.setAttribute('data-index', index);
        
        resultItem.innerHTML = `
            <div class="result-image">
                <img src="${item.image}" alt="${item.name}" loading="lazy">
                <div class="result-badge">${item.type}</div>
            </div>
            <div class="result-content">
                <h4 class="result-title">${this.highlightText(item.name)}</h4>
                <div class="result-location">📍 ${item.location}</div>
                <div class="result-price">${item.formatted_price}/bulan</div>
                <div class="result-rating">
                    <span class="stars">⭐</span>
                    <span>${item.rating}</span>
                    <span>(${item.review_count} ulasan)</span>
                </div>
            </div>
            <div class="result-actions">
                <button class="btn-view" onclick="window.location.href='kos-detail.php?id=${item.id}'">
                    Lihat Detail
                </button>
            </div>
        `;
        
        // Add click handler for the entire item
        resultItem.addEventListener('click', (e) => {
            if (!e.target.closest('.result-actions')) {
                window.location.href = `kos-detail.php?id=${item.id}`;
            }
        });
        
        return resultItem;
    }
    
    displayNoResults(query) {
        this.resultsContainer.innerHTML = `
            <div class="search-no-results">
                <div class="no-results-icon">🏠</div>
                <h4>Tidak ada hasil untuk "${query}"</h4>
                <p>Coba gunakan kata kunci lain atau periksa ejaan Anda</p>
            </div>
        `;
        this.showResults();
    }
    
    displayError(message) {
        this.resultsContainer.innerHTML = `
            <div class="search-error">
                <div class="error-icon">⚠️</div>
                <p>${message}</p>
            </div>
        `;
        this.showResults();
    }
    
    highlightText(text) {
        if (!this.searchInput.value) return text;
        
        const query = this.searchInput.value.trim();
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
    
    showResults() {
        this.resultsContainer.style.display = 'block';
        setTimeout(() => {
            this.resultsContainer.classList.add('show');
        }, 10);
    }
    
    hideResults() {
        this.resultsContainer.classList.remove('show');
        setTimeout(() => {
            this.resultsContainer.style.display = 'none';
        }, 200);
    }
    
    setSearching(isSearching) {
        this.isSearching = isSearching;
        const wrapper = this.searchInput.parentElement;
        const spinner = wrapper.querySelector('.search-spinner');
        const icon = wrapper.querySelector('.search-icon');
        
        if (isSearching) {
            wrapper.classList.add('searching');
            if (spinner) spinner.style.display = 'block';
            if (icon) icon.style.display = 'none';
        } else {
            wrapper.classList.remove('searching');
            if (spinner) spinner.style.display = 'none';
            if (icon) icon.style.display = 'block';
        }
    }
    
    handleKeyNavigation(e) {
        const items = this.resultsContainer.querySelectorAll('.search-result-item');
        if (items.length === 0) return;
        
        const currentActive = this.resultsContainer.querySelector('.search-result-item.active');
        let newIndex = -1;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (currentActive) {
                    newIndex = parseInt(currentActive.getAttribute('data-index')) + 1;
                } else {
                    newIndex = 0;
                }
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                if (currentActive) {
                    newIndex = parseInt(currentActive.getAttribute('data-index')) - 1;
                } else {
                    newIndex = items.length - 1;
                }
                break;
                
            case 'Enter':
                e.preventDefault();
                if (currentActive) {
                    currentActive.click();
                }
                return;
                
            case 'Escape':
                this.hideResults();
                this.searchInput.blur();
                return;
        }
        
        // Update active item
        items.forEach(item => item.classList.remove('active'));
        
        if (newIndex >= 0 && newIndex < items.length) {
            items[newIndex].classList.add('active');
            items[newIndex].scrollIntoView({ block: 'nearest' });
        }
    }
}

// Auto-initialize live search when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize live search for main search input
    const mainSearchInput = document.querySelector('input[name="location"]');
    if (mainSearchInput) {
        new LiveSearch('input[name="location"]', '#liveSearchResults');
    }
    
    // Initialize for any other search inputs with data-live-search attribute
    document.querySelectorAll('input[data-live-search]').forEach(input => {
        const resultsId = input.getAttribute('data-live-search');
        new LiveSearch(input, resultsId);
    });
});

// Export for manual initialization
window.LiveSearch = LiveSearch;