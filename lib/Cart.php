<?php

namespace PHPAP21;

/* @see https://shopify.dev/docs/themes/ajax-api/reference/cart */
use PHPShopify\Ap21Resource;

class Cart extends Ap21Resource {

     /**
     * @inheritDoc
     */
    protected $resourceKey ='cart';

     /**
     * @inheritDoc
     */
    public $searchEnabled = false;

     /**
     * @inheritDoc
     */
    public $readOnly = true;
}
