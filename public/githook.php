<?php
const LOG_DIR = __DIR__ . '/../var/log';
const LOG_PATH = LOG_DIR . '/githook.log';
const DEPLOY_PATH = __DIR__ . '/../deploy.sh';

function writeLog($message)
{
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0777, true);
    }
    file_put_contents(LOG_PATH, '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND);
}

function writeCommit($commit)
{
    writeLog('Details:' . "\n" .
        'commit ' . $commit->id . "\n" .
        'Author: ' . $commit->author->name . '<' . $commit->author->email . '>' . "\n" .
        'Date: ' . (new DateTime($commit->timestamp))->format('Y-m-d H:i:s P') . "\n" .
        str_replace("\n", "\n    ", "\n" . $commit->message)
    );
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

try {
    $payload = json_decode($_POST['payload']);
} catch (Exception $e) {
    exit(1);
}

// log the request
writeLog('Received payload, ref = ' . $payload->ref);

// only execute if pushed to master
if ($payload->ref === 'refs/heads/master') {
    writeCommit($payload->head_commit);
    writeLog('Run ' . DEPLOY_PATH);
    // run deployment script
    try {
        shell_exec(DEPLOY_PATH . ' >>' . LOG_PATH . ' 2>&1 &');
    } catch (Exception $e) {
        writeLog('Caught exception = ' . print_r($e, true));
        exit(1);
    }
}
writeLog('Done.');
exit(0);
