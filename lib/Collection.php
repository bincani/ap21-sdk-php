<?php

namespace PHPAP21;

/**
 * --------------------------------------------------------------------------
 * Collection -> Child Resources
 * --------------------------------------------------------------------------
 *
 * @property-read Product $Product
 *
 * @method Product Product(integer $id = null)
 *
 * @see https://shopify.dev/docs/admin-api/rest/reference/products/collection
 *
 */
class Collection extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    public $readOnly = false;

    /**
     * @inheritDoc
     */
    protected $resourceKey = 'collection';

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'Product',
    );
}