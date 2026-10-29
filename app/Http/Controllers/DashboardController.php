<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Services\DashboardService;
use ObraClara\Support\Auth;
use ObraClara\Support\View;

final class DashboardController extends Controller
{
    /** @param array<string, string> $params */
    public function index(array $params = []): void
    {
        $user = Auth::requireUser();
        $stats = DashboardService::stats();

        View::page('dashboard', [
            'title' => t('nav.dashboard'),
            'nav'   => 'dashboard',
            'user'  => $user,
            'stats' => $stats,
        ]);
    }
}
