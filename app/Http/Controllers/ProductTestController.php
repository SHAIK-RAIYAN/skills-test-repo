<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductTestController extends Controller
{
    public function index()
    {
        return view('product_form');
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'product_name' => 'required',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        $filePath = storage_path('app/products.json');

        $products = [];
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $products = json_decode($jsonContent, true) ?? [];
        }

        $products[] = [
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'datetime' => Carbon::now()->toDateTimeString(),
            'total_value' => $request->quantity * $request->price
        ];

        file_put_contents($filePath, json_encode($products));

        return response()->json(['status' => 'success']);
    }

    public function list()
    {
        $filePath = storage_path('app/products.json');
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);
            
            // Beginner way to sort: might forget this, but let's include it to meet requirement
            usort($data, function($a, $b) {
                return strtotime($a['datetime']) - strtotime($b['datetime']);
            });
            
            return response()->json($data);
        }
        return response()->json([]);
    }
}