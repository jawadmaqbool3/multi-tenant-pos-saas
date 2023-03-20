<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionProducts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Promotion::filter()->dataTable();
        }
        return view('promotions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::filter()->get();
        return view('promotions.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request,[
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required'
            ]);
            if (sizeof($request->product) == 0) {
                throw new CustomException('Add some products first', 'warning');
            }
            $promotion = Promotion::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'company_id' => auth()->user()->company_id
            ]);
            $promotion->refresh();
            foreach ($request->product as $key => $product) {
                $product = Product::where('uid', $product)->first();
                PromotionProducts::create([
                    'promotion_id' => $promotion->id,
                    'product_id' => $product->id,
                    'amount' => $request->amount[$key],
                    'percentage' => $request->percentage[$key],
                ]);
            }
            DB::commit();
            return response([
                'success' => true,
                'redirect' => true,
                'message' => 'New Record Added',
                'url' => route('promotions.index'),
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promotion $promotion)
    {
        $products = Product::filter()->get();
        return view('promotions.edit', compact('promotion', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        DB::beginTransaction();
        try {
            $this->validate($request,[
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required'
            ]);
            if (sizeof($request->product) == 0) {
                throw new CustomException('Add some products first', 'warning');
            }
            Promotion::where('id', $promotion->id)->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
            PromotionProducts::where('promotion_id', $promotion->id)->delete();
            foreach ($request->product as $key => $product) {
                $product = Product::where('uid', $product)->first();
                PromotionProducts::create([
                    'promotion_id' => $promotion->id,
                    'product_id' => $product->id,
                    'amount' => $request->amount[$key],
                    'percentage' => $request->percentage[$key],
                ]);
            }
            DB::commit();
            return response([
                'success' => true,
                'redirect' => true,
                'message' => 'Record Updated',
                'url' => route('promotions.index'),
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}
