<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl  = in_array(substr($locale, 0, 2), ['ar','fa','ur','he']);
@endphp

<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name') }} - @tr('SaaS')</title>

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Arabic Fonts --}}
    @if($isRtl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    @endif
    
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>

    <style>
        [x-cloak]{display:none!important}
        
        @if($isRtl)
        /* ✅ تحسين الخطوط العربية */
        body, html {
            font-family: 'Cairo', 'Tajawal', 'Segoe UI', Tahoma, Arial, sans-serif;
            font-weight: 400;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* تحسين العناوين */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            line-height: 1.4;
        }
        
        /* تحسين الأزرار */
        button, .btn {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-weight: 500;
            letter-spacing: 0;
        }
        
        /* تحسين النصوص الصغيرة */
        .text-xs, .text-sm {
            line-height: 1.6;
        }
        
        /* تحسين المدخلات */
        input, textarea, select {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-weight: 400;
        }
        
        /* تحسين الروابط */
        a {
            font-weight: 500;
        }
        @endif
    </style>

    {{-- Sidebar styles --}}
    @include('saas::sidebar.components.styles')

    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-900">

    {{-- Toast Notifications --}}
    <x-ui.toast />
    <x-ui.flash-toast />

    {{-- Logout Confirmation Dialog --}}
    <x-ui.confirm-dialog
        id="logout"
        :title="tr('Confirm Logout')"
        :message="tr('Are you sure you want to logout? You will need to login again to access your account.')"
        :confirmText="tr('Logout')"
        :cancelText="tr('Cancel')"
        type="warning"
        icon="fa-sign-out-alt"
        confirmAction="@logoutConfirmed"
    />

    <div class="saas-shell">
        {{-- Sidebar --}}
        @include('saas::sidebar.sidebar')

        {{-- Page Content --}}
        <main class="main-content" id="mainContent">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    {{-- Map Picker Alpine Component --}}
    <script>
        function registerMapPickerModal() {
            if (typeof Alpine === 'undefined' || !window.Alpine) {
                // Wait for Alpine to be available
                setTimeout(registerMapPickerModal, 50);
                return;
            }

            // Check if already registered to prevent duplicate registration
            if (Alpine.data('mapPickerModal')) {
                return;
            }

            // Register the component
            Alpine.data('mapPickerModal', () => ({
                mapModalOpen: false,
                map: null,
                marker: null,
                accuracyCircle: null,
                selectedLat: null,
                selectedLng: null,
                isLoading: false,
                isGettingLocation: false,
                
                openModal() {
                    this.mapModalOpen = true;
                    // Wait for modal to be fully visible before initializing map
                    this.$nextTick(() => {
                        // Clear any existing map first
                        if (this.map) {
                            this.map.remove();
                            this.map = null;
                            this.marker = null;
                        }
                        // Small delay to ensure modal is fully rendered
                        setTimeout(() => {
                            this.initMap();
                        }, 100);
                    });
                },
                
                showToast(type, title, message = '') {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: type,
                            title: title,
                            message: message,
                            timeout: 5000
                        }
                    }));
                },
                
                getCurrentLocation() {
                    // Check if geolocation is supported
                    if (!navigator.geolocation) {
                        this.showToast('error', '{{ tr("Geolocation is not supported by your browser") }}');
                        return;
                    }
                    
                    // Check if modal is open (map must be initialized)
                    if (!this.mapModalOpen || !this.map) {
                        // If modal is not open, open it first
                        this.openModal();
                        // Wait for map to initialize
                        setTimeout(() => {
                            this.requestLocation();
                        }, 300);
                    } else {
                        // Map is already initialized, request location directly
                        this.requestLocation();
                    }
                },
                
                requestLocation() {
                    this.isGettingLocation = true;
                    
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            // Set selected coordinates
                            this.selectedLat = lat.toFixed(6);
                            this.selectedLng = lng.toFixed(6);
                            
                            if (this.map) {
                                // Center map on user location
                                this.map.setView([lat, lng], 15);
                                
                                // Remove existing accuracy circle if any
                                if (this.accuracyCircle) {
                                    this.map.removeLayer(this.accuracyCircle);
                                }
                                
                                // Add or update marker
                                if (this.marker) {
                                    this.marker.setLatLng([lat, lng]);
                                } else {
                                    this.marker = L.marker([lat, lng], {
                                        draggable: true
                                    }).addTo(this.map);
                                    
                                    // Handle marker drag
                                    this.marker.on('dragend', (e) => {
                                        const { lat, lng } = e.target.getLatLng();
                                        this.selectedLat = lat.toFixed(6);
                                        this.selectedLng = lng.toFixed(6);
                                    });
                                }
                                
                                // Add circle to show accuracy
                                if (position.coords.accuracy) {
                                    this.accuracyCircle = L.circle([lat, lng], {
                                        radius: position.coords.accuracy,
                                        fillColor: '#3388ff',
                                        fillOpacity: 0.2,
                                        color: '#3388ff',
                                        weight: 1
                                    }).addTo(this.map);
                                }
                                
                                // Invalidate size to ensure map renders correctly
                                setTimeout(() => {
                                    if (this.map) {
                                        this.map.invalidateSize();
                                    }
                                }, 100);
                            }
                            
                            this.isGettingLocation = false;
                        },
                        (error) => {
                            this.isGettingLocation = false;
                            let errorTitle = '{{ tr("Error") }}';
                            let errorMessage = '{{ tr("Error getting your location") }}';
                            
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorTitle = '{{ tr("Location Access Denied") }}';
                                    errorMessage = '{{ tr("Please enable location permissions in your browser settings.") }}';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorTitle = '{{ tr("Location Unavailable") }}';
                                    errorMessage = '{{ tr("Location information unavailable. Please check your GPS settings.") }}';
                                    break;
                                case error.TIMEOUT:
                                    errorTitle = '{{ tr("Request Timeout") }}';
                                    errorMessage = '{{ tr("Location request timed out. Please try again.") }}';
                                    break;
                            }
                            
                            this.showToast('error', errorTitle, errorMessage);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        }
                    );
                },
                
                closeModal() {
                    this.mapModalOpen = false;
                    if (this.map) {
                        this.map.remove();
                        this.map = null;
                        this.marker = null;
                        this.accuracyCircle = null;
                    }
                    this.selectedLat = null;
                    this.selectedLng = null;
                },
                
                initMap() {
                    // Wait a bit to ensure the modal is fully rendered
                    setTimeout(() => {
                        const mapElement = document.getElementById('mapPicker');
                        if (!mapElement) {
                            console.error('Map element not found');
                            return;
                        }
                        
                        // Get current coordinates from Livewire
                        const latInput = document.querySelector('input[wire\\:model\\.defer="lat"]');
                        const lngInput = document.querySelector('input[wire\\:model\\.defer="lng"]');
                        
                        const defaultLat = latInput && latInput.value ? parseFloat(latInput.value) : 15.3694;
                        const defaultLng = lngInput && lngInput.value ? parseFloat(lngInput.value) : 44.1910;
                        
                        // Check if Leaflet is loaded
                        if (typeof L === 'undefined') {
                            console.error('Leaflet is not loaded');
                            return;
                        }
                        
                        // Initialize map
                        this.map = L.map('mapPicker', {
                            zoomControl: true
                        }).setView([defaultLat, defaultLng], 13);
                        
                        // Add tile layer (OpenStreetMap)
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(this.map);
                        
                        // Invalidate size to ensure map renders correctly
                        setTimeout(() => {
                            if (this.map) {
                                this.map.invalidateSize();
                            }
                        }, 100);
                        
                        // Add marker if coordinates exist
                        if (defaultLat && defaultLng) {
                            this.marker = L.marker([defaultLat, defaultLng], {
                                draggable: true
                            }).addTo(this.map);
                            
                            this.selectedLat = defaultLat.toFixed(6);
                            this.selectedLng = defaultLng.toFixed(6);
                        }
                        
                        // Handle map click
                        this.map.on('click', (e) => {
                            const { lat, lng } = e.latlng;
                            
                            if (this.marker) {
                                this.marker.setLatLng([lat, lng]);
                            } else {
                                this.marker = L.marker([lat, lng], {
                                    draggable: true
                                }).addTo(this.map);
                            }
                            
                            this.selectedLat = lat.toFixed(6);
                            this.selectedLng = lng.toFixed(6);
                        });
                        
                        // Handle marker drag
                        if (this.marker) {
                            this.marker.on('dragend', (e) => {
                                const { lat, lng } = e.target.getLatLng();
                                this.selectedLat = lat.toFixed(6);
                                this.selectedLng = lng.toFixed(6);
                            });
                        }
                    }, 200);
                },
                
                async reverseGeocode(lat, lng) {
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`,
                            {
                                headers: {
                                    'User-Agent': 'AthkaHR/1.0'
                                }
                            }
                        );
                        
                        if (!response.ok) {
                            throw new Error('Geocoding failed');
                        }
                        
                        const data = await response.json();
                        
                        if (data && data.address) {
                            const address = data.address;
                            
                            return {
                                country: address.country || '',
                                city: address.city || address.town || address.village || address.municipality || address.county || '',
                                region: address.state || address.region || address.province || '',
                                address_line: [
                                    address.road,
                                    address.house_number,
                                    address.building,
                                    address.neighbourhood
                                ].filter(Boolean).join(', ') || data.display_name || '',
                                postal_code: address.postcode || ''
                            };
                        }
                    } catch (error) {
                        console.error('Reverse geocoding error:', error);
                        return null;
                    }
                    
                    return null;
                },
                
                clearLocation() {
                    // Clear selected coordinates
                    this.selectedLat = null;
                    this.selectedLng = null;
                    
                    // Clear Livewire model
                    let wireComponent = null;
                    
                    if (this.$wire) {
                        wireComponent = this.$wire;
                    } else {
                        // Fallback: find Livewire component
                        const latInput = document.querySelector('input[wire\\:model\\.defer="lat"]');
                        if (latInput) {
                            const wireId = latInput.closest('[wire\\:id]')?.getAttribute('wire:id');
                            if (wireId && window.Livewire) {
                                wireComponent = window.Livewire.find(wireId);
                            }
                        }
                    }
                    
                    if (wireComponent) {
                        wireComponent.set('lat', '');
                        wireComponent.set('lng', '');
                    }
                    
                    // Clear marker from map if modal is open
                    if (this.map && this.marker) {
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }
                    
                    // Clear accuracy circle if any
                    if (this.accuracyCircle) {
                        this.map.removeLayer(this.accuracyCircle);
                        this.accuracyCircle = null;
                    }
                    
                    // Close modal if open
                    if (this.mapModalOpen) {
                        this.closeModal();
                    }
                },
                
                async confirmSelection() {
                    if (this.selectedLat && this.selectedLng) {
                        // Show loading state
                        this.isLoading = true;
                        
                        // Get address from coordinates
                        const addressData = await this.reverseGeocode(this.selectedLat, this.selectedLng);
                        
                        // Find Livewire component
                        let wireComponent = null;
                        
                        if (this.$wire) {
                            wireComponent = this.$wire;
                        } else {
                            // Fallback: find Livewire component
                            const latInput = document.querySelector('input[wire\\:model\\.defer="lat"]');
                            if (latInput) {
                                const wireId = latInput.closest('[wire\\:id]')?.getAttribute('wire:id');
                                if (wireId && window.Livewire) {
                                    wireComponent = window.Livewire.find(wireId);
                                }
                            }
                        }
                        
                        if (wireComponent) {
                            // Set coordinates first
                            wireComponent.set('lat', this.selectedLat);
                            wireComponent.set('lng', this.selectedLng);
                            
                            // Set address fields if available
                            if (addressData) {
                                if (addressData.country) {
                                    wireComponent.set('country', addressData.country);
                                }
                                if (addressData.city) {
                                    wireComponent.set('city', addressData.city);
                                }
                                if (addressData.region) {
                                    wireComponent.set('region', addressData.region);
                                }
                                if (addressData.address_line) {
                                    wireComponent.set('address_line', addressData.address_line);
                                }
                                if (addressData.postal_code) {
                                    wireComponent.set('postal_code', addressData.postal_code);
                                }
                            }
                        }
                        
                        // Hide loading state
                        this.isLoading = false;
                        this.closeModal();
                    }
                }
            }));
        }

        // Register immediately if Alpine is available, or wait for it
        if (typeof Alpine !== 'undefined' && window.Alpine) {
            registerMapPickerModal();
        } else {
            // Wait for Alpine to initialize
            document.addEventListener('alpine:init', () => {
                registerMapPickerModal();
            });
            // Also try to register after a short delay in case the event already fired
            setTimeout(() => {
                if (typeof Alpine !== 'undefined' && window.Alpine && !Alpine.data('mapPickerModal')) {
                    registerMapPickerModal();
                }
            }, 100);
        }
    </script>
    
    @stack('scripts')

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleBtn');
            const mobileToggle = document.getElementById('mobileToggle');

            if (!sidebar) return;

            // collapse/expand
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('collapsed');
                });
            }

            // mobile open/close
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                });
            }

            // close when clicking outside on mobile
            document.addEventListener('click', function (e) {
                if (window.innerWidth < 769) {
                    const isClickInsideSidebar = sidebar.contains(e.target);
                    const isClickOnMobileToggle = mobileToggle && mobileToggle.contains(e.target);

                    if (!isClickInsideSidebar && !isClickOnMobileToggle && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                    }
                }
            });

            // ESC close
            window.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') sidebar.classList.remove('open');
            });

            // resize
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 769) {
                    sidebar.classList.remove('open');
                }
            });
        })();

        // ✅ Fix: Re-initialize Alpine.js after Livewire components load (for Edit mode in modal)
        document.addEventListener('livewire:init', () => {
            if (typeof Alpine !== 'undefined' && window.Alpine && !Alpine.data('mapPickerModal')) {
                registerMapPickerModal();
            }
        });

        document.addEventListener('livewire:update', () => {
            if (typeof Alpine !== 'undefined' && window.Alpine) {
                // Ensure mapPickerModal is registered
                if (!Alpine.data('mapPickerModal')) {
                    registerMapPickerModal();
                }
                // Re-initialize Alpine for new DOM elements loaded by Livewire
                setTimeout(() => {
                    const editModalContent = document.querySelector('[x-show*="editMode"]');
                    if (editModalContent && window.Alpine && typeof window.Alpine.initTree === 'function') {
                        window.Alpine.initTree(editModalContent);
                    }
                }, 200);
            }
        });

        // ✅ View Location Modal (للعرض فقط)
        function registerViewLocationModal() {
            if (typeof Alpine === 'undefined' || !window.Alpine) {
                return;
            }
            
            Alpine.data('viewLocationModal', (lat, lng) => ({
                mapModalOpen: false,
                map: null,
                marker: null,
                
                openModal() {
                    this.mapModalOpen = true;
                    document.body.style.overflow = 'hidden';
                    
                    // Initialize map after modal opens
                    this.$nextTick(() => {
                        this.initMap(lat, lng);
                    });
                },
                
                closeModal() {
                    this.mapModalOpen = false;
                    document.body.style.overflow = '';
                    
                    // Clean up map
                    if (this.map) {
                        this.map.remove();
                        this.map = null;
                        this.marker = null;
                    }
                },
                
                initMap(defaultLat, defaultLng) {
                    if (!window.L || !this.$refs.mapContainer) {
                        return;
                    }
                    
                    // Initialize map
                    this.map = L.map(this.$refs.mapContainer, {
                        center: [defaultLat, defaultLng],
                        zoom: 15,
                        zoomControl: true
                    });
                    
                    // Add tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);
                    
                    // Add marker at location
                    this.marker = L.marker([defaultLat, defaultLng], {
                        draggable: false
                    }).addTo(this.map);
                    
                    // Add popup with coordinates
                    const locationText = @json(tr('Location'));
                    this.marker.bindPopup(`
                        <div class="text-center">
                            <strong>${locationText}</strong><br>
                            <small>Lat: ${defaultLat}<br>Lng: ${defaultLng}</small>
                        </div>
                    `).openPopup();
                    
                    // Invalidate size to ensure map renders correctly
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 100);
                }
            }));
        }

        // Register viewLocationModal
        if (typeof Alpine !== 'undefined' && window.Alpine) {
            registerViewLocationModal();
        } else {
            document.addEventListener('alpine:init', () => {
                registerViewLocationModal();
            });
            setTimeout(() => {
                if (typeof Alpine !== 'undefined' && window.Alpine && !Alpine.data('viewLocationModal')) {
                    registerViewLocationModal();
                }
            }, 100);
        }
    </script>

</body>
</html>
