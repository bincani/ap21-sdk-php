<?php
/**
 * class Order
 */

namespace PHPAP21;

/**
 * @method array close() Close an Order
 * @method array open() Re-open a closed Order
 * @method array cancel(array $data) Cancel an Order
 *
 */
class Order extends HTTPXMLResource
{
    protected $resourceKey = 'order';

    /**
     * @inheritDoc
     */
    protected $childResource = array (
    );

    /**
     * @inheritDoc
     */
    protected $customPostActions = array(
        'close',
        'open',
        'cancel',
    );
}