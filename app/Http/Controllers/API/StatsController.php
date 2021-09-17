<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImageProduct;
use App\Models\User;
use App\Models\Purchase;

class StatsController extends Controller
{
    public function getStats() {
        $totalImages = ImageProduct::get()->count();
        $totalUsers = User::get()->count();
        $totalPurchases = Purchase::get()->count();
        $totalMoneyMoved = Purchase::get()->sum('price');
        $averagePurchasePrice = Purchase::get()->sum('price') / Purchase::get()->count();

        $respuesta = [
            'totalImages' => $totalImages,
            'totalUsers' => $totalUsers,
            'totalPurchases' => $totalPurchases,
            'totalMoneyMoved' => $totalMoneyMoved,
            'averagePurchasePrice' => $averagePurchasePrice
        ];

        return response()->json(['code' => 200, 'message' => $respuesta]);
    }
}
