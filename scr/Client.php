<?php
/**
 * Created by Viktor Leukhin.
 * Tel: +7-926-797-5419
 * E-mail: vsleuhin@ya.ru
 */

namespace LocalBitcoins;

class Client
{
    /**
     * @var string API Key
     */
    protected $key;

    /**
     * @var string API secret key
     */
    protected $secret;

    public function __construct(string $key, string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }
}