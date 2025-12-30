<?php
require_once 'config.php';

// Function to send email using PHPMailer
function sendEmail($to, $subject, $body, $fromName = 'Thought Execution System') {
    // In production, use PHPMailer or similar library
    // For this example, we'll simulate email sending
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Simulate email sending (in production, use mail() or SMTP)
    $logMessage = date('Y-m-d H:i:s') . " - Email to: $to | Subject: $subject\n";
    file_put_contents('email_log.txt', $logMessage, FILE_APPEND);
    
    // For demo, we'll simulate success
    return true;
    
    // Actual email sending (uncomment when configured):
    // return mail($to, $subject, $body, $headers);
}

// Simulated AI breakdown function
function breakdownThought($thought) {
    // In a real app, you'd call an AI API here
    $thought = strtolower($thought);
    $tasks = [];
    
    if (strpos($thought, 'relocate') !== false || strpos($thought, 'move') !== false) {
        $tasks = [
            "Research destination country requirements",
            "Check visa/immigration eligibility",
            "Calculate estimated costs and budget",
            "Research job market opportunities",
            "Gather required documents",
            "Create timeline with milestones",
            "Plan initial accommodation",
            "Learn about local culture"
        ];
    } elseif (strpos($thought, 'learn') !== false) {
        $tasks = [
            "Research courses/programs",
            "Check prerequisites",
            "Create study schedule",
            "Gather learning materials",
            "Set up study environment",
            "Find study groups",
            "Set progress tracking"
        ];
    } elseif (strpos($thought, 'business') !== false) {
        $tasks = [
            "Conduct market research",
            "Define business model",
            "Create business plan",
            "Research legal requirements",
            "Calculate startup costs",
            "Develop prototype",
            "Create marketing strategy"
        ];
    } else {
        $tasks = [
            "Research the topic thoroughly",
            "Break into actionable steps",
            "Create timeline with deadlines",
            "Identify required resources",
            "Set up progress tracking",
            "Find support communities",
            "Schedule regular reviews"
        ];
    }
    
    return $tasks;
}

// Calculate task dates based on frequency
function calculateTaskDates($tasks, $startDate, $frequency) {
    $taskDates = [];
    $currentDate = new DateTime($startDate);
    
    foreach ($tasks as $index => $task) {
        $taskDates[$index] = $currentDate->format('Y-m-d');
        
        // Increment based on frequency
        switch ($frequency) {
            case 'daily':
                $currentDate->modify('+1 day');
                break;
            case 'weekly':
                $currentDate->modify('+1 week');
                break;
            case 'biweekly':
                $currentDate->modify('+2 weeks');
                break;
            case 'monthly':
                $currentDate->modify('+1 month');
                break;
            default:
                $currentDate->modify('+1 week');
        }
    }
    
    return $taskDates;
}

// Handle different actions
$action = $_POST['action'] ?? '';

