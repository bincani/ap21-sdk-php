<?php

namespace PHPAP21;

/**
 * Class AccessScope
 * @package PHPShopify
 * @author Alexey Sinkevich
 */
class AccessScope extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'access_scope';

    /**
     * @inheritDoc
     */
    public $countEnabled = true;

    /**
     * @inheritDoc
     */
    public $readOnly = true;

    /**
     * @param array $urlParams
     * @param null $customAction
     * @return string
     */
    public function generateUrl($urlParams = array(), $customAction = null)
    {
        $url = sprintf(
            "%s%s",
            ShopifySDK::$config['AdminUrl'],
            $this->getResourcePath(),
            isset(ShopifySDK::$config['CountryCode']) ? sprintf("?CountryCode=%s", ShopifySDK::$config['CountryCode']) : ""
        ;
        Log::info(sprintf("%s->url", __METHOD__), [$url]);
        return $url;
    }
}
