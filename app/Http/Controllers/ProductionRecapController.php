<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Services\ProductionRecapService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionRecapController extends Controller
{
    public function __construct(
        private ProductionRecapService $recapService
    ) {}

    public function index(Request $request)
    {
        $date = $request->input('date')
            ?: Carbon::today()->toDateString();

        $request->merge([
            'date' => $date,
        ]);

        $filters = $request->validate([
            'date' => ['required', 'date'],

            'machine_type' => [
                'nullable',
                Rule::in(['AUTO', 'SEMI']),
            ],

            'machine_id' => [
                'nullable',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],

            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $filters['date'] = Carbon::parse(
            $filters['date']
        )->toDateString();

        $recap = $this->recapService->getRecap($filters);

        $machineCodes = array_merge(
            $recap['machine_codes_auto'],
            $recap['machine_codes_semi']
        );

        $machines = Machine::active()
            ->whereIn('machine_code', $machineCodes)
            ->when(
                $filters['machine_type'] ?? null,
                fn($query, $type) => $query->where(
                    'machine_type',
                    $type
                )
            )
            ->orderBy('machine_type')
            ->orderBy('machine_code')
            ->get();

        return view(
            'production-recap.index',
            compact('recap', 'machines', 'filters')
        );
    }
}
