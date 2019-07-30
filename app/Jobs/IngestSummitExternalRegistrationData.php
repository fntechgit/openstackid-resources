<?php namespace App\Jobs;
/**
 * Copyright 2019 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use App\Mail\Registration\ExternalIngestion\SuccessfulIIngestionEmail;
use App\Mail\Registration\ExternalIngestion\UnsuccessfulIIngestionEmail;
use App\Services\Model\IRegistrationIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use libs\utils\ITransactionService;
use models\exceptions\ValidationException;
use models\summit\ISummitRepository;
use models\summit\Summit;
/**
 * Class IngestSummitExternalRegistrationData
 * @package App\Jobs
 */
class IngestSummitExternalRegistrationData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    /**
     * @var int
     */
    private $summit_id;

    /**
     * @var string
     */
    private $email_to;

    /**
     * IngestSummitExternalRegistrationData constructor.
     * @param int $summit_id
     * @param null|string $email_to
     */
    public function __construct(int $summit_id, ?string $email_to = null)
    {
        $this->summit_id  = $summit_id;
        $this->email_to   = $email_to;
    }

    /**
     * @param ISummitRepository $summit_repository
     * @param IRegistrationIngestionService $service
     * @param ITransactionService $tx_service
     */
    public function handle
    (
        ISummitRepository $summit_repository,
        IRegistrationIngestionService $service,
        ITransactionService $tx_service
    )
    {
        try {
            Log::debug("IngestSummitExternalRegistrationData::handle");

            $tx_service->transaction(function () use ($summit_repository, $service) {

                $summit = $summit_repository->getById($this->summit_id);
                if (is_null($summit) || !$summit instanceof Summit) return;
                $service->ingestSummit($summit);
                if(!empty($this->email_to)) {
                    Log::debug(sprintf("IngestSummitExternalRegistrationData::handle - sending result email to %s", $this->email_to));
                    Mail::queue(new SuccessfulIIngestionEmail($this->email_to, $summit));
                }
            });
        }
        catch (ValidationException $ex){
            Log::warning($ex);
            if(!empty($this->email_to)) {
                $summit = $summit_repository->getById($this->summit_id);
                if (is_null($summit) || !$summit instanceof Summit) return;
                Mail::queue(new UnsuccessfulIIngestionEmail($ex->getMessage(), $this->email_to, $summit));
            }
        }
        catch (\Exception $ex){
            Log::error($ex);
            if(!empty($this->email_to)) {
                $summit = $summit_repository->getById($this->summit_id);
                if (is_null($summit) || !$summit instanceof Summit) return;
                Mail::queue(new UnsuccessfulIIngestionEmail($ex->getMessage(), $this->email_to, $summit));
            }
        }
    }

}