<?php
#!/usr/bin/env php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/job_queue.php';

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

$queue = new JobQueue();

$maxJobs = isset($argv[1]) ? (int)$argv[1] : 10;

echo "Job Worker Started - Processing up to $maxJobs jobs\n";
echo str_repeat('-', 50) . "\n";

$processed = $queue->processAll($maxJobs);

echo "\nProcessed $processed jobs\n";
echo "Job Worker Completed\n";
