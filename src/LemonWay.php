<?php

namespace Infinety\LemonWay;

use GuzzleHttp\Client;
use Infinety\LemonWay\Exceptions\LemonWayExceptions;
use Infinety\LemonWay\Models\LemonWayUser;
use Infinety\LemonWay\Models\LemonWayWallet;

class LemonWay
{
    /**
     * @var string API key
     */
    protected $apiKey;

    /**
     * @var string API key
     */
    protected $webkitUrl;

    /**
     * @var string API login
     */
    protected $login;

    /**
     * @var string API password
     */
    protected $password;

    /**
     * @var string API langauge
     */
    protected $language;

    /**
     * @var string API version
     */
    protected $version;

    /**
     * @var string call parameters
     */
    protected $callParameters;

    /**
     * @var string API version
     */
    protected $sslActive;

    /**
     * @var lemonway fee
     */
    protected $fee;

    /**
     * @var array
     */
    protected $walletExtras = [
        'clientTitle',
        'street',
        'postCode',
        'city',
        'cityIso3',
        'phoneNumber',
        'mobileNumber',
        'birthdate',
        'isDebtor',
        'nationalityIso3',
        'birthCity',
        'birthCountryIso3',
        'payerOrBeneficiary',
        'isOneTimeCustomer',
        'isTechWallet',

    ];

    /**
     * @var array
     */
    protected $walletPaymentFormExtras = [
        'amountCom',
        'comment',
        'useRegisteredCard',
        'wkToken',
        'returnUrl',
        'errorUrl',
        'cancelUrl',
        'autoCommission',
        'registerCard',
        'isPreAuth',
        'email',
        'firstNamePayer',
        'lastNamePayer',
        'emailPayer',
        'style',
        'atosStyle',
        'notifUrl',
        'options',
    ];

    /**
     * @var array
     */
    protected $walletPaymentFormWithCardExtras = [
        'amountCom',
        'comment',
        'autoCommission',
        'isPreAuth',
        'delayedDays',
    ];

    /**
     * @const SurveyMonkey Status code:  Success
     */
    const SM_STATUS_SUCCESS = 0;
    /**
     * @const HTTP response code: Success
     */
    const HTTP_RESPONSE_CODE_SUCCESS = 200;

    public function __construct()
    {
        $this->apiKey = config('lemonway.api_url');
        $this->webkitUrl = config('lemonway.webkit_url');
        $this->login = config('lemonway.login');
        $this->password = config('lemonway.password');
        $this->language = config('lemonway.language');
        $this->version = config('lemonway.version');
        $this->sslActive = config('lemonway.ssl');
        $this->fee = config('lemonway.fee');
        $this->createCredentialsData();
    }

