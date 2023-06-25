<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {

//        dump($request->all());
        if ($request->title && $request->variant && $request->price_from && $request->price_to && $request->date) {
            $products = Product::with('productVariantPrices')
                ->where('title', 'LIKE', '%' . $request->title . '%')
                ->whereHas('productVariantPrices', function (Builder $q) use ($request) {
                    $q->where([
                        ['price', '>=', $request->price_from],
                        ['price', '<=', $request->price_to],
                    ])->orWhere('product_variant_one', '=', $request->variant)
                        ->orWhere('product_variant_two', '=', $request->variant)
                        ->orWhere('product_variant_three', '=', $request->variant);
                })
                ->whereDate('created_at', '=', $request->date)
                ->paginate(2);

        } elseif ($request->title && !isset($request->variant) && !isset($request->price_from) && !isset($request->price_to) && !isset($request->date)) {
            $products = Product::with('productVariantPrices')
                ->where('title', 'LIKE', '%' . $request->title . '%')->paginate(2);

        } elseif ($request->date && !isset($request->variant) && !isset($request->price_from) && !isset($request->price_to) && !isset($request->title)) {
            $products = Product::with('productVariantPrices')
                ->whereDate('created_at', '=', $request->date)->paginate(2);

        } elseif ($request->variant && !isset($request->date) && !isset($request->price_from) && !isset($request->price_to) && !isset($request->title)) {
            $products = Product::with('productVariantPrices')
                ->whereHas('productVariantPrices', function (Builder $q) use ($request) {
                    $q->where('product_variant_one', '=', $request->variant)
                        ->orWhere('product_variant_two', '=', $request->variant)
                        ->orWhere('product_variant_three', '=', $request->variant);
                })
                ->paginate(2);

        } elseif ($request->price_from && $request->price_to && !isset($request->variant) && !isset($request->date) && !isset($request->title)) {
            $products = Product::with('productVariantPrices')
                ->whereHas('productVariantPrices', function (Builder $q) use ($request) {
                    $q->where([
                        ['price', '>=', $request->price_from],
                        ['price', '<=', $request->price_to],
                    ]);
                })
                ->paginate(2);

        } else {
            $products = Product::with('productVariantPrices')->paginate(2);
        }
        $data = [
            'products' => $products,
            'variants' => Variant::with('productVariants')->get(),
        ];
        return view('products.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
//        dump($request->product_variant_prices);
        try {

            DB::beginTransaction();
            $product = Product::create([
                'title' => $request->title,
                'sku' => $request->sku,
                'description' => $request->description,
            ]);

            $currentVariant = [];

            foreach ($request->product_variant as $product_variant) {
                if (isset($product_variant['option']) && isset($product_variant['tags'])) {
                    foreach ($product_variant['tags'] as $tag) {
                        $productVariant = ProductVariant::create([
                            'variant' => $tag,
                            'variant_id' => $product_variant['option'],
                            'product_id' => $product->id,
                        ]);
                        $currentVariant = Arr::add($currentVariant, $tag, $productVariant->id);
                    }
                }
            }
            dump($currentVariant);

            foreach ($request->product_variant_prices as $product_variant_price) {
                $titleArr = explode('/', $product_variant_price['title']);

                ProductVariantPrice::create([
                    'product_variant_one' => Arr::pull($currentVariant, $titleArr[0]) ?? null,
                    'product_variant_two' => Arr::pull($currentVariant, $titleArr[1]) ?? null,
                    'product_variant_three' => Arr::pull($currentVariant, $titleArr[2]) ?? null,
                    'price' => $product_variant_price['price'],
                    'stock' => $product_variant_price['stock'],
                    'product_id' => $product->id,
                ]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e);
        }


    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {


        $data = [
            'variants' => Variant::all(),
            'product' => $product,
        ];

        return view('products.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
