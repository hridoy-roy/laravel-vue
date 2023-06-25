@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{route('product.index')}}" method="get" class="card-header">
            @csrf
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" value="{{old('title')}}" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        @foreach($variants as $variant)
                            <option value="">__Select__</option>
                            <optgroup label="{{$variant->title}}" style="padding-left: 20px;">
                                @foreach($variant->productVariants as $productVariant)
                                <option value="{{$productVariant->id}}" style="padding-left: 50px;">{{$productVariant->variant}}</option>
                                @endforeach
                            </optgroup>
                        @endforeach

                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                               class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>


                    @forelse($products as $product)

                        <tr style="height: 80px;">
                            <td>1</td>
                            <td>{{$product->title}} <br> Created at : {{$product->created_at}}</td>
                            <td>{{ Str::limit($product->description, 50)}}</td>
                            <td>
                                @foreach($product->productVariantPrices as $productVariantPrice)
                                    <dl class="row mb-0" style=" overflow: hidden" id="variant">

                                        <dt class="col-sm-3 pb-0">
                                            {{$productVariantPrice->productVariantOne ? $productVariantPrice->productVariantOne->variant.'/' : ''}}
                                            {{$productVariantPrice->productVariantTwo ? $productVariantPrice->productVariantTwo->variant.'/' : ''}}
                                            {{$productVariantPrice->productVariantThree->variant ?? ''}}
                                        </dt>
                                        <dd class="col-sm-9">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 pb-0">Price
                                                    : {{ number_format($productVariantPrice->price,2) }}</dt>
                                                <dd class="col-sm-8 pb-0">InStock
                                                    : {{ number_format($productVariantPrice->stock,2) }}</dd>
                                            </dl>
                                        </dd>
                                    </dl>
                                @endforeach
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', 1) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>

                    @empty

                    @endforelse

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{$products->firstItem()}} to {{$products->lastItem()}} out of {{$products->total()}}</p>
                </div>
                <div class="col-md-2">
                    {!! $products->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection
