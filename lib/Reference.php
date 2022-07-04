<?php
/**
 * class Reference
 */

namespace PHPAP21;

class Reference extends HTTPXMLResource
{
    protected $reference = [];

    protected $resourceKey = 'Reference';

    public $id;
    public $code;
    public $name;
    public $references = [];

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * getValue
     *
     * @param string $id
     * @return string $val
     */
    public function getValue($id) {
        Log::debug(__METHOD__, [$id, empty($this->references)]);
        $found = false;
        if (empty($this->references)) {
            $obj = $this->get();
            //Log::debug(__METHOD__, [$obj]);
            $this->id = $obj->id;
            $this->code = $obj->code;
            $this->name = $obj->name;
            $this->references = $obj->references;
        }
        foreach($this->references as $rId => $reference) {
            Log::debug(__METHOD__, [$rId, $id, ($rId == $id)]);
            if ($rId == $id) {
                $found = $reference['name'];
                break;
            }
        }
        return [$this->code, $found];
    }

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $reference
     */
    public function processResponse($xml, $dataKey = null) {

        Log::debug(__METHOD__, [get_class($xml), is_object($xml)]);
        //Log::debug(__METHOD__, [$dataKey, $this->xml->getName(), $this->pluralizeKey() ]);

        // sanity check
        if (strcasecmp("ReferencesbyType", $xml->getName()) !== 0) {
            throw new \Exception(sprintf("invalid response %s! expecting %s", $xml->getName(), "ReferencesbyType"));
        }

        // process collection
        //Log::debug(sprintf("%s->response: %s", __METHOD__, print_r($xml[0], true)) );
        $this->id = (string)$xml[0]->ReferenceTypeId;
        $this->code = (string)$xml[0]->ReferenceTypeCode;
        $this->name = (string)$xml[0]->ReferenceTypeName;
        Log::debug(__METHOD__, [$this->id, $this->code, $this->name]);
        $this->processEntity($xml[0]->References);
        //Log::debug(__METHOD__, [$this->references]);
        return (object) [
            'id'    => $this->id,
            'code'  => $this->code,
            'name'  => $this->name,
            'references' => $this->references
        ];
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($references) {
        foreach($references->children() as $ref) {
            $rId = (string)$ref->Id;
            $rCode = (string)$ref->Code;
            $rName = (string)$ref->Name;
            //Log::debug(sprintf("col->%s|%s", $cCode, cName));
            $this->references[$rId] = [
                'id'    => $rId,
                'code'  => $rCode,
                'name'  => $rName
            ];
        }
        return $this->references;
    }

}