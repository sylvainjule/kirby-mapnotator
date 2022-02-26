<template>
    <k-field :input="_uid" v-bind="$props" class="k-mapnotator-field" :style="'--mapnotator-color:'+ color">
        <div class="k-input k-mapnotator-input" data-theme="field">
            <input ref="input" v-model="location" class="k-text-input" :placeholder="$t('mapnotator.placeholder')" @input="onLocationInput">
            <button :class="[{disabled: !location.length}]" @click="getCoordinates"><svg><use href="#icon-mapnotator-locate" /></svg> {{ $t('mapnotator.locate') }}</button>
            <k-dropdown-content v-if="autocomplete" ref="dropdown">
                <k-dropdown-item v-for="(option, index) in dropdownOptions"
                                 :key="index"
                                 @click="select(option)"
                                 @keydown.native.enter.prevent="select(option)"
                                 @keydown.native.space.prevent="select(option)">
                    <span v-html="option.name" />
                    <span class="k-location-type" v-html="option.type" />
                </k-dropdown-item>
            </k-dropdown-content>
        </div>
        <k-dialog ref="dialog" @close="error = ''">
            <k-text>{{ error }}</k-text>
            <k-button-group slot="footer">
                <k-button icon="check" @click="$refs.dialog.close()">
                    {{ $t("confirm") }}
                </k-button>
            </k-button-group>
        </k-dialog>

        <div class="k-mapnotator-container">
            <div class="map-container" :data-size="size">
                <div :id="mapId" class="map"></div>
            </div>
        </div>
    </k-field>
</template>

<script>
import L from 'leaflet'
import        '@geoman-io/leaflet-geoman-free'


