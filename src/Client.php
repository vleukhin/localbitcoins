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

    /**
     * Gives feedback to a user.
     *
     * @param string $username
     *
     * @param string $feedback Allowed values are: trust, positive, neutral, block, block_without_feedback.
     * @param string $msg      Feedback message displayed alongside feedback on receivers profile page.
     *
     * @see https://localbitcoins.net/api-docs/#feedback
     *
     * @return mixed
     */
    public function feedback(string $username, string $feedback, string $msg)
    {
        return $this->post("/api/feedback/$username/", compact('feedback', $msg));
    }

    /**
     * Release a trade (does not require money_pin).
     *
     * @param int $contact_id
     *
     * @see https://localbitcoins.net/api-docs/#contact-release
     *
     * @return mixed
     */
    public function contactRelease(int $contact_id)
    {
        return $this->post("/api/contact_release/$contact_id/");
    }

    /**
     * Release a trade (Requires money_pin).
     *
     * @param int    $contact_id Trade ID number.
     * @param string $pincode    User Apps PIN code.
     *
     * @see https://localbitcoins.net/api-docs/#contact-release-pin
     *
     * @return mixed
     */
    public function contactReleasePin(int $contact_id, string $pincode)
    {
        return $this->post("/api/contact_release_pin/$contact_id/", compact('pincode'));
    }

    /**
     * Mark a trade as paid.
     *
     * @param int $contact_id Trade ID number.
     *
     * @see https://localbitcoins.net/api-docs/#contact-paid
     *
     * @return mixed
     */
    public function contactMarkAsPaid(int $contact_id)
    {
        return $this->post("/api/contact_mark_as_paid/$contact_id/");
    }

    /**
     *    Return all chat messages from a specific trade ID.
     *
     * @param int $contact_id Trade ID number.
     *
     * @see https://localbitcoins.net/api-docs/#contact-message
     *
     * @return mixed
     */
    public function contactMessages(int $contact_id)
    {
        return $this->get("/api/contact_messages/$contact_id/");
    }

    /**
     *    Return all chat messages from a specific trade ID.
     *
     * @param int    $contact_id Trade ID number.
     * @param string $msg        Chat message to trade chat.
     *
     * @see https://localbitcoins.net/api-docs/#contact-post
     *
     * @return mixed
     *
     */
    public function contactPostMessage(int $contact_id, string $msg)
    {
        return $this->post("/api/contact_messages/$contact_id/", compact('msg'));
    }

    /**
     * Starts a dispute on the trade ID.
     *
     * @param int    $contact_id Trade ID number.
     * @param string $topic      Short description of issue to LocalBitcoins customer support.
     *
     * @see https://localbitcoins.net/api-docs/#contact-dispute
     *
     * @return mixed
     *
     */
    public function contactDispute(int $contact_id, ?string $topic = null)
    {
        $data = !is_null($topic) ? compact('topic') : [];

        return $this->post("/api/contact_dispute/$contact_id/", $data);
    }

    /**
     * Cancels the trade.
     *
     * @param int $contact_id Trade ID number.
     *
     * @see https://localbitcoins.net/api-docs/#contact-cancel
     *
     * @return mixed
     */
    public function contactCancel(int $contact_id)
    {
        return $this->post("/api/contact_cancel/$contact_id/");
    }

    /**
     * Fund an unfunded Local trade from your LocalBitcoins wallet.
     *
     * @param int $contact_id Trade ID number.
     *
     * @see https://localbitcoins.net/api-docs/#contact-fund
     *
     * @return mixed
     */
    public function contactFund(int $contact_id)
    {
        return $this->post("/api/contact_cancel/$contact_id/");
    }

    /**
     * Mark realname confirmation.
     *
     * @param int  $contact_id          Trade ID number.
     *
     * @param int  $confirmation_status 1 = Name matches
     *                                  2 = Name was different
     *                                  3 = Name was not checked
     *                                  4 = Name was not visible
     * @param bool $id_confirmed        0 for false, 1 for true.
     *
     * @see https://localbitcoins.net/api-docs/#contact-mark-realname
     *
     * @return mixed
     *
     */
    public function contactMarkRealname(int $contact_id, int $confirmation_status, bool $id_confirmed)
    {
        return $this->post("/api/contact_mark_realname/$contact_id/", compact('confirmation_status', 'id_confirmed'));
    }

    /**
     * Mark verification of trade partner as confirmed.
     *
     * @param int $contact_id Trade ID number.
     *
     * @see https://localbitcoins.net/api-docs/#contact-mark-identified
     *
     * @return mixed
     */
    public function contact(int $contact_id)
    {
        return $this->post("/api/contact_mark_identified/$contact_id/", compact('confirmation_status', 'id_confirmed'));
    }

    /**
     * Start a trade from an advertisement.
     *
     * @param int    $ad_id   Advertisement ID.
     * @param float  $amount  Number in the advertisement's fiat currency.
     * @param string $message Optional message to send to the advertiser.
     *
     * @see https://localbitcoins.net/api-docs/#contact-create
     *
     * @return mixed
     */
    public function contactCreate(int $ad_id, float $amount, ?string $message = null)
    {
        $data = is_null($message) ? compact('amount') : compact('amount', 'message');

        return $this->post("/api/contact_create/$ad_id/", $data);
    }

    /**
     * Return information about a single trade ID.
     *
     * @param int $contact_id Trade ID number.
     *
     * @see https://localbitcoins.net/api-docs/#contact-info-id
     *
     * @return mixed
     */
    public function contactInfo(int $contact_id)
    {
        return $this->get("/api/contact_info/$contact_id/");
    }

    /**
     * Return information about a single trade ID.
     *
     * @param array $contact_ids Trade ID numbers.
     *
     * @see https://localbitcoins.net/api-docs/#contact-info
     *
     * @return mixed
     */
    public function contactInfoList(array $contact_ids)
    {
        $contacts = implode($contact_ids);

        return $this->get("/api/contact_info", compact($contacts));
    }
}