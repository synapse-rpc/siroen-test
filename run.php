<?php
/**
 * Created by IntelliJ IDEA.
 * User: xrain
 * Date: 2018/3/13
 * Time: 06:32
 */

require_once "vendor/autoload.php";
require_once "CommandLine.php";

use Rpc\Synapse\Siroen\Synapse;

$inputs = CommandLine::parseArgs();

$opts = $inputs['opts'];

if (!isset($opts['host']) or !isset($opts['user']) or !isset($opts['pass']) or !isset($opts['sys_name'])) {
    echo "Usage: php run.php --host MQ_HOST --user MQ_USER --pass MQ_PASS --sys_name SYSTEM_NAME [--debug] [--server] \n";
    exit;
}
$app = new Synapse();
$app->sys_name = $opts['sys_name'];
$app->app_name = 'php';
$app->mq_host = $opts['host'];
$app->mq_user = $opts['user'];
$app->mq_pass = $opts['pass'];
if (isset($opts['debug'])) {
    $app->debug = true;
}
if (isset($opts['server'])) {
    $app->rpc_callback = [
        'test' => function ($msg, $raw) {
            printf("RPC有请求: %s\n", $raw->body);
            return [
                'from' => 'php',
                'm' => $msg['msg'],
                'number' => 5233
            ];
        }
    ];
    $event = function ($msg, $raw) {
        $props = $raw->get_properties();
        printf("**收到EVENT: %s@%s %s\n", $props['type'], $props['reply_to'], json_encode($msg));
        return true;
    };
    $app->event_callback = [
        'dotnet.test' => $event,
        'golang.test' => $event,
        'python.test' => $event,
        'ruby.test' => $event,
        'php.test' => $event,
        'java.test' => $event,
    ];
}
$app->serve();
function showHelp()
{
    echo '----------------------------------------------' . PHP_EOL;
    echo '|   event usage:                             |' . PHP_EOL;
    echo '|     > event [event] [msg]                  |' . PHP_EOL;
    echo '|   rpc usage:                               |' . PHP_EOL;
    echo '|     > rpc [app] [method] [msg]             |' . PHP_EOL;
    echo '----------------------------------------------' . PHP_EOL;
}

showHelp();
while (true) {
    echo "input >> ";
    $handle = fopen("php://stdin", "r");   // 从 STDIN 读取一行
    $params = fgets($handle);
    $inputs = explode(' ', $params);
    switch ($inputs[0]) {
        case "event":
            if (count($inputs) != 3) {
                showHelp();
                continue;
            }
            $app->sendEvent($inputs[1], ['msg' => $inputs[2]]);
            break;
        case "rpc":
            if (count($inputs) != 4) {
                showHelp();
                continue;
            }
            echo json_encode($app->sendRpc($inputs[1], $inputs[2], ['msg' => $inputs[3]])) . PHP_EOL;
            break;
        default:
            showHelp();
            break;
    }
}
