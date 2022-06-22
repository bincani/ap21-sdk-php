<?php
/**
 * Created by PhpStorm.
 * @author Tareq Mahmood <tareqtms@yahoo.com>
 * Created at 8/19/16 6:05 PM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/multipass Shopify API Reference for Multipass
 */

namespace PHPAP21;


use PHPShopify\Exception\ApiException;

class Multipass extends Ap21Resource
{

    /**
     * Multipass constructor.
     *
     * @param integer $id
     *
     * @throws ApiException
     */
    public function __construct($id = null)
    {
        throw new ApiException("Multipass API is not available yet!");
    }
}