<?php

namespace Payment\Gateways;

use Payment\Connector\GatewayInterface;
use Payment\Exceptions\ArgumentException;
use Payment\Exceptions\ConfigException;
use Payment\Exceptions\SignatureException;
use Payment\Helper\Arr;
use Payment\Helper\Http;
use Payment\Helper\Str;
use Payment\Payment;

class Alipay extends Payment implements GatewayInterface
{
    /**
     * 支付宝扫码支付【当面付】
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return array
     * @throws ArgumentException
     * @throws ConfigException
     */
    public function native(){
        //请求参数
        $requestConfigs = array(
            'biz_content'=>json_encode([
                'out_trade_no'      => $this->payload['out_trade_no'],
                'total_amount'      => sprintf("%.2f", $this->payload['amount']), //支付宝交易范围  [0.01,100000000]
                'subject'           => $this->payload['subject'],  //订单标题
                'timeout_express'   =>'10m'       //该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。

            ])
        );

        $result = self::getGenerateAlipayOrder($requestConfigs, 'alipay.trade.precreate');

        return [
            'order_qr' => $result['qr_code']
        ];
    }


    public function jsapi()
    {
        // TODO: Implement jsapi() method.
    }

    public function web()
    {
        // TODO: Implement web() method.
    }


    public function app()
    {
        // TODO: Implement app() method.
    }

    public function mini()
    {
        // TODO: Implement mini() method.
    }


    /**
     * 手机网站支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return string
     * @throws ArgumentException
     * @throws ConfigException
     */
    public function wap(){
        //请求参数
        $requestConfigs = [
            'biz_content'=>json_encode([
                'out_trade_no'  => $this->payload['out_trade_no'],
                'total_amount'  => sprintf("%.2f", $this->payload['amount']), //支付宝交易范围  [0.01,100000000]
                'subject'       => $this->payload['subject'],  //订单标题
                'quit_url'      => $this->config['quit_url'],
                'product_code'  => 'QUICK_WAP_WAY'
            ])
        ];

        return  self::getGenerateAlipayOrder($requestConfigs, 'alipay.trade.wap.pay',true);
    }



    /**
     * 转账
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @return array|string
     * @throws ArgumentException
     * @throws ConfigException
     */
    public function transfer(){
        //请求参数
        $requestConfigs = [
            'biz_content'=>json_encode([
                'out_biz_no'=> $this->payload['trade_no'],
                'payee_type'=> 'ALIPAY_LOGONID',
                'payee_account'=> $this->payload['account'],
                'amount'=> sprintf("%.1f", $this->payload['amount'])
            ])
        ];

        return self::getGenerateAlipayOrder($requestConfigs, 'alipay.fund.trans.toaccount.transfer');

    }

    public function query($refund = false)
    {
        // TODO: Implement query() method.
    }


    /**
     * 异步回调地址
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param bool $refund
     *
     * @return array|mixed
     * @throws ConfigException
     * @throws SignatureException
     */
    public function notify($refund = false){
        return $this->verifyAliOrderNotify($refund);
    }

    /**
     * 返回成功通知
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     */
    public function success()
    {
        echo "success";
    }

    /******************************支付宝******************************************/

    /**
     * 支付宝统一
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $requestConfigs
     * @param string $trade_type
     * @param bool $isHtml
     *
     * @return string|array
     * @throws ArgumentException
     * @throws ConfigException
     */
    private function getGenerateAlipayOrder($requestConfigs, $trade_type = 'alipay.trade.pay', $isHtml = false){

        $commonConfigs = array(
            //公共参数
            'app_id'        => $this->config['app_id'],
            'method'        => $trade_type,             //接口名称
            'format'        => 'JSON',
            'charset'       => 'utf-8',
            'sign_type'     =>'RSA2',
            'timestamp'     => date('Y-m-d H:i:s'),
            'version'       =>'1.0',
            'notify_url'    => $this->config['notify_url'],
        );
        // 附加公共参数
        if (!empty($requestConfigs)){
            foreach ($requestConfigs as $key => $val){
                $commonConfigs[$key] = $val;
            }
        }
        //签名
        $commonConfigs["sign"] = $this->generateAlipaySign($commonConfigs, $commonConfigs['sign_type']);
        // 判断是否网页支付
        if ($isHtml){
            return Str::buildRequestForm($commonConfigs,'https://openapi.alipay.com/gateway.do');
        }
        //请求
        $response = Http::curlPost('https://openapi.alipay.com/gateway.do',$commonConfigs);
        // 转码
        $response = json_decode(Str::encoding($response,'utf-8', $response['charset'] ?? 'gb2312'),true);

        if(!isset($response['error_response'])){
            // 读取数据
            $result = $response[str_replace(".","_",$trade_type) .'_response'];
            // 数据判断
            if (isset($result['code']) && $result['code'] != '10000') {

                throw new ArgumentException('Create Alipay API Error:'. $result['msg'].' : '.$result['sub_msg']);
            }
            return $result;
        }

        throw new ArgumentException('Create Alipay API Error:'. $response['error_response']['msg'].' : '.$response['error_response']['sub_msg']);
    }

    /**
     * 回调验签
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $refund
     *
     * @return array|mixed
     * @throws ConfigException
     * @throws SignatureException
     */
    public function verifyAliOrderNotify($refund){

        $response = Arr::convertUrlArray(file_get_contents('php://input')); //支付宝异步通知POST返回数据
        //转码
        $response = Str::encoding($response,'utf-8', $response['charset'] ?? 'gb2312');

        //验签
        if (!empty($response) || $this->verify($this->getSignContent($response, true), $response['sign'], $response['sign_type'])) {

            return $response;
        }

        throw new SignatureException('Verify Alipay Sign Error: 签名验证失败');
    }

    /**
     * 支付宝签名
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $params
     * @param $signType
     *
     * @return string
     * @throws ConfigException
     */
    protected function generateAlipaySign($params, $signType){

        return $this->sign($this->getSignContent($params), $signType);
    }

    /**
     * 签名
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $data
     * @param string $signType
     *
     * @return string
     * @throws ConfigException
     */
    protected function sign($data, $signType = "RSA") {
        $priKey = $this->config['private_key'];

        if (is_null($priKey)){
            throw new ConfigException('generate Alipay Sign Error: [支付宝密钥为空.]');
        }

        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
        } else {
            openssl_sign($data, $sign, $res);
        }

        $sign = base64_encode($sign);

        return $sign;
    }

    /**
     * 验证
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $data
     * @param $sign
     * @param string $signType
     *
     * @return bool
     * @throws ConfigException
     */
    protected function verify($data, $sign, $signType = 'RSA') {
        $pubKey= $this->config['public_key'];

        if (is_null($pubKey)){
            throw new ConfigException('generate Alipay Sign Error: 密钥错误');
        }

        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }

        return $result;
    }

    /**
     * 签名排序
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $params
     * @param $verify
     *
     * @return string
     */
    public function getSignContent($params, $verify =false) {

        $data = Str::encoding($params, $params['charset'] ?? 'gb2312');

        ksort($data);

        $stringToBeSigned = '';

        foreach ($data as $k => $v) {
            if (false ===  Str::checkEmpty($v) && "@" != substr($v, 0, 1)) {
                if ($verify && $k != 'sign' && $k != 'sign_type') {
                    $stringToBeSigned .= $k . '=' . $v . '&';
                }
                if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                    $stringToBeSigned .= $k . '=' . $v . '&';
                }
            }
        }
        return trim($stringToBeSigned, '&');
    }

}