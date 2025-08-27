<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income & Expense Payments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
        }
        .summary-cards {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            text-align: center;
        }
        .summary-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            min-width: 150px;
        }
        .summary-card.income { border-color: #28a745; }
        .summary-card.expense { border-color: #dc3545; }
        .summary-card.net { border-color: #007bff; }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .summary-card .amount {
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge-income {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .badge-expense {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Income & Expense Payments Report</h1>
        <p><strong><?= esc($user['first_name'] ?? '') ?> <?= esc($user['last_name'] ?? '') ?></strong></p>
        <p>Property Management System</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Generated On:</span>
            <span><?= date('F j, Y \a\t g:i A', strtotime($generated_at)) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Report Type:</span>
            <span><?= ucfirst($filters['payment_type'] ?? 'All') ?> Payments</span>
        </div>
        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
            <div class="info-row">
                <span class="info-label">Date Range:</span>
                <span>
                    <?= $filters['date_from'] ? date('M j, Y', strtotime($filters['date_from'])) : 'Beginning' ?>
                    to
                    <?= $filters['date_to'] ? date('M j, Y', strtotime($filters['date_to'])) : 'Present' ?>
                </span>
            </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Total Records:</span>
            <span><?= count($payments) ?></span>
        </div>
    </div>

    <div class="summary-cards">
        <div class="summary-card net">
            <h3>Total Net Income</h3>
            <div class="amount">SAR <?= number_format($totals['net_income'], 2) ?></div>
        </div>
        <div class="summary-card expense">
            <h3>Total Expenses</h3>
            <div class="amount">SAR <?= number_format($totals['total_expenses'], 2) ?></div>
        </div>
        <div class="summary-card income">
            <h3>Monthly Net Income</h3>
            <div class="amount">SAR <?= number_format($totals['monthly_net'], 2) ?></div>
        </div>
    </div>

    <?php if (!empty($payments)): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Property</th>
                    <th>Unit</th>
                    <th>Amount</th>
                    <th>Source/Category</th>
                    <th>Description</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($payment['date'])) ?></td>
                        <td class="text-center">
                            <?php if ($payment['type'] === 'income'): ?>
                                <span class="badge-income">Income</span>
                            <?php else: ?>
                                <span class="badge-expense">Expense</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($payment['property_name'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= esc($payment['unit_name'] ?? 'N/A') ?></td>
                        <td class="text-right">SAR <?= number_format($payment['amount'] ?? 0, 2) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $payment['source'] ?? 'N/A')) ?></td>
                        <td><?= esc(substr($payment['description'] ?? '', 0, 50)) ?><?= strlen($payment['description'] ?? '') > 50 ? '...' : '' ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $payment['method'] ?? 'N/A')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 30px;">
            <h3>Summary by Type</h3>
            <?php
            $incomeTotal = 0;
            $expenseTotal = 0;
            foreach ($payments as $payment) {
                if ($payment['type'] === 'income') {
                    $incomeTotal += $payment['amount'];
                } else {
                    $expenseTotal += $payment['amount'];
                }
            }
            ?>
            <table style="width: 50%; margin: 0 auto;">
                <tr>
                    <td><strong>Total Income:</strong></td>
                    <td class="text-right"><strong>SAR <?= number_format($incomeTotal, 2) ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Total Expenses:</strong></td>
                    <td class="text-right"><strong>SAR <?= number_format($expenseTotal, 2) ?></strong></td>
                </tr>
                <tr style="border-top: 2px solid #333;">
                    <td><strong>Net Income:</strong></td>
                    <td class="text-right"><strong>SAR <?= number_format($incomeTotal - $expenseTotal, 2) ?></strong></td>
                </tr>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center" style="margin: 50px 0;">
            <h3>No payments found</h3>
            <p>No payment records match your current filter criteria.</p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>This report was generated automatically by the Property Management System.</p>
        <p>For questions or concerns, please contact support.</p>
    </div>
</body>
</html>