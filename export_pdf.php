<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/utils/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Include TCPDF library (you'll need to install this via Composer or download manually)
// Download from: https://tcpdf.org/
require_once(__DIR__ . '/vendor/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Attendance Management System');
$pdf->SetAuthor('School Administration');
$pdf->SetTitle('Student Attendance Report');
$pdf->SetSubject('Attendance Analytics');
$pdf->SetKeywords('Attendance, Students, Report, Analytics');

// Set default header data
$pdf->SetHeaderData('', 0, 'STUDENT ATTENDANCE REPORT', 'Generated on ' . date('F j, Y \a\t g:i A'));

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font for content
$pdf->SetFont('helvetica', '', 10);

// Get attendance data with enhanced query
$query = "
    SELECT 
        s.student_name, 
        s.roll_no, 
        s.class,
        COALESCE(SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END), 0) AS presents,
        COALESCE(SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END), 0) AS absents,
        COALESCE(COUNT(a.id), 0) AS total_classes,
        MIN(a.date) as first_attendance,
        MAX(a.date) as last_attendance
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id
    GROUP BY s.id, s.student_name, s.roll_no, s.class
    ORDER BY s.class ASC, s.roll_no ASC
";

$result = $mysqli->query($query);

if (!$result) {
    $pdf->Cell(0, 10, 'Error: Unable to fetch attendance data', 0, 1, 'C');
    $pdf->Output('attendance_report_error.pdf', 'D');
    exit;
}

// Report summary section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'ATTENDANCE SUMMARY', 0, 1, 'C');
$pdf->Ln(5);

// Calculate overall statistics
$total_students = $result->num_rows;
$total_present = 0;
$total_absent = 0;
$total_classes = 0;
$class_stats = [];

// First pass: collect statistics
while ($row = $result->fetch_assoc()) {
    $total_present += $row['presents'];
    $total_absent += $row['absents'];
    $total_classes += $row['total_classes'];
    
    // Group by class for class-wise statistics
    $class = $row['class'];
    if (!isset($class_stats[$class])) {
        $class_stats[$class] = [
            'students' => 0,
            'present' => 0,
            'absent' => 0,
            'total' => 0
        ];
    }
    $class_stats[$class]['students']++;
    $class_stats[$class]['present'] += $row['presents'];
    $class_stats[$class]['absent'] += $row['absents'];
    $class_stats[$class]['total'] += $row['total_classes'];
}

$overall_percentage = ($total_classes > 0) ? round(($total_present / $total_classes) * 100, 2) : 0;

// Display summary statistics
$pdf->SetFont('helvetica', '', 10);
$summary_data = [
    ['Metric', 'Value'],
    ['Total Students', $total_students],
    ['Total Present Days', number_format($total_present)],
    ['Total Absent Days', number_format($total_absent)],
    ['Overall Attendance Rate', $overall_percentage . '%'],
    ['Report Generated', date('F j, Y \a\t g:i A')]
];

// Create summary table
$pdf->SetFillColor(230, 230, 230);
foreach ($summary_data as $index => $row_data) {
    $fill = ($index == 0) ? true : false;
    $font_style = ($index == 0) ? 'B' : '';
    $pdf->SetFont('helvetica', $font_style, 10);
    
    $pdf->Cell(60, 8, $row_data[0], 1, 0, 'L', $fill);
    $pdf->Cell(60, 8, $row_data[1], 1, 1, 'C', $fill);
}

$pdf->Ln(10);

// Class-wise summary
if (!empty($class_stats)) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'CLASS-WISE ATTENDANCE SUMMARY', 0, 1, 'C');
    $pdf->Ln(3);
    
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(30, 8, 'Class', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Students', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Present', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Absent', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Total Days', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Avg. Rate', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 9);
    foreach ($class_stats as $class => $stats) {
        $class_percentage = ($stats['total'] > 0) ? 
            round(($stats['present'] / $stats['total']) * 100, 2) : 0;
        
        // Color code based on attendance rate
        if ($class_percentage >= 90) {
            $pdf->SetFillColor(200, 255, 200); // Light green
        } elseif ($class_percentage >= 75) {
            $pdf->SetFillColor(255, 255, 200); // Light yellow
        } else {
            $pdf->SetFillColor(255, 200, 200); // Light red
        }
        
        $pdf->Cell(30, 7, htmlSafeOutput($class), 1, 0, 'C', true);
        $pdf->Cell(25, 7, $stats['students'], 1, 0, 'C', true);
        $pdf->Cell(25, 7, number_format($stats['present']), 1, 0, 'C', true);
        $pdf->Cell(25, 7, number_format($stats['absent']), 1, 0, 'C', true);
        $pdf->Cell(25, 7, number_format($stats['total']), 1, 0, 'C', true);
        $pdf->Cell(30, 7, $class_percentage . '%', 1, 1, 'C', true);
    }
}

$pdf->Ln(10);

// Individual student attendance details
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'INDIVIDUAL STUDENT ATTENDANCE', 0, 1, 'C');
$pdf->Ln(3);

// Table headers for individual students
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(100, 100, 100);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(45, 8, 'Student Name', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Roll No', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Class', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Present', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Absent', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Total', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Attendance %', 1, 1, 'C', true);

// Reset text color
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 8);

// Reset result pointer and display individual student data
$result->data_seek(0);
$row_count = 0;

while ($row = $result->fetch_assoc()) {
    $percentage = ($row['total_classes'] > 0) ? 
        round(($row['presents'] / $row['total_classes']) * 100, 2) : 0;
    
    // Alternate row colors for better readability
    if ($row_count % 2 == 0) {
        $pdf->SetFillColor(245, 245, 245);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    
    // Color code attendance percentage
    if ($percentage >= 90) {
        $pdf->SetTextColor(0, 120, 0); // Dark green
    } elseif ($percentage >= 75) {
        $pdf->SetTextColor(150, 100, 0); // Orange
    } else {
        $pdf->SetTextColor(180, 0, 0); // Dark red
    }
    
    // Truncate long names to fit
    $name = strlen($row['student_name']) > 25 ? 
        substr(htmlSafeOutput($row['student_name']), 0, 22) . '...' : 
        htmlSafeOutput($row['student_name']);
    
    $pdf->Cell(45, 7, $name, 1, 0, 'L', true);
    $pdf->SetTextColor(0, 0, 0); // Reset color for other cells
    $pdf->Cell(20, 7, htmlSafeOutput($row['roll_no']), 1, 0, 'C', true);
    $pdf->Cell(20, 7, htmlSafeOutput($row['class']), 1, 0, 'C', true);
    $pdf->Cell(20, 7, $row['presents'], 1, 0, 'C', true);
    $pdf->Cell(20, 7, $row['absents'], 1, 0, 'C', true);
    $pdf->Cell(20, 7, $row['total_classes'], 1, 0, 'C', true);
    
    // Color the percentage cell
    if ($percentage >= 90) {
        $pdf->SetTextColor(0, 120, 0);
    } elseif ($percentage >= 75) {
        $pdf->SetTextColor(150, 100, 0);
    } else {
        $pdf->SetTextColor(180, 0, 0);
    }
    $pdf->Cell(25, 7, $percentage . '%', 1, 1, 'C', true);
    $pdf->SetTextColor(0, 0, 0); // Reset color
    
    $row_count++;
}

// Add footer information
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'This report contains attendance data for all registered students.', 0, 1, 'C');
$pdf->Cell(0, 5, 'Color coding: Green (≥90%), Orange (75-89%), Red (<75%)', 0, 1, 'C');

// Generate filename and output
$filename = 'attendance_report_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output($filename, 'D');
exit;
?>