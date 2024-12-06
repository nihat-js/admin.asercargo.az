<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'username',
        'password',
        'first_pass',
        'email',
        'address1',
        'address2',
        'address3',
        'zip1',
        'zip2',
        'zip3',
        'phone1',
        'phone2',
        'phone3',
        'passport_number',
        'birthday',
        'gender',
        'language',
        'suite',
        'balance',
        'console_limit',
        'console_option',
        'contract_id',
        'packing_service_id',
        'destination_id',
        'role_id',
        'created_by',
        'deleted_at',
        'deleted_by',
        'is_legality',
        'is_partner',
        'fcm_token',
        'read_notification_count',
        'branch_id',
        'passport_fin',
        'passport_series'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('users'));
    }

    public function deleted_value() {
        if ($this->deleted_by == null) {
            return 0;
        } else {
            return 1;
        }
    }

    public function username() {
        return $this->username;
    }

    public function role() {
        $role = $this->role_id;

        return $role;
    }
    
    public function branch() {
        $branch = $this->branch_id;
        
        return $branch;
    }

    public function location() {
        $location = $this->destination_id;

        return $location;
    }

    public function currency() {
        try {
            $location = $this->destination_id;

            $currency = Location::leftJoin('countries as c', 'locations.country_id', '=', 'c.id')
                ->leftJoin('currency as cur', 'c.currency_id', '=', 'cur.id')
                ->where('locations.id', $location)
                ->select('cur.name')
                ->first();

            if ($currency) {
                return $currency->name;
            } else {
                return "USD";
            }
        } catch (\Exception $exception) {
            return "USD";
        }
    }

    public function local_currency() {
        try {
            $location = $this->destination_id;

            $currency = Location::leftJoin('countries as c', 'locations.country_id', '=', 'c.id')
                ->leftJoin('currency as cur', 'c.local_currency', '=', 'cur.id')
                ->where('locations.id', $location)
                ->select('cur.name')
                ->first();

            if ($currency) {
                return $currency->name;
            } else {
                return "USD";
            }
        } catch (\Exception $exception) {
            return "USD";
        }
    }

    public function location_name() {
        try {
            $location_id = $this->destination_id;
            $location = Location::where('id', $location_id)->select('name')->first();

            if (!$location) {
                return '';
            }

            return $location->name;
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function location_address() {
        try {
            $location_id = $this->destination_id;
            $location = Location::where('id', $location_id)->select('address')->first();

            if (!$location) {
                return '';
            }

            return $location->address;
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function location_airport() {
        try {
            $location_id = $this->destination_id;
            $location = Location::where('id', $location_id)->select('airport')->first();

            if (!$location) {
                return '---';
            }

            return $location->airport;
        } catch (\Exception $exception) {
            return '---';
        }
    }

    public function has_access_for_add_new_seller() {
        try {
            $location_id = $this->destination_id;
            $location = Location::where('id', $location_id)->select('has_access_for_add_new_seller')->first();

            if (!$location) {
                return 0;
            }

            return $location->has_access_for_add_new_seller;
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function countryId()
    {
        $location = Location::find($this->destination_id);
        if ($location) {
            $country =  Countries::find($location->country_id);
            if ($country) {
                return $country->id;
            }
        }

        return null;
    }
}
