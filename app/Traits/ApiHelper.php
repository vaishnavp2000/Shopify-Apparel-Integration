<?php

namespace App\Traits;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

trait ApiHelper
{
   

     public function getHttp($queryString, $variables){
        $domain = Setting::where('type','shopify')->where('code','shopify_store')->where('status',1)->value('value');
        $domain='alvarez-marsal.myshopify.com';
        $url = 'https://' . $domain . '/admin/api/2025-07/graphql.json';
        try 
        {
            $response = Http::accept('application/json')->withHeaders([
                'Content-Type' => 'application/json' ,
                'X-Shopify-Access-Token' =>'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',
                ])
                ->post($url, ['query' => $queryString,'variables'=> $variables])
                ->throw();
    
            return $response->json();
        } 
        catch (\Exception $e) 
        {
            return ['status' => 'failure', 'message' => $e->getMessage()];
        }

    }

}