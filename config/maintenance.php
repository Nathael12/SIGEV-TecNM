<?php

return [
    // Alertar cuando el vehículo esté a esta distancia o menos del siguiente servicio.
    'kilometers_alert' => (int) env('MAINTENANCE_KILOMETERS_ALERT', 500),
];
