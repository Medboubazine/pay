<?php

namespace Medboubazine\Pay\ThirdParty\Paypal;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalOrderElement;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalPaymentCaptureElement;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalPaymentDetailsElement;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalPaymentRefundElement;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalRefundElement;
use Medboubazine\Pay\ThirdParty\Paypal\Elements\PaypalUrlsElement;

final class Paypal
{
    /**
     * Access token
     *
     * @var string
     */
    protected ?string $token;
    /**
     * Live environment is enabled or not
     *
     * @var bool
     */
    protected bool $live_environment;
    /**
     * Http client object
     *
     * @var object
     */
    protected object $http_client;
    /**
     * Constructor
     */
    public function __construct(bool $live_environment, string $client_id, string $client_secret)
    {
        $this->live_environment = $live_environment;
        $this->http_client = new Client([
            'base_uri' => $this->getBaseUri(),
            'timeout'  => 5,
            "allow_redirects" => false,
            "http_errors" => false,
            "verify" => true,
            "headers" => [
                "Accept" => "application/json",
            ],
        ]);
        $this->requestAccessToken($client_id, $client_secret);
    }
    /**
     * get API base uri
     *
     * @return string
     */
    protected function getBaseUri(): string
    {
        if ($this->live_environment) {
            return "https://api-m.paypal.com";
        }
        return "https://api-m.sandbox.paypal.com";
    }
    /**
     * Get access token from paypal
     *
     * @param string $client_id
     * @param string $client_secret
     * @return void
     */
    protected function requestAccessToken(string $client_id, string $client_secret)
    {
        $options = [];
        $options["auth"] = [$client_id, $client_secret];
        //headers
        $options["headers"] = [
            "Content-Type" => "application/x-www-form-urlencoded",
        ];
        //body
        $options["form_params"] = [
            "grant_type" => "client_credentials"
        ];

        $response = $this->http_client->request("POST", "v1/oauth2/token", $options);

        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            $content = json_decode($content, true);

            if (is_array($content) and $content["access_token"]) {
                $this->token = $content["access_token"];
                return $content["access_token"];
            }
            $this->token = null;
            return null;
        }

        PaypalException::error("Error when trying to get access token . Please check your credentials");

