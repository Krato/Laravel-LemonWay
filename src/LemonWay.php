<?php

namespace Infinety\LemonWay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
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
     * @var current user
     */
    protected $currentUser;

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
        $this->login = config('lemonway.login');
        $this->password = config('lemonway.password');
        $this->language = config('lemonway.language');
        $this->version = config('lemonway.version');
        $this->sslActive = config('lemonway.ssl');
        $this->createCredentialsData();
    }

    /**
     * Fet current client IP.
     *
     * @return string
     */
    protected function getUserIP()
    {
        $ip = '';

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
     * @param null $clientTitle
     * @param null $street
     * @param null $postCode
     * @param null $city
     * @param null $cityIso3
     * @param null $phoneNumber
     * @param null $mobileNumber
     * @param null $birthdate
     * @param null $isDebtor
     * @param null $nationalityIso3
     * @param null $birthCity
     * @param null $birthCountryIso3
     * @param null $payerOrBeneficiary
     * @param null $isOneTimeCustomer
     * @param null $isTechWallet
     *
     * @return mixed
     */
    public function setWalletUser($wallet, $clientMail, $clientFirstName, $clientLastName, $clientTitle = null, $street = null, $postCode = null, $city = null, $cityIso3 = null, $phoneNumber = null, $mobileNumber = null, $birthdate = null, $isDebtor = null, $nationalityIso3 = null, $birthCity = null, $birthCountryIso3 = null, $payerOrBeneficiary = null, $isOneTimeCustomer = null, $isTechWallet = null)
    {
        $user = new LemonWayUser();
        $user->wallet = $wallet;
        $user->clientMail = $clientMail;
        $user->clientFirstName = $clientFirstName;
        $user->clientLastName = $clientLastName;
        $user->clientTitle = $clientTitle;
        $user->street = $street;
        $user->postCode = $postCode;
        $user->city = $city;
        $user->cityIso3 = $cityIso3;
        $user->phoneNumber = $phoneNumber;
        $user->mobileNumber = $mobileNumber;
        $user->birthdate = $birthdate;
        $user->isDebtor = $isDebtor;
        $user->nationalityIso3 = $nationalityIso3;
        $user->birthCity = $birthCity;
        $user->birthCountryIso3 = $birthCountryIso3;
        $user->payerOrBeneficiary = $payerOrBeneficiary;
        $user->isOneTimeCustomer = $isOneTimeCustomer;
        $user->isTechWallet = $isTechWallet;

        return $user;
    }

    /**
     * Create a wallet for a user
     *
     * @param LemonWayUser $user
     *
     * @return wallet
     */
    public function createWallet(LemonWayUser $user)
    {
        $result = $this->callService('RegisterWallet', $user->toArray());

        $wallet = new LemonWayWallet();
        $wallet->fill((array) $result->WALLET);

        return ['result' => $result, 'wallet' => $wallet];
    }

    /**
     * Create a wallet for a user
     *
     * @param LemonWayUser $user
     *
     * @return wallet
     */
    public function getWalletDetails(LemonWayUser $user, $walletId)
    {
        $result = $this->callService('GetWalletDetails', ['wallet' => $walletId, 'email' => $user->clientMail]);

        if ($result->E !== null) {

            throw LemonWayExceptions::apiError($result->E->Msg, $result->E->Code);
        }
        $wallet = new LemonWayWallet();
        $wallet->fill((array) $result->WALLET);

        return $wallet;
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
            'base_uri'        => $this->apiKey.'/',
            'headers'         => [
                'Content-type: application/json;charset=utf-8',
                'Accept: application/json',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
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
