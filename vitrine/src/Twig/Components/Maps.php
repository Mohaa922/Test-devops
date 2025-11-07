<?php

namespace App\Twig\Components;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Live\ComponentWithMapTrait;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
#[AsLiveComponent]
final class Maps extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithMapTrait;

    protected function instantiateMap(): Map
    {
        //$icon = Icon::url('https://example.com/marker.png')->width(24)->height(24); // Charger une icône personnalisée
        $exampleData = [
            ['position' => new Point(48.8566, 2.3522), 'title' => 'example1'],
            ['position' => new Point(45.75, 4.85), 'title' => 'example2'],
        ];
        $map = new Map();
        $map->zoom(6);
        $map->maxZoom(6);
        $map->minZoom(6);
        $leafletOptions = (new LeafletOptions())
        ->zoomControl(false)
        ->attributionControl(false);
        $map->options($leafletOptions);
        $map->fitBoundsToMarkers();

        foreach ($exampleData as $data) {
            $map->addMarker(new Marker(
                position: $data['position'],
                title: $data['title'],
                icon: null, // On peut ajouter une icône personnalisée ici
                //infoWindow: new InfoWindow($this->renderView("")) // rajout de la view a afficher dans la popup + arguments
            ));
        }

        return $map;
    }
}

