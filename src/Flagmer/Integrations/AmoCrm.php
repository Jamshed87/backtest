<?php


namespace Flagmer\Integrations;


use Flagmer\Integrations\Amocrm\sendLeadDto;

class AmoCrm
{
    public function sendLeadAction(sendLeadDto $lead): void
    {
        echo "Sending lead {$lead->lead_id} to AmoCRM...\n";
        sleep(random_int(1,3));
        echo "Done {$lead->lead_id}\n";
    }
}