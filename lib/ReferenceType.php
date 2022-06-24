<?php
/**
 * class ReferenceType
 */

namespace PHPAP21;

class ReferenceType extends HTTPResource
{
    protected $referenceTypes = [];

    protected $resourceKey = 'ReferenceType';

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * getByCode
     *
     * @param string $code
     * @return [] $referenceTypes
     */
    public function getByCode($code) {
        $found = false;
        if (empty($this->referenceTypes)) {
            $this->referenceTypes = $this->get();
        }
        foreach($this->referenceTypes as $id => $referenceType) {
            if (preg_match(sprintf("/%s/i", $code), $referenceType['code'])) {
                $found = $referenceType;
                break;
            }
        }
        return $found;
    }

    /**
     * processResponse
     *
     * @param [type] $response
     * @param [type] $dataKey
     *
     * @return [] $referenceTypes
     */
    public function processResponse($response, $dataKey = null) {

        if (!$response) {
            $message = "no response";
            throw new ApiException($message, CurlRequest::$lastHttpCode);
        }

        $referenceTypes = $response->getElementsByTagName('referencetype');
        foreach ($referenceTypes as $referenceType) {
            //Log::debug(__METHOD__, [$referenceTypes->nodeValue]);
            $id = $this->innerHTML($referenceType->getElementsByTagName('id')[0]);
            $this->referenceTypes[$id] = [
                'id'    => $id,
                'code'  => $this->innerHTML($referenceType->getElementsByTagName('code')[0]),
                'name'  => $this->innerHTML($referenceType->getElementsByTagName('name')[0])
            ];
        }
        //Log::debug(__METHOD__, [$this->referenceTypes]);
        if (count($this->referenceTypes) == 1) {
            $this->referenceTypes = array_pop($this->referenceTypes);
        }
        return $this->referenceTypes;
    }
}