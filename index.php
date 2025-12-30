<?php
require_once 'config.php';

// Simple session to store user's plans
if (!isset($_SESSION['plans'])) {
    $_SESSION['plans'] = [];
}

// Check if user has authorized Google Calendar
$hasGoogleAuth = isset($_SESSION['google_access_token']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thought Execution System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-brain"></i> Thought Execution System</h1>
            <p class="tagline">Dump thoughts → Get organized → Execute plans</p>
            
            <div class="auth-status">
                <?php if ($hasGoogleAuth): ?>
                    <div class="connected-badge">
                        <i class="fab fa-google"></i> Google Calendar Connected
                    </div>
                <?php else: ?>
                    <a href="google-auth.php" class="google-connect-btn">
                        <i class="fab fa-google"></i> Connect Google Calendar
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <main>
            <section class="input-section">
                <h2>What's on your mind?</h2>
                <form id="thoughtForm">
                    <div class="input-group">
                        <textarea 
                            id="thoughtInput" 
                            placeholder="Example: I want to relocate to Canada in 2026..." 
                            rows="4"
                            required
                        ></textarea>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-magic"></i> Organize My Thought
                        </button>
                    </div>
                </form>
            </section>

            <section id="breakdownSection" class="breakdown-section hidden">
                <h2>Your Action Plan</h2>
                <div id="planBreakdown"></div>
                
                <div class="reminder-setup">
                    <h3>Set Up Reminders & Calendar Events</h3>
                    <form id="reminderForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="startDate">Start Date:</label>
                                <input type="text" id="startDate" class="date-picker" placeholder="Select start date" required>
                            </div>
                            <div class="form-group">
                                <label for="endDate">Target Completion Date:</label>
                                <input type="text" id="endDate" class="date-picker" placeholder="Select target date">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="frequency">Reminder Frequency:</label>
                                <select id="frequency">
                                    <option value="daily">Daily</option>
                                    <option value="weekly" selected>Weekly</option>
                                    <option value="biweekly">Every 2 Weeks</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reminderTime">Reminder Time:</label>
                                <select id="reminderTime">
                                    <option value="09:00">9:00 AM</option>
                                    <option value="12:00" selected>12:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Your Email:</label>
                                <input type="email" id="email" placeholder="your.email@example.com" required>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Timezone:</label>
                                <select id="timezone">
                                    <option value="America/New_York">Eastern Time (ET)</option>
                                    <option value="America/Chicago">Central Time (CT)</option>
                                    <option value="America/Denver">Mountain Time (MT)</option>
                                    <option value="America/Los_Angeles" selected>Pacific Time (PT)</option>
                                    <option value="UTC">UTC/GMT</option>
                                    <option value="Europe/London">London</option>
                                    <option value="Europe/Paris">Paris</option>
                                    <option value="Asia/Tokyo">Tokyo</option>
                                    <option value="Australia/Sydney">Sydney</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="calendar-options">
                            <h4>Calendar Integration:</h4>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" id="addToCalendar" <?php echo $hasGoogleAuth ? '' : 'disabled'; ?>>
                                    Add milestones to Google Calendar
                                    <?php if (!$hasGoogleAuth): ?>
                                        <span class="hint">(Connect Google Calendar first)</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" id="sendEmailReminders" checked>
                                    Send email reminders
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-success">
                            <i class="fas fa-calendar-plus"></i> Create Plan with Reminders
                        </button>
                    </form>
                </div>
            </section>

            <section class="plans-section">
                <h2>Your Active Plans</h2>
                <div id="plansList">
                    <?php if (empty($_SESSION['plans'])): ?>
                        <p class="empty-state">No active plans yet. Add your first thought above!</p>
                    <?php else: ?>
                        <?php foreach ($_SESSION['plans'] as $index => $plan): ?>
                            <div class="plan-card">
                                <div class="plan-header">
                                    <h3><?php echo htmlspecialchars($plan['goal']); ?></h3>
                                    <div class="plan-actions">
                                        <button class="btn-small btn-send-test" onclick="sendTestReminder(<?php echo $index; ?>)">
                                            <i class="fas fa-paper-plane"></i> Test Reminder
                                        </button>
                                        <button class="btn-small" onclick="removePlan(<?php echo $index; ?>)">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                
                                <p class="plan-date">
                                    <i class="far fa-calendar"></i> Created: <?php echo $plan['created']; ?> | 
                                    <i class="far fa-clock"></i> Next reminder: <?php echo $plan['next_reminder']; ?>
                                </p>
                                
                                <?php if (isset($plan['calendar_events']) && !empty($plan['calendar_events'])): ?>
                                    <div class="calendar-status">
                                        <i class="fab fa-google"></i> 
                                        <?php echo count($plan['calendar_events']); ?> events added to Google Calendar
                                    </div>
                                <?php endif; ?>
                                
                                <div class="tasks">
                                    <h4>Tasks:</h4>
                                    <ul>
                                        <?php foreach ($plan['tasks'] as $taskIndex => $task): ?>
                                            <li>
                                                <?php echo htmlspecialchars($task); ?>
                                                <?php if (isset($plan['task_dates'][$taskIndex])): ?>
                                                    <span class="task-date">(<?php echo $plan['task_dates'][$taskIndex]; ?>)</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <div class="plan-footer">
                                    <span class="badge">
                                        <i class="fas fa-envelope"></i> <?php echo $plan['email']; ?>
                                    </span>
                                    <span class="badge">
                                        <i class="fas fa-sync-alt"></i> <?php echo $plan['frequency']; ?> reminders
                                    </span>
                                    <span class="badge">
                                        <i class="fas fa-clock"></i> <?php echo $plan['reminder_time']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <footer>
            <p>Thought Execution System | Email reminders & Google Calendar integration</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date pickers
        const today = new Date();
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        
        flatpickr("#startDate", {
            minDate: "today",
            dateFormat: "Y-m-d",
            defaultDate: today
        });
        
        flatpickr("#endDate", {
            minDate: "today",
            dateFormat: "Y-m-d",
            defaultDate: nextMonth
        });

        // Handle thought submission
        document.getElementById('thoughtForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const thought = document.getElementById('thoughtInput').value.trim();
            
            if (!thought) {
                alert('Please enter your thought or goal');
                return;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            try {
                // Send to backend for processing
                const response = await fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=breakdown&thought=' + encodeURIComponent(thought)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayBreakdown(thought, result.tasks);
                } else {
                    throw new Error(result.message || 'Processing failed');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Handle reminder setup
        document.getElementById('reminderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const thought = document.getElementById('thoughtInput').value.trim();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const frequency = document.getElementById('frequency').value;
            const reminderTime = document.getElementById('reminderTime').value;
            const email = document.getElementById('email').value;
            const timezone = document.getElementById('timezone').value;
            const addToCalendar = document.getElementById('addToCalendar').checked;
            const sendEmailReminders = document.getElementById('sendEmailReminders').checked;
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            if (!startDate) {
                alert('Please select a start date');
                return;
            }
            
            // Get tasks from the displayed breakdown
            const taskElements = document.querySelectorAll('#planBreakdown .task-item');
            const tasks = Array.from(taskElements).map(el => {
                const text = el.textContent;
                return text.replace('✓ ', '').replace('⏰ ', '');
            });
            
            // Show loading
            const submitBtn = this.querySelector('button');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Setting up your plan...';
            submitBtn.disabled = true;
            
            try {
                // Send to backend to save plan
                const response = await fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'save',
                        goal: thought,
                        tasks: JSON.stringify(tasks),
                        start_date: startDate,
                        end_date: endDate,
                        frequency: frequency,
                        reminder_time: reminderTime,
                        email: email,
                        timezone: timezone,
                        add_to_calendar: addToCalendar ? '1' : '0',
                        send_email_reminders: sendEmailReminders ? '1' : '0'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Reset form
                    document.getElementById('thoughtForm').reset();
                    document.getElementById('reminderForm').reset();
                    document.getElementById('breakdownSection').classList.add('hidden');
                    
                    // Show success message
                    let successMsg = 'Plan created successfully!';
                    if (result.calendar_events) {
                        successMsg += ` ${result.calendar_events} calendar events created.`;
                    }
                    if (sendEmailReminders) {
                        successMsg += ' Email reminders will be sent according to your schedule.';
                    }
                    
                    alert(successMsg);
                    
                    // Reload page to show new plan
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Failed to save plan');
                }
            } catch (error) {
                alert('Error: ' + error.message);
                console.error(error);
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Display the task breakdown with suggested dates
        function displayBreakdown(thought, tasks) {
            const breakdownSection = document.getElementById('breakdownSection');
            const planBreakdown = document.getElementById('planBreakdown');
            
            // Calculate date suggestions for each task
            const today = new Date();
            const taskDates = [];
            
            tasks.forEach((task, index) => {
                const taskDate = new Date(today);
                taskDate.setDate(today.getDate() + (index + 1) * 7); // 1 week apart
                taskDates.push(taskDate.toISOString().split('T')[0]);
            });
            
            // Create breakdown HTML
            let html = `<div class="goal-header">
                            <h3>Goal: ${thought}</h3>
                            <p class="goal-subtitle">AI-generated breakdown with suggested timeline:</p>
                        </div>
                        <div class="task-list">`;
            
            tasks.forEach((task, index) => {
                const taskDate = taskDates[index];
                html += `<div class="task-item">
                            <i class="far fa-calendar-check"></i> 
                            <div class="task-content">
                                <div class="task-text">${task}</div>
                                <div class="task-meta">
                                    <i class="far fa-clock"></i> Suggested date: ${taskDate}
                                </div>
                            </div>
                         </div>`;
            });
            
            html += '</div>';
            planBreakdown.innerHTML = html;
            
            // Pre-fill end date (last task date + 1 week)
            const lastTaskDate = new Date(taskDates[taskDates.length - 1]);
            lastTaskDate.setDate(lastTaskDate.getDate() + 7);
            document.getElementById('endDate').value = lastTaskDate.toISOString().split('T')[0];
            
            // Show the breakdown section
            breakdownSection.classList.remove('hidden');
            
            // Scroll to the breakdown section
            breakdownSection.scrollIntoView({ behavior: 'smooth' });
        }

        // Remove a plan
        function removePlan(index) {
            if (confirm('Are you sure you want to remove this plan and all associated reminders?')) {
                fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=remove&index=' + index
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.reload();
                    }
                });
            }
        }

        // Send a test reminder
        function sendTestReminder(planIndex) {
            if (confirm('Send a test reminder email now?')) {
                fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=test_reminder&index=' + planIndex
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Test reminder sent! Check your email.');
                    } else {
                        alert('Failed to send test reminder: ' + result.message);
                    }
                });
            }
        }
    </script>
</body>
</html>