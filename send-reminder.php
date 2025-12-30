<?php
require_once 'config.php';

// This would be set up as a cron job to run daily
// Example cron: 0 9 * * * /usr/bin/php /path/to/send-reminder.php

session_start();

// In production, you'd load plans from a database
$plans = $_SESSION['plans'] ?? [];

echo "Reminder System - " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n";

$today = date('Y-m-d');
$now = date('H:i');

foreach ($plans as $index => $plan) {
    // Check if it's time to send a reminder
    $nextReminder = $plan['next_reminder'] ?? '';
    
    if (!empty($nextReminder)) {
        $reminderDate = date('Y-m-d', strtotime($nextReminder));
        $reminderTime = date('H:i', strtotime($nextReminder));
        
        if ($reminderDate === $today && $reminderTime === $now) {
            // Send reminder
            $email = $plan['email'];
            $goal = $plan['goal'];
            
            $subject = "Reminder: " . substr($goal, 0, 50) . "...";
            $body = "<h2>Reminder for Your Goal</h2>";
            $body .= "<p><strong>Goal:</strong> $goal</p>";
            $body .= "<h3>Today's Focus:</h3>";
            
            // Find task for today
            $todayTask = "Review your progress and work on the next step";
            foreach ($plan['task_dates'] as $taskIndex => $taskDate) {
                if ($taskDate === $today) {
                    $todayTask = $plan['tasks'][$taskIndex];
                    break;
                }
            }
            
            $body .= "<p>$todayTask</p>";
            $body .= "<p>Keep up the good work! You're making progress toward your goal.</p>";
            
            // Send email
            if (sendEmail($email, $subject, $body)) {
                echo "✓ Sent reminder to: $email for goal: $goal\n";
                
                // Update next reminder date
                switch ($plan['frequency']) {
                    case 'daily':
                        $nextDate = date('Y-m-d H:i', strtotime('+1 day', strtotime($nextReminder)));
                        break;
                    case 'weekly':
                        $nextDate = date('Y-m-d H:i', strtotime('+1 week', strtotime($nextReminder)));
                        break;
                    case 'biweekly':
                        $nextDate = date('Y-m-d H:i', strtotime('+2 weeks', strtotime($nextReminder)));
                        break;
                    case 'monthly':
                        $nextDate = date('Y-m-d H:i', strtotime('+1 month', strtotime($nextReminder)));
                        break;
                    default:
                        $nextDate = date('Y-m-d H:i', strtotime('+1 week', strtotime($nextReminder)));
                }
                
                $_SESSION['plans'][$index]['next_reminder'] = $nextDate;
            } else {
                echo "✗ Failed to send reminder to: $email\n";
            }
        }
    }
}

echo "\nReminder check completed.\n";

// Email sending function (same as in process.php)
function sendEmail($to, $subject, $body) {
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Log for demo
    $logMessage = date('Y-m-d H:i:s') . " - CRON: Email to: $to | Subject: $subject\n";
    file_put_contents('reminder_log.txt', $logMessage, FILE_APPEND);
    
    return true; // Simulate success
}
?>