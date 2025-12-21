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
        <div class="br-dropdown" data-open="false" data-type="districts">
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
        <div class="br-dropdown" data-open="false" data-type="neighborhoods">
            <button type="button" class="br-pill br-dropdown__trigger" id="br-neighborhoods-toggle" aria-expanded="false">
                Barrios
            </button>
            <div class="br-dropdown__panel" aria-label="Barrios de Barcelona">
                <button type="button" class="br-dropdown__option" data-neighborhood="el Raval">el Raval</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Barri Gòtic">el Barri Gòtic</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Barceloneta">la Barceloneta</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Pere, Santa Caterina i la Ribera">Sant Pere, Santa Caterina i la Ribera</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="l’Antiga Esquerra de l’Eixample">l’Antiga Esquerra de l’Eixample</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Nova Esquerra de l’Eixample">la Nova Esquerra de l’Eixample</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Dreta de l’Eixample">la Dreta de l’Eixample</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Sagrada Família">la Sagrada Família</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Fort Pienc">el Fort Pienc</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Antoni">Sant Antoni</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="el Poble-sec">el Poble-sec</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Marina del Prat Vermell">la Marina del Prat Vermell</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Marina de Port">la Marina de Port</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Font de la Guatlla">la Font de la Guatlla</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Hostafrancs">Hostafrancs</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Bordeta">la Bordeta</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sants–Badal">Sants–Badal</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sants">Sants</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="les Corts">les Corts</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Maternitat i Sant Ramon">la Maternitat i Sant Ramon</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Pedralbes">Pedralbes</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="Vallvidrera, el Tibidabo i les Planes">Vallvidrera, el Tibidabo i les Planes</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sarrià">Sarrià</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="les Tres Torres">les Tres Torres</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Gervasi–Bonanova">Sant Gervasi–Bonanova</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Gervasi–Galvany">Sant Gervasi–Galvany</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Putxet i el Farró">el Putxet i el Farró</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="Vallcarca i els Penitents">Vallcarca i els Penitents</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Coll">el Coll</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Salut">la Salut</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Vila de Gràcia">la Vila de Gràcia</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Camp d’en Grassot i Gràcia Nova">el Camp d’en Grassot i Gràcia Nova</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="el Baix Guinardó">el Baix Guinardó</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Can Baró">Can Baró</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Guinardó">el Guinardó</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Font d’en Fargues">la Font d’en Fargues</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Carmel">el Carmel</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Teixonera">la Teixonera</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Genís dels Agudells">Sant Genís dels Agudells</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Montbau">Montbau</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Horta">Horta</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Clota">la Clota</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="Vilapicina i la Torre Llobeta">Vilapicina i la Torre Llobeta</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Porta">Porta</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Turó de la Peira">el Turó de la Peira</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Can Peguera">Can Peguera</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Guineueta">la Guineueta</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Canyelles">Canyelles</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="les Roquetes">les Roquetes</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Verdun">Verdun</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Prosperitat">la Prosperitat</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Trinitat Nova">la Trinitat Nova</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Torre Baró">Torre Baró</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Ciutat Meridiana">Ciutat Meridiana</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Vallbona">Vallbona</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="la Trinitat Vella">la Trinitat Vella</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Baró de Viver">Baró de Viver</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Bon Pastor">el Bon Pastor</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Andreu">Sant Andreu</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Sagrera">la Sagrera</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Congrés i els Indians">el Congrés i els Indians</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Navas">Navas</button>

                <button type="button" class="br-dropdown__option" data-neighborhood="el Camp de l’Arpa del Clot">el Camp de l’Arpa del Clot</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Clot">el Clot</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Parc i la Llacuna del Poblenou">el Parc i la Llacuna del Poblenou</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Vila Olímpica del Poblenou">la Vila Olímpica del Poblenou</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Poblenou">el Poblenou</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Diagonal Mar i el Front Marítim del Poblenou">Diagonal Mar i el Front Marítim del Poblenou</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="el Besòs i el Maresme">el Besòs i el Maresme</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Provençals del Poblenou">Provençals del Poblenou</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="Sant Martí de Provençals">Sant Martí de Provençals</button>
                <button type="button" class="br-dropdown__option" data-neighborhood="la Verneda i la Pau">la Verneda i la Pau</button>
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
        var neighborhoodLayer = null;
        var districtDropdown = document.querySelector('.br-dropdown[data-type="districts"]');
        var districtTrigger = document.getElementById('br-districts-toggle');
        var neighborhoodDropdown = document.querySelector('.br-dropdown[data-type="neighborhoods"]');
        var neighborhoodTrigger = document.getElementById('br-neighborhoods-toggle');
        var registeredDropdowns = [];

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

        function highlightNeighborhood(name) {
            if (!neighborhoodLayer) return;
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
            if (!dropdownEl || !triggerEl) return;
            dropdownEl.dataset.open = open ? 'true' : 'false';
            triggerEl.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function setupDropdown(dropdownEl, triggerEl, onSelect) {
            if (!dropdownEl || !triggerEl) return;
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
                    if (!target.matches('.br-dropdown__option')) return;
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
    </script>

    <?php wp_footer(); ?>
</body>
</html>
