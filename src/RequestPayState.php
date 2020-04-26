<?php

namespace StackNerds\MtnOpenAPI;

class RequestPayState
{
    private $failed;
    private $pending;
    private $rejected;
    private $successful;
    private $unknownError;
    private $providerError;
    private $payment;

    /**
     * RequestPayState constructor.
     * @param $payment object returned containing 
     */
    public function __construct($payment)
    {
        if ($payment) {
            $this->payment = $payment;
            if ($payment->status === "PENDING") {
                $this->pending = true;
            } else if ($payment->status === "SUCCESSFUL") {
                $this->successful = true;
            } else if ($payment->status === "FAILED") {
                if ($payment->reason === "APPROVAL_REJECTED") {
                     $this->rejected = true;
                } else if ($payment->reason === "INTERNAL_PROCESSING_ERROR") {
                    $this->providerError = true;
                }
            } else {
                $this->unknownError = true;
            }
        } else {
            $this->unknownError = true;
        }
    }

    public function failed()
    {
        return $this->failed;
    }

    public function pending()
    {
        return $this->pending;
    }

    public function rejected()
    {
        return $this->rejected;
    }

    public function successful()
    {
        return $this->successful;
    }

    public function unknownError()
    {
        return $this->unknownError;
    }

    public function providerError()
    {
        return $this->providerError;
    }

    public function paymentObject()
    {
        return $this->payment;
    }
}
