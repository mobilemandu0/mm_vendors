<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Spatie\Activitylog\Models\Activity; // Import Spatie's Activity model
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $recentProducts = Product::with('variants')
            ->where('brand_id', $user->brand_id)
            ->latest()
            ->take(5)
            ->get();
        $activities = Activity::where('causer_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('recentProducts', 'activities'));
    }
}
