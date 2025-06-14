<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use Illuminate\Contracts\View\View;

trait FooterScript
{
    public function getFooter(): View
    {
        return view('filament.resources.office-resource.pages.map-search-footer');
    }

    private function mapSearchScript(): string
    {
        return <<<'HTML'
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for map to be fully initialized
            setTimeout(function() {
                initializeMapSearch();
            }, 1000);
        });

        function initializeMapSearch() {
            const mapContainer = document.querySelector('.leaflet-container');
            if (!mapContainer) {
                setTimeout(initializeMapSearch, 500);
                return;
            }
            
            // Avoid duplicates
            if (document.querySelector('.map-search-control')) {
                return;
            }

            // Create search container
            const searchContainer = document.createElement('div');
            searchContainer.className = 'map-search-control';
            searchContainer.style.position = 'absolute';
            searchContainer.style.top = '10px';
            searchContainer.style.left = '50px';
            searchContainer.style.zIndex = '1000';
            searchContainer.style.width = '300px';
            
            // Create search input
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Cari lokasi...';
            searchInput.style.width = '100%';
            searchInput.style.padding = '8px 12px';
            searchInput.style.border = '1px solid #ccc';
            searchInput.style.borderRadius = '4px';
            searchInput.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
            
            // Create results container
            const resultsContainer = document.createElement('div');
            resultsContainer.style.display = 'none';
            resultsContainer.style.position = 'absolute';
            resultsContainer.style.width = '100%';
            resultsContainer.style.maxHeight = '200px';
            resultsContainer.style.overflowY = 'auto';
            resultsContainer.style.backgroundColor = 'white';
            resultsContainer.style.border = '1px solid #ccc';
            resultsContainer.style.borderRadius = '0 0 4px 4px';
            resultsContainer.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
            resultsContainer.style.marginTop = '2px';
            resultsContainer.style.zIndex = '1001';
            
            // Add elements to the DOM
            searchContainer.appendChild(searchInput);
            searchContainer.appendChild(resultsContainer);
            mapContainer.appendChild(searchContainer);
            
            // Add event listeners
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                clearTimeout(searchTimeout);
                
                if (query === '') {
                    resultsContainer.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;
                    
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            resultsContainer.innerHTML = '';
                            
                            if (data.length === 0) {
                                resultsContainer.innerHTML = '<div style="padding: 8px 12px; color: #666;">Tidak ada hasil ditemukan</div>';
                                resultsContainer.style.display = 'block';
                                return;
                            }
                            
                            // Display results
                            data.slice(0, 5).forEach(result => {
                                const resultItem = document.createElement('div');
                                resultItem.style.padding = '8px 12px';
                                resultItem.style.borderBottom = '1px solid #eee';
                                resultItem.style.cursor = 'pointer';
                                resultItem.style.backgroundColor = 'white';
                                resultItem.innerHTML = result.display_name;
                                
                                resultItem.addEventListener('mouseover', function() {
                                    this.style.backgroundColor = '#f0f0f0';
                                });
                                
                                resultItem.addEventListener('mouseout', function() {
                                    this.style.backgroundColor = 'white';
                                });
                                
                                resultItem.addEventListener('mousedown', function(e) {
                                    e.preventDefault();
                                });
                                
                                resultItem.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    
                                    updateMapPosition(result.lat, result.lon);
                                    searchInput.value = result.display_name;
                                    resultsContainer.style.display = 'none';
                                });
                                
                                resultsContainer.appendChild(resultItem);
                            });
                            
                            resultsContainer.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error searching for location:', error);
                            resultsContainer.innerHTML = '<div style="padding: 8px 12px; color: red;">Error searching for location</div>';
                            resultsContainer.style.display = 'block';
                        });
                }, 500);
            });
            
            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchContainer.contains(e.target)) {
                    resultsContainer.style.display = 'none';
                }
            });
        }
        
        function updateMapPosition(lat, lng) {
            // Find the map instance
            const mapContainers = document.querySelectorAll('.leaflet-container');
            if (!mapContainers.length) return;
            
            const container = mapContainers[0];
            let map;
            
            // Try to get map from Leaflet internals
            if (typeof L !== 'undefined' && L._maps) {
                for (const key in L._maps) {
                    const m = L._maps[key];
                    if (m._container === container) {
                        map = m;
                        break;
                    }
                }
            }
            
            if (!map) return;
            
            // Update the map view
            map.setView([lat, lng], 15);
            
            // Update marker position
            let marker;
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    marker = layer;
                }
            });
            
            if (marker) {
                marker.setLatLng([lat, lng]);
            }
            
            // Update form fields
            const latInput = document.querySelector('input[name="latitude"]');
            const lngInput = document.querySelector('input[name="longitude"]');
            const locationInput = document.querySelector('input[name*="location"]');
            
            if (latInput) {
                latInput.value = lat;
                latInput.dispatchEvent(new Event('input', {bubbles: true}));
                latInput.dispatchEvent(new Event('change', {bubbles: true}));
            }
            
            if (lngInput) {
                lngInput.value = lng;
                lngInput.dispatchEvent(new Event('input', {bubbles: true}));
                lngInput.dispatchEvent(new Event('change', {bubbles: true}));
            }
            
            if (locationInput) {
                locationInput.value = JSON.stringify({lat: parseFloat(lat), lng: parseFloat(lng)});
                locationInput.dispatchEvent(new Event('input', {bubbles: true}));
                locationInput.dispatchEvent(new Event('change', {bubbles: true}));
            }
        }
        </script>
        HTML;
    }
}
