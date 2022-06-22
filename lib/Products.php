<?php
/**
 * class Products
 */

namespace PHPAP21;

class Products extends Ap21Resource
{
    protected $resourceKey = 'product';

    protected $customGetActions = array (
        'product_ids' => 'productIds',
    );
}