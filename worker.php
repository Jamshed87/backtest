<?php
namespace Flagmer;


use Flagmer\Integrations\AmoCrm;
use Flagmer\Integrations\AmoCrm\sendLeadDto;
use Flagmer\Billing\Account;
use Flagmer\Billing\Account\processPaymentDto;
use parallel\Runtime;
use parallel\Future;

require __DIR__ . '/vendor/autoload.php';

$runtimes = [];
$futures = [];
$n = 4;

//в реальных задачах вместо файла будет работа с очередю RabbitMQ к примеру
$data = json_decode(file_get_contents('tasks.json'), true);

$worker = function($work) {
    if ($work['category'] === 'account' && $work['task'] === 'processPayment') {
        $processPaymentData = new processPaymentDto();
        $processPaymentData->account_id = $work['data']['account_id'];
        $processPaymentData->amount = $work['data']['amount'];
        $account = new Account();
        $account->processPaymentAction($processPaymentData);
    } elseif ($work['category'] === 'amocrm' && $work['task'] === 'sendLead') {
        $sendLeadData = new sendLeadDto();
        $sendLeadData->lead_id = $work['data']['lead_id'];
        $amoCrm = new AmoCrm();
        $amoCrm->sendLeadAction($sendLeadData);
    }
};

while (true) {
    $j = 4;
    for ($i = 0; $i < $n; $i++) {
        if (!isset($futures[$i]) || $futures[$i]->done()) {
            $work = array_pop($data);
            if ($work) {
                $runtimes[$i] = new Runtime(__DIR__ . '/vendor/autoload.php');
                $futures[$i] = $runtimes[$i]->run($worker, [$work]);
            } else {
                $j--;
            }
        }
    }

    if ($j == 0) {
        exit;
    }
}

