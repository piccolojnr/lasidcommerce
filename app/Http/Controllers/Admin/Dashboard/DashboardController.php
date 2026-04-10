<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAdminDashboard');

        return response('Admin dashboard placeholder');
    }
}
