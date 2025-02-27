<?php
/**
 * class Person
 */

namespace PHPAP21;

class Person extends HTTPXMLResource
{
    const PAGE_LIMIT = 0;
    const DEFAULT_PAGE_ROWS = 500;

    protected $persons = [];
    protected $personLimit = 0;
    protected $totalPersons = 0;
    protected $totalPages = 0;

    protected $resourceKey = 'person';
    protected $dom;

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        //'CustomerAddress' => 'Address',
        'RetailTransactions',
        'Shipment',
        'Orders',
        'Order'
    );

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $people
     */
    public function processResponse($xml, $dataKey = null) {

        if (empty($xml)) {
            if (in_array($this->getMethod(), ['GET'])) {
                $message = sprintf("%s->no response for method %s", __METHOD__, $this->getMethod());
                throw new ApiException($message, CurlRequest::$lastHttpCode);
            }
            else {
                return '';
            }
        }

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        //Log::debug(__METHOD__, [get_class($this->dom)]);
        //Log::debug(__METHOD__, [$dataKey, $xml->getName(), $this->pluralizeKey() ]);
        //Log::debug(__METHOD__, [$xml->asXML()]);

        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new Exception(sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey));
        }

        // process collection
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            $att = $xml->attributes();
            $this->totalPersons = $att['TotalRows'];
            return $this->processCollection($xml);
        }
        else {
            return $this->processEntity($xml);
        }
    }

    /**
     * Generate a HTTP GET request and return results as an array
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     * @param string $url
     * @param string $dataKey Keyname to fetch data from response array
     *
     * @uses HttpRequestXml::get() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        // limit
        if (array_key_exists('limit', $urlParams)) {
            $this->personLimit = $urlParams['limit'];
            unset($urlParams['limit']);
        }

        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        Log::debug(sprintf("%s->url: %s", __METHOD__, $url) );
        if (!$dataKey) {
            $dataKey = $this->id ? $this->resourceKey : $this->pluralizeKey();
        }

        Log::debug(sprintf("%s->dataKey: %s", __METHOD__, $dataKey), [$this->id]);
        Log::debug(sprintf("%s->httpHeaders", __METHOD__), $this->httpHeaders);

        $response = HttpRequestXml::get($url, $this->httpHeaders);
        Log::debug(sprintf("%s->response.length: %d", __METHOD__, strlen($response)), []);

        // implement paging
        if (array_key_exists('startRow', $urlParams)) {
            // set up paging
            $page = 1;
            $startRow = $urlParams['startRow'];
            $urlParams['pageRows'] = array_key_exists('pageRows', $urlParams) ? $urlParams['pageRows'] : self::DEFAULT_PAGE_ROWS;
            // set to limit if greater than limit
            if ($this->personLimit != 0 && $urlParams['pageRows'] > $this->personLimit) {
                $urlParams['pageRows'] = $this->personLimit;
            }

            $this->xml = $this->processResponse($response, $dataKey);
            // calculate the total number of pages
            $this->totalPages = ceil($this->totalPersons / $urlParams['pageRows']);

            Log::info(sprintf("%s->processNextPage", __METHOD__), [
                sprintf('page: %d/%d', $page, $this->totalPages),
                'startRow:' . $urlParams['startRow'],
                'pageRows:' . $urlParams['pageRows'],
                'total:' . $this->totalPersons,
                'limit:' . $this->personLimit
            ]);

            // check we arent already at our limit
            Log::debug(sprintf("%s->check limit %d >= %d", __METHOD__, count($this->xml), $this->personLimit), []);
            if ($this->personLimit != 0 && count($this->xml) >= $this->personLimit) {
                Log::debug(sprintf("%s->person limit %d reached!", __METHOD__, $this->personLimit), []);
            }
            else {
                do {
                    // set startRow to the next amount
                    $urlParams['startRow'] = ($urlParams['pageRows'] * $page) + 1;
                    $url = $this->generateUrl($urlParams);

                    $response = HttpRequestXml::get($url, $this->httpHeaders);
                    Log::info(sprintf("%s->response.length: %d", __METHOD__, strlen($response)), []);

                    if (empty($response) || strlen($response) == 0) {
                        Log::debug(sprintf("%s->end reached!", __METHOD__), []);
                        break;
                    }
                    if (self::PAGE_LIMIT != 0 && $page < self::PAGE_LIMIT) {
                        Log::debug(sprintf("%s->page limit %d reached!", __METHOD__, self::PAGE_LIMIT), []);
                        break;
                    }
                    $page++;
                    Log::info(sprintf("%s->processNextPage", __METHOD__), [
                        sprintf('page: %d/%d', $page, $this->totalPages),
                        'startRow:' . $urlParams['startRow'],
                        'pageRows:' . $urlParams['pageRows'],
                        'total:' . $this->totalPersons,
                        'limit:' . $this->personLimit,
                        'count:' . count($this->xml),
                        sprintf("yes: %d", (count($this->xml) >= $this->personLimit))
                    ]);
                    $persons = $this->processResponse($response, $dataKey);

                    if ($persons && is_array($persons)) {
                        $this->xml = array_merge($this->xml, $persons);
                    }
                    if ($this->personLimit != 0 && count($this->xml) >= $this->personLimit) {
                        Log::debug(sprintf("%s->person limit %d reached!", __METHOD__, $this->personLimit), []);
                        break;
                    }
                }
                while($response);
            }
        }
        else {
            Log::debug(sprintf("%s->%s->processResponse", __METHOD__, get_class($this)), [get_class($response)]);
            $this->xml = $this->processResponse($response, $dataKey);
        }
        return $this->xml;
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($person) {
        $id = $person->Id;
        Log::debug(sprintf("%s->%s|%s|%s", __METHOD__, $id, $person->Code, $person->Name));

        $addresses = [];
        foreach($person->Addresses->children() as $address) {
            $type = strtolower((string)$address->getName());
            //Log::debug(__METHOD__, [$type]);
            $addresses[$type] = [
                'line1' => (string)$address->AddressLine1,
                'line2' => (string)$address->AddressLine2,
                'city'  => (string)$address->City,
                'state' => (string)$address->State,
                'postcode'  => (string)$address->Postcode,
                'country'   => (string)$address->Country
            ];
        }
        $contacts = [];
        foreach($person->Contacts->children() as $contact) {
            $type = (string)$contact->getName();
            //Log::debug(__METHOD__, [$type, get_class($contact)]);
            if (preg_match("/phones/i", $type)) {
                foreach($contact->children() as $phone) {
                    $type = (string)$phone->getName();
                    $contacts[$type] = (string)$phone;
                }
            }
            else {
                $contacts[$type] = (string)$contact;
            }
        }
        $person = [
            'code'      => (string)$person->Code,
            'firstname' => (string)$person->Firstname,
            'surname'   => (string)$person->Surname,
            'addresses' => $addresses,
            'contacts'  => $contacts
        ];
        return $person;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection($xml) {
        // loop SimpleXMLElements
        foreach($xml->children() as $person) {
            $id = $person->Id;
            $persons["$id"] = $this->processEntity($person);
        }
        return $persons;
    }

    /**
     * getTotalPages
     *
     * @return int
     */
    public function getTotalPages() {
        return $this->totalPages;
    }

    /**
     * getTotalPersons
     *
     * @return int
     */
    public function getTotalPersons() {
        return $this->totalPersons;
    }

}
