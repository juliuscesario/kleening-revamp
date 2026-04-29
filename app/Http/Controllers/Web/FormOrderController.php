<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FormOrderParser;
use Illuminate\Http\Request;

class FormOrderController extends Controller
{
    public function parse(Request $request)
    {
        $request->validate([
            'raw_text' => 'required|string|min:10',
        ]);

        $parser = new FormOrderParser();
        $result = $parser->parse($request->raw_text);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
