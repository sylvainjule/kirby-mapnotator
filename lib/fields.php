<?php

return [
    'mapnotator' => [
        'props'  => [
            'tiles' => function($tiles = null) {
                // 13.11.2018 - CARTO currently serves the positron style under the 'light_all' name
                $tiles = $tiles ?? option('sylvainjule.mapnotator.tiles');
                return str_replace('positron', 'light_all', $tiles);
            },
            'zoom' => function($zoom = []) {
                return array(
                    'min'     => $zoom['min']     ?? option('sylvainjule.mapnotator.zoom.min'),
                    'default' => $zoom['default'] ?? option('sylvainjule.mapnotator.zoom.default'),
                    'max'     => $zoom['max']     ?? option('sylvainjule.mapnotator.zoom.max'),
                );
            },
            'shapes' => function($shapes = null) {
                $shapes = $shapes ?? option('sylvainjule.mapnotator.shapes');
                $shapes = array_map('strtolower', $shapes);
                return $shapes;
            },
            'tools' => function($tools = null) {
                $tools = $tools ?? option('sylvainjule.mapnotator.tools');

                if(is_array($tools)) {
                    $tools = array_map('strtolower', $tools);
                }
                elseif($tools) {
                    $tools = option('sylvainjule.mapnotator.tools');
                }

                return $tools;
            },
            'center' => function($center = []) {
                return array(
                    'lat'     => $center['lat'] ?? option('sylvainjule.mapnotator.center.lat'),
                    'lon'     => $center['lon'] ?? option('sylvainjule.mapnotator.center.lon'),
                );
            },
            'size' => function($size = null) {
                return $size ?? option('sylvainjule.mapnotator.size');
            },
            'geocoding' => function($geocoding = null) {
                return $geocoding ?? option('sylvainjule.mapnotator.geocoding');
            },
            'autocomplete' => function($autocomplete = null) {
                return $autocomplete ?? option('sylvainjule.mapnotator.autocomplete');
            },
            'language' => function($language = null) {
                return $language ?? option('sylvainjule.mapnotator.language');
            },
            'color' => function($color = null) {
                return $color ?? option('sylvainjule.mapnotator.color');
            },
            'value' => function($value = null) {
                return Yaml::decode($value);
            },
        ],
        'computed' => [
            'mapbox' => function() {
                $idSwap = [
                    'mapbox.outdoors' => 'mapbox/outdoors-v11',
                    'mapbox.streets'  => 'mapbox/streets-v11',
                    'mapbox.light'    => 'mapbox/light-v10',
                    'mapbox.dark'     => 'mapbox/dark-v10',
                ];

                $setId = option('sylvainjule.mapnotator.mapbox.id');
                $id    = array_key_exists($setId, $idSwap) ? $idSwap[$setId] : $setId;

                return array(
                    'id'    => $id,
                    'token' => option('sylvainjule.mapnotator.mapbox.token', ''),
                );
            }
        ],
    ],
];
