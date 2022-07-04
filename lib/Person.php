<?php
/**
 * class Person
 */

namespace PHPAP21;

class Person extends HTTPXMLResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'person';

    /**
     * @inheritDoc
     */
    public $searchEnabled = true;

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        //'CustomerAddress' => 'Address',
        'RetailTransactions',
        'Shipment',
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

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        //Log::debug(__METHOD__, [get_class($this->dom)]);
        //Log::debug(__METHOD__, [$dataKey, $xml->getName(), $this->pluralizeKey() ]);

        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new Exception(sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey));
        }

        // process collection
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            $att = $xml->attributes();
            $this->productCnt = $att['TotalRows'];
            return $this->processCollection();
        }
        else {
            return $this->processEntity($xml);
        }
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
    protected function processCollection() {
        // loop SimpleXMLElements
        foreach($xml->children() as $person) {
            $id = $person->Id;
            $persons["$id"] = $this->processEntity($person);
        }
        return $persons;
    }

}
