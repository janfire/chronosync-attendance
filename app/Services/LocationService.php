<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Known locations with their coordinates and a small radius (in meters) for matching.
     */
    protected const KNOWN_LOCATIONS = [
        // Example: ZOU National Centre (Approximate coords, can be updated)
        'ZOU National Centre' => [
            'lat' => -17.825166, 
            'lon' => 31.033510, 
            'radius' => 500 // 500 meters radius
        ],
        // Specific location provided by user (Mubatsiri Masiya's location)
        'ZOU Harare Main Campus' => [
            'lat' => -17.8273817,
            'lon' => 31.0448893,
            'radius' => 200 // 200 meters radius
        ],
        // Default Harare Regional Campus (Keeping as fallback or secondary point)
        'ZOU Harare Regional Campus' => [
            'lat' => -17.821629,
            'lon' => 31.049226,
            'radius' => 500
        ],
    ];

    /**
     * Resolve a location name from latitude and longitude.
     *
     * @param float|null $lat
     * @param float|null $lon
     * @return string
     */
    public function resolveLocation(?float $lat, ?float $lon): string
    {
        if (is_null($lat) || is_null($lon)) {
            return 'Location not captured';
        }

        if ($lat == 0 && $lon == 0) {
            return 'Invalid Coordinates';
        }

        // 1. Check Known Locations (Geofencing)
        foreach (self::KNOWN_LOCATIONS as $name => $data) {
            $distance = $this->calculateDistance($lat, $lon, $data['lat'], $data['lon']);
            if ($distance <= $data['radius']) {
                return $name;
            }
        }

        // 2. Fallback to OpenStreetMap/Nominatim Reverse Geocoding
        try {
            // Respect Nominatim Usage Policy:
            // - No heavy use (1 request per second max)
            // - Provide a unique User-Agent
            $response = Http::withHeaders([
                'User-Agent' => 'ZOU-Attendance-System/1.0 (internal tool)',
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lon,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Construct a readable address
                // Use 'display_name' or construct manual parts for brevity
                if (isset($data['display_name'])) {
                    return $data['display_name'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Geocoding failed: ' . $e->getMessage());
        }

        // 3. Ultimate Fallback: Raw Coordinates
        return 'GPS: ' . number_format($lat, 5) . ', ' . number_format($lon, 5);
    }

    /**
     * Calculate distance between two points in meters using Haversine formula.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
