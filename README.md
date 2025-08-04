# Kirby Mapnotator

Annotate maps and generate GeoJSON in Kirby by drawing markers, paths and shapes.

![screenshot](https://user-images.githubusercontent.com/14079751/155854616-3315a12f-dfce-43dd-9b4f-c1648d48a316.jpg)


## Overview

> This plugin is completely free and published under the MIT license. However, if you are using it in a commercial project and want to help me keep up with maintenance, you can consider making a donation of your choice with [GitHub Sponsors](https://github.com/sponsors/sylvainjule) or [Paypal](https://www.paypal.me/sylvainjl).

- [1. Installation](#1-installation)
- [2. Setup](#2-setup)
- [3. Tile-servers](#3-tile-servers)
  * [3.1. Open-source / free tiles](#31-open-source-free-tiles)
  * [3.2. Mapbox tiles](#32-mapbox-tiles)
- [4. Geocoding service](#4-geocoding-service)
  * [4.1. Open-source API (Nominatim)](#41-open-source-nominatim)
  * [4.2. Mapbox API](#42-mapbox-api)
- [5. Per-field options](#5-per-field-options)
- [6. Global options](#6-global-options)
- [7. Front-end usage](#6-front-end-usage)
- [8. Credits](#7-credits)
- [9. License](#8-license)

<br/>

## 1. Installation

Download and copy this repository to ```/site/plugins/mapnotator```

Alternatively, you can install it with composer: ```composer require sylvainjule/mapnotator```

<br/>

## 2. Setup

Out of the box, the field is set to use open-source services both for geocoding (Nominatim) and tiles-rendering (Positron), without any API-key requirements.

Keep in mind that **these services are bound by strict usage policies**, always double-check if your usage is compatible. Otherwise, please set-up the field to use Mapbox, see details below.

```yaml
mymap:
  label: My map
  type: mapnotator
```

<br/>

## 3. Tile-servers

#### 3.1. Open-source / free tiles

![tiles-opensource-2](https://user-images.githubusercontent.com/14079751/155854627-fa4790f2-7923-4254-aae6-840ebdc5fa8e.jpg)

You can pick one of the 4 free tile servers included:

1. ~~`wikimedia` ([Terms of Use](https://foundation.wikimedia.org/wiki/Maps_Terms_of_Use))~~ → Public usage is now forbidden
2. `openstreetmap` ([Terms of Use](https://wiki.openstreetmap.org/wiki/Tile_usage_policy))
3. `positron` (default, [Terms of Use](https://carto.com/legal/) [Under *Free Basemaps Terms of Service*])
4. `voyager` ([Terms of Use](https://carto.com/legal/) [Under *Free Basemaps Terms of Service*])

```yaml
mymap:
  type: mapnotator
  tiles: positron
```

You can also set this globally in your installation's main `config.php`, then you won't have to configure it in every blueprint:

```php
return array(
    'sylvainjule.mapnotator.tiles' => 'positron',
);
```

#### 3.2. Mapbox tiles

![tiles-mapbox-2](https://user-images.githubusercontent.com/14079751/155854635-9ffa270e-7ccf-4efd-b569-245a640857b1.jpg)

1. ~~mapbox.outdoors~~ → `mapbox/outdoors-v11` (default mapbox theme)
2. ~~mapbox.streets~~ → `mapbox/streets-v11`
3. ~~mapbox.light~~ → `mapbox/light-v10`
4. ~~mapbox.dark~~ → `mapbox/dark-v10`

In case your usage doesn't fall into the above policies (or if you don't want to rely on those services), you can set-up the field to use Mapbox' tiles.

You will have to set both the `id` of the tiles you want to use and your mapbox `public key` in your installation's main `config.php`:

```php
return array(
    'sylvainjule.mapnotator.mapbox.id'    => 'mapbox/outdoors-v11',
    'sylvainjule.mapnotator.mapbox.token' => 'pk.vdf561vf8...',
);
```

You can now explicitely state in your blueprint that you want to use Mapbox tiles:

```yaml
mymap:
  type: mapnotator
  tiles: mapbox
```

You can also set this globally in your installation's main `config.php`, then you won't have to configure it in every blueprint:

```php
return array(
    'sylvainjule.mapnotator.tiles' => 'mapbox',
);
```

<br/>

## 4. Geocoding services

#### 4.1. Open-source API (Nominatim)

This is the default geocoding service. It doesn't require any additional configuration, but please double-check if your needs fit the [Nominatim Usage Policy](https://operations.osmfoundation.org/policies/nominatim/).

```yaml
mymap:
  type: mapnotator
  geocoding: nominatim
```

#### 4.2. Mapbox API

In case your usage doesn't fall into the above policy (or if you don't want to use Nominatim), you can set-up the field to use Mapbox API.

If you haven't already, you will have to set your mapbox `public key` in your installation's main `config.php`:

```php
return array(
    'sylvainjule.mapnotator.mapbox.token' => 'pk.vdf561vf8...',
);
```

You can now explicitely state in your blueprint that you want to use Mapbox as a geocoding service:

```yaml
mymap:
  type: mapnotator
  geocoding: mapbox
```

With Mapbox API comes the ability to autocomplete your search. It is activated by default, you can deactivate it by setting the `autocomplete` option to `false`.

```yaml
mymap:
  type: mapnotator
  geocoding: mapbox
  autocomplete: false
```

You can also set this globally in your installation's main `config.php`, then you won't have to configure it in every blueprint:

```php
return array(
    'sylvainjule.mapnotator.geocoding' => 'mapbox',
);
```

<br>

## 5. Per-field options

#### 5.1. `center`

The coordinates of the center of the map, if the field has no stored value. Default is `{lat: 48.864716, lon: 2.349014}` (Paris, FR).

Once the field has at least one shape drawn, it will automatically find its initial center in order to display all the shapes.

```yaml
mymap:
  type: mapnotator
  center:
    lat: 48.864716
    lon: 2.349014
```

#### 5.2. `zoom`

The `min`, `default` and `max` zoom values, where `default` will be the one used on every first-load of the map. Default is: `{min: 2, default: 12, max: 18}`.

Once the field has at least one shape drawn, it will automatically find its initial zoom level in order to display all the shapes.

```yaml
mymap:
  type: mapnotator
  zoom:
    min: 2
    default: 12
    max: 18
```

#### 5.3. `shapes`

The shapes your editors are allowed to draw on the map. They are all activated by default:

```yaml
mymap:
  type: mapnotator
  shapes:
    - marker
    - polyline
    - rectangle
    - polygon
    - circle
    - circleMarker
```

#### 5.4. `tools`

The tools / shape modifiers your editors are allowed ot use. They are all activated by default:

```yaml
mymap:
  type: mapnotator
  tools:
    - edit
    - drag
    - cut
    - remove
    - rotate
```

#### 5.5. `size`

The height of the field. Default is `full`, which will make the field fill the entire height of the viewport. Options are:

- `full` (entire viewport height)
- `large` (fits all buttons in the toolbar)
- `medium` (fits 8 buttons in the toolbar)
- `small` (fits 6 buttons in the toolbar)

#### 5.6. `color`

You can change the shapes / markers color by setting this option to any valid color value. Default it blue (`#2281f7`).

```yaml
mymap:
  type: mapnotator
  color: '#2281f7'
```

<br>

## 6. Global options

The same options are available globally, which means you can set them all in your installation's `config.php` file and don't worry about setting it up individually afterwards:

```php
return array(
    'sylvainjule.mapnotator.token'        => '',
    'sylvainjule.mapnotator.id'           => 'mapbox.outdoors',
    'sylvainjule.mapnotator.tiles'        => 'positron',
    'sylvainjule.mapnotator.zoom.min'     => 2,
    'sylvainjule.mapnotator.zoom.default' => 12,
    'sylvainjule.mapnotator.zoom.max'     => 18,
    'sylvainjule.mapnotator.center.lat'   => 48.864716,
    'sylvainjule.mapnotator.center.lon'   => 2.349014,
    'sylvainjule.mapnotator.shapes'       => ['marker', 'polyline', 'rectangle', 'polygon', 'circle', 'circleMarker'],
    'sylvainjule.mapnotator.tools'        => ['edit', 'drag', 'cut', 'remove', 'rotate'],
    'sylvainjule.mapnotator.size'         => 'full',
    'sylvainjule.mapnotator.geocoding'    => 'nominatim',
    'sylvainjule.mapnotator.autocomplete' => true,
    'sylvainjule.mapnotator.color'        => '#2281f7',
);
```

<br/>

## 7. Front-end usage

The GeoJSON is stored as YAML and therefore needs to be decoded with the `yaml` method.

```php
$location = $page->mymap()->yaml();
```

You can then encode it to JSON using Kirby's toolkit:

```php
$json = Json::encode($location);
```

#### 7.1. circle and circleMarker

The GeoJSON syntax doesn't support circles or circleMarkers. They are stored as a point by default (see [this Medium post](https://medium.com/geoman-blog/how-to-handle-circles-in-geojson-d04dcd6cb2e6) for more details).

Therefore, the field stores additionnal properties alongside their coordinates, to allow you to recreate them in your projects:

```json
{
    "type": "Feature",
    "properties": {
        "shape": "CircleMarker"
    },
    "geometry": {
        "type": "Point",
        "coordinates": [6.862806, 47.967742]
    }
},
{
    "type": "Feature",
    "properties": {
        "shape": "Circle",
        "radius": 241.85391410521
    },
    "geometry": {
        "type": "Point",
        "coordinates": [6.84809, 47.969121]
    }
}
```

When importing the GeoJSON into your project, you will need to check for those properties in order to transform them into the appropriate shapes. With Leaflet, for example, it would look like:

```javascript
L.geoJSON(myGeoJSON, {
    pointToLayer: (feature, latlng) => {
        if (feature.properties.shape == 'Circle') {
            return new L.Circle(latlng, feature.properties.radius);
        }
        else if (feature.properties.shape == 'CircleMarker') {
            return new L.CircleMarker(latlng);
        }
        else {
            return new L.Marker(latlng);
        }
    }
}).addTo(myMap)
```

<br/>

## 8. Credits

**Services:**
- [Openstreetmap](https://www.openstreetmap.org/#map=5/46.449/2.210), [Carto](https://carto.com/) or [Mapbox](https://www.mapbox.com/) as tile servers.
- [Nominatim](https://nominatim.openstreetmap.org/) or [Mapbox Search](https://www.mapbox.com/search/) as a geocoding API
- [Leaflet](https://leafletjs.com/) as a mapping library.
- [Geoman](https://geoman.io/) as a GeoJSON editor.

<br/>

## 9. License

MIT
