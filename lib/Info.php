<?php
/**
 * class Info
 */

namespace PHPAP21;

class Info extends Ap21Resource
{
    protected $info = [];

    protected $resourceKey = '';

    public $countEnabled = false;

    public $readOnly = true;

    public function pluralizeKey()
    {
        // no pluralize
        return '';
    }

    /**
     * Process the request response
     *
     * @param array $responseArray Request response in array format
     * @param string $dataKey Keyname to fetch data from response array
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function processResponse($responseArray, $dataKey = null)
    {
        $lastResponseHeaders = CurlRequest::$lastHttpResponseHeaders;

        if (!$responseArray) {
            $message = "no response";
            throw new ApiException($message, CurlRequest::$lastHttpCode);
        }
        $paras = $responseArray->getElementsByTagName('p');
        $getPayloads = false;
        foreach ($paras as $para) {
            //Log::debug(__METHOD__, [$para->nodeValue]);
            if (preg_match("/Retail API Release/i", $para->nodeValue)) {
                $this->info['api_ver'] = (float) filter_var(
                    $para->nodeValue,
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                );
            }
            else if (preg_match("/Database Connection Details/i", $para->nodeValue)) {
                preg_match_all('/Apparel21 Database Name(.*)/', $para->nodeValue, $matches);
                $this->info['db_name'] = trim($matches[1][0]);
            }
            else if (preg_match("/Database connection is available/i", $para->nodeValue)) {
                $this->info['db_ver'] = (float) filter_var(
                    $para->nodeValue,
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                );
            }
            else if (preg_match("/Apparel21 client DLL version/i", $para->nodeValue)) {
                preg_match('/[0-9]+\.[0-9]+/', $para->nodeValue, $matches);
                $this->info['ddl_ver'] = (float)$matches[0];
            }
            else if (preg_match("/Supported payload versions/i", $para->nodeValue)) {
                $getPayloads = true;
            }
            else if ($getPayloads) {
                if (preg_match("/[0-9]+\.[0-9]+/", $para->nodeValue, $matches)) {
                    $payloads[] = (float)$matches[0];
                }
                else {
                    $getPayloads = false;
                }

            }
        }
        $this->info['payloads'] = $payloads;
        //Log::debug(__METHOD__, [$this->info]);
        return $this->info;
    }
}