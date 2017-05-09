<?php

namespace Infinety\LemonWay\Models;

use Illuminate\Database\Eloquent\Model;
use Infinety\LemonWay\LemonWayFacade as LemonWay;

class LemonWayWallet extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    // /**
    //  * @var array
    //  */
    // protected $appends = ['balances'];

    /**
     * Returns the balances of current wallet
     *
     * @return [type]
     */
    public function getBalancesAttribute()
    {
        $balances = LemonWay::getBalances(false, $this->LWID, $this->LWID);

        if ($balances->WALLET && $balances->WALLET[0]) {
            return $balances->WALLET[0];
        }

        return;
    }

    /**
     * @return null
     */
    public function getTransactionsAttribute()
    {
        return LemonWay::getTransactionsHistory($this);
    }

    /**
     * Uploads a file to current wallet
     *
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
     *
     * @param $documentBuffer
     * @param $autoSigned
     */
    public function uploadFile($name, $type, $documentBuffer, $sddMandateId = false)
    {
        return LemonWay::uploadFileToWallet($this, $name, $type, $documentBuffer.$sddMandateId);
    }
}
