<template>
    <k-field :input="id" v-bind="$props" class="k-mapnotator-field" :style="'--mapnotator-color:'+ color">
        <div class="k-input k-mapnotator-input" data-theme="field">
            <form class="k-mapnotator-input-inner" @submit.prevent.stop="getCoordinates">
                <k-text-input 
                    ref="input"
                    :id="id"
                    :value="location"
                    :placeholder="$t('mapnotator.placeholder')"
                    @input="onLocationInput" />
                
                <button :class="[{ disabled: !location.length }]"
                        :disabled="!location.length"
                        type="submit">
                    <k-icon type="mapnotator-locate" />
                    {{ $t("mapnotator.locate") }}
                </button>
            </form>
            <k-picklist-dropdown v-if="autocomplete" ref="dropdown" 
                  :class="['k-mapnotator-dropdown', {'hidden': !dropdownOptions.length && (!location.length || forceHideDropdown)}]" :options="dropdownOptions" :search="false" @input="select" />
        </div>

        <div class="k-mapnotator-container">
            <div class="map-container" :data-size="size">
                <div :id="mapId" ref="map" class="map"></div>
            </div>
        </div>
    </k-field>
</template>

<script>
import { useId, markRaw } from "vue";
import L from "leaflet";
import "@geoman-io/leaflet-geoman-free";


