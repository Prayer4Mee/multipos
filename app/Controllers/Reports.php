<?php

namespace App\Controllers;

use App\Controllers\BaseRestaurantController;

class Reports extends BaseRestaurantController
{
    /**
     * Reports dashboard
     */
    public function index()
    {
        // $this->requireRole(['manager', 'owner', 'accountant']);
        // 1. Read input
        $fromDate = $this->request->getGet('fromDate') ?: null;
        $toDate = $this->request->getGet('toDate') ?: null; 
        $chartType = $this->request->getGet('chartType') ?: 'daily';
        // 2. Validate before any query
        // ✔ Prevents invalid dates ✔ Prevents DB errors ✔ Blocks malformed requests
        if ($fromDate && $toDate) {
            if (
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) ||
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)
            ) {
                log_message('error', 'Invalid date format received', [
                    'fromDate' => $fromDate,
                    'toDate' => $toDate
                ]);

                return redirect()->to(
                    base_url("restaurant/{$this->tenantId}/reports")
                );
            }
        }
        // 3. Safe to query
        $metrics = $this->getSummaryMetrics($fromDate, $toDate);
        $daily_sales = $this->getDailySalesReport($fromDate, $toDate);
        // DEBUG
        log_message('debug', 'Metrics: ' . json_encode($metrics));
        log_message('debug', 'Daily Sales: ' . json_encode($daily_sales));

        $data = [
            'title' => 'Reports & Analytics',
            // Added metrics for the 4 cards at the top
            'tenant' => $this->tenantConfig,  // ADD THIS LINE
            'tenant_slug' => $this->tenantId,  // ADD THIS LINE
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'metrics' => $metrics,
            'daily_sales' => $daily_sales,
            'popular_items' => $this->getPopularItemsReport($fromDate, $toDate),
            'payment_methods' => $this->getPaymentMethodsReport($fromDate, $toDate),
            // Recent orders for the table at the bottom
            'recent_orders' => $this->getRecentOrders($fromDate, $toDate),
            'chartType' => $chartType,
            'hourly_sales' => $this->getHourlySalesReport($fromDate, $toDate),
            // Add this later on the report
            // 'vat_report' => $this->getMonthlyVATReport()
        ];
        // edit this part to pass data to the view from reports/index to:
        return $this->loadTenantView('reports', $data);
    }
    /**
 * Export current report data to CSV
 */
    public function export()
{
    // 1. Get Filters
    $fromDate = $this->request->getGet('fromDate');
    $toDate = $this->request->getGet('toDate');

    // 2. Collect All Data
    $metrics = $this->getSummaryMetrics($fromDate, $toDate);
    $popular = $this->getPopularItemsReport($fromDate, $toDate);
    $hourly = $this->getHourlySalesReport($fromDate, $toDate);
    $orders = $this->getRecentOrders($fromDate, $toDate);

    // 3. Setup CSV Download
    $filename = "Jollibee_Full_Report_" . ($fromDate ?? date('Y-m-d')) . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // --- SECTION 1: KEY PERFORMANCE INDICATORS ---
    fputcsv($output, ['BUSINESS SUMMARY REPORT']);
    fputcsv($output, ['Period:', ($fromDate ?: 'Start') . ' to ' . ($toDate ?: 'Today')]);
    fputcsv($output, []); // Spacer
    fputcsv($output, ['Total Sales', 'Total Orders', 'Avg Order Value']);
    fputcsv($output, [
        'PHP ' . number_format($metrics->total_sales ?? 0, 2),
        $metrics->total_orders ?? 0,
        'PHP ' . number_format($metrics->avg_order_value ?? 0, 2)
    ]);
    fputcsv($output, []); fputcsv($output, []);

    // --- SECTION 2: TOP SELLING ITEMS ---
    fputcsv($output, ['TOP SELLING ITEMS']);
    fputcsv($output, ['Item Name', 'Price', 'Orders Count', 'Total Sold']);
    foreach ($popular as $item) {
        fputcsv($output, [$item->name, $item->price, $item->orders_count, $item->total_quantity]);
    }
    fputcsv($output, []); fputcsv($output, []);

    // --- SECTION 3: HOURLY PERFORMANCE ---
    fputcsv($output, ['HOURLY SALES BREAKDOWN']);
    fputcsv($output, ['Hour', 'Transactions', 'Revenue']);
    foreach ($hourly as $h) {
        fputcsv($output, [$h->hour . ':00', $h->total_orders, 'PHP ' . number_format($h->total_sales, 2)]);
    }
    fputcsv($output, []); fputcsv($output, []);

    // --- SECTION 4: DETAILED ORDER LOG ---
    fputcsv($output, ['DETAILED ORDER LIST']);
    fputcsv($output, ['Order ID', 'Table', 'Items', 'Payment', 'Total', 'Status', 'Timestamp']);
    foreach ($orders as $order) {
        fputcsv($output, [
            '#' . $order->id,
            $order->table_number ?? 'N/A',
            $order->items,
            ucfirst($order->payment_method),
            $order->total_amount,
            $order->status,
            $order->ordered_at
        ]);
    }

    fclose($output);
    exit;
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
     * Metrics for the 4 cards (Total Sales, Total Orders, etc.)
     */
    private function getSummaryMetrics($fromDate = null, $toDate = null): object
    {
        $whereClause = "WHERE payment_status = 'paid' AND status != 'cancelled'";
        $params = [];

        if ($fromDate && $toDate) {
            $whereClause .= " AND ordered_at >= ? AND ordered_at < DATE_ADD(?, INTERVAL 1 DAY)";
            $params = [$fromDate, $toDate];
        } else {
            // Default: current month if no date range provided
            $whereClause .= " AND MONTH(ordered_at) = MONTH(CURRENT_DATE) AND YEAR(ordered_at) = YEAR(CURRENT_DATE)";
        }

        return $this->tenantDb->query("
            SELECT 
                SUM(total_amount) as total_sales,
                COUNT(id) as total_orders,
                AVG(total_amount) as avg_order_value
            FROM orders 
            $whereClause
        ", $params)->getRow();
        
    }
    /**
     * Generate daily sales report
     */
    private function getDailySalesReport($fromDate = null, $toDate = null): array
    {
         try {
            $whereClause = "WHERE payment_status = 'paid' AND status != 'cancelled'";
            $params = [];

            if ($fromDate && $toDate) {
                $whereClause .= " AND ordered_at >= ? AND ordered_at < DATE_ADD(?, INTERVAL 1 DAY)";
                $params = [$fromDate, $toDate];
            } else {
                // Default: last 30 days if no date range provided
                $whereClause .= " AND ordered_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
            }
            return $this->tenantDb->query("
            SELECT 
                DATE(ordered_at) as sale_date,
                SUM(total_amount) as total_daily_sales
            FROM orders 
            $whereClause
            GROUP BY DATE(ordered_at)
            ORDER BY sale_date ASC
            ", $params)->getResult();
        // log_message('debug', 'Daily sales result: ' . json_encode($result));
        // return $result;
        }
        catch (\Exception $e) {
            log_message('error', 'getDailySalesReport error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular items report
     */
    private function getPopularItemsReport($fromDate = null, $toDate = null): array
    {
        try{
            // We filter by 'completed' status so cancelled orders don't skew popularity
            $whereClause = "WHERE o.payment_status = 'paid' AND o.status != 'cancelled'";
            $params = [];

            if ($fromDate && $toDate) {
                $whereClause .= " AND o.ordered_at >= ? AND o.ordered_at < DATE_ADD(?, INTERVAL 1 DAY)";
                $params = [$fromDate, $toDate];
            } else {
                // Default: last 30 days if no date range provided
                $whereClause .= " AND o.ordered_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
            }

            return $this->tenantDb->query("
                SELECT 
                    mi.name as name, 
                    mi.price as price,
                    SUM(oi.quantity) as total_quantity,
                    COUNT(DISTINCT oi.order_id) as orders_count -- Count total times this item appeared in orders not using distinct                   
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                $whereClause
                GROUP BY mi.id, mi.name, mi.price
                ORDER BY orders_count DESC -- ensures the right order, the most sold item is number 1
                LIMIT 10
            ", $params)->getResult();
            // SUM(oi.quantity) as total_quantity, -- Added this so ORDER BY works
            // Now it is fixed
        }
        catch (\Exception $e) {
            log_message('error', 'getPopularItemsReport error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payment methods report
     */
    private function getPaymentMethodsReport($fromDate = null, $toDate = null): array
    {
        try {
            $whereClause = "WHERE payment_status = 'paid' AND status != 'cancelled'";
            $params = [];

            if ($fromDate && $toDate) {
                $whereClause .= " AND ordered_at >= ? AND ordered_at < DATE_ADD(?, INTERVAL 1 DAY)";
                $params = [$fromDate, $toDate];
            } else {
                // Default: current day if no date range provided
                $whereClause .= " AND DATE(ordered_at) = CURRENT_DATE";
            }

            return $this->tenantDb->query("
                SELECT
                    LOWER(payment_method) as payment_method,  
                    SUM(total_amount) as total_amount,
                    COUNT(id) as transaction_count
                FROM orders 
                $whereClause
                GROUP BY payment_method
            ", $params)->getResult();
        } 
        catch (\Exception $e) {
            log_message('error', 'getPaymentMethodsReport error: ' . $e->getMessage());
            return [];
        }
    }
    // payment_method, -- Added this so you know which label belongs to the amount

    private function getRecentOrders($fromDate = null, $toDate = null): array
    {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];

            if ($fromDate && $toDate) {
                $whereClause .= " AND o.ordered_at >= ? AND o.ordered_at < DATE_ADD(?, INTERVAL 1 DAY)";
                $params = [$fromDate, $toDate];
            }
            return $this->tenantDb->query("
                SELECT 
                    o.id,
                    o.total_amount,
                    o.status,
                    o.ordered_at,
                    o.payment_method,
                    rt.table_number,
                    GROUP_CONCAT(mi.name SEPARATOR ', ') AS items
                FROM orders o
                LEFT JOIN restaurant_tables rt ON o.table_id = rt.id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id
                $whereClause
                GROUP BY o.id
                ORDER BY o.ordered_at DESC
                LIMIT 10
                ", $params)->getResult();
        }
        catch (\Exception $e) {
            log_message('error', 'getRecentOrders error: ' . $e->getMessage());
            return [];
        }
    }

    private function getHourlySalesReport($fromDate = null, $toDate = null): array
    {
        try {
            $whereClause = "WHERE payment_status = 'paid' AND status != 'cancelled'";
            $params = [];

            if ($fromDate && $toDate) {
                $whereClause .= " AND ordered_at >= ? AND ordered_at < DATE_ADD(?, INTERVAL 1 DAY)";
                $params = [$fromDate, $toDate];
            } else {
                $whereClause .= " AND DATE(ordered_at) = CURRENT_DATE";
            }

            return $this->tenantDb->query("
                SELECT
                    HOUR(ordered_at) as hour,
                    COUNT(id) as total_orders,
                    SUM(total_amount) as total_sales
                FROM orders
                $whereClause
                GROUP BY hour
                ORDER BY total_sales DESC
                LIMIT 5
            ", $params)->getResult();
        }
        catch (\Exception $e) {
            log_message('error', 'getHourlySalesReport error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate Z-Reading report
     */
    private function generateZReading(): array
    {
        $today = date('Y-m-d');
        try{
            return $this->tenantDb->query("
                SELECT 
                    SUM(CASE WHEN o.payment_method = 'maya' THEN o.total_amount ELSE 0 END) as maya_sales,
                    SUM(CASE WHEN o.payment_method = 'card' THEN o.total_amount ELSE 0 END) as card_sales,
                    SUM(CASE WHEN o.payment_method = 'cash' THEN o.total_amount ELSE 0 END) as cash_sales,
                    SUM(o.total_amount) as total_sales
                FROM orders o
                WHERE DATE(o.ordered_at) = ?
                AND o.status = 'completed'
            ", [$today])->getRow();
        }
        catch (\Exception $e) {
            log_message('error', 'generateZReading error: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Get monthly VAT report
     */
    private function getMonthlyVATReport(): array
    {
        $month = date('Y-m');
        try {
            return $this->tenantDb->query("
                SELECT 
                    COUNT(id) as total_transactions,
                    SUM(total_amount) as total_sales,
                    SUM(vat_amount) as total_vat
                FROM orders 
                WHERE DATE_FORMAT(ordered_at, '%Y-%m') = ?
                AND status = 'completed'
            ", [$month])->getRow();
        }
        catch (\Exception $e) {
            log_message('error', 'getMonthlyVATReport error: ' . $e->getMessage());
            return [];
        }
    }
    public function filter() {
        $fromDate = $this->request->getPost('fromDate');
        $toDate = $this->request->getPost('toDate');
        
        // Store in session so index() can use it
        session()->set('report_from_date', $fromDate);
        session()->set('report_to_date', $toDate);
        
        return redirect()->to(base_url("restaurant/{$this->tenantId}/reports"));
    }
}

?>