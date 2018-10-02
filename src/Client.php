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

        return $result->data;
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
     * https://localbitcoins.net/api-docs/#myself
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
     * @see https://localbitcoins.net/api-docs/#logout
     *
     * @return mixed
     */
    public function logout()
    {
        return $this->post('/api/logout/');
    }

    /**
     * Returns public user profile information.
     *
     * @see https://localbitcoins.net/api-docs/#account_info
     *
     * @param string $username
     *
     * @return mixed
     */
    public function accountInfo(string $username)
    {
        return $this->get("/api/account_info/$username/");
    }

    /**
     * Returns Open and active trades.
     *
     * @see https://localbitcoins.net/api-docs/#dashboard
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
     * @see https://localbitcoins.net/api-docs/#dashboard-released
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
     * @see https://localbitcoins.net/api-docs/#dashboard-canceled
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
     * @see https://localbitcoins.net/api-docs/#dashboard-closed
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
     * @see https://localbitcoins.net/api-docs/#notifications
     *
     * @return mixed
     */
    public function notifications()
    {
        return $this->get('/api/notifications/');
    }

    /**
     * Marks a specific notification as read.
     *
     * @see https://localbitcoins.net/api-docs/#notifications-read
     *
     * @param int $id
     */
    public function readNotification(int $id)
    {
        $this->post("/api/notifications/mark_as_read/$id/");
    }

    /**
     * Checks the given PIN code against the user's currently active PIN code.
     *
     * @param string $pin 4 digit app PIN code set from profile settings
     *
     * @see https://localbitcoins.net/api-docs/#pin
     *
     * @return mixed
     */
    public function pincode(string $pin)
    {
        return $this->post('/api/pincode/', [
            'pincode' => $pin,
        ]);
    }

    /**
     * Returns a list of real name verifiers of the user.
     *
     * @see https://localbitcoins.net/api-docs/#real-name-verifiers
     *
     * @param $username
     *
     * @return mixed
     */
    public function realNameVerifiers($username)
    {
        return $this->get("/api/real_name_verifiers/$username/");
    }

    /**
     * Returns the 50 latest trade messages.
     *
     * https://localbitcoins.net/api-docs/#recent-messages
     *
     * @return mixed
     */
    public function recentMessages()
    {
        return $this->get('/api/recent_messages/');
    }

    /**
     * Returns information about the token owner's wallet balance.
     *
     * @see https://localbitcoins.net/api-docs/#wallet
     *
     * @return mixed
     */
    public function wallet()
    {
        return $this->get('/api/wallet/');
    }

    /**
     * Same as /api/wallet/ but only returns the fields message, receiving_address and total.
     *
     * @see https://localbitcoins.net/api-docs/#wallet-balance
     *
     * @return mixed
     */
    public function walletBalance()
    {
        return $this->get('/api/wallet-balance/');
    }

    /**
     * Sends amount bitcoins from the token owner's wallet to address.
     *
     * @see https://localbitcoins.net/api-docs/#wallet-send
     *
     * @param string $address Bitcoin address where you're sending Bitcoin to.
     * @param float  $amount  Amount of Bitcoin to send.
     *
     * @return mixed
     */
    public function walletSend(string $address, float $amount)
    {
        return $this->post('/api/wallet-send/', compact('address', 'amount'));
    }

    /**
     * Sends amount of bitcoins from the token owner's wallet to address, requires PIN.
     *
     * @param string $address Bitcoin address where you're sending Bitcoin to.
     * @param float  $amount  Amount of Bitcoin to send.
     * @param string $pincode Token owners PIN code. @see https://localbitcoins.net/api-docs/#pin
     *
     * @see https://localbitcoins.net/api-docs/#wallet-send-pin
     *
     * @return mixed
     */
    public function walletSendPin(string $address, float $amount, string $pincode)
    {
        return $this->post('/api/wallet-send-pin/', compact('address', 'amount', 'pincode'));
    }

    /**
     * Gets the latest unused receiving address for the token owner's wallet.
     *
     * @see https://localbitcoins.net/api-docs/#wallet-addr
     *
     * @return mixed
     */
    public function walletAttr()
    {
        return $this->get('/api/wallet-addr/');
    }

    /**
     * Returns outgoing and deposit fees in bitcoins (BTC).
     *
     * @see https://localbitcoins.net/api-docs/#fees
     *
     * @return mixed
     */
    public function fees()
    {
        return $this->get('/api/fees/');
    }
}