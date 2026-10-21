<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model Toko Grosir (Tenant).
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Store extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'status',
    ];

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * @return HasMany<StoreAdmin, $this>
     */
    public function admins(): HasMany
    {
        return $this->hasMany(StoreAdmin::class);
    }

    /**
     * @return HasMany<CommissionRate, $this>
     */
    public function commissionRates(): HasMany
    {
        return $this->hasMany(CommissionRate::class);
    }

    /**
     * @return HasOne<StoreShippingRate, $this>
     */
    public function shippingRate(): HasOne
    {
        return $this->hasOne(StoreShippingRate::class);
    }

    /**
     * @return HasMany<StoreBankAccount, $this>
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(StoreBankAccount::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<SubOrder, $this>
     */
    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    /**
     * @return HasMany<Withdrawal, $this>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
