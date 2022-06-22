<?php
/**
 * Created by PhpStorm.
 * @author Tareq Mahmood <tareqtms@yahoo.com>
 * Created at 8/19/16 5:28 PM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/fulfillmentservice Shopify API Reference for FulfillmentService
 */

namespace PHPAP21;


class FulfillmentService extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'fulfillment_service';

    /**
     * @inheritDoc
     */
    public $countEnabled = false;
}