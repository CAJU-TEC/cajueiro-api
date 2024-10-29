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

        $checkList = CheckList::find($id);

        try {
            $this->pdf->loadView('reports.checklist', [
                'payload' => [
                    'checkList' => $checkList,
                ]
            ]);
            $this->pdf->setOptions([
                'page-size' => 'a4',
                'margin-top' => 5,
                'margin-bottom' => 5,
                'margin-left' => 5,
                'margin-right' => 5,
                'orientation' => 'landscape'
            ]);
            return $this->pdf->stream();
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
