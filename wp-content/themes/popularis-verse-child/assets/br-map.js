document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') {
        return;
    }

    var mapElement = document.getElementById('br-map');
    if (!mapElement) {
        return;
    }

    var config = window.brMapConfig || {};
    var center = Array.isArray(config.center) ? config.center : [41.3851, 2.1734];
    var zoom = typeof config.zoom === 'number' ? config.zoom : 13;
    var bounds = config.bounds || {
        latMin: 41.35,
        latMax: 41.42,
        lngMin: 2.11,
        lngMax: 2.19
    };
    var markersCount = typeof config.markersCount === 'number' ? config.markersCount : 7;
    var enableShuffle = config.enableShuffle !== false;
    var enableToggle = !!config.enableToggle;
    var enableGeoFilters = !!config.enableGeoFilters;

    // Centro aproximado de Barcelona
    var map = L.map('br-map', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView(center, zoom);

    // Mosaico tipo Carto Voyager
    L.tileLayer(
        'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
        {
            maxZoom: 19,
            subdomains: 'abcd',
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> ' +
                'contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
        }
    ).addTo(map);

    function randomLatLng() {
        var lat =
            bounds.latMin +
            Math.random() * (bounds.latMax - bounds.latMin);
        var lng =
            bounds.lngMin +
            Math.random() * (bounds.lngMax - bounds.lngMin);
        return [lat, lng];
    }

    var pinIcon = L.icon({
        iconUrl: config.pinUrl || '',
        iconSize: [32, 48],
        iconAnchor: [16, 46],
        className: 'br-pin-marker'
    });

    var markers = [];
    var shuffleInterval = null;
    var shuffleActive = enableShuffle;
    var toggleButton = document.getElementById('br-toggle-markers');

    if (enableShuffle && markersCount > 0) {
        for (var i = 0; i < markersCount; i++) {
            var marker = L.marker(randomLatLng(), {
                icon: pinIcon
            }).addTo(map);
            markers.push(marker);
        }
    }

    function setMarkerVisible(marker, visible) {
        var el = marker.getElement();
        if (!el) {
            return;
        }

        if (visible) {
            el.classList.add('is-visible');
        } else {
            el.classList.remove('is-visible');
        }
    }

    function shuffleMarkers() {
        markers.forEach(function (marker) {
            if (Math.random() < 0.5) {
                marker.setLatLng(randomLatLng());
                setMarkerVisible(marker, true);
            } else {
                setMarkerVisible(marker, false);
            }
        });
    }

    function updateToggleButton() {
        if (!toggleButton) {
            return;
        }
        toggleButton.textContent = shuffleActive ? 'Historias: ON' : 'Historias: OFF';
        toggleButton.setAttribute('aria-pressed', shuffleActive ? 'true' : 'false');
    }

    function startShuffle() {
        shuffleMarkers();
        shuffleInterval = setInterval(shuffleMarkers, 3000);
        shuffleActive = true;
        updateToggleButton();
    }

    function stopShuffle() {
        if (shuffleInterval) {
            clearInterval(shuffleInterval);
            shuffleInterval = null;
        }
        markers.forEach(function (marker) {
            setMarkerVisible(marker, false);
        });
        shuffleActive = false;
        updateToggleButton();
    }

    if (enableShuffle && markers.length) {
        startShuffle();
    } else {
        stopShuffle();
    }

    if (enableToggle && toggleButton) {
        toggleButton.addEventListener('click', function () {
            if (shuffleActive) {
                stopShuffle();
            } else {
                startShuffle();
            }
        });
    }

    if (!enableGeoFilters) {
        return;
    }

    var districtLayer = null;
    var neighborhoodLayer = null;
    var districtDropdown = document.querySelector('.br-dropdown[data-type="districts"]');
    var districtTrigger = document.getElementById('br-districts-toggle');
    var neighborhoodDropdown = document.querySelector('.br-dropdown[data-type="neighborhoods"]');
    var neighborhoodTrigger = document.getElementById('br-neighborhoods-toggle');
    var registeredDropdowns = [];

    function highlightDistrict(name) {
        if (!districtLayer) {
            return;
        }
        var matchedLayer = null;

        districtLayer.eachLayer(function (layer) {
            var isMatch = layer.feature && layer.feature.properties && layer.feature.properties.NOM === name;
            if (isMatch) {
                matchedLayer = layer;
                layer.setStyle({
                    color: '#f5c14b',
                    weight: 3,
                    fillColor: '#f5c14b',
                    fillOpacity: 0.15
                });
            } else {
                layer.setStyle({
                    color: '#f5e2b8',
                    weight: 1,
                    fillColor: '#f5e2b8',
                    fillOpacity: 0.05
                });
            }
        });

        if (matchedLayer) {
            map.fitBounds(matchedLayer.getBounds(), { padding: [40, 40] });
            matchedLayer.bringToFront();
        }
    }

    function highlightNeighborhood(name) {
        if (!neighborhoodLayer) {
            return;
        }
        var matchedLayer = null;

        neighborhoodLayer.eachLayer(function (layer) {
            var props = layer.feature && layer.feature.properties;
            var neighborhoodName =
                (props && (props.NOM || props.BARRI || props.BARRI_NOM || props.NOM_CAS)) || '';
            var isMatch = neighborhoodName === name;

            if (isMatch) {
                matchedLayer = layer;
                layer.setStyle({
                    color: '#f59f4b',
                    weight: 3,
                    fillColor: '#f59f4b',
                    fillOpacity: 0.15
                });
            } else {
                layer.setStyle({
                    color: '#f5e2b8',
                    weight: 1,
                    fillColor: '#f5e2b8',
                    fillOpacity: 0.05
                });
            }
        });

        if (matchedLayer) {
            map.fitBounds(matchedLayer.getBounds(), { padding: [40, 40] });
            matchedLayer.bringToFront();
        }
    }

    function toggleDropdown(dropdownEl, triggerEl, open) {
        if (!dropdownEl || !triggerEl) {
            return;
        }
        dropdownEl.dataset.open = open ? 'true' : 'false';
        triggerEl.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setupDropdown(dropdownEl, triggerEl, onSelect) {
        if (!dropdownEl || !triggerEl) {
            return;
        }
        var panelEl = dropdownEl.querySelector('.br-dropdown__panel');
        registeredDropdowns.push({ dropdownEl: dropdownEl, triggerEl: triggerEl });

        triggerEl.addEventListener('click', function () {
            var isOpen = dropdownEl.dataset.open === 'true';
            registeredDropdowns.forEach(function (item) {
                toggleDropdown(item.dropdownEl, item.triggerEl, item.dropdownEl === dropdownEl && !isOpen);
            });
        });

        if (panelEl) {
            panelEl.addEventListener('click', function (event) {
                var target = event.target;
                if (!target.matches('.br-dropdown__option')) {
                    return;
                }
                onSelect(target);
                toggleDropdown(dropdownEl, triggerEl, false);
            });
        }
    }

    setupDropdown(districtDropdown, districtTrigger, function (target) {
        var districtName = target.getAttribute('data-district');
        highlightDistrict(districtName);
    });

    setupDropdown(neighborhoodDropdown, neighborhoodTrigger, function (target) {
        var neighborhoodName = target.getAttribute('data-neighborhood');
        highlightNeighborhood(neighborhoodName);
    });

    document.addEventListener('click', function (event) {
        registeredDropdowns.forEach(function (item) {
            var isInside = item.dropdownEl.contains(event.target);
            if (!isInside) {
                toggleDropdown(item.dropdownEl, item.triggerEl, false);
            }
        });
    });

    fetch('https://raw.githubusercontent.com/jcanalesluna/bcn-geodata/master/districtes/districtes.geojson')
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            districtLayer = L.geoJSON(data, {
                style: function () {
                    return {
                        color: '#f5e2b8',
                        weight: 1,
                        fillColor: '#f5e2b8',
                        fillOpacity: 0.05
                    };
                }
            }).addTo(map);

            map.fitBounds(districtLayer.getBounds(), { padding: [30, 30] });
        })
        .catch(function (error) {
            console.error('No se pudieron cargar los distritos:', error);
        });

    fetch('https://raw.githubusercontent.com/jcanalesluna/bcn-geodata/master/barris/barris.geojson')
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            neighborhoodLayer = L.geoJSON(data, {
                style: function () {
                    return {
                        color: '#f5e2b8',
                        weight: 1,
                        fillColor: '#f5e2b8',
                        fillOpacity: 0.05
                    };
                }
            }).addTo(map);
        })
        .catch(function (error) {
            console.error('No se pudieron cargar los barrios:', error);
        });
});