export default {
    data() {
        return {
            map: null,
            tileLayer: null,
            customLayer: null,
            location: '',
            dropdownOptions: [],
            limit: 1,
            forceHideDropdown: false,
            lastEmittedValue: null,
            searchRequest: 0,
        }
    },
    props: {
        tiles:        String,
        tilesKey:     String,
        zoom:         Object,
        center:       Object,
        mapbox:       Object,
        shapes:       [Boolean, Array],
        tools:        [Boolean, Array],
        size:         String,
        geocoding:    [String, Boolean],
        autocomplete: Boolean,
        language:     [String, Boolean],
        color:        String,

        // general options
        id: {
            type: [Number, String],
            default: () => useId(),
        },
        parent: String,
        name: [String, Number],
        label:     String,
        disabled:  Boolean,
        help:      String,
        value:     [Array, Object],
        required:  Boolean,
        type:      String
    },
    emits: ["input"],
    computed: {
        mapId() {
            return "map-" + this.id;
        },
        icon() {
            let icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 142"><path fill="'+ this.color +'" d="M60,0A59.68,59.68,0,0,0,27.37,9.62c-.31.19-.61.39-.92.6A55.74,55.74,0,0,0,7.57,30.71,59.75,59.75,0,0,0,2.63,77.35q.45,1.53,1,3a83.85,83.85,0,0,0,20.53,32.08c9.72,9.51,20.75,17.68,31.2,26.45l3.7,3.09h2c4.7-3.67,9.69-7,14-11.1,9.2-8.69,18.47-17.37,26.92-26.77.49-.54,1-1.09,1.44-1.64l.3-.36c.43-.54.86-1.06,1.28-1.6s.9-1.16,1.34-1.76,1-1.31,1.4-2,.8-1.21,1.19-1.82c.06-.09.12-.18.17-.27.36-.56.72-1.14,1.06-1.72l.08-.13c.29-.5.58-1,.86-1.51.46-.82.91-1.64,1.34-2.48a58.77,58.77,0,0,0,5.35-13s0,0,0,0A59.85,59.85,0,0,0,60,0Zm0,37.55a22.23,22.23,0,1,1-22.2,22.33A22.15,22.15,0,0,1,60,37.55Z"/></svg>'
            let iconUrl = 'data:image/svg+xml;base64,' + btoa(icon);

            return L.icon({
                iconUrl: iconUrl,
                iconSize: [26, 31],
                iconAnchor: [13, 31]
            })
        },
        defaultCoords() {
            return [this.center.lat, this.center.lon]
        },
        tileUrl() {
            if(this.tiles == "mapbox" || this.tiles == "mapbox.custom") {
                return (
                    "https://api.mapbox.com/styles/v1/" +
                    this.mapbox.id +
                    "/tiles/256/{z}/{x}/{y}" +
                    (L.Browser.retina ? "@2x" : "") +
                    "?access_token=" +
                    this.mapbox.token
                );
            } else if(this.tiles == "wikimedia") {
                return (
                    "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}" +
                    (L.Browser.retina ? "@2x.png" : ".png")
                );
            } else if(this.tiles == "openstreetmap") {
                return "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
            } else if(this.tiles == "light_all" || this.tiles == "voyager") {
                return (
                    "https://basemaps.cartocdn.com/rastertiles/" +
                    this.tiles +
                    "/{z}/{x}/{y}" +
                    (L.Browser.retina ? "@2x.png" : ".png") +
                    (this.tilesKey ? "?key=" + encodeURIComponent(this.tilesKey) : "")
                );
            } else return "";
        },
        attribution() {
            if(this.tiles == "mapbox" || this.tiles == "mapbox.custom") {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://www.mapbox.com/">Mapbox</a>';
            } else if(this.tiles == "wikimedia") {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://maps.wikimedia.org" target="_blank" rel="noreferrer">Wikimedia</a>';
            } else if(this.tiles == "openstreetmap") {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>';
            } else if(this.tiles == "light_all" || this.tiles == "voyager") {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://carto.com/attribution" target="_blank" rel="noreferrer">CARTO</a>';
            } else
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>';
        },
        searchQuery() {
            if(this.geocoding == "nominatim") {
                let languageParam = this.language
                    ? "&accept-language=" + this.language
                    : "";
                return (
                    "https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=1&q=" +
                    encodeURIComponent(this.location) +
                    languageParam
                );
            } else if(this.geocoding == "mapbox") {
                let languageParam = this.language
                    ? "&language=" + this.language
                    : "";
                return (
                    "https://api.mapbox.com/geocoding/v5/mapbox.places/" +
                    encodeURIComponent(this.location) +
                    ".json?types=address,country,postcode,place,locality&limit=" +
                    this.limit +
                    "&access_token=" +
                    this.mapbox.token +
                    languageParam
                );
            } else return "";
        },
    },
    mounted() {
        this.initMap()
    },
    beforeUnmount() {
        this.searchRequest++;
        const map = this.map;
        this.map = null;
        if(map) {
            map.remove();
            map.off();
        }
        this.customLayer = null;
        this.tileLayer = null;
    },
    watch: {
        value(value) {
            if(!this.map || !this.customLayer) return;

            if(JSON.stringify(value) === this.lastEmittedValue) return;

            this.lastEmittedValue = null;
            this.syncLayers();
        },
    },
    methods: {
        initMap() {
            this.map = markRaw(L.map(this.$refs.map, {
                minZoom: this.zoom.min,
                maxZoom: this.zoom.max,
            }).setView(this.defaultCoords, this.zoom.default));

            this.tileLayer = markRaw(L.tileLayer(this.tileUrl, {
                attribution: this.attribution,
                referrerPolicy: "strict-origin-when-cross-origin",
            }));

            this.map.addLayer(this.tileLayer);
            this.initGeoman();
        },
        initGeoman() {
            this.customLayer = markRaw(L.featureGroup())
            this.customLayer.addTo(this.map)

            this.map.pm.addControls({
              position: 'topleft',
              drawMarker:       this.shapes && this.shapes.includes('marker'),
              drawCircleMarker: this.shapes && this.shapes.includes('circlemarker'),
              drawPolyline:     this.shapes && this.shapes.includes('polyline'),
              drawRectangle:    this.shapes && this.shapes.includes('rectangle'),
              drawPolygon:      this.shapes && this.shapes.includes('polygon'),
              drawCircle:       this.shapes && this.shapes.includes('circle'),
              editMode:         this.tools  && this.tools.includes('edit'),
              dragMode:         this.tools  && this.tools.includes('drag'),
              cutPolygon:       this.tools  && this.tools.includes('cut'),
              removalMode:      this.tools  && this.tools.includes('remove'),
              rotateMode:       this.tools  && this.tools.includes('rotate'),
            });

            this.map.pm.setLang(this.$panel.language.code);

            this.map.pm.setGlobalOptions({
                layerGroup: this.customLayer,
                markerStyle: {
                    icon: this.icon
                },
                templineStyle: {
                    color: this.color,
                },
                hintlineStyle: {
                    color: this.color,
                    dashArray: [5, 5],
                },
                pathOptions: {
                    color: this.color,
                    fillColor: this.color,
                    fillOpacity: 0.2,
                },
                finishOnDoubleClick: true,
            })

            this.syncLayers()
            let bounds = this.customLayer.getBounds()
            if(bounds.isValid()) {
                this.map.fitBounds(bounds)
            }

            this.map.on('pm:create', (e) => {
                this.updateValue()
                e.layer.on('pm:edit', (e) => {
                    this.updateValue()
                })
            })
            this.map.on('pm:remove', ({ layer }) => {
                this.updateValue()
            })
        },
        syncLayers() {
            if(!this.customLayer) return;
            this.customLayer.clearLayers()

            if(this.value && this.value.features && this.value.features.length) {
                let _this = this

                L.geoJSON(this.value.features, {
                    pointToLayer: (feature, latlng) => {
                        if(feature.properties?.shape == 'Circle') {
                            return new L.Circle(latlng, feature.properties.radius * 1);
                        }
                        else if(feature.properties?.shape == 'CircleMarker') {
                            return new L.CircleMarker(latlng);
                        }
                        else {
                            return new L.Marker(latlng, {icon: this.icon});
                        }
                    },
                    onEachFeature: (feature, layer) => {
                        layer.on('pm:edit', (e) => _this.updateValue());
                    },
                    style: (feature) => {
                        return {
                            color: this.color,
                            fillColor: this.color,
                            fillOpacity: 0.2,
                        }
                    }
                }).addTo(this.customLayer)
            }
        },
        onLocationInput(location) {
            this.location = location;
            this.forceHideDropdown = false;
            const request = ++this.searchRequest;

            if(!this.autocomplete || this.geocoding !== "mapbox" || !location.length) {
                this.dropdownOptions = [];
                this.$refs.dropdown?.close();
                return;
            }

            const fetchInit = {
                referrerPolicy: "strict-origin-when-cross-origin",
            };

            this.limit = 5;
            fetch(this.searchQuery, fetchInit)
                .then((response) => response.json())
                .then((response) => {
                    if(!this.map || request !== this.searchRequest) return;

                    if(response.features?.length) {
                        let suggestions = response.features.filter(
                            (el) => el.relevance == 1
                        );
                        this.dropdownOptions = suggestions.map((el) => {
                            return {
                                text: el.place_name,
                                value: el.place_name,
                            };
                        });
                        this.$refs.dropdown?.open();
                    } else {
                        this.dropdownOptions = [];
                        this.$refs.dropdown?.close();
                    }
                })
                .catch(() => {
                    if(!this.map || request !== this.searchRequest) return;
                    this.showError(this.$t("mapnotator.error"));
                    this.dropdownOptions = [];
                });
        },
        select(values) {
            this.location = values[0];
            this.dropdownOptions = []
            this.forceHideDropdown = true
            this.getCoordinates();
        },
        getCoordinates(e) {
            if(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const request = ++this.searchRequest;
            this.$refs.dropdown?.close();
            this.dropdownOptions = [];
            this.forceHideDropdown = true;
            this.limit = 1;

            if(this.isLatLon(this.location)) {
                const coords = this.location.split(",").map((coord) => parseFloat(coord.trim()));
                this.moveToCoordinates(coords);
                this.forceHideDropdown = false;
                return true;
            }

            if(!this.geocoding || !this.location.length) {
                this.forceHideDropdown = false;
                return;
            }

            const fetchInit = { referrerPolicy: "strict-origin-when-cross-origin" };

            fetch(this.searchQuery, fetchInit)
                .then((response) => response.json())
                .then((response) => {
                    if(!this.map || request !== this.searchRequest) return;

                    let coords = null;
                    if(this.geocoding == "nominatim" && response.length) {
                        coords = [parseFloat(response[0].lat), parseFloat(response[0].lon)];
                    } else if(this.geocoding == "mapbox" && response.features?.length) {
                        coords = [parseFloat(response.features[0].center[1]), parseFloat(response.features[0].center[0])];
                    }

                    if(coords && coords.every(Number.isFinite)) {
                        this.moveToCoordinates(coords);
                        this.location = "";
                    } else {
                        this.showError(this.$t("mapnotator.error"));
                    }
                    this.forceHideDropdown = false;
                })
                .catch(() => {
                    if(!this.map || request !== this.searchRequest) return;
                    this.showError(this.$t("mapnotator.error"));
                    this.forceHideDropdown = false;
                });
        },
        isLatLon(str) {
            const regexExp =
                /^((\-?|\+?)?\d+(\.\d+)?),\s*((\-?|\+?)?\d+(\.\d+)?)$/gi;
            return regexExp.test(str);
        },
        moveToCoordinates(coords) {
            if(this.map) this.map.panTo(coords);
        },
        updateValue() {
            if(!this.map) return;
            const value = this.getValue();
            this.lastEmittedValue = JSON.stringify(value);
            this.$emit("input", value);
        },
        getValue() {
            let layers = this.map.pm.getGeomanLayers(true)

            var value    = layers.toGeoJSON()
            var features = []

            layers.eachLayer(layer => {
                features.push(this.layerToJson(layer))
            })

            value.features = features

            return value
        },
        layerToJson(layer) {
            const json = layer.toGeoJSON()

            if(layer instanceof L.Circle) {
                json.properties.shape = 'Circle'
                json.properties.radius = layer.getRadius()
            }
            else if(layer instanceof L.CircleMarker) {
                json.properties.shape = 'CircleMarker'
            }

            return json
        },
        showError(text) {
            this.$panel.dialog.open({
                component: "k-text-dialog",
                props: {
                    text: text,
                    cancelButton: this.$t("close"),
                    submitButton: false,
                },
            });
        },
    },
};
</script>

<style lang="scss">
    @use "../assets/css/styles.scss";
</style>
