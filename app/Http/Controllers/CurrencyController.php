<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    private $apiUrl = 'https://api.exchangerate-api.com/v4/latest/USD';

    public function getRates()
    {
        try {
            $rates = Cache::remember('currency_rates', 86400, function () {
                $response = Http::timeout(10)->get($this->apiUrl);
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                throw new \Exception('Failed to fetch currency rates');
            });
            
            return response()->json([
                'success' => true,
                'base' => $rates['base'],
                'rates' => $rates['rates'],
                'last_updated' => $rates['time_last_updated'] ?? now()->toDateTimeString(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch currency rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function convert(Request $request)
    {
        $request->validate([
            'from' => 'required|string|size:3',
            'amount' => 'required|numeric|min:0',
        ]);
        
        try {
            $rates = Cache::remember('currency_rates', 86400, function () {
                $response = Http::timeout(10)->get($this->apiUrl);
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                throw new \Exception('Failed to fetch currency rates');
            });
            
            $fromCurrency = strtoupper($request->from);
            $amount = $request->amount;

            if (!isset($rates['rates'][$fromCurrency])) {
                return response()->json([
                    'success' => false,
                    'message' => "Currency code '{$fromCurrency}' not found"
                ], 404);
            }

            $rateToUSD = $rates['rates'][$fromCurrency];
            $usdAmount = $amount / $rateToUSD;

            return response()->json([
                'success' => true,
                'from' => $fromCurrency,
                'to' => 'USD',
                'amount' => $amount,
                'converted_amount' => round($usdAmount, 2),
                'rate' => round(1 / $rateToUSD, 4),
                'last_updated' => $rates['time_last_updated'] ?? now()->toDateTimeString(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Conversion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
