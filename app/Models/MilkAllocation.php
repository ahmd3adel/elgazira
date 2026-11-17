<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkAllocation extends Model
{
    use HasFactory;

    // ============ الثوابت ============
    const BISCUIT_PACKS_PER_CARTON = 108;
    const MILK_CANS_PER_CARTON     = 27;
    const MILK_CARTONS_PER_BISCUIT_CARTON = 4;

    protected $fillable = [
        'department_id',
        'allocation_date',
        'biscuit_cartons',
        'biscuit_packs',
        'milk_cans',
        'milk_cartons',
        'total_meals',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'biscuit_cartons' => 'integer',
        'biscuit_packs'   => 'integer',
        'milk_cans'       => 'integer',
        'milk_cartons'    => 'integer',
        'total_meals'     => 'integer',
    ];

    // ============ العلاقات ============
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============ دوال الحساب ============
    /**
     * تحويل كراتين السادة إلى كل الأرقام الأخرى
     */
    public static function calculateFromCartons(int $biscuitCartons): array
    {
        $biscuitPacks = $biscuitCartons * self::BISCUIT_PACKS_PER_CARTON;
        $milkCans     = $biscuitPacks; // 1:1
        $milkCartons  = (int) ceil($milkCans / self::MILK_CANS_PER_CARTON);
        $totalMeals   = $biscuitPacks;

        return [
            'biscuit_cartons' => $biscuitCartons,
            'biscuit_packs'   => $biscuitPacks,
            'milk_cans'       => $milkCans,
            'milk_cartons'    => $milkCartons,
            'total_meals'     => $totalMeals,
        ];
    }

    // ============ Boot ============
    protected static function booted()
    {
        static::saving(function ($model) {
            // احسب كل الأرقام تلقائياً قبل الحفظ
            $calc = self::calculateFromCartons((int) $model->biscuit_cartons);

            $model->biscuit_packs = $calc['biscuit_packs'];
            $model->milk_cans     = $calc['milk_cans'];
            $model->milk_cartons  = $calc['milk_cartons'];
            $model->total_meals   = $calc['total_meals'];
        });
    }
}