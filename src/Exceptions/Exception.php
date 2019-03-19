<?php
namespace Iredcap\Payment\Exceptions;

class Exception extends \Exception
{
    /**
     * Raw error info.
     *
     * @var array
     */
    public $raw;

    /**
     * Exception constructor.
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param string       $message
     * @param array|string $raw
     * @param int|string   $code
     */
    public function __construct($message, $raw = [], $code = 9999)
    {
        $this->raw = is_array($raw) ? $raw : [$raw];
        parent::__construct($message, intval($code));
    }
}