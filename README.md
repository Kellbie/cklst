# Thought Execution System - MVP

A simple web application that helps users organize their thoughts into actionable plans with reminders.

## Features

- **No-login system**: Uses PHP sessions for simplicity
- **AI-powered breakdown**: Simulates AI task breakdown for common goals
- **Reminder scheduling**: Set up email/WhatsApp reminders (simulated)
- **Responsive design**: Works on mobile and desktop

## Installation

1. Upload all files to a PHP-enabled web server
2. Ensure PHP sessions are enabled
3. Open `index.php` in your browser

## How It Works

1. User enters a thought/goal (e.g., "I want to relocate to Canada in 2026")
2. System breaks it down into actionable tasks
3. User sets up reminder schedule and contact method
4. System stores the plan and would send reminders (simulated in this MVP)

## File Structure

- `index.php` - Main application interface
- `process.php` - Backend processing and "AI" breakdown
- `reminder.php` - Simulation of reminder system
- `style.css` - All styling for the application

## Future Enhancements

1. Integrate with real AI APIs (OpenAI, etc.)
2. Implement actual email/WhatsApp reminders
3. Add user accounts for persistence
4. Calendar integration
5. Progress tracking features

## Technologies Used

- PHP 7+ for backend logic
- JavaScript for interactive features
- CSS3 for responsive design
- Flatpickr for date picking
- Font Awesome for icons

# Thought Execution System with Email & Google Calendar Integration

A complete productivity system that breaks down thoughts into actionable plans with email reminders and Google Calendar integration.

## Features

- **No-login system** using PHP sessions
- **AI-powered task breakdown** (simulated)
- **Real email reminders** sent on schedule
- **Google Calendar integration** for milestone tracking
- **Responsive web interface**

## Prerequisites

1. PHP 7.4+ with cURL extension
2. Web server (Apache, Nginx, etc.)
3. Gmail account for sending emails
4. Google Cloud Project for Calendar API

## Setup Instructions

### 1. Email Configuration

1. For Gmail, enable 2-factor authentication
2. Generate an app-specific password:
   - Go to Google Account → Security
   - Enable 2-Step Verification
   - Generate app password for "Mail"
3. Update `config.php` with your email credentials

### 2. Google Calendar API Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable Google Calendar API
4. Create OAuth 2.0 credentials:
   - Application type: Web application
   - Add authorized redirect URI: `http://your-domain.com/google-callback.php`
5. Download credentials and update `config.php`

### 3. Installation Steps

1. Upload all files to your web server
2. Update `config.php` with your settings:
   - `SITE_URL` - Your website URL
   - Email settings (SMTP credentials)
   - Google API credentials
3. Ensure PHP sessions are enabled
4. Set file permissions:
   ```bash
   chmod 644 *.php *.css
   chmod 666 email_log.txt reminder_log.txt (if using log files)