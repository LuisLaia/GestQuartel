<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentAssignment;
use App\Models\Person;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard.index', [
            'peopleCount' => Person::count(),
            'activePeopleCount' => Person::where('status', 'ativo')->count(),
            'equipmentCount' => Equipment::count(),
            'lowStockCount' => Equipment::whereColumn('available_quantity', '<=', 'minimum_quantity')->count(),
            'activeAssignmentsCount' => EquipmentAssignment::whereNull('returned_at')->count(),
            'recentAssignments' => EquipmentAssignment::with(['person', 'equipment'])
                ->latest('assigned_at')
                ->latest()
                ->limit(6)
                ->get(),
            'lowStockEquipment' => Equipment::whereColumn('available_quantity', '<=', 'minimum_quantity')
                ->orderBy('available_quantity')
                ->limit(6)
                ->get(),
        ]);
    }
}
