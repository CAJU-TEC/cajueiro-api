<?php

namespace App\Http\Controllers\CheckLists;

use App\Http\Controllers\Controller;
use App\Models\CheckList;
use Exception;
use Illuminate\Http\Request;
use PDF;

class CheckListsReportController extends Controller
{
    private $pdf;

    public function __construct(private CheckList $checkList)
    {
        $this->pdf = PDF::loadHTML('');
    }

    public function __invoke(Request $request, $id)
    {
        //
        try {
            $this->pdf->loadView('reports.checklist', [
                'payload' => []
            ]);
            $this->pdf->setOptions([
                'page-size' => 'a6',
                'margin-top' => 5,
                'margin-bottom' => 5,
                'margin-left' => 5,
                'margin-right' => 5,
            ]);
            return $this->pdf->stream();
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
