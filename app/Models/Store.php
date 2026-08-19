<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['store_name', 'description', 'address', 'owner_name', 'mobile_number', 'email', 'admin_redirect_link', 'customer_redirect_link'])]
class Store extends Model
{
    use HasFactory, SoftDeletes;
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
