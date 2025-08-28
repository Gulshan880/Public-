<?php

namespace Razorpay\Api;

/**
 * Razorpay PHP SDK
 * 
 * @version 2.8.7
 * @license MIT
 * @author  Razorpay
 */

use Requests;
use Exception;
use Razorpay\Api\Errors;
use Razorpay\Api\Errors\ErrorCode;

/**
 * Razorpay PHP SDK
 * 
 * @package Razorpay\Api
 */
class Api
{
    /**
     * @var string
     */
    protected $key = null;

    /**
     * @var string
     */
    protected $secret = null;

    /**
     * @var string
     */
    protected $baseUrl = "https://api.razorpay.com/v1";

    /**
     * @var string
     */
    protected $version = "2.8.7";

    /**
     * @var Request|null
     */
    protected $request = null;

    /**
     * @var array
     */
    protected $appsDetails = array();

    /**
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->request = new Request($this);
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function __get($name)
    {
        $className = __NAMESPACE__.'\\'.ucwords($name);

        $entity = new $className();

        return $entity;
    }

    /**
     * @param array $appsDetails
     */
    public function setAppDetails($appsDetails)
    {
        $this->appsDetails = $appsDetails;
    }

    /**
     * @return array
     */
    public function getAppsDetails()
    {
        return $this->appsDetails;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }
}

/**
 * Request class to communicate with Razorpay API
 */
class Request
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Api $api
     */
    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Make a request to the API
     *
     * @param string $method
     * @param string $relativeUrl
     * @param array $data
     * @param array $additionHeader
     * 
     * @return array
     */
    public function request($method, $relativeUrl, $data = array(), $additionHeader = array())
    {
        $url = $this->api->getBaseUrl() . $relativeUrl;
        
        $headers = $this->getRequestHeaders($additionHeader);
        
        $response = $this->makeRequest($method, $url, $headers, $data);
        
        return $this->processResponse($response);
    }

    /**
     * Get request headers
     *
     * @param array $additionHeader
     * 
     * @return array
     */
    protected function getRequestHeaders($additionHeader = array())
    {
        $userAgent = 'Razorpay/v1 PHPSDK/'.$this->api->getVersion();
        
        $headers = array(
            'Content-type: application/json',
            'Authorization: Basic ' . base64_encode($this->api->getKey() . ':' . $this->api->getSecret()),
            'User-Agent: ' . $userAgent
        );
        
        if (!empty($additionHeader)) {
            $headers = array_merge($headers, $additionHeader);
        }
        
        return $headers;
    }

    /**
     * Make HTTP request
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param array $data
     * 
     * @return array
     */
    protected function makeRequest($method, $url, $headers, $data = array())
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Razorpay-PHP-SDK');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('CURL error: ' . $error);
        }
        
        curl_close($ch);
        
        return array(
            'body' => $response,
            'code' => $httpStatusCode
        );
    }

    /**
     * Process API response
     *
     * @param array $response
     * 
     * @return array
     */
    protected function processResponse($response)
    {
        $body = $response['body'];
        $code = $response['code'];
        
        $responseArray = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid response from API: ' . $body);
        }
        
        if ($code >= 400) {
            $this->handleError($responseArray, $code);
        }
        
        return $responseArray;
    }

    /**
     * Handle API error
     *
     * @param array $response
     * @param int $code
     */
    protected function handleError($response, $code)
    {
        $errorCode = 'NA';
        $description = 'Unknown error occurred';
        
        if (isset($response['error'])) {
            if (isset($response['error']['code'])) {
                $errorCode = $response['error']['code'];
            }
            
            if (isset($response['error']['description'])) {
                $description = $response['error']['description'];
            }
        }
        
        throw new Exception('Razorpay Error: ' . $description . ' (Code: ' . $errorCode . ', HTTP Status: ' . $code . ')');
    }
}

/**
 * Order class for order operations
 */
class Order
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Api $api
     */
    public function __construct($api = null)
    {
        if ($api !== null) {
            $this->api = $api;
        } else {
            $this->api = new Api('', '');
        }
    }

    /**
     * Create a new order
     *
     * @param array $data
     * 
     * @return array
     */
    public function create($data = array())
    {
        return $this->api->getRequest()->request('POST', '/orders', $data);
    }

    /**
     * Fetch an order by ID
     *
     * @param string $id
     * 
     * @return array
     */
    public function fetch($id)
    {
        return $this->api->getRequest()->request('GET', '/orders/' . $id);
    }

    /**
     * Get all orders
     *
     * @param array $options
     * 
     * @return array
     */
    public function all($options = array())
    {
        return $this->api->getRequest()->request('GET', '/orders', $options);
    }

    /**
     * Fetch payments for an order
     *
     * @param string $id
     * 
     * @return array
     */
    public function payments($id)
    {
        return $this->api->getRequest()->request('GET', '/orders/' . $id . '/payments');
    }
}

/**
 * Payment class for payment operations
 */
class Payment
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Api $api
     */
    public function __construct($api = null)
    {
        if ($api !== null) {
            $this->api = $api;
        } else {
            $this->api = new Api('', '');
        }
    }

    /**
     * Fetch a payment by ID
     *
     * @param string $id
     * 
     * @return array
     */
    public function fetch($id)
    {
        return $this->api->getRequest()->request('GET', '/payments/' . $id);
    }

    /**
     * Capture a payment
     *
     * @param string $id
     * @param array $data
     * 
     * @return array
     */
    public function capture($id, $data = array())
    {
        return $this->api->getRequest()->request('POST', '/payments/' . $id . '/capture', $data);
    }

    /**
     * Get all payments
     *
     * @param array $options
     * 
     * @return array
     */
    public function all($options = array())
    {
        return $this->api->getRequest()->request('GET', '/payments', $options);
    }

    /**
     * Refund a payment
     *
     * @param string $id
     * @param array $data
     * 
     * @return array
     */
    public function refund($id, $data = array())
    {
        return $this->api->getRequest()->request('POST', '/payments/' . $id . '/refund', $data);
    }
}

/**
 * Utility class for signature verification
 */
class Utility
{
    /**
     * Verify payment signature
     *
     * @param array $attributes
     * 
     * @return bool
     */
    public function verifyPaymentSignature($attributes)
    {
        $actualSignature = $attributes['razorpay_signature'];
        
        $paymentId = $attributes['razorpay_payment_id'];
        $orderId = $attributes['razorpay_order_id'];
        
        $payload = $orderId . '|' . $paymentId;
        
        $secret = ''; // You need to set your secret key here
        
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        if ($actualSignature === $expectedSignature) {
            return true;
        }
        
        return false;
    }
}

/**
 * Error codes class
 */
class ErrorCode
{
    const BAD_REQUEST_ERROR = 'BAD_REQUEST_ERROR';
    const SERVER_ERROR = 'SERVER_ERROR';
    const GATEWAY_ERROR = 'GATEWAY_ERROR';
}

?>