<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'description', 'min_amount', 'max_amount', 'roi_daily_percent', 'duration_days', 'status', 'sort_order'])]
class InvestmentPlan extends Model
{
    public function userInvestments()
    {
        return $this->hasMany(UserInvestment::class, 'plan_id');
    }
}
