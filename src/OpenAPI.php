<?php

namespace StackNerds\MtnOpenAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

define("BASE_URL", "https://sandbox.momodeveloper.mtn.com/v1_0");
define("BASE_URL_COLLECTION", "https://sandbox.momodeveloper.mtn.com/collection");

/**
 * Class OpenAPI
 *
 * @author  Fenn-CS@StackNerds <normad@stacknerds.com>
 */
class OpenAPIG
{

    /**
     * @var  \StackNerds\MtnOpenAPI\Config
     */
    private $config;
    /**
     * @var string
     */
    private $subscriptionKey;
    /**
     * @var string use to store
     */
    private $target_environment;
    /**
     * OpenAPI constructor.
     *
     * @param \StackNerds\MtnOpenAPI\Config $config
     */
    public function __construct($ocp_subscription_key, $target_environment = "sandbox")
    {
        $this->subscriptionKey = $ocp_subscription_key;
        $this->target_environment = $target_environment;
    }

    private function gen_uuid()
    {
        try {
            return (string) Uuid::uuid4();
        } catch (UnsatisfiedDependencyException $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

    /**
     * @param $reference_id
     * example response:
     * @param $callbackUrl
     * @return string|false return the reference_id if successful or false if it fails
     */
    function createAPIUser($reference_id, $callbackUrl)
    {
        $url = BASE_URL . "/apiuser";
        if ($reference_id === null) {
            $reference_id = $this->gen_uuid();
        }
        $body = [
            'providerCallbackHost' => $callbackUrl
        ];

        $headers = [
            'Content-Type' => "application/json",
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            'X-Reference-Id' => $reference_id
        ];
        $response = $this->guzzleRequest($url, $headers, $body, "POST", false);
        return ($response['status'] == 201) ? $reference_id : false;
    }

    public function get_api_key($reference_id)
    {
        $url = BASE_URL . "/apiuser" . '/' . $reference_id . "/apikey";
        $headers = [
            'Content-Type' => "application/json",
            'Host' => "sandbox.momodeveloper.mtn.com",
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
        ];
        $response = $this->guzzleRequest($url, $headers, null);
        return ($response['status'] == 201) ? $response['body']->apiKey : null;
    }

    /**
     * retrieve the access token for the provided reference_id(api_user) and $api_key pair
     * @param $reference_id
     * @param $api_key
     * @return mixed|null an object with keys access_token,token_type, expires_in or null on failure
     */
    public function get_access_token($reference_id, $api_key)
    {
        $url = BASE_URL_COLLECTION . "/token/";
        $data_base64 = base64_encode("$reference_id:$api_key");
        $headers = array(
            'Authorization' => "Basic $data_base64",
            'Content-Type' => "application/json",
            'Host' => "sandbox.momodeveloper.mtn.com",
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
        );
        $response = $this->guzzleRequest($url, $headers, null);
        return ($response['status'] == 200) ? $response['body'] : null;
    }

    /**
     * @param  $access_token
     * @param RequestPayBody $requestPayBody
     * @param $callback_url string not used for testing
     * @param $external_reference string external reference id
     * @return bool true when successful or false other wise
     */
    public function requestPay($access_token, RequestPayBody $requestPayBody, $callback_url = "", $external_reference)
    {
        $url = BASE_URL_COLLECTION . "/v1_0/requesttopay";
        $headers = [
            'Authorization' =>  "Bearer $access_token",
            'X-Reference-Id' => $external_reference,
            'X-Target-Environment' => $this->target_environment,
            'Content-Type' => "application/json",
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey
        ];
        $response = $this->guzzleRequest($url, $headers, $requestPayBody);
        return ($response['status'] == 202) ? true : false;
    }

    /**
     * @param $access_token
     * @param $reference_id
     * @return mixed
     */
    public function checkPaymentStatus($access_token, $reference_id)
    {
        $url = BASE_URL_COLLECTION . "/v1_0/requesttopay/$reference_id";
        $headers = [
            'Authorization' =>  "Bearer $access_token",
            'X-Target-Environment' => $this->target_environment,
            'Content-Type' => "application/json",
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey
        ];
        $response = $this->guzzleRequest($url, $headers, '{}', 'GET');
        $requestPayState = new RequestPayState($response['body']);
        return $requestPayState;
    }


    public function checkBalance($access_token)
    {
        $url = BASE_URL_COLLECTION . "/v1_0/account/balance";
        $headers = [
            'Authorization' =>  "Bearer $access_token",
            'Content-Type' => "application/json",
            'X-Target-Environment' => $this->target_environment,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey
        ];
        $response = $this->guzzleRequest($url, $headers, '{}', 'GET');
        return $response['body'];
    }

    public function isUserAccountActive($access_token, $partyIdType, $partyId)
    {
        $url = BASE_URL_COLLECTION . "/v1_0/accountholder/$partyIdType/$partyId/active";
        $headers = [
            'Authorization' =>  "Bearer $access_token",
            'Content-Type' => "application/json",
            'X-Target-Environment' => $this->target_environment,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey
        ];
        $response = $this->guzzleRequest($url, $headers, '{}', 'GET');
        //  print_r($response);
        //todo may be an error too
        return ($response['status'] == 200) ? true : false;
    }

    /**
     * Use to make http request
     * @param $url string the url to send the request
     * @param $headers array of string. the headers for the http request
     * @param $body array|object associative array of the http body
     * @param string $type request type POST or GET, default is POST
     * @param bool $returnResult , the return type is boolean when false or the result of the request when true
     * @return mixed|null return array of request result when $returnResult=true or boolean when $returnResult=false
     */
    private function guzzleRequest($url, $headers, $body, $type = "POST", $returnResult = true)
    {
        $client = new Client();
        $response = null;
        try {
            $response = $client->request($type, $url, [
                'headers' => $headers,
                'body' => json_encode($body)
            ]);
            return ['status' => $response->getStatusCode(), 'body' => json_decode($response->getBody())];
        } catch (GuzzleException $exp) {
            throw new FailedRequest($exp);
        }
    }

    /**
     * @param $name
     *
     * @return  string
     */
    public function sayHello($name)
    {
        $greeting = $this->config->get('greeting');

        return $greeting . ' ' . $name;
    }
}
