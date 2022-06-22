<?php
/**
 * Created by PhpStorm.
 * @author Sergiu Cazac <kilobyte2007@gmail.com>
 * Created at 5/06/19 2:06 AM UTC+03:00
 *
 * @see https://help.shopify.com/api/reference/discounts/pricerule Shopify API Reference for PriceRule
 */

namespace PHPAP21;


/**
 * --------------------------------------------------------------------------
 * PriceRule -> Child Resources
 * --------------------------------------------------------------------------
 * @property-read Ap21Resource $DiscountCode
 *
 * @method Ap21Resource DiscountCode(integer $id = null)
 * @method Ap21Resource Batch()
 *
 */
class PriceRule extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    public $resourceKey = 'price_rule';

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'DiscountCode',
        'Batch',
    );
}
