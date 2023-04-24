<?php


namespace App\Models;


use Carbon\Carbon;
use Carbon\CarbonInterval;

class TotalsModel
{
    /** @var int User or team ID */
    public $id;

    /** @var string */
    public $userName;

    /** @var string */
    public $teamName;

    public $distance;

    public $lastActivityAt;

    public $activityCount;

    public $duration;

    public int $ploggingBags = 0;

    /** @var ?string */
    public $imageUrl;

    public function getReadableLastActivityAt(): string
    {
        if (!$this->lastActivityAt) {
            return '-';
        }
        return Carbon::createFromTimestamp($this->lastActivityAt)->diffForHumans();
    }

    public function getReadableDistance(): string
    {
        return round($this->distance / 1000, 2) . ' km';
    }

    public function getReadableDuration(): string
    {
        if (!$this->duration) {
            return '-';
        }

        $secondsLeft = $this->duration;

        $days = floor($secondsLeft / (24 * 3600));

        $secondsLeft -= $days * 24 * 3600;

        $hrs = floor($secondsLeft / 3600);

        $secondsLeft -= $hrs * 3600;

        $min = floor($secondsLeft / 60);

        return CarbonInterval::days($days)->hours($hrs)->minutes($min)->forHumans(['short' => true]);
    }
}