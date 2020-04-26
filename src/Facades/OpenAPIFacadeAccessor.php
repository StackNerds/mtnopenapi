<?php

namespace StackNerds\MtnOpenAPI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class OpenAPIFacadeAccessor
 *
 * @author  Fenn-CS@StackNerds <normad@stacknerds.com>
 */
class OpenAPIFacadeAccessor extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mtnopenapi.OpenAPI';
    }
}
