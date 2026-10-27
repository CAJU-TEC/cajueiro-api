<?php

namespace App\Http\Controllers\Dumps;

use App\Http\Controllers\Controller;
use App\Models\Dump;
use Illuminate\Http\Request;

class DumpsIndexController extends Controller
{
    public function __construct(private Dump $dumps) {}

    public function __invoke(Request $request)
    {
        $dumps = $this->dumps
            ->with(['corporate:id,first_name,last_name', 'user:id,name'])
            ->when($request->corporate_id, fn ($query, $id) => $query->where('corporate_id', $id))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->makeHidden('log'); // o log pode ser grande; só o Show devolve

        return response()->json($dumps, 200);
    }
}
