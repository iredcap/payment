<?php


namespace Payment\Helper;


class Arr
{

    /**
     * 数组转Xml
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $arr
     * @return string
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * Xml转数组
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $xml
     * @return mixed
     */
    public static function xmlToArray($xml){

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);

    }

    /**
     * url参数转化成数组
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $query
     *
     * @return array
     */
    public static function convertUrlArray($query)
    {
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }
}