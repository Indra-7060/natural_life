<?php

namespace App\Libraries;

/**
 * Shipping rule verified on the original store (2026-11-11):
 * flat Rs 40 below a Rs 500 subtotal, free at Rs 500 and above, India-wide.
 * Both numbers are editable in Admin > Settings > Shop.
 */
class ShippingCalculator
{
    public function quote(float $subtotal): array
    {
        $flat      = (float) setting('shipping_flat_rate', '40');
        $threshold = (float) setting('free_shipping_threshold', '500');

        if ($subtotal <= 0) {
            return ['amount' => 0.0, 'label' => '', 'threshold' => $threshold, 'remaining' => $threshold];
        }
        if ($threshold > 0 && $subtotal >= $threshold) {
            return ['amount' => 0.0, 'label' => 'Free shipping', 'threshold' => $threshold, 'remaining' => 0.0];
        }

        return [
            'amount'    => $flat,
            'label'     => 'Standard shipping',
            'threshold' => $threshold,
            'remaining' => $threshold > 0 ? round($threshold - $subtotal, 2) : 0.0,
        ];
    }
}
