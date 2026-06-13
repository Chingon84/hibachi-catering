<?php

namespace App\Support;

class CaliforniaCateringTax
{
    /**
     * California catering tax: taxable base includes food/items subtotal, travel fee,
     * and mandatory gratuity/service charge. Voluntary tips are excluded.
     */
    public static function taxableBase(
        float $subtotal,
        float $travelFee = 0,
        float $mandatoryGratuity = 0,
        float $serviceCharge = 0,
        float $taxableAdjustments = 0,
        float $discount = 0
    ): float {
        return max(0, round($subtotal + $travelFee + $mandatoryGratuity + $serviceCharge + $taxableAdjustments - $discount, 2));
    }

    public static function tax(float $taxableBase, float $taxRatePercent): float
    {
        $taxableCents = (int) round(max(0, $taxableBase) * 100);
        $taxCents = (int) round($taxableCents * (max(0, $taxRatePercent) / 100));

        return round($taxCents / 100, 2);
    }
}
