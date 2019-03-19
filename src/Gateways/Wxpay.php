<?php

namespace Payment\Gateways;

use Payment\Connector\GatewayInterface;
use Payment\Exceptions\ArgumentException;
use Payment\Exceptions\SignatureException;
use Payment\Helper\Arr;
use Payment\Helper\Http;
use Payment\Helper\Str;
use Payment\Payment;

class Wxpay extends Payment implements GatewayInterface
{
    /**
     * 扫码支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return array
     * @throws ArgumentException
     */
    public function native(){
        //获取预下单
        $unifiedOrder = self::getWxpayUnifiedOrder($this->payload);
        //数据返回
        return [
            'prepay_id' => $unifiedOrder['prepay_id'],
            'order_qr' => $unifiedOrder['code_url']
        ];
    }

    /**
     * 微信公众号支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return array
     * @throws ArgumentException
     */
    public function jsapi(){
        //获取预下单
        $unifiedOrder = self::getWxpayUnifiedOrder($this->payload, 'JSAPI');
        //构建微信支付
        $jsBizPackage = array(
            "appId" => $this->config['app_id'],
            "timeStamp" => (string)time(),        //这里是字符串的时间戳
            "nonceStr" => Str::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder['prepay_id'],
            "signType" => 'MD5',
        );
        $jsBizPackage['paySign'] = self::getWxpaySign($jsBizPackage, $this->config['mch_key']);

        //数据返回
        return $jsBizPackage;
    }

    /**
     * 微信APP支付【待测】
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return array
     * @throws ArgumentException
     */
    public function app(){
        //获取预下单
        $unifiedOrder = self::getWxpayUnifiedOrder($this->payload, 'JSAPI');
        //构建微信支付
        $jsBizPackage = array(
            "appid" => $this->config['app_id'],  //应用号
            "partnerid" => $this->config['mch_id'], //商户号
            "prepayid" => $unifiedOrder['prepay_id'],
            "package" => "Sign=WXPay",
            "timeStamp" => (string)time(),        //这里是字符串的时间戳
            "nonceStr" => Str::createNonceStr()
        );
        $jsBizPackage['sign'] = self::getWxpaySign($jsBizPackage, $this->config['mch_key']);

        //数据返回
        return $jsBizPackage;
    }

    /**
     * 小程序支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return array
     * @throws ArgumentException
     */
    public function mini(){
        //获取预下单
        $unifiedOrder = self::getWxpayUnifiedOrder($this->payload, 'JSAPI');
        //构建微信支付
        $jsBizPackage = array(
            "appId" => $this->config['app_id'],
            "timeStamp" => (string)time(),        //这里是字符串的时间戳
            "nonceStr" => Str::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder['prepay_id'],
            "signType" => 'MD5',
        );
        $jsBizPackage['paySign'] = self::getWxpaySign($jsBizPackage, $this->config['mch_key']);

        //数据返回
        return $jsBizPackage;
    }

    /**
     * 微信H5支付
     *
     * 常见错误：
     * 1.网络环境未能通过安全验证，请稍后再试（原因：终端IP(spbill_create_ip)与用户实际调起支付时微信侧检测到的终端IP不一致）
     * 2.商家参数格式有误，请联系商家解决（原因：当前调起H5支付的referer为空）
     * 3.商家存在未配置的参数，请联系商家解决（原因：当前调起H5支付的域名与申请H5支付时提交的授权域名不一致）
     * 4.支付请求已失效，请重新发起支付（原因：有效期为5分钟，如超时请重新发起支付）
     * 5.请在微信外打开订单，进行支付（原因：H5支付不能直接在微信客户端内调起）
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return array
     * @throws ArgumentException
     */
    public function wap(){
        //获取预下单
        $unifiedOrder = self::getWxpayUnifiedOrder($this->payload, 'MWEB');
        //数据返回
        return [
            'mweb_url' => $unifiedOrder['mweb_url']
        ];
    }

    public function web()
    {
        // TODO: Implement web() method.
    }


    /**
     * 查单
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param bool $refund
     *
     * @return array|void
     */
    public function query($refund = false)
    {
        // TODO: Implement query() method.
    }


    /**
     * 验签
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param bool $refund
     *
     * @return array|mixed
     * @throws SignatureException
     */
    public function notify($refund = false){
        return $this->verifyWxOrderNotify($refund);
    }

    /**
     * 返回成功通知
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     */
    public function success()
    {
        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
    }
    /******************微信***********************************/


    /**
     * 微信预下单
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $order
     * @param string $trade_type
     *
     * @return mixed
     * @throws ArgumentException
     */
    private function getWxpayUnifiedOrder($order, $trade_type = 'NATIVE'){

        //请求参数
        $unified = array(
            'appid' => $this->config['app_id'],
            'attach' => 'qianchengpx',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $order['subject'],
            'mch_id' =>  $this->config['mch_id'],
            'nonce_str' => Str::createNonceStr(),
            'notify_url' => $this->config['notify_url'],
            'out_trade_no' => $order['out_trade_no'],
            'spbill_create_ip' => Http::getClientIp(),
            'total_fee' => intval(bcmul(100, $order['amount'])),       //单位 转为分
            'trade_type' => $trade_type,
        );

        //是否含有附加参数
        if(isset($order['extra'])){
            foreach ($order['extra'] as $k => $v){
                ($k == 'openid' && $v != '' && !is_array($v)) ?$unified[$k] = $v : '';
            }
        }
        //签名
        $unified['sign'] = self::getWxpaySign($unified, $this->config['mch_key']);
        //数据请求
        $responseXml = Http::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', Arr::arrayToXml($unified));
        //XML转ARRAY
        $result = Arr::xmlToArray($responseXml);
    
        //判断成功
        if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            throw new ArgumentException('Create Wechat API Error:'.($result['return_msg'] ? $result['return_msg'] : $result['retmsg']));
        }
        //数据返回
        return $result;
    }

    /**
     * 回调验签
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param bool      $refund
     *
     * @return mixed
     * @throws SignatureException
     */
    private function verifyWxOrderNotify($refund){
        libxml_disable_entity_loader(true);
        //Object  对象
        $response = json_decode(json_encode(simplexml_load_string(file_get_contents("php://input"), 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE),true);

        if (!$response || self::getWxpaySign($response, $this->config['mch_key']) !== $response['sign']) {
            throw new SignatureException('Verify Wechat API Error: 签名验证失败');
        }
        return $response;

    }

    /**
     * 获取微信签名
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $params
     * @param $key
     *
     * @return string
     */
    private static function getWxpaySign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatWxpayQueryParaMap($params);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    /**
     * 微信字符串排序
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $paraMap
     *
     * @return bool|string
     */
    private static function formatWxpayQueryParaMap($paraMap)
    {
        $buff = "";
        ksort($paraMap);

        foreach ($paraMap as $k => $v) {
            $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k.'='.$v.'&' : '';
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}