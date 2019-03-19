<?php


namespace Payment\Exceptions;


class ConfigException extends Exception
{
    /**
     * ConfigException constructor.
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param string       $message
     * @param array|string $raw
     * @param int|string   $code
     */
    public function __construct($message, $raw = [], $code = 5)
    {
        parent::__construct($message, $raw, $code);
    }
}