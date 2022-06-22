<?php
/**
 * @see https://help.shopify.com/api/reference/discounts/discountcode Shopify API Reference for PriceRule
 */

namespace PHPAP21;


/**
 * --------------------------------------------------------------------------
 * DiscountCode -> Batch action
 * --------------------------------------------------------------------------
 *
 */

class Batch extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'batch';

    protected function getResourcePath()
    {
        return $this->resourceKey;
    }

    protected function wrapData($dataArray, $dataKey = null)
    {
        return ['discount_codes' => $dataArray];
    }

}