    /**
     * Fet current client IP.
     *
     * @return string
     */
    protected function getUserIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '127.0.0.1';
        }

        return $ip;
    }

    /**
     * Create credentials data for use in all calls.
     */
    private function createCredentialsData()
    {
        $this->callParameters = [
            'wlLogin'  => $this->login,
            'wlPass'   => $this->password,
            'language' => $this->language,
            'version'  => $this->version,
            'walletIp' => $this->getUserIP(),
            'walletUa' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'ua',
        ];
    }

    /**
     * Return a LemonUser to use with api calls.
     *
     * @param  $wallet
     * @param  $clientMail
     * @param  $clientFirstName
     * @param  $clientLastName
     *
     * @return mixed
     */
    public function setWalletUser($wallet, $clientMail, $clientFirstName, $clientLastName, $extras = [])
    {
        $user = new LemonWayUser();
        $user->wallet = $wallet;
        $user->clientMail = $clientMail;
        $user->clientFirstName = $clientFirstName;
        $user->clientLastName = $clientLastName;

        foreach ($extras as $extra => $value) {
            if (in_array($extra, $this->walletExtras)) {
                $user->{$extra} = $value;
            }
        }

        return $user;
    }

    /**
     * Create a wallet for a user.
     *
     * @param LemonWayUser $user
     *
     * @return wallet
     */
    public function createWallet(LemonWayUser $user, $returnAsWallet = true)
    {
        $result = $this->callService('RegisterWallet', $user->toArray());

        $this->checkError($result);

        if (!$returnAsWallet) {
            return $result;
        }

        return $this->getWalletDetails($user, $result->WALLET->LWID);
    }

    /**
     * Create a wallet for a user.
     *
     * @param $email
     * @param null $walletId
     *
     * @return wallet
     */
    public function getWalletDetails($email, $walletId = null)
    {
        $result = $this->callService('GetWalletDetails', ['wallet' => $walletId, 'email' => $email]);

        $this->checkError($result);

        $wallet = new LemonWayWallet();
        $wallet->fill((array) $result->WALLET);

        return $wallet;
    }

    /**
     * Upload a file to a wallet
     * http://documentation.lemonway.fr/api-en/directkit/manage-wallets/uploadfile-document-upload-for-kyc.
     *
     * @param LemonWayWallet $wallet
     * @param $fileName
     * @param $type
     *
     * 0: ID card
     * 1: Proof of address
     * 2: Scan of a proof of IBAN
     * 3: Passport (European Community)
     * 4: Passport (outside the European Community)
     * 5: Residence permit
     * 7: Official company registration document
     * 11 to 20: other documents
     * @param $documentBuffer
     * @param $autoSigned
     *
     * @return object
     */
    public function uploadFileToWallet(LemonWayWallet $wallet, $fileName, $type, $documentBuffer, $sddMandateId = false)
    {
        $request = ['wallet' => $wallet->ID, 'fileName' => $fileName, 'type' => $type, 'buffer' => $documentBuffer];

        if ($sddMandateId != false) {
            $request['sddMandateId'] = $sddMandateId;
        }

        $result = $this->callService('UploadFile', $request);

        $this->checkError($result);

        return $result->UPLOAD;
    }

    /**
     * Gets modified wallets from a given timestamp date.
     *
     * @param $timeStamp
     *
     * @return object
     */
    public function getWalletsModified($timestamp)
    {
        if (!$this->isTimestamp($timestamp)) {
            throw LemonWayExceptions::isNotATimeStamp();
        }

        $result = $this->callService('GetKycStatus', ['updateDate' => $timestamp]);

        $this->checkError($result);

        return $result->WALLETS;
    }

    /**
     * Get Balances for given update date or for wallet between walletIdStart and walletIdEned.
     *
     * @param $updateDate
     * @param false|string $walletIdStart
     * @param false|string $walletIdEnd
     */
    public function getBalances($updateDate = false, $walletIdStart = false, $walletIdEnd = false)
    {
        if ($updateDate) {
            if (!$this->isTimestamp($updateDate)) {
                throw LemonWayExceptions::isNotATimeStamp();
            }
            $request = ['updateDate' => $updateDate];
        } else {
            if (!$walletIdStart) {
                return 'Wallet ID Start is mandatory if updateDate is false';
            }
            if (!$walletIdEnd) {
                return 'Wallet ID End is mandatory if updateDate is false';
            }

            $request = ['walletIdStart' => $walletIdStart, 'walletIdEnd' => $walletIdEnd];
        }

        $result = $this->callService('GetBalances', $request);

        $this->checkError($result);

        return $result->WALLETS;
    }

    /**
     * Get list of all transactions of a wallet.
     *
     * @param LemonWayWallet $wallet
     * @param null           $startDate
     * @param null           $endDate
     *
     * @return object
     */
    public function getTransactionsHistory(LemonWayWallet $wallet, $startDate = null, $endDate = null)
    {
        if ($startDate != null && !$this->isTimestamp($startDate)) {
            throw LemonWayExceptions::isNotATimeStamp('StartDate');
        }

        if ($endDate != null && !$this->isTimestamp($endDate)) {
            throw LemonWayExceptions::isNotATimeStamp('EndDate');
        }

        $result = $this->callService('GetWalletTransHistory', ['wallet' => $wallet->ID, 'startDate' => $startDate, 'endDate' => $endDate]);

        $this->checkError($result);

        return $result->TRANS;
    }

    /**
     * Creates a oayment form and returns the ID.
     *
     * @param LemonWayWallet $wallet
     * @param float          $amount - two decimals
     * @param array          $extras
     *
     * @return object
     */
    public function createPaymentForm(LemonWayWallet $wallet, $amount, $extras = [], $asLink = true, $lang = 'en', $style = '')
    {
        $request = ['wallet' => $wallet->ID, 'amountTot' => $amount];

        foreach ($extras as $extra => $value) {
            if (in_array($extra, $this->walletPaymentFormExtras)) {
                $request[$extra] = $value;
            }
        }

        $result = $this->callService('MoneyInWebInit', $request);

        $this->checkError($result);

        if ($asLink == true) {
            $data = $result->MONEYINWEB;

            return $this->webkitUrl.'?moneyInToken='.$data->TOKEN.'&p='.$style.'&lang='.$lang;
        }

        return $result->MONEYINWEB;
    }

    /**
     * Creates a oayment form and returns the ID.
     *
     * @param LemonWayWallet $wallet
     * @param float          $amount - two decimals
     * @param array          $extras
     *
     * @return object
     */
    public function createPaymentFormWithCardId(LemonWayWallet $wallet, $amount, $cardId)
    {
        $request = ['wallet' => $wallet->ID, 'cardId' => $cardId, 'amountTot' => $amount];

        foreach ($extras as $extra => $value) {
            if (in_array($extra, $this->walletPaymentFormWithCardExtras)) {
                $request[$extra] = $value;
            }
        }

        $result = $this->callService('MoneyInWithCardId', $request);

        $this->checkError($result);

        return $result;
    }

    /**
     * @param LemonWayWallet $wallet
     * @param $holder
     * @param $iban
     * @param $address1
     * @param $address2
     * @param $bic
     * @param null $comment
     */
    public function registerIban(LemonWayWallet $wallet, $holder, $iban, $address1, $address2, $bic = null, $comment = null)
    {

        //Test IBAN
        $validateIban = Validator::make(['iban' => $iban], ['iban' => 'iban'])->passes();
        if (!$validateIban) {
            throw LemonWayExceptions::ibanIsNotValid();
        }

        if ($bic != null) {
            $validateBic = Validator::make(['bic' => $bic], ['bic' => 'bic_swift'])->passes();

            if (!$validateBic) {
                throw LemonWayExceptions::bicSwiftIsNotValid();
            }
        }

        $result = $this->callService('RegisterIBAN', ['wallet' => $wallet->ID, 'holder' => $holder, 'iban' => $iban, 'dom1' => $address1, 'dom2' => $address2, 'comment' => $comment]);

        $this->checkError($result);

        return $result->IBAN_REGISTER;
    }

    /**
     * Check if the given result has an error.
     *
     * @param $result
     */
    private function checkError($result)
    {
        if ($result->E !== null) {
            throw LemonWayExceptions::apiError($result->E->Msg, $result->E->Code);
        }
    }

    /**
     * Check if given timestamp is valid.
     *
     * @param $timestamp
     */
    private function isTimestamp($timestamp)
    {
        $date = DateTime::createFromFormat('U', $timestamp);

        return $date && DateTime::getLastErrors()['warning_count'] == 0 && DateTime::getLastErrors()['error_count'] == 0;
    }

    /**
     * Call a service.
     *
     * @param string $serviceName
     * @param array  $parameters
     *
     * @return string
     */
    public function callService($serviceName, array $parameters)
    {
        $parameters = array_merge($this->callParameters, $parameters);

        // wrap to 'p'
        $request = ['p' => $parameters];

        $serviceUrl = $this->apiKey.'/'.$serviceName;

        $client = new Client([
            'base_uri'        => $this->apiKey.' / ',
            'headers'         => [
                'Content - type:application / json; charset = utf - 8',
                'Accept:application / json',
                'Cache - Control:no - cache',
                'Pragma:no - cache',
            ],
            'connect_timeout' => 60,
            'verify'          => $this->sslActive,
            'json'            => $request,
        ]);

        $response = null;

        try {
            $response = $client->post($serviceName);
        } catch (RequestException $e) {
            $context = $e->getHandlerContext();
            if (isset($context['error'])) {
                $error = $context['error'];
                dump($error);
            } else {
                echo Psr7\str($e->getRequest());
                if ($e->hasResponse()) {
                    echo Psr7\str($e->getResponse());
                }
            }
        }

        if ($response) {
            $body = $response->getBody();

            $obj = json_decode($body)->d;

            return (object) $obj;
        }

        return [];
    }
}
