<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Domain\Dashboard\Queries\GetAdminDashboardDataQuery;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private GetAdminDashboardDataQuery $dashboardQuery,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAdminDashboard');

        return Inertia::render('admin/dashboard/index', $this->dashboardQuery->execute());
    }
}
