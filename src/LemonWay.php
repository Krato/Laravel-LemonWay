<?php

namespace Infinety\LemonWay;

use App\LemonWayUser;

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
        $this->apiKey    = config('lemonway.api_url');
        $this->login     = config('lemonway.login');
        $this->password  = config('lemonway.password');
        $this->language  = config('lemonway.language');
        $this->version   = config('lemonway.version');
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
     * Return a LemonUser to use with api calls
     *
     * @param $wallet
     * @param $clientMail
     * @param $clientFirstName
     * @param $clientLastName
     * @param $clientTitle
     * @param $street
     * @param $postCode
     * @param $city
     * @param $cityIso3
     * @param $phoneNumber
     * @param $mobileNumber
     * @param $birthdate
     * @param $isDebtor
     * @param null $nationalityIso3
     * @param null $birthCity
     * @param null $birthCountryIso3
     * @param null $payerOrBeneficiary
     * @param null $isOneTimeCustomer
     * @param null $isTechWallet
     *
     * @return mixed
     */
    public function setWalletUser($wallet, $clientMail, $clientFirstName, $clientLastName, $clientTitle, $street = null, $postCode = null, $city = null, $cityIso3 = null, $phoneNumber = null, $mobileNumber = null, $birthdate = null, $isDebtor = null, $nationalityIso3 = null, $birthCity = null, $birthCountryIso3 = null, $payerOrBeneficiary = null, $isOneTimeCustomer = null, $isTechWallet = null)
    {
        $user                     = new LemonWayUser;
        $user->wallet             = $wallet;
        $user->clientMail         = $clientMail;
        $user->clientFirstName    = $clientFirstName;
        $user->clientLastName     = $clientLastName;
        $user->clientTitle        = $clientTitle;
        $user->street             = $street;
        $user->postCode           = $postCode;
        $user->city               = $city;
        $user->cityIso3           = $cityIso3;
        $user->phoneNumber        = $phoneNumber;
        $user->mobileNumber       = $mobileNumber;
        $user->birthdate          = $birthdate;
        $user->isDebtor           = $isDebtor;
        $user->nationalityIso3    = $nationalityIso3;
        $user->birthCity          = $birthCity;
        $user->birthCountryIso3   = $birthCountryIso3;
        $user->payerOrBeneficiary = $payerOrBeneficiary;
        $user->isOneTimeCustomer  = $isOneTimeCustomer;
        $user->isTechWallet       = $isTechWallet;

        return $user;
    }

    public function createWallet()
    {
        //
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

        // dump($parameters);
        // return false;

        // wrap to 'p'
        $request    = json_encode(['p' => $parameters]);
        $serviceUrl = $this->apiKey . '/' . $serviceName;

        $headers = ['Content-type: application/json;charset=utf-8',
            'Accept: application/json',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            //"Content-Length:".strlen($request)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslActive);

        $response = curl_exec($ch);

        $network_err = curl_errno($ch);
        if ($network_err) {
            error_log('curl_err: ' . $network_err);
            throw new Exception($network_err);
        } else {
            $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpStatus == 200) {
                $unwrapResponse = json_decode($response)->d;
                $businessErr    = $unwrapResponse->E;
                if ($businessErr) {
                    error_log($businessErr->Code . ' - ' . $businessErr->Msg . ' - Technical info: ' . $businessErr->Error);
                    throw new \Exception($businessErr->Code . ' - ' . $businessErr->Msg);
                }

                return $unwrapResponse;
            } else {
                throw new \Exception("Service return HttpStatus $httpStatus");
            }
        }
    }
}
