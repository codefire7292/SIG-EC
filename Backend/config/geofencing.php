<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | CRE Coordinates
    |--------------------------------------------------------------------------
    |
    | GPS coordinates of the CRE de Kolda. These are used by the
    | GeofencingService to compute the Haversine distance.
    |
    */

    'cre_latitude'  => env('CRE_LATITUDE', 12.8983),  // Kolda, Sénégal
    'cre_longitude' => env('CRE_LONGITUDE', -14.9500),

    /*
    |--------------------------------------------------------------------------
    | Maximum Allowed Radius (meters)
    |--------------------------------------------------------------------------
    */

    'max_radius' => env('CRE_MAX_RADIUS', 20),

];
