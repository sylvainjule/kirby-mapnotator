<?php

Kirby::plugin('sylvainjule/mapnotator', array(
    'options' => array(
        'token'        => '',
        'id'           => 'mapbox.outdoors',
        'tiles'        => 'positron',
        'zoom.min'     => 2,
        'zoom.default' => 12,
        'zoom.max'     => 18,
        'center.lat'   => 48.864716,
        'center.lon'   => 2.349014,
        'shapes'       => ['marker', 'polyline', 'rectangle', 'polygon', 'circle', 'circleMarker'],
        'tools'        => ['edit', 'drag', 'cut', 'remove', 'rotate'],
        'size'         => 'full',
        'geocoding'    => 'nominatim',
        'autocomplete' => true,
        'language'     => false,
        'color'        => '#2281f7',
    ),
    'fields'       => require_once __DIR__ . '/lib/fields.php',
    'translations' => array(
        'en' => require_once __DIR__ . '/lib/languages/en.php',
        'fr' => require_once __DIR__ . '/lib/languages/fr.php',
    ),
));
