<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= $order->order_number ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .restaurant-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .address {
            font-size: 10px;
            margin-bottom: 5px;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .order-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .items {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .item {
            margin-bottom: 5px;
        }
        .item-name {
            font-weight: bold;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-left: 10px;
        }
        .totals {
            margin-bottom: 15px;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .total {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .payment-info {
            border-top: 1px dashed #333;
            padding-top: 10px;
            margin-bottom: 15px;
        }
        .payment-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            border-top: 1px dashed #333;
            padding-top: 10px;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .receipt { border: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="restaurant-name"><?= $tenant->restaurant_name ?></div>
            <div class="address"><?= $tenant->address ?></div>
            <div class="address">Tel: <?= $tenant->phone ?></div>
        </div>

        <div class="order-info">
            <div>
                <span>Order #:</span>
                <span><?= $order->order_number ?></span>
            </div>
            <div>
                <span>Date:</span>
                <span><?= date('M d, Y H:i', strtotime($order->ordered_at)) ?></span>
            </div>
            <div>
                <span>Table:</span>
                <span><?= $order->table_numbers ?? 'N/A' ?></span>
            </div>
            <div>
                <span>Cashier:</span>
                <span><?= $order->cashier_name ?? 'System' ?></span>
            </div>
        </div>

        <div class="items">
            <?php foreach ($order->items as $item): ?>
            <div class="item">
                <div class="item-name"><?= esc($item->item_name) ?></div>
                <div class="item-details">
                    <span><?= $item->quantity ?> x ₱<?= number_format($item->unit_price, 2) ?></span>
                    <span>₱<?= number_format($item->total_price, 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="totals">
            <div>
                <span>Subtotal:</span>
                <span>₱<?= number_format($order->subtotal, 2) ?></span>
            </div>
            <?php if ($order->service_charge > 0): ?>
            <div>
                <span>Service Charge:</span>
                <span>₱<?= number_format($order->service_charge, 2) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order->vat_amount > 0): ?>
            <div>
                <span>VAT:</span>
                <span>₱<?= number_format($order->vat_amount, 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="total">
                <span>TOTAL:</span>
                <span>₱<?= number_format($order->total_amount, 2) ?></span>
            </div>
        </div>

        <div class="payment-info">
            <div>
                <span>Payment Method:</span>
                <span><?= ucfirst($order->payment_method) ?></span>
            </div>
            <?php if ($order->payment_method === 'cash'): ?>
            <div>
                <span>Amount Received:</span>
                <span>₱<?= number_format($order->amount_received, 2) ?></span>
            </div>
            <div>
                <span>Change:</span>
                <span>₱<?= number_format($order->change_amount, 2) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <div>Thank you for your order!</div>
            <div>Please come again</div>
            <div style="margin-top: 10px;">
                <button onclick="window.print()" style="padding: 5px 10px; font-size: 10px;">Print</button>
                <button onclick="window.close()" style="padding: 5px 10px; font-size: 10px; margin-left: 5px;">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
