<?php
/*
Template Name: Mapa Pantalla Completa (Leaflet sin header)
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>

    <!-- Leaflet CSS (CDN) -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
</head>
<body <?php body_class( 'fullmap-body' ); ?>>

    <!-- Título flotante renacentista -->
    <a class="br-title" href="<?php echo home_url( '/mapa-pantalla/' ); ?>">
        BarcelonaRutas
    </a>

    <div id="fullmap-wrapper">
        <div id="br-map"></div>
    </div>

    <!-- Menú flotante renacentista -->
    <nav class="br-float-menu" aria-label="Selector de vistas">
        <a href="<?php echo home_url( '/rutas/' ); ?>" class="br-pill">Rutas</a>
        <button type="button" class="br-pill" id="br-toggle-markers" aria-pressed="true">
            Historias: ON
        </button>
        <div class="br-dropdown" data-open="false">
            <button type="button" class="br-pill br-dropdown__trigger" id="br-districts-toggle" aria-expanded="false">
                Districtes
            </button>
            <div class="br-dropdown__panel" aria-label="Districtes de Barcelona">
                <button type="button" class="br-dropdown__option" data-district="Ciutat Vella">Ciutat Vella</button>
                <button type="button" class="br-dropdown__option" data-district="Eixample">Eixample</button>
                <button type="button" class="br-dropdown__option" data-district="Sants-Montjuïc">Sants-Montjuïc</button>
                <button type="button" class="br-dropdown__option" data-district="Les Corts">Les Corts</button>
                <button type="button" class="br-dropdown__option" data-district="Sarrià–Sant Gervasi">Sarrià–Sant Gervasi</button>
                <button type="button" class="br-dropdown__option" data-district="Gràcia">Gràcia</button>
                <button type="button" class="br-dropdown__option" data-district="Horta-Guinardó">Horta-Guinardó</button>
                <button type="button" class="br-dropdown__option" data-district="Nou Barris">Nou Barris</button>
                <button type="button" class="br-dropdown__option" data-district="Sant Andreu">Sant Andreu</button>
                <button type="button" class="br-dropdown__option" data-district="Sant Martí">Sant Martí</button>
            </div>
        </div>
    </nav>

    <!-- Leaflet JS (CDN) -->
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Centro aproximado de Barcelona
        var map = L.map('br-map', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([41.3851, 2.1734], 13);

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

        // Zona recortada: centro de Barcelona + L'Hospitalet
        var bounds = {
            latMin: 41.35,  // parte baja (L'Hospitalet / Sants)
            latMax: 41.42,  // evitamos demasiada montaña
            lngMin: 2.11,   // un poco hacia el interior
            lngMax: 2.19    // sin meterse en mar abierto
        };

        function randomLatLng() {
            var lat =
                bounds.latMin +
                Math.random() * (bounds.latMax - bounds.latMin);
            var lng =
                bounds.lngMin +
                Math.random() * (bounds.lngMax - bounds.lngMin);
            return [lat, lng];
        }

        // Icono personalizado inspirado en el pin rojo clásico
        var pinIcon = L.icon({
            iconUrl: '<?php echo esc_url( get_stylesheet_directory_uri() . '/map-pin-red.svg' ); ?>',
            iconSize: [32, 48],
            iconAnchor: [16, 46],
            className: 'br-pin-marker'
        });

        var markers = [];
        var totalMarkers = 7; // 6–7 puntos aleatorios

        // Creamos los marcadores, inicialmente ocultos (sin clase is-visible)
        for (var i = 0; i < totalMarkers; i++) {
            var marker = L.marker(randomLatLng(), {
                icon: pinIcon
            }).addTo(map);
            markers.push(marker);
        }

        function setMarkerVisible(marker, visible) {
            var el = marker.getElement();
            if (!el) return;

            if (visible) {
                el.classList.add('is-visible');
            } else {
                el.classList.remove('is-visible');
            }
        }

        function shuffleMarkers() {
            markers.forEach(function (marker) {
                // 50% de probabilidad de mostrarse
                if (Math.random() < 0.5) {
                    marker.setLatLng(randomLatLng());
                    setMarkerVisible(marker, true);  // aparece con animación
                } else {
                    setMarkerVisible(marker, false); // desaparece con animación
                }
            });
        }

        // Gestión de animación de marcadores
        var shuffleInterval = null;
        var shuffleActive = true;
        var toggleButton = document.getElementById('br-toggle-markers');

        function updateToggleButton() {
            if (!toggleButton) return;
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

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                if (shuffleActive) {
                    stopShuffle();
                } else {
                    startShuffle();
                }
            });
        }

        // Primera “oleada” de puntos y arranque de la animación
        startShuffle();

        // --- Distritos de Barcelona ---
        var districtLayer = null;
        var dropdown = document.querySelector('.br-dropdown');
        var dropdownTrigger = document.getElementById('br-districts-toggle');
        var dropdownPanel = document.querySelector('.br-dropdown__panel');

        function highlightDistrict(name) {
            if (!districtLayer) return;
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

        function toggleDropdown(open) {
            if (!dropdown || !dropdownTrigger || !dropdownPanel) return;
            dropdown.dataset.open = open ? 'true' : 'false';
            dropdownTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        if (dropdownTrigger) {
            dropdownTrigger.addEventListener('click', function () {
                var isOpen = dropdown && dropdown.dataset.open === 'true';
                toggleDropdown(!isOpen);
            });
        }

        if (dropdownPanel) {
            dropdownPanel.addEventListener('click', function (event) {
                var target = event.target;
                if (!target.matches('.br-dropdown__option')) return;
                var districtName = target.getAttribute('data-district');
                highlightDistrict(districtName);
                toggleDropdown(false);
            });
        }

        document.addEventListener('click', function (event) {
            if (!dropdown) return;
            var isInside = dropdown.contains(event.target);
            if (!isInside) {
                toggleDropdown(false);
            }
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
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
