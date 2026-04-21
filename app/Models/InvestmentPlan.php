<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['tier', 'name', 'description', 'price', 'target_return', 'duration_days', 'status', 'sort_order'])]
class InvestmentPlan extends Model
{
    public function userInvestments()
    {
        return $this->hasMany(UserInvestment::class, 'plan_id');
    }
}
