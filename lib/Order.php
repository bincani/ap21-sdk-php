<?php
/**
 * class Order
 */

namespace PHPAP21;

use PHPAP21\Person\Orders;

/**
 * @method array close() Close an Order
 * @method array open() Re-open a closed Order
 * @method array cancel(array $data) Cancel an Order
 *
 */
class Order extends Orders
{

}