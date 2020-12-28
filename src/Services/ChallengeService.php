<?php

namespace App\Services;

use App\Models\ChallengeModel;
use Carbon\Carbon;

class ChallengeService
{
    /**
     * @return ChallengeModel|null
     */
    public function getCurrent(): ?ChallengeModel
    {
        return $this->getById(1);
    }

    public function getById(int $challengeId): ?ChallengeModel
    {
        foreach ($this->all() as $c) {
            if ($c->id === $challengeId) {
                return $c;
            }
        }
        return null;
    }

    /**
     * @return ChallengeModel[]
     */
    private function all(): array
    {
        $all = [];

        $challenge = new ChallengeModel();
        $challenge->id = 1;
        $challenge->openFrom = Carbon::createFromDate(2021, 1, 11, 'America/New_York')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2021, 1, 31, 'America/New_York')->setTime(23, 59, 59);
        $all[] = $challenge;

        return $all;
    }
}