<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'bank_name', 'bank_account', 'status_kyc', 'is_disabled', 'is_withdraw_unlocked', 'role', 'country_code', 'country_name', 'currency_code', 'currency_symbol'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function investments()
    {
        return $this->hasMany(UserInvestment::class);
    }

    public function kycRequest()
    {
        return $this->hasOne(KycRequest::class);
    }

    /**
     * Get masked bank account for security.
     * Example: *******6789
     */
    public function getMaskedBankAccountAttribute()
    {
        if (empty($this->bank_account)) return '-';
        $len = strlen($this->bank_account);
        if ($len <= 4) return '****' . $this->bank_account;
        return str_repeat('*', $len - 4) . substr($this->bank_account, -4);
    }
}
