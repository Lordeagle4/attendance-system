<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/utils/functions.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Generate filename with current date and time
$filename = 'attendance_report_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

$output = fopen('php://output', 'w');

// BOM for proper UTF-8 encoding in Excel
fputs($output, "\xEF\xBB\xBF");

// Write CSV headers with the escape parameter
fputcsv($output, [
    'Student Name', 
    'Roll Number', 
    'Class', 
    'Days Present', 
    'Days Absent', 
    'Total Days', 
    'Attendance Percentage'
], ',', '"', '\\');

// Query with error handling
$query = "
    SELECT 
        s.student_name, 
        s.roll_no, 
        s.class,
        COALESCE(SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END), 0) AS presents,
        COALESCE(SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END), 0) AS absents,
        COALESCE(COUNT(a.id), 0) AS total_classes
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id
    GROUP BY s.id, s.student_name, s.roll_no, s.class
    ORDER BY s.class ASC, s.roll_no ASC
";

$result = $mysqli->query($query);

if (!$result) {
    // Handle query error gracefully
    fputcsv($output, ['Error: Unable to fetch attendance data'], ',', '"', '\\');
    fclose($output);
    exit;
}

// Check if we have any data
if ($result->num_rows === 0) {
    fputcsv($output, ['No student attendance data found'], ',', '"', '\\');
    fclose($output);
    exit;
}

// Process and write student data
while ($row = $result->fetch_assoc()) {
    // Calculate attendance percentage
    $percentage = ($row['total_classes'] > 0) ? 
        round(($row['presents'] / $row['total_classes']) * 100, 2) : 0;
    
    // Clean and sanitize data
    $clean_data = [
        trim(htmlSafeOutput($row['student_name'])),
        trim(htmlSafeOutput($row['roll_no'])),
        trim(htmlSafeOutput($row['class'])),
        (int)$row['presents'],
        (int)$row['absents'],
        (int)$row['total_classes'],
        $percentage . '%'
    ];
    
    // Write row with escape parameter
    fputcsv($output, $clean_data, ',', '"', '\\');
}

// Summary statistics
fputcsv($output, [], ',', '"', '\\'); // Empty row for separation and better presentation
fputcsv($output, ['=== SUMMARY STATISTICS ==='], ',', '"', '\\');

// Calculate overall statistics
$result->data_seek(0); // Reset result pointer
$total_students = $result->num_rows;
$total_present = 0;
$total_absent = 0;
$total_classes = 0;

while ($row = $result->fetch_assoc()) {
    $total_present += $row['presents'];
    $total_absent += $row['absents'];
    $total_classes += $row['total_classes'];
}

$overall_percentage = ($total_classes > 0) ? 
    round(($total_present / $total_classes) * 100, 2) : 0;

fputcsv($output, ['Total Students', $total_students], ',', '"', '\\');
fputcsv($output, ['Total Present Days', $total_present], ',', '"', '\\');
fputcsv($output, ['Total Absent Days', $total_absent], ',', '"', '\\');
fputcsv($output, ['Overall Attendance Rate', $overall_percentage . '%'], ',', '"', '\\');
fputcsv($output, ['Report Generated', date('Y-m-d H:i:s')], ',', '"', '\\');

fclose($output);
exit;
?>