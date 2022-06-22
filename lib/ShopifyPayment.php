<?php
/**
 * Created by PhpStorm.
 * @author Victor Kislichenko <v.kislichenko@gmail.com>
 * Created at 01/06/2020 16:45 AM UTC+03:00
 *
 * @see https://shopify.dev/docs/admin-api/rest/reference/shopify_payments Shopify API Reference for ShopifyPayment
 */

namespace PHPAP21;


/**
 * --------------------------------------------------------------------------
 * ShopifyPayment -> Child Resources
 * --------------------------------------------------------------------------
 * @property-read Ap21Resource $Dispute
 *
 * @method Ap21Resource Dispute(integer $id = null)
 *
 * @property-read Ap21Resource $Balance
 *
 * @method Ap21Resource Balance(integer $id = null)
 *
 * @property-read Ap21Resource $Payouts
 *
 * @method Ap21Resource Payouts(integer $id = null)
 *

 */
class ShopifyPayment extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    public $resourceKey = 'shopify_payment';

    /**
     * If the resource is read only. (No POST / PUT / DELETE actions)
     *
     * @var boolean
     */
    public $readOnly = true;

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'Balance',
        'Dispute',
        'Payouts',
    );
}