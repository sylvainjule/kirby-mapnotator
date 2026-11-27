<?php

namespace SylvainJule\Mapnotator;

use Kirby\Data\Data;
use Kirby\Form\Field\InputField;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;

/**
 * A map annotation & GeoJSON field
 */
class MapnotatorField extends InputField {
    /**
     * Shows up to 5 suggestions while typing (Mapbox geocoding only)
     */
    #[Prop]
    protected bool $autocomplete = true;

    /**
     * Default center of the map (`lat`, `lon`)
     */
    #[Prop(setter: 'setCenter')]
    protected array $center = [
        'lat' => 48.864716,
        'lon' => 2.349014,
    ];

    /**
     * Geocoding service: `nominatim` or `mapbox`
     */
    #[Prop]
    protected string|bool $geocoding = 'nominatim';

    /**
     * Language of the geocoding results, or `false`
     */
    #[Prop]
    protected string|bool $language = false;

    /**
     * Mapbox settings (`id`, `token`)
     */
    #[Prop]
    protected array $mapbox = [
        'id'    => '',
        'token' => '',
    ];

    /**
     * Tile server of the map
     */
    #[Prop]
    protected string $tiles = 'positron';

    /**
     * API key for CARTO basemap tiles
     */
    #[Prop]
    protected string $tilesKey = '';

    /**
     * Zoom levels of the map (`min`, `default`, `max`)
     */
    #[Prop(setter: 'setZoom')]
    protected array $zoom = [
        'min'     => 2,
        'default' => 12,
        'max'     => 18,
    ];

    /**
     * Color of shapes and markers
     */
    #[Prop]
    protected string $color = '#2281f7';

    /**
     * Shapes available in the drawing toolbar
     */
    #[Prop(setter: 'setShapes')]
    protected array|bool $shapes = [
        'marker', 'polyline', 'rectangle', 'polygon', 'circle', 'circleMarker',
    ];

    /**
     * Map height: `full`, `large`, `medium` or `small`
     */
    #[Prop]
    protected string $size = 'full';

    /**
     * Tools available in the editing toolbar
     */
    #[Prop(setter: 'setTools')]
    protected array|bool $tools = [
        'edit', 'drag', 'cut', 'remove', 'rotate',
    ];

    protected array $value = [];

    public function config(): array {
        // plugin options use dot notation (e.g. `zoom.min`),
        // props expect them as nested arrays
        return A::nest($this->kirby()->option('sylvainjule.mapnotator', []));
    }

    public function fill(mixed $value): static {
        return parent::fill(
            value: Data::decode($value, 'yaml')
        );
    }

    public function mapbox(): array {
        $aliases = [
            'mapbox.outdoors' => 'mapbox/outdoors-v11',
            'mapbox.streets'  => 'mapbox/streets-v11',
            'mapbox.light'    => 'mapbox/light-v10',
            'mapbox.dark'     => 'mapbox/dark-v10',
        ];

        $id = $this->mapbox['id'] ?? '';

        return [
            'id'    => $aliases[$id] ?? $id,
            'token' => $this->mapbox['token'] ?? '',
        ];
    }

    public function tiles(): string {
        return str_replace('positron', 'light_all', $this->tiles);
    }

    protected function setCenter(array $center): void {
        foreach ($this->center as $key => $value) {
            $this->center[$key] = $center[$key] ?? $value;
        }
    }

    protected function setZoom(array $zoom): void {
        foreach ($this->zoom as $key => $value) {
            $this->zoom[$key] = $zoom[$key] ?? $value;
        }
    }

    protected function setShapes(array|bool $shapes): void {
        $this->shapes = $shapes;
    }

    protected function setTools(array|bool $tools): void {
        $this->tools = $tools;
    }

    public function shapes(): array|bool {
        $shapes = $this->shapes;

        if ($shapes === true) {
            $shapes = $this->kirby()->option('sylvainjule.mapnotator.shapes');
            if (!is_array($shapes)) {
                $shapes = ['marker', 'polyline', 'rectangle', 'polygon', 'circle', 'circleMarker'];
            }
        }

        return is_array($shapes) ? array_map('strtolower', $shapes) : false;
    }

    public function tools(): array|bool {
        $tools = $this->tools;

        if ($tools === true) {
            $tools = $this->kirby()->option('sylvainjule.mapnotator.tools');
            if ($tools === true || $tools === null) {
                $tools = ['edit', 'drag', 'cut', 'remove', 'rotate'];
            }
        }

        return is_array($tools) ? array_map('strtolower', $tools) : false;
    }
}
