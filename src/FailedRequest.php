<?php

namespace StackNerds\MtnOpenAPI;

class FailedRequest extends \Exception
{
    /**
     * FailedRequest constructor.
     * @param $response object returned containing 
     * @param $exception GuzzleException object.
     */
    public function __construct($exception)
    {
        parent::__construct($exception->getMessage(), $exception->getCode(), $exception);
        print_r($exception->getMessage());
        $object = json_decode($exception->getMessage());
        print_r($object);
    }

    public function is400()
    {
        return $this->code == 400 ? true : false;
    }

    public function is404()
    {
        return $this->code == 404 ? true : false;
    }

    public function is500()
    {
        return $this->code == 500 ? true : false;
    }

}
