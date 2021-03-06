<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ToolsResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tool;
use Symfony\Component\HttpFoundation\Response;

class ToolsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $tools = Tool::all();
        return response()->json([
            'message' => 'Berhasil Menampilkan Tools',
            'status' => true,
            'data' => ToolsResource::collection($tools),
		], Response::HTTP_OK);
    }

    public function validateQrCode($id, $name)
    {
        $tool = Tool::where('id', $id)
		->where('name', $name)
		->first();
        if ($tool) {
            return response()->json([
                'message' => 'tool found',
                'status' => true,
                'data' => new ToolsResource($tool)
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'message' => 'tool not found',
                'status' => false,
                'data' => (object)[]
            ], Response::HTTP_NOT_FOUND);
        }
    }

}