if ($action === 'breakdown') {
    $thought = $_POST['thought'] ?? '';
    
    if (empty($thought)) {
        echo json_encode(['success' => false, 'message' => 'No thought provided']);
        exit;
    }
    
    $tasks = breakdownThought($thought);
    
    echo json_encode([
        'success' => true,
        'thought' => $thought,
        'tasks' => $tasks
    ]);
    
} elseif ($action === 'save') {
    $goal = $_POST['goal'] ?? '';
    $tasks = json_decode($_POST['tasks'] ?? '[]', true);
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $endDate = $_POST['end_date'] ?? '';
    $frequency = $_POST['frequency'] ?? 'weekly';
    $reminderTime = $_POST['reminder_time'] ?? '12:00';
    $email = $_POST['email'] ?? '';
    $timezone = $_POST['timezone'] ?? 'America/Los_Angeles';
    $addToCalendar = $_POST['add_to_calendar'] ?? '0';
    $sendEmailReminders = $_POST['send_email_reminders'] ?? '0';
    
    if (empty($goal) || empty($tasks) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }
    
    // Calculate task dates
    $taskDates = calculateTaskDates($tasks, $startDate, $frequency);
    
    // Calculate next reminder date
    $nextReminder = date('Y-m-d H:i', strtotime("$startDate $reminderTime"));
    
    // Create the plan
    $plan = [
        'goal' => $goal,
        'tasks' => $tasks,
        'task_dates' => $taskDates,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'frequency' => $frequency,
        'reminder_time' => $reminderTime,
        'email' => $email,
        'timezone' => $timezone,
        'add_to_calendar' => $addToCalendar,
        'send_email_reminders' => $sendEmailReminders,
        'created' => date('Y-m-d H:i:s'),
        'next_reminder' => $nextReminder,
        'calendar_events' => []
    ];
    
    // Add to Google Calendar if requested and authorized
    $calendarEventsCount = 0;
    if ($addToCalendar == '1' && isset($_SESSION['google_access_token'])) {
        // In a real implementation, you'd call Google Calendar API here
        // For this demo, we'll simulate event creation
        
        foreach ($tasks as $index => $task) {
            $eventDate = $taskDates[$index];
            $plan['calendar_events'][] = [
                'task' => $task,
                'date' => $eventDate,
                'google_event_id' => 'simulated_' . uniqid()
            ];
            $calendarEventsCount++;
        }
    }
    
    // Send welcome email
    if ($sendEmailReminders == '1') {
        $emailSubject = "Your Thought Execution Plan: " . substr($goal, 0, 50) . "...";
        $emailBody = "<h2>Your Thought Execution Plan</h2>";
        $emailBody .= "<p><strong>Goal:</strong> $goal</p>";
        $emailBody .= "<p><strong>Start Date:</strong> $startDate</p>";
        $emailBody .= "<p><strong>Reminder Frequency:</strong> $frequency at $reminderTime</p>";
        $emailBody .= "<h3>Your Tasks:</h3><ul>";
        
        foreach ($tasks as $index => $task) {
            $dueDate = $taskDates[$index] ?? 'Not set';
            $emailBody .= "<li>$task (Suggested date: $dueDate)</li>";
        }
        
        $emailBody .= "</ul>";
        $emailBody .= "<p>You'll receive reminders according to your schedule.</p>";
        $emailBody .= "<p>Thank you for using Thought Execution System!</p>";
        
        sendEmail($email, $emailSubject, $emailBody);
    }
    
    // Save plan to session
    $_SESSION['plans'][] = $plan;
    
    echo json_encode([
        'success' => true,
        'message' => 'Plan saved successfully',
        'calendar_events' => $calendarEventsCount,
        'plan_count' => count($_SESSION['plans'])
    ]);
    
} elseif ($action === 'remove') {
    $index = $_POST['index'] ?? null;
    
    if ($index !== null && isset($_SESSION['plans'][$index])) {
        // In a real app, you'd also remove calendar events
        array_splice($_SESSION['plans'], $index, 1);
        echo json_encode(['success' => true, 'message' => 'Plan removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
    }
    
} elseif ($action === 'test_reminder') {
    $index = $_POST['index'] ?? null;
    
    if ($index !== null && isset($_SESSION['plans'][$index])) {
        $plan = $_SESSION['plans'][$index];
        $email = $plan['email'];
        $goal = $plan['goal'];
        
        $subject = "Test Reminder: " . substr($goal, 0, 50) . "...";
        $body = "<h2>Test Reminder</h2>";
        $body .= "<p>This is a test reminder for your goal:</p>";
        $body .= "<p><strong>$goal</strong></p>";
        $body .= "<p>Your reminder system is working correctly!</p>";
        $body .= "<p>Next scheduled reminder: " . $plan['next_reminder'] . "</p>";
        
        if (sendEmail($email, $subject, $body)) {
            echo json_encode(['success' => true, 'message' => 'Test reminder sent']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}