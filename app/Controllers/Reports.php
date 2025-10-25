<?php

namespace App\Controllers\Restaurant;

use App\Controllers\BaseRestaurantController;

class Reports extends BaseRestaurantController
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        $this->requireRole(['manager', 'owner', 'accountant']);

        $data = [
            'title' => 'Reports & Analytics',
            'daily_sales' => $this->getDailySalesReport(),
            'popular_items' => $this->getPopularItemsReport(),
            'payment_methods' => $this->getPaymentMethodsReport()
        ];

        return $this->loadTenantView('reports/index', $data);
    }

    /**
     * BIR Sales reports
     */
    public function birReports()
    {
        $this->requireRole(['manager', 'owner', 'accountant']);

        $data = [
            'title' => 'BIR Compliance Reports',
            'z_reading' => $this->generateZReading(),
            'monthly_vat' => $this->getMonthlyVATReport()
        ];

        return $this->loadTenantView('reports/bir', $data);
    }

    /**
     * Generate daily sales report
     */
    private function getDailySalesReport(): array
    {
        return $this->tenantDb->query("
            SELECT 
                SUM(service_charge) as total_service_charge
            FROM orders 
            WHERE status = 'completed' 
            AND ordered_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY DATE(ordered_at)
            ORDER BY date DESC
        ")->getResult();
    }

    /**
     * Get popular items report
     */
    private function getPopularItemsReport(): array
    {
        return $this->tenantDb->query("
            SELECT 
                COUNT(DISTINCT oi.order_id) as orders_count
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed'
            AND o.ordered_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY mi.id, mi.name
            ORDER BY total_quantity DESC
            LIMIT 10
        ")->getResult();
    }

    /**
     * Get payment methods report
     */
    private function getPaymentMethodsReport(): array
    {
        return $this->tenantDb->query("
            SELECT 
                SUM(amount) as total_amount
            FROM payments 
            WHERE status = 'completed'
            AND DATE(processed_at) = CURRENT_DATE
            GROUP BY payment_method
        ")->getResult();
    }

    /**
     * Generate Z-Reading report
     */
    private function generateZReading(): array
    {
        $today = date('Y-m-d');
        
        return $this->tenantDb->query("
            SELECT 
                SUM(CASE WHEN p.payment_method = 'maya' THEN p.amount ELSE 0 END) as maya_sales
            FROM orders o
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE DATE(o.ordered_at) = ?
            AND o.status = 'completed'
        ", [$today])->getRow();
    }

    /**
     * Get monthly VAT report
     */
    private function getMonthlyVATReport(): array
    {
        $month = date('Y-m');
        
        return $this->tenantDb->query("
            SELECT 
                COUNT(id) as total_transactions
            FROM orders 
            WHERE DATE_FORMAT(ordered_at, '%Y-%m') = ?
            AND status = 'completed'
        ", [$month])->getRow();
    }
}

?>