        $this->token = null;
        return null;
    }
    /**
     * Create paypal order
     *
     * @param string $amount
     * @param string $currency
     * @param string $reference
     * @return PaypalOrderElement|null
     */
    public function createPaypalOrder(string $amount, string $currency, PaypalPaymentDetailsElement $payment,  PaypalUrlsElement $urls): ?PaypalOrderElement
    {
        $options = [];
        //headers
        $options["headers"] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$this->token}",
        ];
        //body
        $options["json"] = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "invoice_id" => $payment->getId(),
                    "description" => $payment->getDescription(),
                    "amount" => [
                        "currency_code" => $currency,
                        "value" => $amount,
                        "breakdown" => [
                            "item_total" => [
                                "currency_code" => $currency,
                                "value" => $amount,
                            ]
                        ],
                    ],

                    "items" => [
                        [
                            "name" => $payment->getProductName(),
                            "quantity" => 1,
                            "category" => "DIGITAL_GOODS",
                            "unit_amount" => [
                                "currency_code" => $currency,
                                "value" => $amount
                            ],
                        ]
                    ]
                ]
            ],
            "payment_source" => [
                "paypal" => [
                    "experience_context" => [
                        "shipping_preference" => "NO_SHIPPING",
                        "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                        "locale" => "en-US",
                        "return_url" => $urls->getReturnUrl(),
                        "cancel_url" => $urls->getCancelUrl(),
                    ]
                ]
            ],
        ];

        $response = $this->http_client->request("POST", "v2/checkout/orders", $options);

        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);

        if ($response->getStatusCode() === 201 or $response->getStatusCode() === 200) {
            if (isset($content["id"])) {

                $payer_action = array_filter($content['links'], function ($value) {
                    return $value['rel']  === "payer-action";
                });
                $payer_action = current($payer_action) ?? [];
                $payer_action_uri =  $payer_action['href'] ?? null;

                return (new PaypalOrderElement())
                    ->setId($content["id"])
                    ->setStatus($content["status"])
                    ->setCheckoutUrl($payer_action_uri);
            }
        } elseif ($response->getStatusCode() === 400 or $response->getStatusCode() === 422) {
            PaypalException::createOrderError($content);
        }
        PaypalException::error("Something happend when trying to create order");

        return null;
    }
    /**
     * get Order Details
     *
     * @param string $order_id
     * @return PaypalOrderElement|null
     */
    public function getOrder(string $order_id): ?PaypalOrderElement
    {
        $options = [];
        //headers
        $options["headers"] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$this->token}",
        ];
        $response = $this->http_client->request("GET", "v2/checkout/orders/{$order_id}", $options);

        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);

        if ($response->getStatusCode() === 200) {
            if (isset($content["id"])) {
                $create_time = $content["create_time"] ?? null;
                $return = (new PaypalOrderElement())
                    ->setId($content["id"])
                    ->setIntent($content["intent"])
                    ->setStatus($content["status"])
                    ->setCreatedAt($create_time ? Carbon::parse($create_time) : null);
                if (isset($content["payer"])) {
                    $payer = $content["payer"] ?? [];
                    $return->setPayerId($payer["payer_id"]  ?? null);
                    $return->setPayerFullname(implode(" ", array_values($payer["name"] ?? [])));
                    $return->setPayerEmail($payer["email_address"]  ?? null);
                }
                if (isset($content["purchase_units"]) and $content["status"] === "COMPLETED") {
                    $collect = collect();
                    foreach ($content["purchase_units"] as $unit) {
                        $item = [];
                        /////
                        ///// Basic
                        /////
                        $item["invoice_id"] = $unit['invoice_id'] ?? "";
                        $item["description"] = $unit['description'] ?? "";
                        $item["soft_description"] = $unit['soft_descriptor'] ?? "";
                        /////
                        $unit_payments = $unit["payments"];
                        /////
                        ///// Captures
                        /////
                        $_captures = collect();
                        $unit_captures = $unit_payments['captures'] ?? [];

                        foreach ($unit_captures as $capture) {
                            $element = new PaypalPaymentCaptureElement();
                            $element->setId($capture['id']);
                            $element->setStatus($capture['status']);
                            $element->setAmount($capture['amount']['value']);
                            $element->setCurrency($capture['amount']['currency_code']);
                            $element->setIsFinalCapture(boolval($capture['final_capture']));
                            $element->setCreatedAt(Carbon::parse($capture['create_time']));
                            $element->setUpdatedAt(Carbon::parse($capture['update_time']));

                            $_captures->push($element);
                        }
                        $item["captures"] = $_captures;
                        /////
                        ///// Refunds
                        /////
                        $_refunds = collect();
                        $unit_refunds = $unit_payments['refunds'] ?? [];

                        foreach ($unit_refunds as $refund) {
                            $element = new PaypalPaymentRefundElement();
                            $element->setId($refund['id']);
                            $element->setStatus($refund['status']);
                            $element->setAmount($refund['amount']['value']);
                            $element->setCurrency($refund['amount']['currency_code']);
                            $element->setNote($refund['note_to_payer']);
                            $element->setCreatedAt(Carbon::parse($capture['create_time']));
                            $element->setUpdatedAt(Carbon::parse($capture['update_time']));

                            $_refunds->push($element);
                        }
                        $item["refunds"] = $_refunds;
                        /////
                        ///// END
                        /////
                        $collect->push($item);
                    }
                    $return->setUnits($collect);
                }

                return $return;
            }
        }
        PaypalException::error("Something happend when trying to get order details");

        return null;
    }
    /**
     * Capture order
     *
     * @param string $order_id
     * @return PaypalOrderElement|null
     */
    public function captureOrder(string $order_id): ?PaypalOrderElement
    {
        $options = [];
        //headers
        $options["headers"] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$this->token}",
        ];
        $response = $this->http_client->request("POST", "v2/checkout/orders/{$order_id}/capture", $options);

        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);

        if ($response->getStatusCode() === 201) {
            if (isset($content["id"])) {
                return (new PaypalOrderElement())
                    ->setId($content["id"])
                    ->setStatus($content["status"]);
            }
        } elseif ($response->getStatusCode() === 400 or $response->getStatusCode() === 422) {
            PaypalException::createOrderError($content);
        }

        PaypalException::error("Something happend when trying to capture order");

        return null;
    }
    /**
     * Refund payment
     *
     * @param string $order_id
     * @param string $amount
     * @param string $currency
     * @param string|null $note
     * @return PaypalRefundElement|null
     */
    public function refund(string $order_id, string $amount, string $currency, ?string $note = null): ?PaypalRefundElement
    {
        $options = [];
        //headers
        $options["headers"] = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$this->token}",
        ];
        //body
        $options["json"] = [
            "amount" => [
                "value" => $amount,
                "currency_code" => $currency,
            ],
            "note_to_payer" => $note,
        ];

        $response = $this->http_client->request("POST", "v2/payments/captures/{$order_id}/refund/", $options);

        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);

        if ($response->getStatusCode() === 201) {
            if (isset($content["id"])) {
                return (new PaypalRefundElement())
                    ->setId($content["id"])
                    ->setStatus($content["status"]);
            }
        } elseif ($response->getStatusCode() === 400 or $response->getStatusCode() === 422) {
            PaypalException::createOrderError($content);
        }

        PaypalException::error("Something happend when trying to capture order");

        return null;
    }
}
