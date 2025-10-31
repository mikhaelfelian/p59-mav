<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;
use CodeIgniter\Exceptions\ConfigException;
use RuntimeException;

class MigrationController extends Controller
{
    /**
     * Check if request is from localhost
     * 
     * @return bool
     */
    protected function isLocalhost(): bool
    {
        $ip = $this->request->getIPAddress();
        
        // Check for localhost IPs
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) 
            || $ip === '0.0.0.0'
            || strpos($ip, '127.') === 0
            || strpos($ip, '192.168.') === 0
            || strpos($ip, '10.') === 0;
    }

    /**
     * Output HTML response with styling
     * 
     * @param string $title
     * @param string $message
     * @param bool $success
     * @return string
     */
    protected function outputHTML(string $title, string $message, bool $success = true): string
    {
        $color = $success ? '#28a745' : '#dc3545';
        $icon = $success ? '✅' : '❌';
        
        // Split message and details if they exist
        $parts = explode("\n\nDetails:", $message);
        $mainMessage = $parts[0];
        $details = isset($parts[1]) ? "\n\nDetails:" . $parts[1] : '';
        
        $parts2 = explode("\n\nErrors:", $message);
        if (count($parts2) > 1) {
            $mainMessage = $parts2[0];
            $details = "\n\nErrors:" . $parts2[1];
        }
        
        $parts3 = explode("\n\nStack trace:", $message);
        if (count($parts3) > 1) {
            $mainMessage = $parts3[0];
            $details = "\n\nStack trace:" . $parts3[1];
        }
        
        $detailsHTML = '';
        if (!empty($details)) {
            $detailsHTML = '<div class="details">' . nl2br(htmlspecialchars(trim($details))) . '</div>';
        }
        
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            background-color: ' . ($success ? '#d4edda' : '#f8d7da') . ';
            border: 1px solid ' . ($success ? '#c3e6cb' : '#f5c6cb') . ';
            color: ' . ($success ? '#155724' : '#721c24') . ';
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            border-left: 4px solid ' . $color . ';
            font-family: "Courier New", monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
        }
        .back-link {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
            margin-right: 15px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>' . htmlspecialchars($title) . '</h1>
        <div class="message">
            <strong>' . $icon . ' ' . htmlspecialchars($mainMessage) . '</strong>
        </div>
        ' . $detailsHTML . '
        <div class="back-link">
            <a href="' . base_url() . '">← Back to Home</a>
            <a href="' . base_url('migrate/run') . '">Run Migrations</a>
            <a href="' . base_url('migrate/rollback') . '">Rollback Migrations</a>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Run all pending migrations
     * Equivalent to: php spark migrate
     * 
     * @return string
     */
    public function run(): string
    {
        // Optional: Restrict to localhost only (uncomment to enable)
        // if (!$this->isLocalhost()) {
        //     return $this->outputHTML(
        //         'Access Denied',
        //         'This action is only allowed from localhost. Your IP: ' . $this->request->getIPAddress(),
        //         false
        //     );
        // }

        try {
            $runner = Services::migrations();
            $runner->clearCliMessages();

            $result = $runner->latest();
            $messages = $runner->getCliMessages();

            if ($result) {
                $message = 'Migrations completed successfully!';
                if (!empty($messages)) {
                    $message .= "\n\nDetails:\n" . implode("\n", $messages);
                } else {
                    $message .= "\n\nNo new migrations to run.";
                }
                
                return $this->outputHTML('Migration Run', $message, true);
            } else {
                $errorMsg = 'Migration failed!';
                if (!empty($messages)) {
                    $errorMsg .= "\n\nErrors:\n" . implode("\n", $messages);
                }
                
                return $this->outputHTML('Migration Run', $errorMsg, false);
            }

        } catch (ConfigException $e) {
            return $this->outputHTML(
                'Migration Run - Configuration Error',
                'Migrations are disabled in configuration. Error: ' . $e->getMessage(),
                false
            );
        } catch (RuntimeException $e) {
            return $this->outputHTML(
                'Migration Run - Runtime Error',
                'Error running migrations: ' . $e->getMessage(),
                false
            );
        } catch (\Exception $e) {
            return $this->outputHTML(
                'Migration Run - Error',
                'Unexpected error: ' . $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString(),
                false
            );
        }
    }

    /**
     * Rollback all migrations (last batch)
     * Equivalent to: php spark migrate:rollback
     * 
     * @return string
     */
    public function rollback(): string
    {
        // Optional: Restrict to localhost only (uncomment to enable)
        // if (!$this->isLocalhost()) {
        //     return $this->outputHTML(
        //         'Access Denied',
        //         'This action is only allowed from localhost. Your IP: ' . $this->request->getIPAddress(),
        //         false
        //     );
        // }

        try {
            $runner = Services::migrations();
            $runner->clearCliMessages();

            // Get the last batch number and rollback to previous batch (-1)
            $lastBatch = $runner->getLastBatch();
            
            if ($lastBatch <= 0) {
                return $this->outputHTML(
                    'Migration Rollback',
                    'No migrations to rollback. Database is at batch 0.',
                    true
                );
            }

            $targetBatch = $lastBatch - 1;
            $result = $runner->regress($targetBatch);
            $messages = $runner->getCliMessages();

            if ($result !== false) {
                $message = "Migrations rolled back successfully from batch {$lastBatch} to batch {$targetBatch}!";
                if (!empty($messages)) {
                    $message .= "\n\nDetails:\n" . implode("\n", $messages);
                }
                
                return $this->outputHTML('Migration Rollback', $message, true);
            } else {
                $errorMsg = 'Rollback failed!';
                if (!empty($messages)) {
                    $errorMsg .= "\n\nErrors:\n" . implode("\n", $messages);
                }
                
                return $this->outputHTML('Migration Rollback', $errorMsg, false);
            }

        } catch (ConfigException $e) {
            return $this->outputHTML(
                'Migration Rollback - Configuration Error',
                'Migrations are disabled in configuration. Error: ' . $e->getMessage(),
                false
            );
        } catch (RuntimeException $e) {
            return $this->outputHTML(
                'Migration Rollback - Runtime Error',
                'Error rolling back migrations: ' . $e->getMessage(),
                false
            );
        } catch (\Exception $e) {
            return $this->outputHTML(
                'Migration Rollback - Error',
                'Unexpected error: ' . $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString(),
                false
            );
        }
    }
}

