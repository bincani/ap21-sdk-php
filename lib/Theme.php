<?php
/**
 * Created by PhpStorm.
 * @author Tareq Mahmood <tareqtms@yahoo.com>
 * Created at 8/18/16 10:46 AM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/theme Shopify API Reference for Theme
 */

namespace PHPAP21;


/**
 * --------------------------------------------------------------------------
 * Theme -> Child Resources
 * --------------------------------------------------------------------------
 * @property-read Asset $Asset
 *
 * @method Asset Asset(integer $id = null)
 *
 */
class Theme extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    public $resourceKey = 'theme';

    /**
     * @inheritDoc
     */
    public $countEnabled = false;

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'Asset'
    );
}