<?php
/**
 * Created by PhpStorm.
 * @author Robert Jacobson <rjacobson@thexroadz.com>
 * @author Matthew Crigger <mcrigger@thexroadz.com>
 *
 * @see https://help.shopify.com/en/api/reference/shopify_payments/payout Shopify API Reference for Shopify Payment Payouts
 */

namespace PHPAP21;

/**
 * --------------------------------------------------------------------------
 * ShopifyPayment -> Child Resources
 * --------------------------------------------------------------------------
 *
 *
 */
class Payouts extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'payout';
}