<?php

namespace App\Services;

use App\Models\Location;
use App\Helpers\GeoHelper;

class LocationValidator {
    public function validate(float $lat, float $lng): ?Location {
        foreach (Location::all() as $loc) {
            $distance = GeoHelper::distanceInMeters($lat, $lng, $loc->latitude, $loc->longitude);
            if ($distance <= $loc->radius) {
                return $loc;
            }
        }
        return null;
    }
}
