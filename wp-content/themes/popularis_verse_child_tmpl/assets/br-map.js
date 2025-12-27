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
    var enableToggle = !!config.enableToggle;
    var enableGeoFilters = !!config.enableGeoFilters;
    var restUrl = typeof config.restUrl === 'string' ? config.restUrl : '';
    var restDetailUrl = typeof config.restDetailUrl === 'string' ? config.restDetailUrl : '';

    var map = L.map('br-map', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView(center, zoom);

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

    var pinIcon = L.icon({
        iconUrl: config.pinUrl || '',
        iconSize: [32, 48],
        iconAnchor: [16, 46],
        className: 'br-pin-marker'
    });

    var markersLayer = L.layerGroup().addTo(map);
    var markersEnabled = true;
    var toggleButton = document.getElementById('br-toggle-markers');
    var drawer = document.getElementById('br-story-drawer');
    var drawerTitle = drawer ? drawer.querySelector('.br-story-drawer__title') : null;
    var drawerSubtitle = drawer ? drawer.querySelector('.br-story-drawer__subtitle') : null;
    var drawerMeta = drawer ? drawer.querySelector('.br-story-drawer__meta') : null;
    var drawerTerms = drawer ? drawer.querySelector('.br-story-drawer__terms') : null;
    var drawerBody = drawer ? drawer.querySelector('.br-story-drawer__body') : null;
    var drawerLink = drawer ? drawer.querySelector('.br-story-drawer__link') : null;
    var drawerClose = drawer ? drawer.querySelector('.br-story-drawer__close') : null;

    var activeFilters = {
        district: '',
        period: ''
    };
    var activeRequest = null;
    var activeDetailRequest = null;

    function clearMarkers() {
        markersLayer.clearLayers();
    }

    function clearNode(node) {
        if (!node) {
            return;
        }
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    }

    function openDrawer() {
        if (!drawer) {
            return;
        }
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
    }

    function closeDrawer() {
        if (!drawer) {
            return;
        }
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
    }

    function renderMetaItem(label, value) {
        if (!drawerMeta || !value) {
            return;
        }
        var item = document.createElement('div');
        item.className = 'br-story-drawer__meta-item';

        var itemLabel = document.createElement('span');
        itemLabel.className = 'br-story-drawer__meta-label';
        itemLabel.textContent = label;

        var itemValue = document.createElement('span');
        itemValue.className = 'br-story-drawer__meta-value';
        itemValue.textContent = value;

        item.appendChild(itemLabel);
        item.appendChild(itemValue);
        drawerMeta.appendChild(item);
    }

    function renderTermGroup(label, terms) {
        if (!drawerTerms || !Array.isArray(terms) || terms.length === 0) {
            return;
        }
        var group = document.createElement('div');
        group.className = 'br-story-drawer__term-group';

        var groupLabel = document.createElement('span');
        groupLabel.className = 'br-story-drawer__term-label';
        groupLabel.textContent = label;
        group.appendChild(groupLabel);

        var groupList = document.createElement('div');
        groupList.className = 'br-story-drawer__term-list';

        terms.forEach(function (term) {
            var chip = document.createElement('span');
            chip.className = 'br-story-drawer__term';
            chip.textContent = term.name;
            groupList.appendChild(chip);
        });

        group.appendChild(groupList);
        drawerTerms.appendChild(group);
    }

    function renderStoryDrawer(story) {
        if (!drawer) {
            return;
        }

        if (drawerTitle) {
            drawerTitle.textContent = story.title || '';
        }

        if (drawerSubtitle) {
            drawerSubtitle.textContent = story.subtitle || '';
        }

        clearNode(drawerMeta);
        renderMetaItem('Años', story.years);
        renderMetaItem('Fuente', story.source);
        renderMetaItem('Autor', story.author);

        clearNode(drawerTerms);
        if (story.terms) {
            renderTermGroup('Distrito', story.terms.district);
            renderTermGroup('Barrio', story.terms.neighborhood);
            renderTermGroup('Época', story.terms.period);
            renderTermGroup('Tema', story.terms.theme);
            renderTermGroup('Tipo', story.terms.type);
        }

        if (drawerBody) {
            drawerBody.innerHTML = story.excerpt || story.content || '';
        }

        if (drawerLink) {
            if (story.permalink) {
                drawerLink.href = story.permalink;
                drawerLink.classList.remove('is-hidden');
            } else {
                drawerLink.removeAttribute('href');
                drawerLink.classList.add('is-hidden');
            }
        }

        openDrawer();
    }

    function renderLoadingState() {
        if (drawerTitle) {
            drawerTitle.textContent = 'Cargando historia…';
        }
        if (drawerSubtitle) {
            drawerSubtitle.textContent = '';
        }
        clearNode(drawerMeta);
        clearNode(drawerTerms);
        if (drawerBody) {
            drawerBody.textContent = '';
        }
        if (drawerLink) {
            drawerLink.classList.add('is-hidden');
        }
        openDrawer();
    }

    function buildStoryQuery() {
        if (!restUrl) {
            return '';
        }

        var mapBounds = map.getBounds();
        var bbox = [
            mapBounds.getWest(),
            mapBounds.getSouth(),
            mapBounds.getEast(),
            mapBounds.getNorth()
        ].join(',');

        var params = new URLSearchParams();
        params.set('bbox', bbox);

        if (activeFilters.district) {
            params.set('district', activeFilters.district);
        }

        if (activeFilters.period) {
            params.set('period', activeFilters.period);
        }

        return restUrl + '?' + params.toString();
    }

    function buildStoryDetailUrl(storyId) {
        if (!restDetailUrl) {
            return '';
        }
        return restDetailUrl.replace(/\/$/, '') + '/' + storyId;
    }

    function fetchStoryDetail(storyId) {
        if (!storyId) {
            return;
        }

        var detailEndpoint = buildStoryDetailUrl(storyId);
        if (!detailEndpoint) {
            return;
        }

        if (activeDetailRequest) {
            activeDetailRequest.abort();
        }

        activeDetailRequest = new AbortController();
        renderLoadingState();

        fetch(detailEndpoint, { signal: activeDetailRequest.signal })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Detalle no disponible');
                }
                return response.json();
            })
            .then(function (data) {
                renderStoryDrawer(data);
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                if (drawerBody) {
                    drawerBody.textContent = 'No se pudo cargar la historia.';
                }
                console.error('No se pudo cargar la historia:', error);
            });
    }

    function renderMarkers(items) {
        clearMarkers();

        items.forEach(function (item) {
            var marker = L.marker([item.lat, item.lng], {
                icon: pinIcon
            });
            marker.on('click', function () {
                fetchStoryDetail(item.id || item.slug);
            });
            marker.addTo(markersLayer);
        });
    }

    function updateToggleButton() {
        if (!toggleButton) {
            return;
        }
        toggleButton.textContent = markersEnabled ? 'Historias: ON' : 'Historias: OFF';
        toggleButton.setAttribute('aria-pressed', markersEnabled ? 'true' : 'false');
    }

    function fetchStories() {
        if (!restUrl || !markersEnabled) {
            return;
        }

        var endpoint = buildStoryQuery();
        if (!endpoint) {
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        fetch(endpoint, { signal: activeRequest.signal })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!Array.isArray(data)) {
                    return;
                }
                renderMarkers(data);
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                console.error('No se pudieron cargar las historias:', error);
            });
    }

    updateToggleButton();
    fetchStories();
    map.on('moveend', fetchStories);

    if (enableToggle && toggleButton) {
        toggleButton.addEventListener('click', function () {
            markersEnabled = !markersEnabled;
            updateToggleButton();
            if (markersEnabled) {
                fetchStories();
            } else {
                clearMarkers();
            }
        });
    }

    if (drawerClose) {
        drawerClose.addEventListener('click', closeDrawer);
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
    var periodDropdown = document.querySelector('.br-dropdown[data-type="periods"]');
    var periodTrigger = document.getElementById('br-periods-toggle');
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
        var districtName = target.getAttribute('data-district-name') || target.textContent;
        var districtSlug = target.getAttribute('data-district-slug');
        highlightDistrict(districtName);
        activeFilters.district = districtSlug || '';
        fetchStories();
    });

    setupDropdown(periodDropdown, periodTrigger, function (target) {
        var periodSlug = target.getAttribute('data-period-slug');
        activeFilters.period = periodSlug || '';
        fetchStories();
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
