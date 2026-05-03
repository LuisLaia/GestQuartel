<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\UniformAssignment;
use App\Models\UniformItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $peopleCount = Person::count();
        $activePeopleCount = Person::where('status', 'ativo')->count();

        return view('dashboard.index', [
            'peopleCount' => $peopleCount,
            'activePeopleCount' => $activePeopleCount,

            'uniformItemCount' => UniformItem::count(),

            'lowStockCount' => UniformItem::whereColumn('available_quantity', '<=', 'minimum_quantity')->count(),

            'activeAssignmentsCount' => UniformAssignment::whereNull('returned_at')->count(),

            'recentAssignments' => UniformAssignment::with(['person', 'uniformItem'])
                ->latest('assigned_at')
                ->limit(6)
                ->get(),

            'recentPersonnel' => Person::latest()->limit(5)->get(),

            'lowStockUniformItems' => UniformItem::whereColumn('available_quantity', '<=', 'minimum_quantity')
                ->orderBy('available_quantity')
                ->limit(6)
                ->get(),
        ]);
    }
}
