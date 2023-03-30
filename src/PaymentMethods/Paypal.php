<?php

namespace Medboubazine\Pay\PaymentMethods;

use Exception;
use Medboubazine\Pay\Core\Abstracts\PaymentMethod;
use Medboubazine\Pay\Core\Elements\Attributes;
use Medboubazine\Pay\Core\Elements\Credentials;
use Medboubazine\Pay\Core\Elements\Payment;
use Medboubazine\Pay\Core\Helpers\PaymentStatus;
use Medboubazine\Pay\Core\Interfaces\PaymentMethodInterface;
use Medboubazine\Pay\Validation\Paypal\PaypalAttributesForCreateValidation;
use Medboubazine\Pay\Validation\Paypal\PaypalAttributesForProcessValidation;
use Medboubazine\Pay\Validation\Paypal\PaypalCredentialsForCreateValidation;
use Medboubazine\Pay\Validation\Paypal\PaypalCredentialsForProcessValidation;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class Paypal extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * Create Payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment
     */
    public function createPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::createPayment($credentials, $attributes);
        //
        $api_context = new ApiContext(new OAuthTokenCredential($credentials->getApiKey(), $credentials->getSecretKey()));
        $api_context->setConfig([
            'mode' => $credentials->getEnv(),
            'log.LogEnabled' => $credentials->getLogEnabled(),
            'log.FileName' => $credentials->getLogPath(),
            'log.LogLevel' => 'DEBUG',
        ]);
        //Payer
        $paypal_payer = new Payer();
        $paypal_payer->setPaymentMethod('paypal');
        //$item
        $items = [];
        $item = new Item();
        //item info
        $currency = $attributes->getCurrency();
        $price = $attributes->getAmount();
        $description = $attributes->getDescription();
        $process_url = $attributes->getProcessUrl();
        $back_url = $attributes->getBackUrl();
        //
        $paypal_items[] = $item->setName($description)
            ->setCurrency($currency)
            ->setQuantity(1)
            ->setPrice($price)
            ->setDescription($description);
        //item list
        $paypal_item_list = new ItemList();
        $paypal_item_list->setItems($items);
        //details
        //amount
        $paypal_amount = new Amount();
        $paypal_amount->setCurrency($currency)
            ->setTotal($price);
        //transaction
        $paypal_transaction = new Transaction();
        $paypal_transaction->setAmount($paypal_amount)
            ->setItemList($paypal_item_list)
            ->setDescription($description);
        //redirect urls
        $paypal_redirect = new RedirectUrls();
        $paypal_redirect->setReturnUrl($process_url)
            ->setCancelUrl($back_url);
        //payment
        $paypal_payment = new PayPalPayment();
        $paypal_payment->setIntent('sale')
            ->setPayer($paypal_payer)
            ->setRedirectUrls($paypal_redirect)
            ->setTransactions([$paypal_transaction]);
        //create payment
        $paypal_payment->create($api_context);
        if ($paypal_payment) {

            //get DATA
            $url = $paypal_payment->getApprovalLink();
            $id = $paypal_payment->getId();
            //
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return (new Payment)
                    ->setId($id)
                    ->setUrl($url);
            }
        }
        return null;
    }
    /**
     * Process payment
     *
     * @param Credentials $credentials
     * @param Attributes $attributes
     * @return Payment
     */
    public function processPayment(Credentials $credentials, Attributes $attributes): ?Payment
    {
        parent::processPayment($credentials, $attributes);
        //
        $paypal_payment_id = $_GET['paymentId'] ?? null;
        $paypal_payer_id = $_GET['PayerID'] ?? null;
        //
        $api_context = new ApiContext(new OAuthTokenCredential($credentials->getApiKey(), $credentials->getSecretKey()));
        $api_context->setConfig([
            'mode' => $credentials->getEnv(),
            'log.LogEnabled' => $credentials->getLogEnabled(),
            'log.FileName' => $credentials->getLogPath(),
            'log.LogLevel' => 'DEBUG',
        ]);
        //
        $paypal_payment = null;
        if ($paypal_payment_id and $paypal_payer_id) {
            try {
                $paypal_payment = PayPalPayment::get($paypal_payment_id, $api_context) ?? null;
            } catch (Exception) {
                return null;
            }
        }
        //
        if ($paypal_payment) {
            if (!$attributes->getAcceptOnlyVerifiedAccounts() or $paypal_payment->getPayer()->getStatus() === 'VERIFIED') {
                /**
                 * END payment execution
                 */
                if ($paypal_payment->getState() === "created") {
                    $execution = new PaymentExecution();
                    $execution->setPayerId($paypal_payer_id);
                    $paypal_payment = $paypal_payment->execute($execution, $api_context);
                } else {
                    $paypal_payment = $paypal_payment;
                }
                /**
                 * START payment execution
                 */
                $paypal_payer = $paypal_payment->getPayer();
                $paypal_payer_info = $paypal_payer->getPayerInfo();

                $f_name = $paypal_payer_info->getFirstName() ?? null;
                $l_name = $paypal_payer_info->getLastName() ?? null;
                $full_name = "{$f_name} {$l_name}";
                $status = PaymentStatus::paypal($paypal_payment->getState());
                dd($paypal_payment);

                return (new Payment())
                    //REQUIRED
                    ->setId($paypal_payment->getId())
                    ->setOrderId($paypal_payment->getId())
                    ->setStatus($status)
                    ->setPayerFirstName($f_name)
                    ->setPayerLastName($l_name)
                    ->setPayerFullName($full_name)
                    ->setAmount("0.00")
                    ->setFee("0.00")
                    ->setTotal("0.00")
                    ->setCurrency("0.00")
                    //OPTIONAL
                    ->setPayerStatus($paypal_payer->getStatus())
                    ->setPayerPaymentMethod($paypal_payer->getPaymentMethod())
                    ->setPayerId($paypal_payer_info->getPayerId())
                    ->setPayerEmail($paypal_payer_info->getEmail())
                    ->setPayerPhone($paypal_payer_info->getPhone())
                    ->setPayerCountryCode($paypal_payer_info->getCountryCode());
            }
        }

        return null;
    }
    /**
     * Validations
     *
     * @return array
     */
    public function validation(): array
    {
        return [
            "create" => [
                "credentials" => PaypalCredentialsForCreateValidation::class,
                "attributes" => PaypalAttributesForCreateValidation::class,
            ],
            "process" => [
                "credentials" => PaypalCredentialsForProcessValidation::class,
                "attributes" => PaypalAttributesForProcessValidation::class,
            ],
        ];
    }
}
