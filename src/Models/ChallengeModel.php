<?php

namespace App\Models;

use Carbon\Carbon;

class ChallengeModel
{
    /** @var int */
    public $id;

    /** @var Carbon When upload is open */
    public $openFrom;

    /** @var Carbon When upload is not open */
    public $openUntil;

    /**
     * Walking flag indicates that GPX has to be parsed more carefully,
     * discarding large distance jumps in a short time, which can occur due to GPS glitches.
     *
     * @var bool
     */
    public $isWalking;

    public function isOpen(): bool
    {
        return $this->openFrom->isPast() && $this->openUntil->isFuture();
    }
}