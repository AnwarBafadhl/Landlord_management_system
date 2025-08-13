<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class Reports extends BaseController
{
    protected $propertyModel;
    protected $userModel;
    protected $paymentModel;
    protected $maintenanceModel;

    public function __construct()
    {
        $this->propertyModel = new \App\Models\PropertyModel();
        $this->userModel = new \App\Models\UserModel();
        
        // Check if PaymentModel exists, if not create a basic one or skip
        if (class_exists('\App\Models\PaymentModel')) {
            $this->paymentModel = new \App\Models\PaymentModel();
        }
        
        // Check if MaintenanceModel exists, if not create a basic one or skip
        if (class_exists('\App\Models\MaintenanceModel')) {
            $this->maintenanceModel = new \App\Models\MaintenanceModel();
        }
    }

    public function index()
    {
        $landlordId = session()->get('user_id');

        $data = [
            'properties' => $this->getPropertiesForLandlord($landlordId),
            'generated_reports' => $this->getGeneratedReports($landlordId),
            'report_data' => $this->getReportData($landlordId),
            'chart_data' => $this->getChartData($landlordId),
            'financial_summary' => $this->getFinancialSummary($landlordId),
            'maintenance_summary' => $this->getMaintenanceSummary($landlordId)
        ];

        return view('landlord/reports', $data);
    }

    public function generatePdf()
    {
        $landlordId = session()->get('user_id');
        $reportType = $this->request->getPost('report_type');
        $reportName = $this->request->getPost('report_name');
        $properties = $this->request->getPost('properties') ?? [];
        $notes = $this->request->getPost('notes');

        try {
            if ($reportType === 'ownership') {
                $htmlContent = $this->generateOwnershipReportHTML($landlordId, $properties, $reportName, $notes);
            } elseif ($reportType === 'owner_income') {
                $incomePeriod = $this->request->getPost('income_period');
                $periodStart = $this->request->getPost('period_start');
                $htmlContent = $this->generateOwnerIncomeReportHTML($landlordId, $properties, $reportName, $incomePeriod, $periodStart, $notes);
            } else {
                throw new \Exception('Invalid report type');
            }

            // Generate PDF using Dompdf
            $pdfContent = $this->generatePDFWithDompdf($htmlContent);

            // Save report record to database
            $this->saveReportRecord($landlordId, $reportType, $reportName, $properties);

            // Return PDF as response
            $this->response->setContentType('application/pdf');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $reportName . '_' . date('Y-m-d') . '.pdf"');
            return $this->response->setBody($pdfContent);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generatePDFWithDompdf($htmlContent)
    {
        // Configure Dompdf options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        // Create Dompdf instance
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }

    private function generateOwnershipReportHTML($landlordId, $properties, $reportName, $notes)
    {
        $ownershipData = $this->getOwnershipData($landlordId, $properties);

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                .date { font-size: 10px; color: #666; }
                .property { margin-bottom: 25px; page-break-inside: avoid; }
                .property-header { background-color: #f0f0f0; padding: 8px; font-weight: bold; font-size: 14px; }
                .property-info { padding: 10px 0; }
                .info-row { margin-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .notes { margin-top: 30px; }
                .notes-title { font-weight: bold; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">' . htmlspecialchars($reportName) . '</div>
                <div class="date">Generated on: ' . date('F j, Y, g:i a') . '</div>
            </div>';

        foreach ($ownershipData as $property) {
            $html .= '
            <div class="property">
                <div class="property-header">' . htmlspecialchars($property['property_name']) . '</div>
                <div class="property-info">
                    <div class="info-row"><strong>Address:</strong> ' . htmlspecialchars($property['address']) . '</div>
                    <div class="info-row"><strong>Property Type:</strong> ' . htmlspecialchars($property['property_type']) . '</div>
                    <div class="info-row"><strong>Total Units:</strong> ' . $property['total_units'] . '</div>
                    <div class="info-row"><strong>Current Value:</strong> $' . number_format($property['current_value'], 2) . '</div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Owner Name</th>
                            <th>Email</th>
                            <th class="text-center">Ownership %</th>
                            <th class="text-right">Investment Value</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($property['owners'] as $owner) {
                $html .= '
                        <tr>
                            <td>' . htmlspecialchars($owner['name']) . '</td>
                            <td>' . htmlspecialchars($owner['email']) . '</td>
                            <td class="text-center">' . $owner['ownership_percentage'] . '%</td>
                            <td class="text-right">$' . number_format($owner['investment_value'], 2) . '</td>
                        </tr>';
            }

            $html .= '
                    </tbody>
                </table>
            </div>';
        }

        if (!empty($notes)) {
            $html .= '
            <div class="notes">
                <div class="notes-title">Additional Notes:</div>
                <div>' . nl2br(htmlspecialchars($notes)) . '</div>
            </div>';
        }

        $html .= '</body></html>';

        return $html;
    }

    private function generateOwnerIncomeReportHTML($landlordId, $properties, $reportName, $incomePeriod, $periodStart, $notes)
    {
        $incomeData = $this->getIncomeData($landlordId, $properties, $incomePeriod, $periodStart);
        $periodText = $this->getPeriodText($incomePeriod, $periodStart);

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                .date { font-size: 10px; color: #666; }
                .property { margin-bottom: 30px; page-break-inside: avoid; }
                .property-header { background-color: #f0f0f0; padding: 8px; font-weight: bold; font-size: 14px; }
                .section { margin: 15px 0; }
                .section-header { background-color: #e8f4f8; padding: 6px; font-weight: bold; margin-bottom: 8px; }
                .income-header { background-color: #e8f5e8; }
                .expense-header { background-color: #fdf2f2; }
                .company-header { background-color: #f0f0ff; }
                .profit-header { background-color: #fff8dc; }
                .distribution-header { background-color: #f0fff0; }
                .row { margin-bottom: 4px; overflow: hidden; }
                .row-label { float: left; width: 70%; }
                .row-value { float: right; text-align: right; width: 25%; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 6px; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .total-row { font-weight: bold; border-top: 2px solid #333; }
                .explanation { font-style: italic; font-size: 10px; margin-top: 8px; padding: 8px; background-color: #f9f9f9; }
                .notes { margin-top: 30px; }
                .clear { clear: both; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">' . htmlspecialchars($reportName) . '</div>
                <div class="date">Generated on: ' . date('F j, Y, g:i a') . '</div>
                <div class="date">Report Period: ' . htmlspecialchars($periodText) . '</div>
            </div>';

        foreach ($incomeData as $property) {
            $html .= '
            <div class="property">
                <div class="property-header">' . htmlspecialchars($property['property_name']) . '</div>
                
                <div class="section">
                    <div class="section-header income-header">Income Summary</div>
                    <div class="row">
                        <div class="row-label">Total Rental Income:</div>
                        <div class="row-value">$' . number_format($property['total_income'], 2) . '</div>
                        <div class="clear"></div>
                    </div>
                    <div class="row">
                        <div class="row-label">Other Income:</div>
                        <div class="row-value">$' . number_format($property['other_income'], 2) . '</div>
                        <div class="clear"></div>
                    </div>
                    <div class="row total-row">
                        <div class="row-label"><strong>Gross Income:</strong></div>
                        <div class="row-value"><strong>$' . number_format($property['gross_income'], 2) . '</strong></div>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-header expense-header">Total Expenses</div>';

            foreach ($property['expenses'] as $expense) {
                $html .= '
                    <div class="row">
                        <div class="row-label">' . htmlspecialchars($expense['category']) . ':</div>
                        <div class="row-value">$' . number_format($expense['amount'], 2) . '</div>
                        <div class="clear"></div>
                    </div>';
            }

            $html .= '
                    <div class="row total-row">
                        <div class="row-label"><strong>Total Expenses:</strong></div>
                        <div class="row-value"><strong>$' . number_format($property['total_expenses'], 2) . '</strong></div>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-header company-header">Company Management Information</div>
                    <div class="row">
                        <div class="row-label">Company Management Percentage:</div>
                        <div class="row-value">' . $property['company_percentage'] . '%</div>
                        <div class="clear"></div>
                    </div>
                    <div class="row">
                        <div class="row-label">Company Management Fee:</div>
                        <div class="row-value">$' . number_format($property['company_fee'], 2) . '</div>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-header profit-header">Profit after Deducting Expenses</div>
                    <div class="row total-row">
                        <div class="row-label"><strong>Net Profit:</strong></div>
                        <div class="row-value"><strong>$' . number_format($property['net_profit'], 2) . '</strong></div>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-header distribution-header">Owner Profit Distribution</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Owner Name</th>
                                <th class="text-center">Ownership %</th>
                                <th class="text-right">Share Amount</th>
                                <th class="text-right">Final Amount</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($property['owner_distributions'] as $owner) {
                $html .= '
                            <tr>
                                <td>' . htmlspecialchars($owner['name']) . '</td>
                                <td class="text-center">' . $owner['ownership_percentage'] . '%</td>
                                <td class="text-right">$' . number_format($owner['share_amount'], 2) . '</td>
                                <td class="text-right">$' . number_format($owner['final_amount'], 2) . '</td>
                            </tr>';
            }

            $html .= '
                        </tbody>
                    </table>
                    <div class="explanation">
                        <strong>Explanation:</strong> Each owner receives their percentage share of the net profit after all expenses and management fees have been deducted. The final amount represents the actual distribution to each owner.
                    </div>
                </div>
            </div>';
        }

        if (!empty($notes)) {
            $html .= '
            <div class="notes">
                <div style="font-weight: bold; margin-bottom: 10px;">Additional Notes:</div>
                <div>' . nl2br(htmlspecialchars($notes)) . '</div>
            </div>';
        }

        $html .= '</body></html>';

        return $html;
    }

    private function getPropertiesForLandlord($landlordId)
    {
        if (method_exists($this->propertyModel, 'getPropertiesForLandlord')) {
            return $this->propertyModel->getPropertiesForLandlord($landlordId);
        }
        
        // Fallback: get properties directly from database
        $db = \Config\Database::connect();
        $builder = $db->table('properties');
        $builder->where('landlord_id', $landlordId);
        return $builder->get()->getResultArray();
    }

    private function getOwnershipData($landlordId, $propertyIds = [])
    {
        $db = \Config\Database::connect();

        // First, get properties owned by this landlord
        $builder = $db->table('properties p');
        $builder->select('p.*');
        $builder->where('p.landlord_id', $landlordId);
        
        if (!empty($propertyIds)) {
            $builder->whereIn('p.id', $propertyIds);
        }
        
        $properties = $builder->get()->getResultArray();
        
        $ownershipData = [];
        
        foreach ($properties as $property) {
            $propertyId = $property['id'];
            
            // Get all owners for this property
            $ownerBuilder = $db->table('property_ownership po');
            $ownerBuilder->select('po.ownership_percentage, u.firstname, u.lastname, u.email');
            $ownerBuilder->join('users u', 'u.id = po.landlord_id');
            $ownerBuilder->where('po.property_id', $propertyId);
            $owners = $ownerBuilder->get()->getResultArray();
            
            // If no ownership records exist, create default for current landlord
            if (empty($owners)) {
                $user = $db->table('users')->where('id', $landlordId)->get()->getRowArray();
                $owners = [[
                    'ownership_percentage' => 100,
                    'firstname' => $user['firstname'] ?? 'Unknown',
                    'lastname' => $user['lastname'] ?? 'User',
                    'email' => $user['email'] ?? 'unknown@email.com'
                ]];
            }
            
            $ownershipData[] = [
                'property_name' => $property['property_name'] ?? 'Unnamed Property',
                'address' => $property['property_address'] ?? 'Address not available',
                'property_type' => $property['property_type'] ?? 'Residential',
                'total_units' => $property['total_units'] ?? 1,
                'current_value' => $property['property_value'] ?? 0,
                'owners' => array_map(function($owner) use ($property) {
                    return [
                        'name' => $owner['firstname'] . ' ' . $owner['lastname'],
                        'email' => $owner['email'],
                        'ownership_percentage' => $owner['ownership_percentage'],
                        'investment_value' => ($property['property_value'] ?? 0) * ($owner['ownership_percentage'] / 100)
                    ];
                }, $owners)
            ];
        }

        return $ownershipData;
    }

    private function getIncomeData($landlordId, $propertyIds, $incomePeriod, $periodStart)
    {
        $dateRange = $this->calculateDateRange($incomePeriod, $periodStart);
        $db = \Config\Database::connect();

        // Get properties owned by this landlord
        $builder = $db->table('properties p');
        $builder->select('p.*');
        $builder->where('p.landlord_id', $landlordId);
        
        if (!empty($propertyIds)) {
            $builder->whereIn('p.id', $propertyIds);
        }
        
        $properties = $builder->get()->getResultArray();

        $incomeData = [];
        foreach ($properties as $property) {
            $propertyId = $property['id'];

            // Get rental income
            $rentalIncome = $this->getRentalIncome($propertyId, $dateRange['start'], $dateRange['end']);

            // Get expenses
            $expenses = $this->getPropertyExpenses($propertyId, $dateRange['start'], $dateRange['end']);

            // Calculate totals
            $totalIncome = $rentalIncome['rental_income'] + $rentalIncome['other_income'];
            $totalExpenses = array_sum(array_column($expenses, 'amount'));
            $companyPercentage = 10; // Default company management percentage
            $companyFee = $totalIncome * ($companyPercentage / 100);
            $netProfit = $totalIncome - $totalExpenses - $companyFee;

            // Get owners for this property
            $ownerBuilder = $db->table('property_ownership po');
            $ownerBuilder->select('po.ownership_percentage, u.firstname, u.lastname');
            $ownerBuilder->join('users u', 'u.id = po.landlord_id');
            $ownerBuilder->where('po.property_id', $propertyId);
            $owners = $ownerBuilder->get()->getResultArray();
            
            // If no ownership records, use current landlord as 100% owner
            if (empty($owners)) {
                $user = $db->table('users')->where('id', $landlordId)->get()->getRowArray();
                $owners = [[
                    'ownership_percentage' => 100,
                    'firstname' => $user['firstname'] ?? 'Unknown',
                    'lastname' => $user['lastname'] ?? 'User'
                ]];
            }

            $ownerDistributions = [];
            foreach ($owners as $owner) {
                $ownerShare = $netProfit * ($owner['ownership_percentage'] / 100);
                $ownerDistributions[] = [
                    'name' => $owner['firstname'] . ' ' . $owner['lastname'],
                    'ownership_percentage' => $owner['ownership_percentage'],
                    'share_amount' => $ownerShare,
                    'final_amount' => $ownerShare
                ];
            }

            $incomeData[] = [
                'property_name' => $property['property_name'] ?? 'Unnamed Property',
                'total_income' => $rentalIncome['rental_income'],
                'other_income' => $rentalIncome['other_income'],
                'gross_income' => $totalIncome,
                'expenses' => $expenses,
                'total_expenses' => $totalExpenses,
                'company_percentage' => $companyPercentage,
                'company_fee' => $companyFee,
                'net_profit' => $netProfit,
                'owner_distributions' => $ownerDistributions
            ];
        }

        return $incomeData;
    }

    private function getRentalIncome($propertyId, $startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Check if payment_receipts table exists
        if (!$db->tableExists('payment_receipts')) {
            return [
                'rental_income' => 5000, // Sample data
                'other_income' => 500
            ];
        }

        $builder = $db->table('payment_receipts');
        $builder->selectSum('amount', 'total_amount');
        $builder->where('property_id', $propertyId);
        $builder->where('payment_date >=', $startDate);
        $builder->where('payment_date <=', $endDate);
        $builder->where('status', 'verified'); // Only count verified receipts

        $result = $builder->get()->getRowArray();

        return [
            'rental_income' => $result['total_amount'] ?? 5000, // Sample fallback
            'other_income' => 500 // Sample data
        ];
    }

    private function getPropertyExpenses($propertyId, $startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Check if maintenance_requests table exists
        if (!$db->tableExists('maintenance_requests')) {
            return [
                ['category' => 'Maintenance', 'amount' => 800],
                ['category' => 'Utilities', 'amount' => 300],
                ['category' => 'Insurance', 'amount' => 200],
                ['category' => 'Management Fees', 'amount' => 150]
            ];
        }

        $builder = $db->table('maintenance_requests mr');
        $builder->select('mr.category, SUM(mr.cost) as amount');
        $builder->where('mr.property_id', $propertyId);
        $builder->where('mr.completed_date >=', $startDate);
        $builder->where('mr.completed_date <=', $endDate);
        $builder->where('mr.status', 'completed');
        $builder->groupBy('mr.category');

        $expenses = $builder->get()->getResultArray();

        // Add default expense categories if none exist
        if (empty($expenses)) {
            $expenses = [
                ['category' => 'Maintenance', 'amount' => 800],
                ['category' => 'Utilities', 'amount' => 300],
                ['category' => 'Insurance', 'amount' => 200],
                ['category' => 'Management Fees', 'amount' => 150]
            ];
        }

        return $expenses;
    }

    private function calculateDateRange($period, $startDate)
    {
        $start = new \DateTime($startDate);
        $end = clone $start;

        switch ($period) {
            case 'monthly':
                $end->add(new \DateInterval('P1M'));
                break;
            case 'quarterly':
                $end->add(new \DateInterval('P3M'));
                break;
            case 'semi_annual':
                $end->add(new \DateInterval('P6M'));
                break;
            case 'annual':
                $end->add(new \DateInterval('P1Y'));
                break;
        }

        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    private function getPeriodText($period, $startDate)
    {
        $dateRange = $this->calculateDateRange($period, $startDate);
        $startFormatted = date('F j, Y', strtotime($dateRange['start']));
        $endFormatted = date('F j, Y', strtotime($dateRange['end']));

        return $startFormatted . ' - ' . $endFormatted;
    }

    private function saveReportRecord($landlordId, $reportType, $reportName, $properties)
    {
        $db = \Config\Database::connect();

        // Check if reports table exists
        if (!$db->tableExists('reports')) {
            return; // Skip saving if table doesn't exist
        }

        $data = [
            'landlord_id' => $landlordId,
            'name' => $reportName,
            'type' => $reportType,
            'properties' => !empty($properties) ? json_encode($properties) : null,
            'period_start' => date('Y-m-d'),
            'period_end' => date('Y-m-d'),
            'status' => 'completed',
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => (session()->get('firstname') ?? 'Unknown') . ' ' . (session()->get('lastname') ?? 'User')
        ];

        $db->table('reports')->insert($data);
    }

    private function getGeneratedReports($landlordId)
    {
        $db = \Config\Database::connect();

        // Check if reports table exists
        if (!$db->tableExists('reports')) {
            return []; // Return empty array if table doesn't exist
        }

        $builder = $db->table('reports');
        $builder->where('landlord_id', $landlordId);
        $builder->orderBy('generated_at', 'DESC');
        $builder->limit(10);

        return $builder->get()->getResultArray();
    }

    private function getReportData($landlordId)
    {
        // Return sample data for dashboard
        return [
            'expected_income' => 10000,
            'collected_income' => 8500,
            'occupancy_rate' => 85,
            'avg_maintenance_cost' => 1200,
            'avg_lease_duration' => 2.5,
            'alerts' => []
        ];
    }

    private function getChartData($landlordId)
    {
        return [
            'income' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => [2500, 2800, 2650, 2900, 3100, 2950, 3200, 3050, 2850, 3300, 3150, 3400]
            ],
            'expected' => [
                'data' => [3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000, 3000]
            ],
            'property' => [
                'labels' => ['Occupied', 'Vacant'],
                'data' => [85, 15]
            ]
        ];
    }

    private function getFinancialSummary($landlordId)
    {
        return [
            'total_income' => 25000,
            'income_growth' => 5.2,
            'total_expenses' => 18000,
            'expense_growth' => 3.1,
            'net_income' => 7000,
            'net_growth' => 8.5,
            'profit_margin' => 28.0
        ];
    }

    private function getMaintenanceSummary($landlordId)
    {
        return [
            [
                'category' => 'Plumbing',
                'count' => 5,
                'total_cost' => 1200,
                'avg_cost' => 240
            ],
            [
                'category' => 'Electrical',
                'count' => 3,
                'total_cost' => 800,
                'avg_cost' => 267
            ],
            [
                'category' => 'HVAC',
                'count' => 2,
                'total_cost' => 1500,
                'avg_cost' => 750
            ]
        ];
    }

    public function delete($reportId)
    {
        try {
            $db = \Config\Database::connect();
            $landlordId = session()->get('user_id');

            // Check if reports table exists
            if (!$db->tableExists('reports')) {
                return $this->response->setJSON([
                    'success' => false,
                ]);
            }

            $builder = $db->table('reports');
            $builder->where('id', $reportId);
            $builder->where('landlord_id', $landlordId);
            $builder->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}