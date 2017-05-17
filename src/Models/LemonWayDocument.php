<?php

namespace Infinety\LemonWay\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LemonWayDocument extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['ID', 'S', 'TYPE', 'VD', 'C'];

    /**
     * Return the Id.
     *
     * @return int
     */
    public function getIdAttribute()
    {
        return $this->attributes['ID'];
    }

    /**
     * Return the Id.
     *
     * @return int
     */
    public function getValidDateAttribute()
    {
        if ($this->attributes['VD'] == '') {
            return;
        }

        return Carbon::createFromFormat('d/m/Y', $this->attributes['VD']);
    }

    /**
     * Return the status string.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        switch ($this->attributes['S']) {
            case 0:
                return 'Waiting';
                break;
            case 1:
                return 'Not verified';
                break;
            case 2:
                return 'Accepted';
                break;
            case 3:
                return 'Not Accepted';
                break;
            case 4:
                return 'Unredeable';
                break;
            case 5:
                return 'Expired';
                break;
            case 6:
                return 'Wrong type';
                break;
            case 7:
                return 'Wrong name';
                break;
            default:
                return 'Waiting';
                break;
        }
    }

    /**
     * Return the type string.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        switch ($this->attributes['TYPE']) {
            case 0:
                return 'ID';
                break;
            case 1:
                return 'Address';
                break;
            case 2:
                return 'IBAN';
                break;
            case 3:
                return 'Passport (European Community)';
                break;
            case 4:
                return 'Passport (outside the European Community)';
                break;
            case 5:
                return 'Residence permit';
                break;
            case 6:
                return 'Registry commerce number';
                break;
            case 21:
                return 'SDD mandate';
                break;
            default:
                return 'Others documents';
                break;
        }
    }
}
