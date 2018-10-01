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

    public function test()
    {
        return $this->request('api/wallet', ['test' => 1, 'key' => '😑ufg']);
    }

    /**
     * Make API call
     *
     * @param string $url
     * @param array  $data
     */
    protected function request(string $url, array $data)
    {
        $nonce = $this->generateNonce();

        $signature = $this->getSignature($nonce, $url, $data);

        var_dump($signature);

        $this->curl->setHeaders([
            'Apiauth-Key'       => $this->key,
            'Apiauth-Nonce'     => (string)$nonce,
            'Apiauth-Signature' => $signature,
        ]);

        return true;
    }

    /**
     * @return int
     */
    protected function generateNonce(): int
    {
        return (int)(microtime(true) * 10000);
    }

    protected function getSignature(int $nonce, string $url, array $data): string
    {
        $params = http_build_query($data);

        $message = (string)$nonce . $this->key . $url . $params;

        var_dump($message);

        return hash_hmac('sha256', $message, $this->key);
    }
}