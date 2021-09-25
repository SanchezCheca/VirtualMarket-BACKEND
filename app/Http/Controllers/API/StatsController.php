<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImageProduct;
use App\Models\User;
use App\Models\Purchase;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function getStats(Request $request) {
        $selectedDate = $request->input('date');
        if ($selectedDate == 'today') {
            $date = Carbon::now()->subDays(1);
            $respuesta = $this->getStatsWithDaysLimit($date);
            $respuesta += ['datosTImages' => $this->getUploadedImagesToday()];
            $respuesta += ['datosTPurchases' => $this->getPurchasesToday()];
            return response()->json(['code' => 200, 'message' => $respuesta]);
        } else if ($selectedDate == 'last28') {
            $date = Carbon::now()->subDays(28);
            $respuesta = $this->getStatsWithDaysLimit($date);
            $respuesta += ['datosTImages' => $this->getUploadedImagesInLastNDays(28)];
            $respuesta += ['datosTPurchases' => $this->getPurchasesInLastNDays(28)];
            return response()->json(['code' => 200, 'message' => $respuesta]);
        } else if ($selectedDate == 'last90') {
            $date = Carbon::now()->subDays(90);
            $respuesta = $this->getStatsWithDaysLimit($date);
            $respuesta += ['datosTImages' => $this->getUploadedImagesInLastNDays(90)];
            $respuesta += ['datosTPurchases' => $this->getPurchasesInLastNDays(90)];
            return response()->json(['code' => 200, 'message' => $respuesta]);
        } else if ($selectedDate == 'always') {
            //Nº total de imágenes
            $totalImages = ImageProduct::get()->count();
            //Nº total de usuarios
            $totalUsers = User::get()->count();
            //Nº total de compras
            $totalPurchases = Purchase::get()->count();
            //Cantidad de dinero movido en las compras
            $totalMoneyMoved = Purchase::get()->sum('price');
            //Valor medio de cada compra
            if (Purchase::get()->count() != 0) {
                $averagePurchasePrice = Purchase::get()->sum('price') / Purchase::get()->count();
            } else {
                $averagePurchasePrice = 0;
            }
            
            //Cantidad total de todos los monederos
            $totalWalletValue = User::get()->sum('balance');

            $respuesta = [
                'totalImages' => $totalImages,
                'totalUsers' => $totalUsers,
                'totalPurchases' => $totalPurchases,
                'totalMoneyMoved' => $totalMoneyMoved,
                'averagePurchasePrice' => $averagePurchasePrice,
                'totalWalletValue' => $totalWalletValue
            ];

            return response()->json(['code' => 200, 'message' => $respuesta]);
        } else {
            return response()->json(['code' => 400, 'message' => 'Valor no válido']);
        }
    }

    //Devuelve las estadísticas completas en los últimos n días
    private function getStatsWithDaysLimit($days) {
        //Nº total de imágenes
        $totalImages = ImageProduct::where('created_at', '>=', $days)->get()->count();
        //Nº total de usuarios
        $totalUsers = User::where('created_at', '>=', $days)->get()->count();
        //Nº total de compras
        $totalPurchases = Purchase::where('created_at', '>=', $days)->get()->count();
        //Cantidad de dinero movido en las compras
        $totalMoneyMoved = Purchase::where('created_at', '>=', $days)->get()->sum('price');
        //Valor medio de cada compra
        if (Purchase::where('created_at', '>=', $days)->get()->count() != 0) {
            $averagePurchasePrice = Purchase::where('created_at', '>=', $days)->get()->sum('price') / Purchase::where('created_at', '>=', $days)->get()->count();            
        } else {
            $averagePurchasePrice = 0;
        }
        //Cantidad total de todos los monederos
        $totalWalletValue = User::get()->sum('balance');

        $respuesta = [
            'totalImages' => $totalImages,
            'totalUsers' => $totalUsers,
            'totalPurchases' => $totalPurchases,
            'totalMoneyMoved' => $totalMoneyMoved,
            'averagePurchasePrice' => $averagePurchasePrice,
            'totalWalletValue' => $totalWalletValue
        ];

        return $respuesta;
    }

    //Devuelve los datos formateados para la tabla de las compras en el día de hoy
    private function getPurchasesToday() {
        $nHoras = Carbon::today()->diff(Carbon::now())->h;
        $horasT = [];
        $comprasT = [];
        
        for ($i=0; $i < $nHoras; $i++) { 
            $horasT[] = $i;
            $comprasT[] = Purchase::where('created_at','>=',(Carbon::today()->addHours($i)))->where('created_at','<',(Carbon::today()->addHours($i + 1)))->count();
        }

        $datosT = [$horasT,$comprasT];

        return $datosT;
    }

    //Devuelve los datos formateados para la tabla de las compras en los últimos n días
    private function getPurchasesInLastNDays($days) {
        $diasT = [];
        $comprasT = [];

        for ($i=0; $i < $days; $i++) { 
            $diasT[] = Carbon::today()->subDays($days - $i)->day . '/' . Carbon::today()->subDays($days - $i)->month;
            $comprasT[] = Purchase::where('created_at','>=',Carbon::today()->subDays($days - $i))->where('created_at','<=',Carbon::today()->subDays($days - $i - 1))->count();
        }

        $datosT = [$diasT,$comprasT];
        return $datosT;
    }

    //Devuelve los datos formateados para tabla de las imágenes subidas en el día de hoy
    private function getUploadedImagesToday() {
        $nHoras = Carbon::today()->diff(Carbon::now())->h;
        $horasT = [];
        $imagesT = [];
        
        for ($i=0; $i < $nHoras; $i++) { 
            $horasT[] = $i;
            $imagesT[] = ImageProduct::where('created_at','>=',(Carbon::today()->addHours($i)))->where('created_at','<',(Carbon::today()->addHours($i + 1)))->count();
        }

        $datosT = [$horasT,$imagesT];

        return $datosT;
    }

    //Devuelve los datos formateados para tabla de las imágenes subidas en los últimos n días
    private function getUploadedImagesInLastNDays($days) {
        $diasT = [];
        $imagesT = [];

        for ($i=0; $i < $days; $i++) { 
            $diasT[] = Carbon::today()->subDays($days - $i)->day . '/' . Carbon::today()->subDays($days - $i)->month;
            $imagesT[] = ImageProduct::where('created_at','>=',Carbon::today()->subDays($days - $i))->where('created_at','<=',Carbon::today()->subDays($days - $i - 1))->count();
        }

        $datosT = [$diasT,$imagesT];
        return $datosT;
    }
}
