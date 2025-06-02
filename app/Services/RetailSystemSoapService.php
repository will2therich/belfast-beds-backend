<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RetailSystemSoapService
{

    private $soapOptions;

    private $parameters;

    public function __construct()
    {

        $this->soapOptions = [
            'trace'        => 1, // Enable tracing to see request/response
            'exceptions'   => true, // Throw SoapFault exceptions on errors
            'cache_wsdl'   => WSDL_CACHE_NONE, // Disable WSDL caching for development
            'soap_version' => SOAP_1_2, // Explicitly use SOAP 1.2
            'encoding'     => 'UTF-8',
        ];

        $this->parameters = [
            'AccountGUID' => env("RS_GUID"),
            'UserLogin'   => env("RS_LOGIN"),
        ];

    }


    /**
     * Get The Catalog from retailsystem GetCatalog Endpoint.
     * Cached per hour
     *
     * @return mixed
     */
    public function getCatalog()
    {
        return Cache::remember('rs_catalog', 3600, function () {
            try {
                $wsdl = 'https://belfastbeds.retailsystem.net/services/v2/GetCatalog.asmx?WSDL';
                $soapClient = new \SoapClient($wsdl, $this->soapOptions);

                $result = $soapClient->GetCatalog($this->parameters);

                $xml = new \SimpleXMLElement($result->GetCatalogResult->any);
                return json_decode(json_encode((array)$xml), true);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        });
    }

    /**
     * Get The Catalog from retailsystem GetCatalog Endpoint.
     * Cached per hour
     *
     * @return mixed
     */
    public function getStock()
    {
        return Cache::remember('rs_stock', 3600, function () {
            try {
                $wsdl = 'https://belfastbeds.retailsystem.net/services/v2/Stock.asmx?WSDL';
                $soapClient = new \SoapClient($wsdl, $this->soapOptions);

                $result = $soapClient->StockListing($this->parameters);

                $xml = new \SimpleXMLElement($result->StockListingResult->any);
                return json_decode(json_encode((array)$xml), true);
            } catch (\Exception $e) {
                dd($e->getMessage());
                Log::error($e->getMessage());
            }
        });
    }
}
