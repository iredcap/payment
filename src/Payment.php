<?php

namespace Payment;

use Payment\Exceptions\GatewayException;

class Payment
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var
     */
    protected $payload = [];

    /**
     * constructor.
     *
     * @param array     $config
     * @param array     $payload
     */
    private function __construct($config = [], $payload = [])
    {
        $this->config   = array_merge($this->config, $config);
        $this->payload  = array_merge($this->payload, $payload);
    }


    /**
     * 构建支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param string    $method
     * @param array     $options
     * @param array     $payload
     *
     * @return mixed
     * @throws \Exception
     */
    public static function createPayment($method, $options, $payload){

        $payment = __NAMESPACE__ . '\\Gateways\\' . ucwords($method);

        if (class_exists($payment)) {
            return new $payment($options,$payload);
        }

        throw new GatewayException("Payment Gateways [{$method}] Not Exists.");
     }

}