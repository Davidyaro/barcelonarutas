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

        // Primera “oleada” de puntos
        shuffleMarkers();

        // Cada 3 segundos cambiamos posiciones / visibilidad
        setInterval(shuffleMarkers, 3000);
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
