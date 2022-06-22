<?php

namespace PHPAP21;

class TenderTransaction extends Ap21Resource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'tender_transaction';

    /**
     * If the resource is read only. (No POST / PUT / DELETE actions)
     *
     * @var boolean
     */
    public $readOnly = true;
}
