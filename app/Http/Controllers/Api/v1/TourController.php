<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Travel;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Travel $travel, Request $request)
    {
        $request->validate([
            'priceFrom' => 'numeric',
            'priceTo' => 'numeric',
            'dateFrom' => 'date',
            'dateTo' => 'date',
            'sortBy' => 'in:price',
            'sortOrder' => 'in:asc,desc',

        ], [
            'priceFrom.numeric' => 'The price from must be a number.',
            'priceTo.numeric' => 'The price to must be a number.',
            'dateFrom.date' => 'The date from must be a date.',
            'dateTo.date' => 'The date to must be a date.',
            'sortBy.in' => 'The sort by must be one of: price.',
            'sortOrder.in' => 'The sort order must be one of: asc, desc.',
        ]);
        $tours = $travel
            ->tours()
            ->when($request->has('priceFrom'), function ($query) use ($request) {
                return $query->where('price', '>=', $request->priceFrom * 100);
            })
            ->when($request->has('priceTo'), function ($query) use ($request) {
                return $query->where('price', '<=', $request->priceTo * 100);
            })
            ->when($request->has('dateFrom'), function ($query) use ($request) {
                return $query->where('starting_date', '>=', $request->dateFrom);
            })
            ->when($request->has('dateTo'), function ($query) use ($request) {
                return $query->where('starting_date', '<=', $request->dateTo);
            })
            ->when($request->has('sortBy'), function ($query) use ($request) {
                return $query->orderBy($request->sortBy, $request->sortOrder);
            })
            ->orderBy('starting_date')
            ->paginate();

        return TourResource::collection($tours);
    }
}
