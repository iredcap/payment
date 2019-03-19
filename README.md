<h1 align="center">Payment</h1>

**！！请先熟悉 支付宝/微信 说明文档！！请具有基本的 debug 能力！！**

欢迎 Star，欢迎 PR！

QQ交流群：暂无

## 运行环境
- PHP 7.0+
- composer


## 支持的支付方法

|  method   |   描述       |
| :-------: | :-------:   |
|  web      | 电脑支付     |
|  wap      | 手机网站支付 |
|  app      | APP 支付    |
|  native   | 扫码支付  |
|  transfer | 帐户转账  |
|  mini     | 小程序支付 |



## 安装
```shell
composer require iredcap/payment
```

## 使用基础

```php
<?php

namespace App\Http\Controllers;

use Iredcap\Payment\Payment;

class PayController
{
    protected $config = [
        'wxpay' => [
            'app_id'              => 'wxc90xxxx34c', // 微信支付MCHID 商户收款账号
            'mch_id'              => '149xxxx22', // 微信支付MCHID 商户收款账号
            'mch_key'             => 'a50e731409xxxxxxx50b254e62a', // 微信支付KEY
            'notify_url'          => 'https://www.baidu.cn/payment/notify', // 接收支付结果通知
        ],
        
        'alipay' => [
            'app_id'    => '2019021863254220', // 支付宝应用appid
            // 支付宝应用私钥
            'private_key'   => 'MIIEpAIBAAKCAxxxxxxx',
            // 支付宝公钥
            'public_key'    => 'MIIBIjANBgkqhxxxxxxxxxxxxx',
            'notify_url'    => 'https://www.baidu.cn/payment/notify',// 接收支付结果通知
        ],
    ];

    public function index()
    {
        $payload = [
            'out_trade_no' => uniqid(),
            'subject' =>  '技能购买',
            'amount' => '0.01',
            'extra' => [
                'openid'    => 'farhbuthnjfgyhsbtfhngfyj'
            ]
        ];
        
        $payment = Payment::createPayment('alipay', $this->config['alipay'], $payload);
        
        //$result = $payment->native();
        //$result = $payment->wap();
        //$result = $payment->app();
        //$result = $payment->mini();
        //$result = $payment->jsapi();
        //$result = $payment->query();
    }

    public function notify()
    {
        $payment = Payment::createPayment('alipay', $this->config['alipay']);

        try{
            $data = $payment->notify();

            //$data['out_trade_no'];
        } catch (\Exception $e) {
            // $e->getMessage();
        }
        
        return $payment->success();
    }
}
```

### 所有异常

* Iredcap\Payment\Exceptions\GatewayException ，表示使用没有此支付网关，可自行参考添加。
* Iredcap\Payment\Exceptions\SignException ，表示支付数据验签失败。
* Iredcap\Payment\Exceptions\ConfigException ，表示缺少配置参数。


## 代码贡献
如果您有其它支付网关的需求，或者发现本项目中需要改进的代码，**_欢迎 Fork 并提交 PR！_**

## 赏一杯咖啡吧

![pay](docs/pay.jpg)

## LICENSE
MIT