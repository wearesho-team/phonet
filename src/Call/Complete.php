<?php

namespace Wearesho\Phonet\Call;

use Carbon\Carbon;
use Wearesho\Phonet;

/**
 * Class Complete
 * @package Wearesho\Phonet\Call
 */
class Complete extends Phonet\Call
{
    /** @var Carbon */
    protected $endAt;

    /** @var string|null */
    protected $subjectNumber;

    /** @var string|null */
    protected $subjectName;

    /** @var Complete\Status */
    protected $status;

    /** @var string|null */
    protected $trunk;

    /** @var int */
    protected $billSecs;

    /** @var int */
    protected $duration;

    /** @var string */
    protected $transferHistory;

    /** @var string|null */
    protected $audioRecUrl;

    public function __construct(
        string $uuid,
        Direction $direction,
        Phonet\Employee $employeeCaller,
        Carbon $endAt,
        Complete\Status $status,
        int $billSecs,
        int $duration,
        string $parentUuid = null,
        Phonet\Employee $employeeCallTaker = null,
        string $subjectNumber = null,
        string $subjectName = null,
        string $trunk = null,
        string $transferHistory = null,
        string $audioRecUrl = null
    ) {
        $this->endAt = $endAt;
        $this->subjectNumber = $subjectNumber;
        $this->subjectName = $subjectName;
        $this->status = $status;
        $this->trunk = $trunk;
        $this->billSecs = $billSecs;
        $this->duration = $duration;
        $this->transferHistory = $transferHistory;
        $this->audioRecUrl = $audioRecUrl;

        parent::__construct($uuid, $direction, $employeeCaller, $employeeCallTaker, $parentUuid);
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'parentUuid' => $this->parentUuid,
            'direction' => $this->direction,
            'employeeCaller' => $this->employeeCaller,
            'employeeCallTaker' => $this->employeeCallTaker,
            'endAt' => $this->endAt,
            'subjectNumber' => $this->subjectNumber,
            'subjectName' => $this->subjectName,
            'disposition' => $this->status,
            'trunk' => $this->trunk,
            'billSecs' => $this->billSecs,
            'duration' => $this->duration,
            'transferHistory' => $this->transferHistory,
            'audioRecUrl' => $this->audioRecUrl,
        ];
    }

    public function getEndAt(): Carbon
    {
        return $this->endAt;
    }

    public function getSubjectNumber(): ?string
    {
        return $this->subjectNumber;
    }

    public function getSubjectName(): ?string
    {
        return $this->subjectName;
    }

    public function getStatus(): Complete\Status
    {
        return $this->status;
    }

    public function getTrunk(): ?string
    {
        return $this->trunk;
    }

    public function getBillSecs(): int
    {
        return $this->billSecs;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getTransferHistory(): ?string
    {
        return $this->transferHistory;
    }

    public function getAudioRecUrl(): ?string
    {
        return $this->audioRecUrl;
    }
}
