<?php

namespace Iredcap\Payment\Helper;

class Str
{

    /**
     * 建立请求，以表单HTML形式构造（默认）
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param  array    $commonConfigs
     * @param string    $actionUrl
     *
     * @return string
     */
    public static function buildRequestForm($commonConfigs, $actionUrl = 'https://openapi.alipay.com/gateway.do') {
        $sHtml = "<form id='paymentsubmit' name='paymentsubmit' action='". $actionUrl ."?charset=utf-8' method='POST'>";
        foreach ($commonConfigs as $key => $val) {
            if (false === self::checkEmpty($val)) {
                $val = str_replace("'","&apos;",$val);
                $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
            }
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='Loading...' style='display:none;''></form>";
        $sHtml = $sHtml."<script>document.forms['paymentsubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 校验$value是否非空
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $value
     *
     * @return bool
     */
    public static function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    /**
     * 随机字符串
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param int $length
     * @return string
     */
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 编码转换
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $data
     * @param $to_encoding
     * @param string $from_encoding
     *
     * @return mixed
     */
    public static function encoding($data, $to_encoding, $from_encoding = 'gb2312')
    {
        $encoded = [];
        if (is_string($data)){
            $encoded = mb_convert_encoding(urldecode($data), $to_encoding, $from_encoding );
        }elseif (is_array($data)){
            foreach ($data as $key => $value) {
                $encoded[$key] = is_array($value) ? self::encoding($value, $to_encoding, $from_encoding) :
                    mb_convert_encoding(urldecode($value), $to_encoding, $from_encoding);
            }
        }
        return $encoded;
    }

}