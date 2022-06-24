<?php
/**
 * class Shipment
 */

namespace PHPAP21;

class Shipment extends HTTPXMLResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'shipment';

    /**
     * @inheritDoc
     */
    public $countEnabled = false;

    /**
     * @inheritDoc
     */
    public $readOnly = true;
}