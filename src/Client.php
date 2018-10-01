<?php
/**
 * Created by Viktor Leukhin.
 * Tel: +7-926-797-5419
 * E-mail: vsleuhin@ya.ru
 */

namespace LocalBitcoins;

use Curl\Curl;

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

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var string Host to make API calls
     */
    protected $host = 'https://localbitcoins.com';

    /**
     * Client constructor.
     *
     * @param string $key
     * @param string $secret
     *
     * @throws \ErrorException
     */
    public function __construct(string $key, string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->curl = new Curl($this->host);
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return Client
     */
    public function setHost(string $host): Client
    {
        $this->host = $host;
        $this->curl->setUrl($host);

        return $this;
    }

    /**
     * Make API call
     *
     * @param string $url
     * @param array  $data
     */
    protected function request(string $url, array $data)
    {
        $this->curl->setHeaders([
            'Apiauth-Key'       => $this->key,
            'Apiauth-Nonce'     => (string)$this->generateNonce(),
            'Apiauth-Signature' => $this->getSignature(),
        ]);
    }

    /**
     * @return int
     */
    protected function generateNonce(): int
    {
        return (int)(microtime(true) * 10000);
    }

    protected function getSignature(): string
    {
        return '';
    }
}