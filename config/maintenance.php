<?php

return [
    // Umbrales de aviso para el siguiente servicio.
    'kilometers_alert' => (int) env('MAINTENANCE_KILOMETERS_ALERT', 500),
    'days_alert' => (int) env('MAINTENANCE_DAYS_ALERT', 20),
];
