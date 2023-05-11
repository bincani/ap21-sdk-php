<?php
/**
 * class Colours
 *
 * GET https://retailapi.apparel21.com/RetailAPI/Colours?countryCode=AU&query=1,1506
 */

namespace PHPAP21;

class Colour extends HTTPXMLResource
{
    protected $colours = [];

    protected $resourceKey = 'Colour';

    public $dom;

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $colours
     */
    public function processResponse($xml, $dataKey = null) {

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        Log::debug(__METHOD__, [get_class($this->dom)]);

        $colours = $this->dom->getElementsByTagName('Colour');
        //Log::debug(__METHOD__, ["colours.count: " . count($colours)]);

        // no colour
        if ($colours->length == 0) {
            $this->processEntity($this->dom);
        }
        else {
            foreach ($colours as $colour) {
                $this->processEntity($colour);
            }
        }
        //Log::debug(__METHOD__, [$this->colours]);
        if (count($this->colours) == 1) {
            $this->colours = array_pop($this->colours);
        }
        return $this->colours;
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($colour) {
        $code = $this->innerHTML($colour->getElementsByTagName('Code')[0]);
        $name = $this->innerHTML($colour->getElementsByTagName('Name')[0]);
        //Log::debug(__METHOD__, [$code, $name, $colour->nodeValue]);
        $this->colours[$code] = [
            'code' => $code,
            'name' => $name
        ];
    }

}