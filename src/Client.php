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
     *
     * @return mixed
     */
    protected function request(string $method, string $url, array $data = [])
    {
        $nonce = $this->generateNonce();

        $signature = $this->getSignature($nonce, $url, $data);

        $this->curl->setHeaders([
            'Apiauth-Key'       => $this->key,
            'Apiauth-Nonce'     => (string)$nonce,
            'Apiauth-Signature' => $signature,
        ]);

        $result = $this->curl->{$method}($url, $data);

        return $result;
    }

    /**
     * Make GET request to API
     *
     * @param string $url
     * @param array  $data
     *
     * @return mixed
     */
    protected function get(string $url, array $data = [])
    {
        return $this->request('get', $url, $data);
    }

    /**
     * Make POST request to API
     *
     * @param string $url
     * @param array  $data
     *
     * @return mixed
     */
    protected function post(string $url, array $data = [])
    {
        return $this->request('post', $url, $data);
    }

    /**
     * Generate request nonce
     *
     * @return int
     */
    protected function generateNonce(): int
    {
        return (int)(microtime(true) * 10000);
    }

    /**
     * Get request signature
     *
     * @param int    $nonce
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    protected function getSignature(int $nonce, string $url, array $data): string
    {
        $params = http_build_query($data);

        $message = (string)$nonce . $this->key . $url . $params;

        return mb_strtoupper(hash_hmac('SHA256', $message, $this->secret));
    }

    /**
     * Returns information of the currently
     * logged in user (the owner of authentication token)
     *
     * @return mixed
     */
    public function myself()
    {
        return $this->get('/api/myself/');
    }

    /**
     * Immediately expires the current access token.
     *
     * @return mixed
     */
    public function logout()
    {
        return $this->get('/api/logout/');
    }

    /**
     * Returns public user profile information.
     *
     * @param string $nickname
     *
     * @return mixed
     */
    public function accountInfo(string $nickname)
    {
        return $this->get("/api/account_info/$nickname/");
    }

    /**
     * Returns Open and active trades.
     *
     * @return mixed
     */
    public function dashboard()
    {
        return $this->get('/api/dashboard/');
    }

    /**
     * Returns released trades.
     *
     * @return mixed
     */
    public function releasedTrades()
    {
        return $this->get('/api/dashboard/released/');
    }

    /**
     * Returns canceled trades.
     *
     * @return mixed
     */
    public function canceledTrades()
    {
        return $this->get('/api/dashboard/canceled/');
    }

    /**
     * Returns closed trades.
     *
     * @return mixed
     */
    public function closedTrades()
    {
        return $this->get('/api/dashboard/closed/');
    }

    /**
     * Returns a list of notifications.
     *
     * @return mixed
     */
    public function notifications()
    {
        return $this->get('/api/notifications/');
    }
}