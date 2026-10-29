<?php

declare(strict_types=1);

namespace ObraClara\Http\Controllers;

use ObraClara\Repositories\EventRepository;
use ObraClara\Support\Auth;
use ObraClara\Support\View;

final class HistoryController extends Controller
{
    /** @param array<string, string> $params */
    public function index(array $params = []): void
    {
        $user = Auth::requireUser();

        View::page('history/index', [
            'title'  => 'Histórico',
            'nav'    => 'history',
            'user'   => $user,
            'events' => EventRepository::recent(100),
        ]);
    }
}
