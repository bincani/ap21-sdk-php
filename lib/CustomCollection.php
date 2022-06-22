<?php
/**
 * Created by PhpStorm.
 * @author Tareq Mahmood <tareqtms@yahoo.com>
 * Created at 8/19/16 11:46 AM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/customcollection Shopify API Reference for CustomCollection
 */

namespace PHPAP21;


/**
 * --------------------------------------------------------------------------
 * CustomCollection -> Child Resources
 * --------------------------------------------------------------------------
 * @property-read Event $Event
 * @property-read Metafield $Metafield
 *
 * @method Event Event(integer $id = null)
 * @method Metafield Metafield(integer $id = null)
 *
 */
class CustomCollection extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'custom_collection';

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'Event',
        'Metafield',
    );
}