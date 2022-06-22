<?php
/**
 * class Product
 */

namespace PHPAP21;

/**
 * --------------------------------------------------------------------------
 * Product -> Child Resources
 * --------------------------------------------------------------------------
 * @property-read ProductImage $Image
 * @property-read ProductVariant $Variant
 * @property-read Metafield $Metafield
 * @property-read Event $Event
 *
 * @method ProductImage Image(integer $id = null)
 * @method ProductVariant Variant(integer $id = null)
 * @method Metafield Metafield(integer $id = null)
 * @method Event Event(integer $id = null)
 *
 */
class Product extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    public $resourceKey = 'product';

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'ProductImage'      => 'Image',
        'ProductVariant'    => 'Variant',
        'Metafield',
        'Event'
    );
}