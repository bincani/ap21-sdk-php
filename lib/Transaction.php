<?php
/**
 * Created by PhpStorm.
 * @author Tareq Mahmood <tareqtms@yahoo.com>
 * Created at 8/19/16 7:27 PM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/transaction Shopify API Reference for Transaction
 */

namespace PHPAP21;


class Transaction extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'transaction';
}