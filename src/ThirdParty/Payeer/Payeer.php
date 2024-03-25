<?php

namespace Medboubazine\Pay\ThirdParty\Payeer;

use Carbon\Carbon;
use Medboubazine\Pay\Core\Helpers\HttpRequest;

class Payeer
{
    /**
     * Merchnat ID
     *
     * @var string
     */
    protected string $merchant;
    /**
     * Secret
     *
     * @var string
     */
    protected string $secret;
    /**
     * Undocumented variable
     *
     * @var string
     */
    protected string $encryption_key;
    /**
     * constructor
     *
     * @return void
     */
    public function __construct(string $merchant, string $secret, string $encryption_key)
    {
        $this->merchant = $merchant;
        $this->secret = $secret;
        $this->encryption_key = $encryption_key;
    }
    /**
     * Get Payment link
     *
     * @param string $order
     * @param string $amount
     * @param string $currency
     * @param string|null $description
     * @param string $back_url
     * @param string $webhook_url
     * @param int $method
     * @return string|null
     */
    public function getCheckoutUrl(string $order, string $amount, string $currency, string $description = null, string $back_url, string $webhook_url, int $method = PayeerMethod::PAYEER): ?string
    {
        $description  =  base64_encode($description ?? "Buy Item");
        $arr_hash = array(
            $this->merchant,
            $order,
            $amount,
            $currency,
            $description
        );
        $params = array(
            'success_url' => $back_url,
            'fail_url' => $back_url,
            'status_url' => $webhook_url,
        );
        //
        $enc_key = md5($this->encryption_key . $order);
        //
        $encrypted_params = $this->encryptParams($params, $enc_key);
        //
        $arr_hash[] = $encrypted_params;
        $arr_hash[] = $this->secret;
        //
        $sign = strtoupper(hash('sha256', implode(':', $arr_hash)));
        //
        return $this->buildUrl([
            'm_shop' => $this->merchant,
            'm_orderid' => $order,
            'm_amount' => $amount,
            'm_desc' => $description,
            'm_sign' => $sign,
            'm_curr' => $currency,
            'form' => [
                "ps" => $method,
                "curr[{$method}]" => $currency,
            ],
            'm_params' => $encrypted_params,
            'm_cipher_method' => "AES-256-CBC",
        ]);
    }
    /**
     * Parse webhook request
     *
     * @return void
     */
    public function parseWebhook(): ?array
    {
        $request_data = HttpRequest::data();
        //
        $data_payment_id = $request_data["transfer_id"];
        $data_operation_id = $request_data["m_operation_id"];
        $data_operation_ps = $request_data["m_operation_ps"];
        $data_operation_date = $request_data["m_operation_date"];
        $data_operation_paid_at = $request_data["m_operation_pay_date"];
        $data_merchant = $request_data["m_shop"];
        $data_order = $request_data["m_orderid"];
        $data_amount = $request_data["m_amount"];
        $data_currency = $request_data["m_curr"];
        $data_description = $request_data["m_desc"];
        $data_status = $request_data["m_status"];
        $data_sign = $request_data["m_sign"];
        $data_params = $request_data["m_params"];
        $data_client_email = $request_data["client_email"] ?? null;
        //
        if ($data_operation_id and $data_sign) {
            //
            $arr_hash = [
                $data_operation_id,
                $data_operation_ps,
                $data_operation_date,
                $data_operation_paid_at,
                $data_merchant,
                $data_order,
                $data_amount,
                $data_currency,
                $data_description,
                $data_status,
            ];
            if ($data_params) {
                $arr_hash[] = $data_params;
            }
            $arr_hash[] = $this->secret;
            //
            $hash = strtoupper(hash('sha256', implode(':', $arr_hash)));
            //
            if (hash_equals($data_sign, $hash) and $this->merchant == $data_merchant) {
                if ($data_status == 'success') {
                    $created_at = Carbon::parse($data_operation_date, "MSK");
                    $paid_at = Carbon::parse($data_operation_paid_at, "MSK");

                    return [
                        "id" => $data_payment_id,
                        "order" => $data_order,
                        "client_email" => $data_client_email,
                        "method" => $data_operation_ps,
                        "merchant" => $data_merchant,
                        "amount" => $data_amount,
                        "currency" => $data_currency,
                        "desciption" => base64_decode($data_description),
                        "status" => $data_status,
                        "created_at" => $created_at->timezone("UTC")->format("Y-m-d H:i:s T"),
                        "paid_at" =>  $paid_at->timezone("UTC")->format("Y-m-d H:i:s T"),
                    ];
                }
            }
        }
        return null;
    }
    /**
     * Encrypt Params
     *
     * @param array $params
     * @param string $key
     * @return string|null
     */
    protected function encryptParams(array $params, string $key)
    {
        $raw = @openssl_encrypt(json_encode($params), 'AES-256-CBC', $key, OPENSSL_RAW_DATA);
        return @urlencode(base64_encode($raw));
    }
    /**
     * Query Params
     *
     * @param array $query_params
     * @return string
     */
    protected function buildUrl(array $query_params)
    {
        return "https://payeer.com/merchant/?" . http_build_query($query_params);
    }
}