export default {
    data() {
        return {
            map: null,
            tileLayer: null,
            customLayer: null,
            location: '',
            error: '',
            dropdownOptions: [],
            limit: 1,
        }
    },
    props: {
        tiles:        String,
        zoom:         Object,
        center:       Object,
        mapbox:       Object,
        shapes:       [Boolean, Array],
        tools:        [Boolean, Array],
        size:         String,
        geocoding:    String,
        autocomplete: Boolean,
        language:     [String, Boolean],
        color:        String,

        // general options
        label:     String,
        disabled:  Boolean,
        help:      String,
        value:     [Array, Object],
        required:  Boolean,
        type:      String
    },
    computed: {
        mapId() {
            return 'map-'+ this._uid
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
        currentLanguage() {
            return this.$store.state.languages ? this.$store.state.languages.current : this.$language
        },
        defaultCoords() {
            return [this.center.lat, this.center.lon]
        },
        tileUrl() {
            if(this.tiles == 'mapbox' || this.tiles == 'mapbox.custom') {
                return 'https://api.mapbox.com/styles/v1/'+ this.mapbox.id +'/tiles/256/{z}/{x}/{y}'+ (L.Browser.retina ? '@2x' : '') +'?access_token='+ this.mapbox.token
            }
            else if(this.tiles == 'wikimedia') {
                return 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}' + (L.Browser.retina ? '@2x.png' : '.png')
            }
            else if(this.tiles == 'openstreetmap') {
                return 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
            }
            else if(this.tiles == 'light_all' || this.tiles == 'voyager') {
                return 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/rastertiles/'+ this.tiles +'/{z}/{x}/{y}' + (L.Browser.retina ? '@2x.png' : '.png')
            }
            else return ''
        },
        attribution() {
            if(this.tiles == 'mapbox' || this.tiles == 'mapbox.custom') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://www.mapbox.com/">Mapbox</a>'
            }
            else if(this.tiles == 'wikimedia') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://maps.wikimedia.org" target="_blank" rel="noreferrer">Wikimedia</a>'
            }
            else if(this.tiles == 'openstreetmap') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>'
            }
            else if(this.tiles == 'light_all' || this.tiles == 'voyager') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://carto.com/attribution" target="_blank" rel="noreferrer">CARTO</a>'
            }
            else return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>'
        },
        searchQuery() {
            if(this.geocoding == 'nominatim') {
                let languageParam = this.language ? '&accept-language=' + this.language : ''
                return 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=1&q=' + this.location + languageParam
            }
            else if(this.geocoding == 'mapbox') {
                let languageParam = this.language ? '&language=' + this.language : ''
                return 'https://api.mapbox.com/geocoding/v5/mapbox.places/'+ this.location +'.json?types=address,country,postcode,place,locality&limit='+ this.limit +'&access_token=' + this.mapbox.token + languageParam
            }
            else return ''
        },
    },
    mounted() {
        this.initMap()

        this.$store.subscribeAction({
            after: (action, state) => {
                if(action.type == 'content/revert') {
                    this.syncLayers()
                }
            }
        })
    },
    methods: {
        initMap() {
            this.map = L.map(this.mapId, {
                minZoom: this.zoom.min,
                maxZoom: this.zoom.max,
            }).setView(this.defaultCoords, this.zoom.default)

            // set the tile layer
            this.tileLayer   = L.tileLayer(this.tileUrl, {attribution: this.attribution})

            // add event listeners to override the panel's referrerpolicy while loading tiles through Mapbox API
            if(this.tiles == 'mapbox' || this.tiles == 'mapbox.custom') {
                this.tileLayer.on('loading', () => document.querySelector("meta[name=referrer]").content = "strict-origin-when-cross-origin")
                this.tileLayer.on('load', () => document.querySelector("meta[name=referrer]").content = "same-origin")
            }

            // add the tile layer to the map
            this.map.addLayer(this.tileLayer)

            this.initGeoman()
        },
        initGeoman() {
            this.customLayer = L.featureGroup()
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

            this.map.pm.setLang(this.$user.language);

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
            this.map.fitBounds(this.customLayer.getBounds())


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
            this.customLayer.clearLayers()

            if(this.value && this.value.features && this.value.features.length) {
                let _this = this

                L.geoJSON(this.value.features, {
                    pointToLayer: (feature, latlng) => {
                        if (feature.properties.shape == 'Circle') {
                            return new L.Circle(latlng, feature.properties.radius * 1);
                        }
                        else if (feature.properties.shape == 'CircleMarker') {
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
        onLocationInput() {
            if(!this.autocomplete) return false

            if(this.geocoding && this.location.length) {
                if(this.geocoding != 'mapbox') return false

                const fetchInit = { referrerPolicy: 'strict-origin-when-cross-origin' }

                this.limit = 5
                fetch(this.searchQuery, fetchInit)
                    .then(response => response.json())
                    .then(response => {
                        // if places are found
                        if(response.features.length) {
                            // keep the relevant ones
                            let suggestions = response.features.filter(el => el.relevance == 1)
                            // make them the dropdown options
                            this.dropdownOptions = suggestions.map(el => {
                                return {
                                    name: el.place_name,
                                    type: this.capitalize(el.place_type[0]),
                                }
                            })
                            this.$refs.dropdown.open()
                        }
                        else {
                            this.$refs.dropdown.close()
                        }
                    })
                    .catch(error => {
                        this.error = this.$t('locator.error')
                        this.$refs.dialog.open()
                        this.$refs.dropdown.close()
                    })
            }
            else {
                this.$refs.dropdown.close()
            }
        },
        select(option) {
            this.location = option.name
            this.getCoordinates()
        },
        getCoordinates(e) {
            if(e) {
                e.preventDefault()
                e.stopPropagation()
            }

            if(this.isLatLon(this.location)) {
                this.moveToCoordinates(this.location)
                return true
            }

            if(this.location.length) {
                fetch(this.searchQuery)
                    .then(response => response.json())
                    .then(response => {
                        if(response.length || Object.keys(response).length) {
                            if(this.geocoding == 'nominatim') {
                                response = response[0]
                                this.moveToCoordinates([parseFloat(response.lat), parseFloat(response.lon)])
                            }
                            else if(this.geocoding == 'mapbox')  {
                                response = response.features[0]
                                this.moveToCoordinates([parseFloat(response.center[1]), parseFloat(response.center[0])])
                            }
                            this.location = ''
                        }
                        else {
                            this.error = this.$t('mapnotator.error')
                            this.$refs.dialog.open()
                        }
                    })
                    .catch(error => {
                        this.error = this.$t('mapnotator.error')
                        this.$refs.dialog.open()
                    })
            }
        },
        isLatLon(str) {
            const regexExp = /^((\-?|\+?)?\d+(\.\d+)?),\s*((\-?|\+?)?\d+(\.\d+)?)$/gi;
            return regexExp.test(str);
        },
        moveToCoordinates(coords) {
            this.map.panTo(coords)
        },
        updateValue() {
            this.value = this.getValue()
            this.$emit("input", this.value)
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

            if (layer instanceof L.Circle) {
                json.properties.shape = 'Circle'
                json.properties.radius = layer.getRadius()
            }
            else if (layer instanceof L.CircleMarker) {
                json.properties.shape = 'CircleMarker'
            }

            return json
        },
        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
    },
};
</script>

<style lang="scss">
    @import '../assets/css/styles.scss'
</style>
