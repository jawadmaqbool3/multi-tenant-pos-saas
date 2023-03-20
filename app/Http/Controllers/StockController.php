<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $table = new Product();
            if ($request->status) {
                switch ($request->status) {
                    case 'in_stock':
                        $table = $table->filter()->where('stock', '>=', 10);
                        break;
                    case 'out_of_stock':
                        $table = $table->filter()->where('stock', '=', 0);
                        break;
                    case 'low_stock':
                        $table = $table->filter()->where('stock', '<', 10)->where('stock', '>', 0);
                        break;
                }
            }
            return $table->filter()->stockTable();
        }
        return view('reports.stocks.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function print($type)
    {
        if ($type == 'all') {
            $products = Product::filter()->get();
            $status = 'All Products';
        } elseif ($type == 'in_stock') {
            $status = 'In Stock';
            $products = Product::filter()->where('stock', '>=', 10)->get();
        } elseif ($type == 'low_stock') {
            $status = 'Low Stock';
            $products = Product::filter()->where('stock', '<', 10)->where('stock', '>', 0)->get();
        } else {
            $status = 'Out of Stock';
            $products = Product::filter()->where('stock', 0)->get();
        }
        return view('reports.stocks.print', compact('products', 'status'));
    }
}
