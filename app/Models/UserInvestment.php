<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'plan_id', 'plan_name', 'amount', 'roi_daily_percent', 'duration_days', 'start_at', 'end_at', 'status'])]
class UserInvestment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(InvestmentPlan::class, 'plan_id');
    }
}
