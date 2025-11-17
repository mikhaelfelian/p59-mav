<?php
/**
 * General Helper Functions
 * 
 * Reusable helper functions for common operations across the application
 */

if (!function_exists('get_payment_status_badge')) {
    /**
     * Get payment status badge HTML
     * 
     * @param string $status Payment status ('0' = unpaid, '1' = partial, '2' = paid)
     * @param int|null $saleId Optional sale ID to detect paylater (checks if no payment record exists)
     * @return string HTML badge string
     */
    function get_payment_status_badge(string $status, ?int $saleId = null): string
    {
        // Check for paylater: if status is '0' (unpaid) and no payment record exists
        if ($status === '0' && $saleId !== null) {
            $db = \Config\Database::connect();
            $paymentExists = $db->table('sales_payments')
                ->where('sale_id', $saleId)
                ->countAllResults() > 0;
            
            // If no payment record exists and status is unpaid, it's likely paylater
            if (!$paymentExists) {
                return '<span class="badge bg-secondary">Paylater</span>';
            }
        }
        
        // Standard payment status badges
        $badges = [
            '0' => '<span class="badge bg-warning">Unpaid</span>',
            '1' => '<span class="badge bg-info">Partial</span>',
            '2' => '<span class="badge bg-success">Paid</span>',
            '3' => '<span class="badge bg-success">Paylater</span>', // Explicit paylater status if used
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}

