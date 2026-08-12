jQuery(document).ready(function($) {
    
    // Inject Theme Background Color into CSS Variables dynamically for popups
    let bgSource = document.querySelector('.site') || document.querySelector('#page') || document.body;
    let themeBg = window.getComputedStyle(bgSource).backgroundColor;
    if (themeBg === 'rgba(0, 0, 0, 0)' || themeBg === 'transparent') {
        themeBg = window.getComputedStyle(document.body).backgroundColor;
        if (themeBg === 'rgba(0, 0, 0, 0)' || themeBg === 'transparent') {
            themeBg = '#ffffff'; 
        }
    }
    document.documentElement.style.setProperty('--cs-theme-bg', themeBg);

    // Compute total sticky header height (admin bar + brand header) and set CSS variable.
    // This keeps sidebar sticky top and scroll anchors correctly positioned regardless of
    // whether the WP admin bar is visible (logged-in users) or not (logged-out users).
    function computeHeaderHeight() {
        const adminBar = document.getElementById('wpadminbar');
        const headerWrapper = document.querySelector('.csp-sticky-header-wrapper');
        const adminBarH = adminBar ? adminBar.offsetHeight : 0;
        const wrapperH = headerWrapper ? headerWrapper.offsetHeight : 0;
        const totalH = adminBarH + wrapperH;
        document.documentElement.style.setProperty('--tsw-header-height', totalH + 'px');
    }
    computeHeaderHeight();
    window.addEventListener('resize', computeHeaderHeight);

    // Move ALL modals and drawer to <body> to escape any stacking context.
    // This ensures position:fixed overlays cover the full viewport regardless of
    // any transform/overflow on the plugin container.
    const drawerEl = document.getElementById('csp-cart-drawer');
    const modalEl = document.getElementById('csp-product-modal');
    const infoModalEl = document.getElementById('csp-info-modal');
    const startupModalEl = document.getElementById('csp-startup-modal');
    if (drawerEl) document.body.appendChild(drawerEl);
    if (modalEl) document.body.appendChild(modalEl);
    if (infoModalEl) document.body.appendChild(infoModalEl);
    if (startupModalEl) document.body.appendChild(startupModalEl);

    // ==========================================
    // 1. Startup Modal Screens & Toggles
    // ==========================================
    const startupModal = document.getElementById('csp-startup-modal');
    const screenLocation = document.getElementById('csp-screen-select-location');
    const screenScheduled = document.getElementById('csp-screen-scheduled-order');

    function deg2rad(deg) {
        return deg * (Math.PI / 180);
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radius of the earth in km
        const dLat = deg2rad(lat2 - lat1);
        const dLon = deg2rad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const d = R * c; // Distance in km
        return d;
    }

    function getShortAddress(address) {
        if (!address) return '';
        const parts = address.split(',');
        return parts[0] + (parts[1] ? ', ' + parts[1].trim() : '');
    }

    function updateCombinedButtonText(method, address, distance, dateVal, timeVal) {
        const addressTextSpan = $('.csp-combined-method-address-text');
        const timeTextSpan = $('.csp-combined-time-text');

        if (method === 'pickup') {
            const addr = (customShopData && customShopData.i18n && customShopData.i18n.pickupAddress) ? customShopData.i18n.pickupAddress : '';
            const pickupAtFormat = (customShopData && customShopData.i18n && customShopData.i18n.pickupAt) ? customShopData.i18n.pickupAt : 'Pickup at %s';
            addressTextSpan.text(pickupAtFormat.replace('%s', addr));
        } else {
            if (address) {
                const distStr = distance ? ` (${parseFloat(distance).toFixed(2)} km)` : '';
                const deliveryToFormat = (customShopData && customShopData.i18n && customShopData.i18n.deliveryTo) ? customShopData.i18n.deliveryTo : 'Delivery to %s';
                addressTextSpan.text(deliveryToFormat.replace('%s', getShortAddress(address)) + distStr);
            } else {
                addressTextSpan.text((customShopData && customShopData.i18n && customShopData.i18n.deliveryToStuttgart) ? customShopData.i18n.deliveryToStuttgart : 'Delivery to Stuttgart');
            }
        }

        if (dateVal && timeVal) {
            timeTextSpan.text(formatShortDate(dateVal) + ', ' + timeVal);
        } else {
            timeTextSpan.text((customShopData && customShopData.i18n && customShopData.i18n.scheduleForLater) ? customShopData.i18n.scheduleForLater : 'Schedule for later');
        }
    }

    function showLocationStatus(type, message) {
        const el = $('#csp-delivery-location-status');
        el.removeClass('loading success error')
          .addClass(type)
          .text(message)
          .show();
    }

    function checkStartupModalState() {
        const savedTime = sessionStorage.getItem('tsw_selected_time');
        const savedDate = sessionStorage.getItem('tsw_selected_date');
        const isPickupEnabled = $('#csp-webshop-container').data('pickup-enabled') !== 'no';
        const isDeliveryEnabled = $('#csp-webshop-container').data('delivery-enabled') !== 'no';

        let savedMethod = sessionStorage.getItem('tsw_chosen_method');
        if (!isPickupEnabled) {
            savedMethod = 'delivery';
            sessionStorage.setItem('tsw_chosen_method', 'delivery');
        } else if (!isDeliveryEnabled) {
            savedMethod = 'pickup';
            sessionStorage.setItem('tsw_chosen_method', 'pickup');
        } else if (!savedMethod) {
            savedMethod = 'pickup';
            sessionStorage.setItem('tsw_chosen_method', 'pickup');
        }
        const savedAddress = sessionStorage.getItem('tsw_delivery_address') || '';
        const savedDistance = sessionStorage.getItem('tsw_delivery_distance') || '';

        if (savedDate) {
            $('.csp-date-btn').removeClass('selected');
            $(`.csp-date-btn[data-date="${savedDate}"]`).addClass('selected');
            $('#csp-startup-date-input').val(savedDate);
        }
        if (savedTime) {
            $('.csp-timeslot-row-item').removeClass('selected-row');
            $(`.csp-timeslot-row-item[data-time="${savedTime}"]`).addClass('selected-row');
            $(`.csp-timeslot-row-item[data-time="${savedTime}"] input[type="radio"]`).prop('checked', true);
            $('#csp-schedule-order-submit-btn').prop('disabled', false);
        }

        // Sync switcher buttons active class on load
        $('.csp-method-switcher-capsule .csp-method-btn').removeClass('active');
        $(`.csp-method-switcher-capsule .csp-method-btn[data-method="${savedMethod}"]`).addClass('active');

        $('#csp-screen-select-location .csp-startup-method-btn').removeClass('active');
        $(`#csp-screen-select-location .csp-startup-method-btn[data-type="${savedMethod}"]`).addClass('active');

        // Toggle visibility of method-specific panels
        if (savedMethod === 'pickup') {
            $('.csp-location-store-card').show();
            $('.csp-delivery-location-container').hide();
        } else {
            $('.csp-location-store-card').hide();
            $('.csp-delivery-location-container').show();
            if (savedAddress) {
                $('#csp-delivery-address-input').val(savedAddress);
                showLocationStatus('success', `Location set: ${savedAddress} (${parseFloat(savedDistance).toFixed(2)} km)`);
            }
        }

        // Sync the combined settings button text
        updateCombinedButtonText(savedMethod, savedAddress, savedDistance, savedDate, savedTime);

        if (startupModal) {
            if (!savedTime || !savedDate || (savedMethod === 'delivery' && !savedAddress)) {
                // Show modal overlay
                startupModal.style.display = 'flex';
                document.body.classList.add('csp-modal-open');
                // Show Screen 1
                if (screenLocation) screenLocation.style.display = 'block';
                if (screenScheduled) screenScheduled.style.display = 'none';
            } else {
                // Restore state in main header time selector
                updateHeaderLabel(savedDate, savedTime);
                // Bug #9 fix: if already scheduled, enable the View Menu button
                $('#csp-startup-confirm-btn').prop('disabled', false);
                $('.csp-schedule-check-icon').addClass('active');
                let displayLabel = formatShortDate(savedDate) + ', ' + savedTime;
                $('.csp-schedule-text-label').html('<strong>' + displayLabel + '</strong>');
            }
        }
    }
    checkStartupModalState();

    // Toggle Pickup vs Delivery in Startup Modal (Screen 1)
    $(document).on('click', '#csp-screen-select-location .csp-startup-method-btn', function(e) {
        e.preventDefault();
        if ($(this).attr('disabled') || $(this).data('disabled') === 'yes') return;
        const isPickupEnabled = $('#csp-webshop-container').data('pickup-enabled') !== 'no';
        const isDeliveryEnabled = $('#csp-webshop-container').data('delivery-enabled') !== 'no';
        const type = $(this).data('type');
        if (type === 'delivery' && !isDeliveryEnabled) return;
        if (type === 'pickup' && !isPickupEnabled) return;

        $('#csp-screen-select-location .csp-startup-method-btn').removeClass('active');
        $(this).addClass('active');
        sessionStorage.setItem('tsw_chosen_method', type);
        
        // Sync to new top switcher
        $('.csp-method-switcher-capsule .csp-method-btn').removeClass('active');
        $(`.csp-method-switcher-capsule .csp-method-btn[data-method="${type}"]`).addClass('active');

        // Toggle store card / delivery search panels
        if (type === 'pickup') {
            $('.csp-location-store-card').show();
            $('.csp-delivery-location-container').hide();
        } else {
            $('.csp-location-store-card').hide();
            $('.csp-delivery-location-container').show();
        }

        const savedAddress = sessionStorage.getItem('tsw_delivery_address') || '';
        const savedDistance = sessionStorage.getItem('tsw_delivery_distance') || '';
        const savedTime = sessionStorage.getItem('tsw_selected_time') || '';
        const savedDate = sessionStorage.getItem('tsw_selected_date') || '';
        updateCombinedButtonText(type, savedAddress, savedDistance, savedDate, savedTime);
    });

    // Toggle Pickup vs Delivery in Top Switcher Section
    $(document).on('click', '.csp-method-switcher-capsule .csp-method-btn', function(e) {
        e.preventDefault();
        if ($(this).hasClass('active') || $(this).attr('disabled') || $(this).data('disabled') === 'yes') return;

        const isPickupEnabled = $('#csp-webshop-container').data('pickup-enabled') !== 'no';
        const isDeliveryEnabled = $('#csp-webshop-container').data('delivery-enabled') !== 'no';
        const method = $(this).data('method');
        if (method === 'delivery' && !isDeliveryEnabled) return;
        if (method === 'pickup' && !isPickupEnabled) return;
        
        $('.csp-method-switcher-capsule .csp-method-btn').removeClass('active');
        $(this).addClass('active');

        sessionStorage.setItem('tsw_chosen_method', method);

        // Sync to startup modal Screen 1 switcher
        $('#csp-screen-select-location .csp-startup-method-btn').removeClass('active');
        $(`#csp-screen-select-location .csp-startup-method-btn[data-type="${method}"]`).addClass('active');

        // Toggle store card / delivery search panels
        if (method === 'pickup') {
            $('.csp-location-store-card').show();
            $('.csp-delivery-location-container').hide();
        } else {
            $('.csp-location-store-card').hide();
            $('.csp-delivery-location-container').show();
        }

        const savedAddress = sessionStorage.getItem('tsw_delivery_address') || '';
        const savedDistance = sessionStorage.getItem('tsw_delivery_distance') || '';
        const savedTime = sessionStorage.getItem('tsw_selected_time') || '';
        const savedDate = sessionStorage.getItem('tsw_selected_date') || '';
        updateCombinedButtonText(method, savedAddress, savedDistance, savedDate, savedTime);

        // If switching to delivery and no address has been set yet, open the location picker modal
        if (method === 'delivery' && !savedAddress) {
            if (startupModal) {
                startupModal.style.display = 'flex';
                document.body.classList.add('csp-modal-open');
                if (screenLocation) screenLocation.style.display = 'block';
                if (screenScheduled) screenScheduled.style.display = 'none';
            }
        } else {
            // Otherwise, save choice to session immediately
            saveLocationToSession(method, savedAddress, savedDistance);
        }
    });

    // Click "Use my current position" link
    $(document).on('click', '#csp-use-device-location-btn', function(e) {
        e.preventDefault();
        if (!navigator.geolocation) {
            showLocationStatus('error', 'Geolocation is not supported by your browser.');
            return;
        }

        showLocationStatus('loading', 'Locating you via device GPS...');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Store coordinates
                sessionStorage.setItem('tsw_delivery_lat', lat);
                sessionStorage.setItem('tsw_delivery_lon', lon);

                // Calculate distance to Nadlerstraße 14 (48.773950, 9.177527)
                const distance = calculateDistance(lat, lon, 48.773950, 9.177527);
                sessionStorage.setItem('tsw_delivery_distance', distance);

                // Reverse geocoding via Nominatim
                showLocationStatus('loading', 'GPS coordinates acquired. Looking up address name...');
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;
                
                fetch(url, {
                    headers: { 'Accept-Agent': 'Antigravity-Shop-Plugin' }
                })
                .then(res => res.json())
                .then(data => {
                    const address = data.display_name || `Lat: ${lat.toFixed(4)}, Lon: ${lon.toFixed(4)}`;
                    sessionStorage.setItem('tsw_delivery_address', address);
                    $('#csp-delivery-address-input').val(address);
                    
                    showLocationStatus('success', `Location set: ${address} (${distance.toFixed(2)} km). Routing...`);

                    // Post settings to WC session
                    saveLocationToSession('delivery', address, distance);

                    // Sync the combined settings button text
                    const savedTime = sessionStorage.getItem('tsw_selected_time');
                    const savedDate = sessionStorage.getItem('tsw_selected_date');
                    updateCombinedButtonText('delivery', address, distance, savedDate, savedTime);

                    // Auto transition to time picking screen
                    setTimeout(function() {
                        if (screenLocation) screenLocation.style.display = 'none';
                        if (screenScheduled) screenScheduled.style.display = 'block';
                    }, 1200);
                })
                .catch(err => {
                    const address = `Lat: ${lat.toFixed(4)}, Lon: ${lon.toFixed(4)}`;
                    sessionStorage.setItem('tsw_delivery_address', address);
                    $('#csp-delivery-address-input').val(address);
                    showLocationStatus('success', `Location set: ${address} (${distance.toFixed(2)} km). Routing...`);
                    
                    saveLocationToSession('delivery', address, distance);

                    // Sync the combined settings button text
                    const savedTime = sessionStorage.getItem('tsw_selected_time');
                    const savedDate = sessionStorage.getItem('tsw_selected_date');
                    updateCombinedButtonText('delivery', address, distance, savedDate, savedTime);
                    
                    setTimeout(function() {
                        if (screenLocation) screenLocation.style.display = 'none';
                        if (screenScheduled) screenScheduled.style.display = 'block';
                    }, 1200);
                });
            },
            function(error) {
                const msg = (customShopData && customShopData.i18n && customShopData.i18n.unableRetrieveLocation) ? customShopData.i18n.unableRetrieveLocation : 'Unable to retrieve your location';
                showLocationStatus('error', msg + ': ' + error.message);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });

    // Handle Enter key on delivery input
    $(document).on('keypress', '#csp-delivery-address-input', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#csp-delivery-search-btn').click();
        }
    });

    // Click Delivery Search button
    $(document).on('click', '#csp-delivery-search-btn', function(e) {
        e.preventDefault();
        const address = $('#csp-delivery-address-input').val().trim();
        if (!address) {
            const msg = (customShopData && customShopData.i18n && customShopData.i18n.enterDeliveryAddress) ? customShopData.i18n.enterDeliveryAddress : 'Please enter a delivery address.';
            showLocationStatus('error', msg);
            return;
        }

        const searchMsg = (customShopData && customShopData.i18n && customShopData.i18n.searchingAddress) ? customShopData.i18n.searchingAddress : 'Searching for address...';
        showLocationStatus('loading', searchMsg);

        const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address) + '&limit=1';
        fetch(url, {
            headers: { 'Accept-Agent': 'Antigravity-Shop-Plugin' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                const displayName = data[0].display_name;
                
                sessionStorage.setItem('tsw_delivery_lat', lat);
                sessionStorage.setItem('tsw_delivery_lon', lon);
                sessionStorage.setItem('tsw_delivery_address', displayName);
                $('#csp-delivery-address-input').val(displayName);

                const distance = calculateDistance(lat, lon, 48.773950, 9.177527);
                sessionStorage.setItem('tsw_delivery_distance', distance);

                const foundTpl = (customShopData && customShopData.i18n && customShopData.i18n.addressFound) ? customShopData.i18n.addressFound : 'Address found: %s (%s km).';
                const successMsg = foundTpl.replace('%s', displayName).replace('%s', distance.toFixed(2));
                showLocationStatus('success', successMsg);

                // Post settings to WC session
                saveLocationToSession('delivery', displayName, distance);

                // Sync the combined settings button text
                const savedTime = sessionStorage.getItem('tsw_selected_time');
                const savedDate = sessionStorage.getItem('tsw_selected_date');
                updateCombinedButtonText('delivery', displayName, distance, savedDate, savedTime);

                // Auto transition to time picking screen
                setTimeout(function() {
                    if (screenLocation) screenLocation.style.display = 'none';
                    if (screenScheduled) screenScheduled.style.display = 'block';
                }, 1200);
            } else {
                const notFoundMsg = (customShopData && customShopData.i18n && customShopData.i18n.addressNotFound) ? customShopData.i18n.addressNotFound : 'Address not found. Please try adding more details (street number, city).';
                showLocationStatus('error', notFoundMsg);
            }
        })
        .catch(err => {
            const errGeoc = (customShopData && customShopData.i18n && customShopData.i18n.geocoderError) ? customShopData.i18n.geocoderError : 'Error connecting to geocoder. Please check your connection.';
            showLocationStatus('error', errGeoc);
        });
    });

    function saveLocationToSession(method, address, distance) {
        const timeVal = sessionStorage.getItem('tsw_selected_time') || '';
        const dateVal = sessionStorage.getItem('tsw_selected_date') || '';
        $.ajax({
            type: 'POST',
            url: customShopData.ajaxUrl || '/wp-admin/admin-ajax.php',
            data: {
                action: 'save_pickup_time_session',
                security: (typeof customShopData !== 'undefined') ? customShopData.nonce : '',
                pickup_time: timeVal,
                pickup_date: dateVal,
                shipping_method: method,
                delivery_address: address,
                delivery_distance: distance
            }
        });
    }

    // Open date/time scheduler on clicking combined settings button
    $(document).on('click', '#csp-combined-settings-trigger', function(e) {
        e.preventDefault();
        if (startupModal) {
            startupModal.style.display = 'flex';
            document.body.classList.add('csp-modal-open');
            // Go straight to Screen 2 (date and time picking screen)
            if (screenLocation) screenLocation.style.display = 'none';
            if (screenScheduled) screenScheduled.style.display = 'block';
        }
    });

    // Toggle Pickup vs Delivery in Startup Modal (Screen 2 Switcher if exists)
    $(document).on('click', '#csp-screen-scheduled-order .csp-startup-method-btn', function(e) {
        e.preventDefault();
        if ($(this).attr('disabled') || $(this).data('disabled') === 'yes') return;
        const type = $(this).data('type');
        const isPickupEnabled = $('#csp-webshop-container').data('pickup-enabled') !== 'no';
        const isDeliveryEnabled = $('#csp-webshop-container').data('delivery-enabled') !== 'no';
        if (type === 'delivery' && !isDeliveryEnabled) return;
        if (type === 'pickup' && !isPickupEnabled) return;

        $('#csp-screen-scheduled-order .csp-startup-method-btn').removeClass('active');
        $(this).addClass('active');

        sessionStorage.setItem('tsw_chosen_method', type);

        $('.csp-method-switcher-capsule .csp-method-btn').removeClass('active');
        $(`.csp-method-switcher-capsule .csp-method-btn[data-method="${type}"]`).addClass('active');

        $('#csp-screen-select-location .csp-startup-method-btn').removeClass('active');
        $(`#csp-screen-select-location .csp-startup-method-btn[data-type="${type}"]`).addClass('active');

        if (type === 'pickup') {
            $('.csp-location-store-card').show();
            $('.csp-delivery-location-container').hide();
        } else {
            $('.csp-location-store-card').hide();
            $('.csp-delivery-location-container').show();
        }
    });

    // Click "Schedule for later" or "Change" link
    $(document).on('click', '#csp-schedule-trigger-btn', function(e) {
        e.preventDefault();
        if (screenLocation) screenLocation.style.display = 'none';
        if (screenScheduled) {
            screenScheduled.style.display = 'block';
            // Auto-select first date button if none chosen
            if (!$('.csp-date-btn.selected').length) {
                $('.csp-date-btn').first().trigger('click');
            } else {
                const selectedDateVal = $('.csp-date-btn.selected').data('date');
                if (selectedDateVal) {
                    updateTimeslotAvailability(selectedDateVal);
                }
            }
        }
    });

    // Click Back arrow inside Scheduled Order Screen
    $(document).on('click', '#csp-scheduled-back-btn', function(e) {
        e.preventDefault();
        if (screenScheduled) screenScheduled.style.display = 'none';
        if (screenLocation) screenLocation.style.display = 'block';
    });

    // Date button clicks in Screen 2
    $(document).on('click', '.csp-date-btn', function(e) {
        e.preventDefault();
        $('.csp-date-btn').removeClass('selected');
        $(this).addClass('selected');
        const dateVal = $(this).data('date');
        $('#csp-startup-date-input').val(dateVal);
        
        // Disable passed times slots depending on the chosen date
        updateTimeslotAvailability(dateVal);
    });

    function updateTimeslotAvailability(dateVal) {
        // Simple client-side slot updates with Germany (Europe/Berlin) timezone
        const options = {
            timeZone: 'Europe/Berlin',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        };
        const formatter = new Intl.DateTimeFormat('en-US', options);
        const parts = formatter.formatToParts(new Date());
        const map = {};
        parts.forEach(p => {
            if (p.type !== 'literal') {
                map[p.type] = p.value;
            }
        });
        const todayStr = `${map.year}-${map.month}-${map.day}`;
        const currentHour = parseInt(map.hour, 10);
        const currentMin = parseInt(map.minute, 10);

        const listItems = document.querySelectorAll('.csp-scheduled-timeslots-list li');

        listItems.forEach(function(item) {
            const timeStr = item.getAttribute('data-time');
            if (!timeStr) return;
            const parts = timeStr.split(':');
            const hour = parseInt(parts[0]);
            const min = parseInt(parts[1]);

            if (dateVal === todayStr) {
                // If today, hide passed times slots
                if (hour < currentHour || (hour === currentHour && min <= currentMin)) {
                    item.classList.add('disabled');
                    const radio = item.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.disabled = true;
                        radio.checked = false;
                    }
                } else {
                    item.classList.remove('disabled');
                    const radio = item.querySelector('input[type="radio"]');
                    if (radio) radio.disabled = false;
                }
            } else {
                item.classList.remove('disabled');
                const radio = item.querySelector('input[type="radio"]');
                if (radio) radio.disabled = false;
            }
        });
        
        // Reset selections
        $('#csp-schedule-order-submit-btn').prop('disabled', true);
        $('.csp-scheduled-timeslots-list li').removeClass('selected-row');
        $('.csp-scheduled-timeslots-list li input[type="radio"]').prop('checked', false);
    }

    // Toggle Accordion "Show more" / "Show less"
    $(document).on('click', '#csp-date-accordion-toggle', function(e) {
        e.preventDefault();
        const panel = $('#csp-date-accordion-panel');
        const spanText = $(this).find('span').first();
        const arrow = $(this).find('.csp-arrow-icon');

        panel.slideToggle(200, function() {
            if (panel.is(':visible')) {
                const showLessTxt = (customShopData && customShopData.i18n && customShopData.i18n.showLess) ? customShopData.i18n.showLess : 'Show less';
                spanText.text(showLessTxt);
                arrow.css('transform', 'rotate(180deg)');
            } else {
                const showMoreTxt = (customShopData && customShopData.i18n && customShopData.i18n.showMore) ? customShopData.i18n.showMore : 'Show more';
                spanText.text(showMoreTxt);
                arrow.css('transform', 'rotate(0deg)');
            }
        });
    });

    // Select timeslot row
    $(document).on('click', '.csp-timeslot-row-item', function(e) {
        if ($(this).hasClass('disabled')) return;
        
        $('.csp-timeslot-row-item').removeClass('selected-row');
        $('.csp-timeslot-row-item input[type="radio"]').prop('checked', false);
        
        $(this).addClass('selected-row');
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);

        $('#csp-schedule-order-submit-btn').prop('disabled', false);
    });

    // Schedule Order submit click (Screen 2)
    $(document).on('click', '#csp-schedule-order-submit-btn', function(e) {
        e.preventDefault();

        const dateVal = $('#csp-startup-date-input').val();
        // Bug #6 fix: read data-time from the li element directly (not from a sub-selector)
        const selectedRow = $('.csp-scheduled-timeslots-list li.selected-row');
        const timeVal = selectedRow.length ? selectedRow.attr('data-time') : null;

        if (!dateVal || !timeVal) {
            const dtMsg = (customShopData && customShopData.i18n && customShopData.i18n.selectDateTime) ? customShopData.i18n.selectDateTime : 'Please select a date and time.';
            alert(dtMsg);
            return;
        }

        // Save choices
        sessionStorage.setItem('tsw_selected_time', timeVal);
        sessionStorage.setItem('tsw_selected_date', dateVal);
        document.cookie = "tsw_selected_time=" + encodeURIComponent(timeVal) + "; path=/; max-age=" + (3600 * 24);
        document.cookie = "tsw_selected_date=" + encodeURIComponent(dateVal) + "; path=/; max-age=" + (3600 * 24);

        // Sync via AJAX
        const chosenMethod = sessionStorage.getItem('tsw_chosen_method') || 'pickup';
        const address = sessionStorage.getItem('tsw_delivery_address') || '';
        const distance = sessionStorage.getItem('tsw_delivery_distance') || '';
        $.ajax({
            type: 'POST',
            url: customShopData.ajaxUrl || '/wp-admin/admin-ajax.php',
            data: {
                action: 'save_pickup_time_session',
                security: (typeof customShopData !== 'undefined') ? customShopData.nonce : '',
                pickup_time: timeVal,
                pickup_date: dateVal,
                shipping_method: chosenMethod,
                delivery_address: address,
                delivery_distance: distance
            }
        });

        // Update Screen 1 Schedule Box
        $('.csp-schedule-check-icon').addClass('active');
        let displayLabel = formatShortDate(dateVal) + ', ' + timeVal;
        $('.csp-schedule-text-label').html('<strong>' + displayLabel + '</strong>');

        // Update combined button label
        updateCombinedButtonText(chosenMethod, address, distance, dateVal, timeVal);

        // Enable View Menu
        $('#csp-startup-confirm-btn').prop('disabled', false);

        // Go back to Screen 1
        if (screenScheduled) screenScheduled.style.display = 'none';
        if (screenLocation) screenLocation.style.display = 'block';
    });

    // Confirm "View Menu" click
    $(document).on('click', '#csp-startup-confirm-btn', function(e) {
        e.preventDefault();
        if ($(this).prop('disabled')) return;

        const savedTime = sessionStorage.getItem('tsw_selected_time');
        const savedDate = sessionStorage.getItem('tsw_selected_date');
        const savedMethod = sessionStorage.getItem('tsw_chosen_method') || 'pickup';
        const savedAddress = sessionStorage.getItem('tsw_delivery_address') || '';
        const savedDistance = sessionStorage.getItem('tsw_delivery_distance') || '';

        updateHeaderLabel(savedDate, savedTime);
        updateCombinedButtonText(savedMethod, savedAddress, savedDistance, savedDate, savedTime);

        if (startupModal) startupModal.style.display = 'none';
        document.body.classList.remove('csp-modal-open');
    });

    function formatShortDate(dateStr) {
        // E.g. converts "2026-07-11" to "Sat 11. Jul" or "Jul 11"
        const dateObj = new Date(dateStr);
        const options = { weekday: 'short', month: 'short', day: 'numeric' };
        const locale = document.documentElement.lang || 'en-US';
        return dateObj.toLocaleDateString(locale, options);
    }

    function updateHeaderLabel(dateVal, timeVal) {
        const timeSpan = $('#csp-selected-time');
        if (timeSpan.length && dateVal && timeVal) {
            let label = dateVal.substring(5).replace('-', '.') + ' - ' + timeVal;
            timeSpan.text(label);
            timeSpan.addClass('time-selected');
        }
    }


    // ==========================================
    // 2. Info / Store Hours Modal Dialog
    // ==========================================
    $(document).on('click', '.csp-info-trigger', function(e) {
        e.preventDefault();
        if (infoModalEl) {
            // Temporarily hide startup modal backdrop so info modal appears on top,
            // without changing z-index order. We re-show it when info modal closes.
            if (startupModalEl && startupModalEl.style.display !== 'none') {
                startupModalEl.dataset.wasOpen = 'true';
                startupModalEl.style.visibility = 'hidden';
            }
            infoModalEl.style.display = 'flex';
            document.body.classList.add('csp-modal-open');
        }
    });

    $(document).on('click', '.csp-info-close-btn', function(e) {
        e.preventDefault();
        if (infoModalEl) {
            infoModalEl.style.display = 'none';
            // Restore startup modal visibility if it was open underneath
            if (startupModalEl && startupModalEl.dataset.wasOpen === 'true') {
                startupModalEl.style.visibility = '';
                delete startupModalEl.dataset.wasOpen;
                // Keep body.csp-modal-open since startup modal is still active
            } else {
                document.body.classList.remove('csp-modal-open');
            }
        }
    });

    if (infoModalEl) {
        infoModalEl.addEventListener('click', function(e) {
            if (e.target === infoModalEl) {
                infoModalEl.style.display = 'none';
                document.body.classList.remove('csp-modal-open');
            }
        });
    }


    // ==========================================
    // 3. Category Scrolling Sidebar active sync
    // ==========================================
    const categorySections = document.querySelectorAll('.category-section, .subcategory-section');
    const sidebarCategoryItems = document.querySelectorAll('.shop-categories-list li');

    if (categorySections.length > 0 && sidebarCategoryItems.length > 0) {
        function activeCategoryOnScroll() {
            const totalOffset = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--tsw-header-height')
            ) || 0;
            const scrollPos = window.pageYOffset + totalOffset + 80;

            let activeId = '';
            categorySections.forEach(function(section) {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    activeId = section.getAttribute('id');
                }
            });

            if (activeId) {
                sidebarCategoryItems.forEach(function(li) {
                    li.classList.remove('active');
                    const link = li.querySelector('a.cat-link');
                    if (link && link.getAttribute('href') === '#' + activeId) {
                        li.classList.add('active');
                    }
                });
            }
        }
        window.addEventListener('scroll', activeCategoryOnScroll);
        window.addEventListener('resize', activeCategoryOnScroll);
        activeCategoryOnScroll();
    }

    // Sidebar Category clicks smooth scroll
    $(document).on('click', '.shop-categories-list a.cat-link', function(e) {
        e.preventDefault();
        const targetId = $(this).attr('href');
        const targetEl = document.querySelector(targetId);
        if (targetEl) {
            // Use the pre-computed header height variable (admin bar + brand header)
            const totalOffset = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--tsw-header-height')
            ) || 0;
            const extraPadding = 16;
            const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - totalOffset - extraPadding;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });

    // ==========================================
    // 3b. Sticky Category Bar, Modal Drawer & Search Overlay
    // ==========================================

    // Move category modal to body so it escapes any stacking context
    const categoryModalEl = document.getElementById('csp-category-modal');
    if (categoryModalEl) document.body.appendChild(categoryModalEl);

    // --- Hamburger → Category Modal ---
    const hamburgerBtn = document.getElementById('csp-hamburger-btn');
    if (hamburgerBtn && categoryModalEl) {
        hamburgerBtn.addEventListener('click', function() {
            categoryModalEl.style.display = 'block';
            document.body.classList.add('csp-modal-open');
        });

        // Close button
        const catModalCloseBtn = categoryModalEl.querySelector('.csp-category-modal-close');
        if (catModalCloseBtn) {
            catModalCloseBtn.addEventListener('click', function() {
                categoryModalEl.style.display = 'none';
                document.body.classList.remove('csp-modal-open');
            });
        }

        // Click on backdrop closes modal
        categoryModalEl.addEventListener('click', function(e) {
            if (e.target === categoryModalEl) {
                categoryModalEl.style.display = 'none';
                document.body.classList.remove('csp-modal-open');
            }
        });
    }

    // --- Category Modal Item clicks → scroll to section & close modal ---
    $(document).on('click', '.csp-category-modal-item', function(e) {
        e.preventDefault();
        const slug = $(this).data('slug');
        const targetEl = document.getElementById('cat-' + slug);
        if (targetEl) {
            const totalOffset = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--tsw-header-height')
            ) || 0;
            const extraPadding = 16;
            const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - totalOffset - extraPadding;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }
        // Close the modal
        if (categoryModalEl) {
            categoryModalEl.style.display = 'none';
            document.body.classList.remove('csp-modal-open');
        }
    });

    // --- Category Bar Pill clicks → scroll to section ---
    $(document).on('click', '.csp-catbar-pill', function(e) {
        e.preventDefault();
        const slug = $(this).data('slug');
        const targetEl = document.getElementById('cat-' + slug);
        if (targetEl) {
            const totalOffset = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--tsw-header-height')
            ) || 0;
            const extraPadding = 16;
            const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - totalOffset - extraPadding;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }

        // Set active state
        $('.csp-catbar-pill').removeClass('active');
        $(this).addClass('active');

        // Scroll the pill into view within the horizontal scroller
        const scrollContainer = document.getElementById('csp-catbar-scroll');
        if (scrollContainer && this) {
            const pillLeft = this.offsetLeft - scrollContainer.offsetLeft;
            const pillCenter = pillLeft - scrollContainer.clientWidth / 2 + this.clientWidth / 2;
            scrollContainer.scrollTo({ left: pillCenter, behavior: 'smooth' });
        }
    });

    // --- Active pill sync on page scroll ---
    const catBarPills = document.querySelectorAll('.csp-catbar-pill');
    const parentCategorySections = document.querySelectorAll('.category-section.is-parent-category');

    if (catBarPills.length > 0 && parentCategorySections.length > 0) {
        function syncCatBarActiveOnScroll() {
            const totalOffset = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--tsw-header-height')
            ) || 0;
            const scrollPos = window.pageYOffset + totalOffset + 100;

            let activeSlug = '';
            parentCategorySections.forEach(function(section) {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    // id format: "cat-slug"
                    activeSlug = section.getAttribute('id').replace('cat-', '');
                }
            });

            if (activeSlug) {
                catBarPills.forEach(function(pill) {
                    if (pill.getAttribute('data-slug') === activeSlug) {
                        if (!pill.classList.contains('active')) {
                            catBarPills.forEach(function(p) { p.classList.remove('active'); });
                            pill.classList.add('active');
                            // Auto-scroll the pill into view (only if category bar is visible)
                            const scrollContainer = document.getElementById('csp-catbar-scroll');
                            if (scrollContainer && scrollContainer.offsetParent !== null) {
                                const pillLeft = pill.offsetLeft - scrollContainer.offsetLeft;
                                const pillCenter = pillLeft - scrollContainer.clientWidth / 2 + pill.clientWidth / 2;
                                scrollContainer.scrollTo({ left: pillCenter, behavior: 'smooth' });
                            }
                        }
                    }
                });

                // Also sync the modal items
                document.querySelectorAll('.csp-category-modal-item').forEach(function(item) {
                    item.classList.remove('active');
                    if (item.getAttribute('data-slug') === activeSlug) {
                        item.classList.add('active');
                    }
                });
            }
        }

        window.addEventListener('scroll', syncCatBarActiveOnScroll);
        syncCatBarActiveOnScroll();
    }

    // --- Search Overlay ---
    const searchToggleBtn = document.getElementById('csp-search-toggle');
    const searchOverlay = document.getElementById('csp-search-overlay');
    const searchCancelBtn = document.getElementById('csp-search-cancel');
    const searchOverlayInput = document.getElementById('csp-search-overlay-input');

    if (searchToggleBtn && searchOverlay) {
        searchToggleBtn.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            if (searchOverlayInput) searchOverlayInput.focus();
        });
    }

    if (searchCancelBtn && searchOverlay) {
        searchCancelBtn.addEventListener('click', function() {
            searchOverlay.classList.remove('active');
            if (searchOverlayInput) {
                searchOverlayInput.value = '';
                // Trigger search reset
                $(searchOverlayInput).trigger('input');
            }
        });
    }

    // Search overlay input → reuse same filtering logic as the old sidebar search
    if (searchOverlayInput) {
        $(searchOverlayInput).on('input', function() {
            const val = $(this).val().toLowerCase();
            $('.custom-product-item').each(function() {
                const title = $(this).find('.product-item-title').text().toLowerCase();
                if (title.indexOf(val) > -1) {
                    $(this).removeClass('csp-hidden');
                } else {
                    $(this).addClass('csp-hidden');
                }
            });

            // Hide empty subcategories
            $('.subcategory-section').each(function() {
                const visibleProducts = $(this).find('.custom-product-item:not(.csp-hidden)').length;
                if (visibleProducts > 0) {
                    $(this).removeClass('csp-hidden');
                } else {
                    $(this).addClass('csp-hidden');
                }
            });

            // Hide empty parent categories
            $('.category-section').each(function() {
                const visibleProducts = $(this).find('.custom-product-item:not(.csp-hidden)').length;
                if (visibleProducts > 0) {
                    $(this).removeClass('csp-hidden');
                } else {
                    $(this).addClass('csp-hidden');
                }
            });
        });
    }

    // ==========================================
    // 4. Slide-Out Cart Drawer Toggles & Methods
    // ==========================================
    const standaloneDrawer = document.getElementById('csp-cart-drawer');
    const drawerCloseBtn = standaloneDrawer ? standaloneDrawer.querySelector('.csp-drawer-close') : null;
    const cartTriggers = document.querySelectorAll('#csp-header-cart-trigger, #csp-floating-cart-trigger');

    cartTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (standaloneDrawer) standaloneDrawer.classList.add('active');
        });
    });

    if (drawerCloseBtn) {
        drawerCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (standaloneDrawer) standaloneDrawer.classList.remove('active');
        });
    }

    if (standaloneDrawer) {
        standaloneDrawer.addEventListener('click', function(e) {
            if (e.target === standaloneDrawer) {
                standaloneDrawer.classList.remove('active');
            }
        });

        // Quantity Adjuster controls inside Drawer
        standaloneDrawer.addEventListener('click', function(e) {
            const btn = e.target.closest('.csp-item-qty-btn');
            if (btn) {
                e.preventDefault();
                const key = btn.getAttribute('data-key');
                const isMinus = btn.classList.contains('minus');
                const card = btn.closest('.csp-drawer-item-card');
                const qtyValSpan = card.querySelector('.csp-item-qty-val');
                let qty = parseInt(qtyValSpan.textContent) || 1;

                if (isMinus) {
                    qty--;
                } else {
                    qty++;
                }
                updateDrawerCartQty(key, qty);
            }
        });

        // Add from drawer "People also order"
        standaloneDrawer.addEventListener('click', function(e) {
            const addBtn = e.target.closest('.csp-cross-card-add-btn');
            if (addBtn) {
                e.preventDefault();
                const productId = addBtn.closest('.csp-drawer-cross-card').getAttribute('data-id');
                if (productId) {
                    addSimpleProductToCartAJAX(productId);
                }
            }
        });
    }

    function addSimpleProductToCartAJAX(productId) {
        if (standaloneDrawer) standaloneDrawer.classList.add('csp-drawer-loading');

        const params = new URLSearchParams();
        params.append('action', 'woocommerce_add_to_cart');
        params.append('product_id', productId);
        params.append('quantity', 1);

        fetch(customShopData.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: params
        })
        .then(res => res.json())
        .then(data => {
            if (data.fragments) {
                updateCartDrawerFragments(data.fragments);
            }
        })
        .finally(() => {
            if (standaloneDrawer) standaloneDrawer.classList.remove('csp-drawer-loading');
        });
    }

    function updateDrawerCartQty(key, qty) {
        if (!standaloneDrawer) return;
        standaloneDrawer.classList.add('csp-drawer-loading');

        const params = new URLSearchParams();
        params.append('action', 'tsw_update_cart_item_qty');
        params.append('cart_item_key', key);
        params.append('qty', qty);
        if (typeof customShopData !== 'undefined' && customShopData.nonce) {
            params.append('security', customShopData.nonce);
        }

        fetch(customShopData.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: params
        })
        .then(res => res.json())
        .then(data => {
            let fragments = data.fragments || (data.data && data.data.fragments);
            if (fragments) {
                updateCartDrawerFragments(fragments);
            }
        })
        .finally(() => {
            if (standaloneDrawer) standaloneDrawer.classList.remove('csp-drawer-loading');
        });
    }

    function updateCartDrawerFragments(fragments) {
        Object.keys(fragments).forEach(function(selector) {
            const elements = document.querySelectorAll(selector);
            elements.forEach(function(el) {
                const temp = document.createElement('div');
                temp.innerHTML = fragments[selector];
                const newEl = temp.firstElementChild;
                if (newEl) {
                    el.replaceWith(newEl);
                } else {
                    el.innerHTML = fragments[selector];
                }
            });
        });
        
        // Extract total quantity from WooCommerce's native count span if available
        let totalQty = 0;
        if (fragments && fragments['span.cart-contents-count']) {
            const temp = document.createElement('div');
            temp.innerHTML = fragments['span.cart-contents-count'];
            totalQty = parseInt(temp.textContent) || 0;
        } else {
            // Fallback: sum the quantities inside the drawer card items
            $('.csp-drawer-item-card').each(function() {
                const val = parseInt($(this).find('.csp-item-qty-val').text()) || 1;
                totalQty += val;
            });
        }
        
        $('.csp-n14-header-cart-count').text(totalQty);
    }


    // ==========================================
    // 5. Product Options Lightbox Modal & Options
    // ==========================================
    const productModal = document.getElementById('csp-product-modal');
    
    // Intercept clicks on product card plus buttons
    $(document).on('click', '.csp-n14-plus-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const savedTime = sessionStorage.getItem('tsw_selected_time');
        const savedDate = sessionStorage.getItem('tsw_selected_date');
        
        // Require date & time slots chosen first
        if (!savedTime || !savedDate) {
            if (startupModal) {
                startupModal.style.display = 'flex';
                document.body.classList.add('csp-modal-open');
            }
            return;
        }

        const card = this.closest('.custom-product-item');
        if (card) {
            openProductModal(card);
        }
    });

    function openProductModal(card) {
        if (!productModal) return;

        // Get info fields from card
        const title = card.querySelector('.product-item-title').innerHTML;
        const fullImgUrl = card.getAttribute('data-full-image');
        const imgEl = card.querySelector('.product-item-image');
        const imgSrc = fullImgUrl || (imgEl ? imgEl.getAttribute('src') : '');
        const desc = card.querySelector('.product-excerpt') ? card.querySelector('.product-excerpt').textContent : '';
        
        const variationsDataAttr = card.getAttribute('data-product_variations');
        const variationsData = variationsDataAttr ? JSON.parse(variationsDataAttr) : [];

        // Set values inside modal
        document.getElementById('csp-modal-title').innerHTML = title;
        document.getElementById('csp-modal-desc').textContent = desc.trim();

        // Image display check
        const modalImg = document.getElementById('csp-modal-img');
        if (imgSrc) {
            modalImg.src = imgSrc;
            modalImg.parentElement.style.display = 'block';
        } else {
            modalImg.parentElement.style.display = 'none';
        }

        // Reset inputs
        const qtyInput = document.getElementById('csp-modal-qty-input');
        if (qtyInput) qtyInput.value = 1;

        const notesInput = document.getElementById('csp-modal-notes-input');
        if (notesInput) notesInput.value = '';

        // Bug #7 fix: guard against null .csp-card-price element
        // Bug #8 fix: store base price on card for simple product fallback in updateModalPrice
        const priceEl = card.querySelector('.csp-card-price');
        const priceText = priceEl ? priceEl.textContent : '0';
        const numericPrice = parseFloat(priceText.replace(/[^0-9,.]/g, '').replace(',', '.')) || 0;
        // Store on card dataset so updateModalPrice can read it without querying DOM again
        card.dataset.basePrice = numericPrice;
        // (Points capsule removed — no earn-points-val element)

        // Load variation radio selectors
        const optionsContainer = document.getElementById('csp-modal-options-container');
        optionsContainer.innerHTML = '';

        const nativeSelects = card.querySelectorAll('.variation-select-wrapper select');
        nativeSelects.forEach(function(select) {
            const attrName = select.getAttribute('name');
            const selectId = select.id;
            const attrLabel = card.querySelector(`label[for="${selectId}"]`) ? card.querySelector(`label[for="${selectId}"]`).textContent : 'Deine Zutat';

            const attrBlock = document.createElement('div');
            attrBlock.className = 'csp-modal-attr-group';
            attrBlock.innerHTML = `
                <div class="csp-modal-attr-header">
                    <h3>${attrLabel}</h3>
                </div>
                <div class="csp-modal-radio-list"></div>
            `;

            const radioList = attrBlock.querySelector('.csp-modal-radio-list');
            const options = select.querySelectorAll('option');

            // Find the lowest priced variation among the valid options in this attribute group
            const optionPrices = [];
            options.forEach(function(opt) {
                const val = opt.getAttribute('value');
                if (!val) return;
                const matchingVar = variationsData.find(v => v.attributes[attrName] === val || v.attributes[attrName] === '');
                if (matchingVar) {
                    optionPrices.push(matchingVar.display_price);
                }
            });
            const minPrice = optionPrices.length > 0 ? Math.min(...optionPrices) : 0;

            options.forEach(function(opt) {
                const val = opt.getAttribute('value');
                if (!val) return; // skip placeholder
                const name = opt.textContent;

                let priceDiff = '';
                let valPrice = 0;
                const matchingVar = variationsData.find(v => v.attributes[attrName] === val || v.attributes[attrName] === '');
                if (matchingVar) {
                    valPrice = matchingVar.display_price;
                    const diff = Math.max(0, valPrice - minPrice);
                    priceDiff = `+ ${diff.toFixed(2).replace('.', ',')} €`;
                }

                const radioRow = document.createElement('label');
                radioRow.className = 'csp-modal-radio-row';
                radioRow.innerHTML = `
                    <span class="radio-label-name">${name}</span>
                    <span class="radio-price-add">${priceDiff}</span>
                    <input type="radio" name="modal_${attrName}" value="${val}" data-price="${valPrice}">
                    <span class="radio-custom-circle"></span>
                `;

                const input = radioRow.querySelector('input');
                if (select.value === val) {
                    input.checked = true;
                }

                input.addEventListener('change', function() {
                    select.value = val;
                    $(select).trigger('change');
                    updateModalPrice(card, variationsData);
                    // Clear any prior validation error when user picks a new option
                    const errEl = document.getElementById('csp-modal-error-msg');
                    if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
                });

                radioList.appendChild(radioRow);
            });

            optionsContainer.appendChild(attrBlock);
        });

        // Update Total Prices
        updateModalPrice(card, variationsData);

        // Open modal window
        productModal.classList.add('active');
        document.body.classList.add('csp-modal-open');
        productModal.activeCard = card;
        initProductModalEvents();
    }

    function findMatchingVariation(card, variationsData) {
        if (!variationsData || !variationsData.length) return null;

        const nativeSels = card.querySelectorAll('.variation-select-wrapper select');
        const selectedAttrs = {};
        let allSelected = true;
        nativeSels.forEach(function(sel) {
            const attrName = sel.getAttribute('name');
            selectedAttrs[attrName] = sel.value;
            if (!sel.value || sel.value === '') {
                allSelected = false;
            }
        });

        if (!allSelected) return null;

        return variationsData.find(function(v) {
            for (const attrName in selectedAttrs) {
                const selectedVal = selectedAttrs[attrName];
                const varVal = v.attributes[attrName];
                // Support exact match and wildcards/any attribute (empty string)
                if (varVal !== undefined && varVal !== '' && varVal !== selectedVal) {
                    return false;
                }
            }
            return true;
        }) || null;
    }

    function updateModalPrice(card, variationsData) {
        const qtyInput = document.getElementById('csp-modal-qty-input');
        const qty = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
        let basePrice = 0;

        // Try client-side resolution first
        const matchingVar = findMatchingVariation(card, variationsData);
        let varIdInput = card.querySelector('input.variation_id');

        if (matchingVar) {
            basePrice = matchingVar.display_price;
            if (!varIdInput) {
                const form = card.querySelector('form');
                if (form) {
                    varIdInput = document.createElement('input');
                    varIdInput.type = 'hidden';
                    varIdInput.name = 'variation_id';
                    varIdInput.className = 'variation_id';
                    form.appendChild(varIdInput);
                }
            }
            if (varIdInput) {
                varIdInput.value = matchingVar.variation_id;
            }
        } else {
            // Reset variation id if not fully selected
            if (varIdInput) {
                varIdInput.value = '0';
            }

            // Fallback for simple products or when variation not matched yet
            const checkedRadios = productModal.querySelectorAll('input[type="radio"]:checked');
            checkedRadios.forEach(function(r) {
                basePrice = Math.max(basePrice, parseFloat(r.getAttribute('data-price')) || 0);
            });
            if (basePrice === 0) {
                basePrice = parseFloat(card.dataset.basePrice) || 0;
            }
        }

        const priceValEl = document.getElementById('csp-modal-price');
        const addPriceEl = document.getElementById('csp-modal-add-price');

        if (basePrice > 0) {
            const singleFormatted = basePrice.toFixed(2).replace('.', ',') + ' €';
            const totalFormatted = (basePrice * qty).toFixed(2).replace('.', ',') + ' €';
            priceValEl.textContent = singleFormatted;
            addPriceEl.textContent = totalFormatted;
        } else {
            priceValEl.textContent = '';
            addPriceEl.textContent = '';
        }
    }

    function initProductModalEvents() {
        const modalClose = productModal.querySelector('.csp-modal-close');
        if (modalClose) {
            modalClose.onclick = function() {
                productModal.classList.remove('active');
                document.body.classList.remove('csp-modal-open');
            };
        }

        // Overlay click to close
        productModal.onclick = function(e) {
            if (e.target === productModal) {
                productModal.classList.remove('active');
                document.body.classList.remove('csp-modal-open');
            }
        };

        // Qty adjust
        const minus = productModal.querySelector('.csp-modal-qty-container .minus');
        const plus = productModal.querySelector('.csp-modal-qty-container .plus');
        const qtyInput = document.getElementById('csp-modal-qty-input');

        if (minus && plus && qtyInput) {
            minus.onclick = function() {
                let val = parseInt(qtyInput.value) || 1;
                if (val > 1) {
                    qtyInput.value = val - 1;
                    triggerModalPriceUpdate();
                }
            };
            plus.onclick = function() {
                let val = parseInt(qtyInput.value) || 1;
                qtyInput.value = val + 1;
                triggerModalPriceUpdate();
            };
            qtyInput.onchange = function() {
                let val = parseInt(qtyInput.value) || 1;
                if (val < 1) qtyInput.value = 1;
                triggerModalPriceUpdate();
            };
        }

        // Product info accordion toggle
        const accHeader = document.getElementById('csp-info-accordion-trigger');
        const accContent = document.getElementById('csp-info-accordion-content');
        if (accHeader && accContent) {
            accHeader.onclick = function(e) {
                e.preventDefault();
                const isOpening = !$(accContent).is(':visible');
                $(accContent).slideToggle(200);
                $(accHeader).find('.arrow-icon').css('transform', isOpening ? 'rotate(180deg)' : 'rotate(0deg)');
            };
        }

        // Add to cart click handler
        const addBtnAction = document.getElementById('csp-modal-add-btn');
        const modalErrorMsg = document.getElementById('csp-modal-error-msg');

        function showModalError(msg) {
            if (modalErrorMsg) {
                modalErrorMsg.textContent = msg;
                modalErrorMsg.style.display = 'block';
            }
        }
        function clearModalError() {
            if (modalErrorMsg) {
                modalErrorMsg.textContent = '';
                modalErrorMsg.style.display = 'none';
            }
        }
        // Helper: strip all HTML tags from a string
        function stripHtml(html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }

        if (addBtnAction) {
            addBtnAction.onclick = function(e) {
                e.preventDefault();
                clearModalError();
                const card = productModal.activeCard;
                if (!card) return;

                // --- Client-side validation for variable products ---
                // Check that every native <select> inside the product form has a value
                const nativeSels = card.querySelectorAll('.variation-select-wrapper select');
                let missingAttr = false;
                nativeSels.forEach(function(sel) {
                    if (!sel.value || sel.value === '') missingAttr = true;
                });
                if (missingAttr) {
                    const msg = (customShopData && customShopData.i18n && customShopData.i18n.selectOptions)
                        ? customShopData.i18n.selectOptions
                        : 'Please select all product options before adding to cart.';
                    showModalError(msg);
                    return;
                }

                // Check variation_id has been resolved by WC variation script
                const varIdInput = card.querySelector('input.variation_id');
                if (nativeSels.length > 0 && (!varIdInput || !parseInt(varIdInput.value))) {
                    const msg = (customShopData && customShopData.i18n && customShopData.i18n.selectValidOptions)
                        ? customShopData.i18n.selectValidOptions
                        : 'Please select valid product options.';
                    showModalError(msg);
                    return;
                }

                // Sync quantity
                const realQtyInput = card.querySelector('input.qty');
                if (realQtyInput) {
                    realQtyInput.value = qtyInput ? qtyInput.value : '1';
                }

                // Sync Special Note text metadata
                const notesInput = document.getElementById('csp-modal-notes-input');
                if (notesInput) {
                    let cardNoteInput = card.querySelector('input[name="special_request_note"]');
                    if (!cardNoteInput) {
                        cardNoteInput = document.createElement('input');
                        cardNoteInput.type = 'hidden';
                        cardNoteInput.name = 'special_request_note';
                        const cardForm = card.querySelector('form');
                        if (cardForm) cardForm.appendChild(cardNoteInput);
                    }
                    cardNoteInput.value = notesInput.value;
                }

                // AJAX Form Submit
                const form = card.querySelector('form');
                if (form) {
                    const formData = new FormData(form);
                    const params = new URLSearchParams();
                    for (const [key, value] of formData.entries()) {
                        if (key === 'add-to-cart') {
                            params.append('product_id', value);
                        } else {
                            params.append(key, value);
                        }
                    }
                    
                    // Resolve variable product variation ID
                    const varIdInput = card.querySelector('input.variation_id');
                    const resolvedVarId = varIdInput ? parseInt(varIdInput.value) : 0;
                    
                    if (resolvedVarId) {
                        // For variable products, WooCommerce's AJAX handler expects the variation ID in product_id
                        params.set('product_id', resolvedVarId);
                        if (!params.has('variation_id')) {
                            params.append('variation_id', resolvedVarId);
                        }
                    } else if (!params.has('product_id')) {
                        const productId = card.getAttribute('data-id');
                        params.append('product_id', productId);
                    }
                    params.append('action', 'woocommerce_add_to_cart');

                    // Set button to pulsing state
                    const originalHtml = addBtnAction.innerHTML;
                    const addingText = (customShopData && customShopData.i18n && customShopData.i18n.adding) ? customShopData.i18n.adding : 'Adding...';
                    addBtnAction.innerHTML = '<span class="btn-text">' + addingText + '</span>';
                    addBtnAction.classList.add('csp-btn-pulsing');

                    fetch(customShopData.ajaxUrl || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: params
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Check for WooCommerce error response
                        if (data.error || data.success === false ||
                            (data.notices && data.notices.some && data.notices.some(n => n.notice_type === 'error'))) {
                            addBtnAction.innerHTML = originalHtml;
                            addBtnAction.classList.remove('csp-btn-pulsing');
                            // Build a clean error message by stripping HTML product name
                            let errMsg = '';
                            if (data.notices && data.notices.length) {
                                errMsg = data.notices
                                    .filter(n => n.notice_type === 'error')
                                    .map(n => stripHtml(n.notice))
                                    .join(' ');
                            } else if (typeof data.error === 'string') {
                                errMsg = stripHtml(data.error);
                            }
                            if (!errMsg) {
                                errMsg = (customShopData && customShopData.i18n && customShopData.i18n.errorAddToCart)
                                    ? customShopData.i18n.errorAddToCart
                                    : 'Error adding to cart. Please try again.';
                            }
                            showModalError(errMsg);
                            return;
                        }

                        // Success state
                        const addedText = (customShopData && customShopData.i18n && customShopData.i18n.added) ? customShopData.i18n.added : '✓ Added!';
                        addBtnAction.innerHTML = '<span class="btn-text">' + addedText + '</span>';
                        addBtnAction.classList.remove('csp-btn-pulsing');
                        addBtnAction.classList.add('csp-btn-success');

                        let fragments = data.fragments || (data.data && data.data.fragments);
                        if (fragments) {
                            updateCartDrawerFragments(fragments);
                        }

                        setTimeout(function() {
                            // Reset button
                            addBtnAction.innerHTML = originalHtml;
                            addBtnAction.classList.remove('csp-btn-success');
                            
                            // Close product modal
                            productModal.classList.remove('active');
                            document.body.classList.remove('csp-modal-open');

                            // Slide out cart drawer automatically
                            if (standaloneDrawer) {
                                standaloneDrawer.classList.add('active');
                            }
                        }, 1000);
                    })
                    .catch(err => {
                        console.error(err);
                        addBtnAction.innerHTML = originalHtml;
                        addBtnAction.classList.remove('csp-btn-pulsing');
                        const errAddMsg = (customShopData && customShopData.i18n && customShopData.i18n.errorAddToCartRetry)
                            ? customShopData.i18n.errorAddToCartRetry
                            : ((customShopData && customShopData.i18n && customShopData.i18n.errorAddToCart)
                                ? customShopData.i18n.errorAddToCart
                                : 'Error adding to cart. Please try again.');
                        showModalError(errAddMsg);
                    });
                }
            };
        }
    }

    function triggerModalPriceUpdate() {
        if (productModal && productModal.activeCard) {
            const card = productModal.activeCard;
            const variationsDataAttr = card.getAttribute('data-product_variations');
            const variationsData = variationsDataAttr ? JSON.parse(variationsDataAttr) : [];
            updateModalPrice(card, variationsData);
        }
    }

    // Live Search Filter
    $(document).on('input', '#custom-live-search', function() {
        const val = $(this).val().toLowerCase();
        $('.custom-product-item').each(function() {
            const title = $(this).find('.product-item-title').text().toLowerCase();
            if (title.indexOf(val) > -1) {
                $(this).removeClass('csp-hidden');
            } else {
                $(this).addClass('csp-hidden');
            }
        });
        
        // Hide empty subcategories
        $('.subcategory-section').each(function() {
            const visibleProducts = $(this).find('.custom-product-item:not(.csp-hidden)').length;
            if (visibleProducts > 0) {
                $(this).removeClass('csp-hidden');
            } else {
                $(this).addClass('csp-hidden');
            }
        });

        // Hide empty parent categories
        $('.category-section').each(function() {
            const visibleProducts = $(this).find('.custom-product-item:not(.csp-hidden)').length;
            if (visibleProducts > 0) {
                $(this).removeClass('csp-hidden');
            } else {
                $(this).addClass('csp-hidden');
            }
        });
    });

});
