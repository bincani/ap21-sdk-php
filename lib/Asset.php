<?php
/**
 * Created by PhpStorm.
 * @author Tareq Mahmood <tareqtms@yahoo.com>
 * Created at 8/18/16 3:39 PM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/asset Shopify API Reference for Asset
 */

namespace PHPAP21;


class Asset extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'asset';
}