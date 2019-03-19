<?php

namespace Iredcap\Payment\Connector;


interface GatewayInterface
{
    /**
     * 原生扫码
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return mixed
     */
    public function native();

    /**
     * 公众号
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return mixed
     */
    public function jsapi();

    /**
     * 手机网页支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return mixed
     */
    public function wap();

    /**
     * 电脑网页支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return mixed
     */
    public function web();

    /**
     * APP支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return mixed
     */
    public function app();

    /**
     * 小程序支付
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     *
     * @return mixed
     */
    public function mini();

    /**
     * 订单查询
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param bool $refund
     *
     * @return mixed
     */
    public function query($refund = false);

    /**
     * 异步通知
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param bool $refund
     *
     * @return mixed
     */
    public function notify($refund = false);
}