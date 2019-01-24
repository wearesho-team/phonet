<?php

namespace Wearesho\Phonet\Data;

use Carbon\Carbon;
use Wearesho\Phonet\Enum\Direction;

/**
 * Class CompleteCall
 * @package Wearesho\Phonet\Data
 */
class CompleteCall extends BaseCall
{
    /** @var Carbon */
    protected $endAt;

    /** @var string|null */
    protected $subjectNumber;

    /** @var string|null */
    protected $subjectName;

    /** @var int */
    protected $disposition;

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
        ?string $parentUuid,
        Direction $direction,
        Employee $employeeCaller,
        ?Employee $employeeCallTaker,
        Carbon $endAt,
        ?string $subjectNumber,
        ?string $subjectName,
        int $disposition,
        ?string $trunk,
        int $billSecs,
        int $duration,
        ?string $transferHistory,
        ?string $audioRecUrl
    ) {
        $this->endAt = $endAt;
        $this->subjectNumber = $subjectNumber;
        $this->subjectName = $subjectName;
        $this->disposition = $disposition;
        $this->trunk = $trunk;
        $this->billSecs = $billSecs;
        $this->duration = $duration;
        $this->transferHistory = $transferHistory;
        $this->audioRecUrl = $audioRecUrl;

        parent::__construct($uuid, $parentUuid, $direction, $employeeCaller, $employeeCallTaker);
    }

    public function jsonSerialize(): array
    {
        return [
            'endAt' => $this->endAt,
            'subjectNumber' => $this->subjectNumber,
            'subjectName' => $this->subjectName,
            'disposition' => $this->disposition,
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

    public function getDisposition(): int
    {
        return $this->disposition;
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
