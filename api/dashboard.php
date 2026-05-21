<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/services/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stats = api_dashboard_stats();

    if (api_prefere_html()) {
        api_html(api_render('dashboard-stats', ['stats' => $stats]));
    }

    api_json(['data' => $stats]);
}

api_json(['erro' => 'metodo_nao_suportado'], 405);
