<?php

namespace App\Models;

use Carbon\Carbon;

class ChallengeModel
{
    /** @var int */
    public int $id;

    /** @var Carbon When upload is open */
    public Carbon $openFrom;

    /** @var Carbon When upload is not open */
    public Carbon $openUntil;

    /**
     * Walking flag indicates that GPX has to be parsed more carefully,
     * discarding large distance jumps in a short time, which can occur due to GPS glitches.
     *
     * @var bool
     */
    public bool $isWalking;

    /**
     * Allow providing distance manually
     */
    public bool $allowManualInput;

    /** Enable plogging logging */
    public bool $isPlogging = false;

    public function isOpen(): bool
    {
        return $this->openFrom->isPast() && $this->openUntil->isFuture();
    }
}