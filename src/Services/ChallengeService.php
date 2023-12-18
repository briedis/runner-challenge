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
        $all = $this->all();

        return array_pop($all) ?: null;
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
        $challenge->openFrom = Carbon::createFromDate(2019, 7, 8, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2019, 7, 29, 'Europe/Riga')->setTime(23, 59, 59);
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 2;
        $challenge->openFrom = Carbon::createFromDate(2019, 8, 6, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2019, 8, 27, 'Europe/Riga')->setTime(23, 59, 59);
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 3;
        $challenge->openFrom = Carbon::createFromDate(2019, 9, 6, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2019, 9, 26, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 4;
        $challenge->openFrom = Carbon::createFromDate(2020, 3, 12, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2020, 4, 2, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 5;
        $challenge->openFrom = Carbon::createFromDate(2020, 4, 10, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2020, 5, 4, 'Europe/Riga')->setTime(23, 59, 59);
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 6;
        $challenge->openFrom = Carbon::createFromDate(2020, 5, 17, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2020, 6, 7, 'Europe/Riga')->setTime(23, 59, 59);
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 7;
        $challenge->openFrom = Carbon::createFromDate(2020, 9, 18, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2020, 10, 9, 'Europe/Riga')->setTime(23, 59, 59);
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 8;
        $challenge->openFrom = Carbon::createFromDate(2020, 10, 26, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2020, 11, 16, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 9;
        $challenge->openFrom = Carbon::createFromDate(2020, 12, 11, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2021, 01, 03, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 10;
        $challenge->openFrom = Carbon::createFromDate(2021, 3, 9, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2021, 4, 6, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 11;
        $challenge->openFrom = Carbon::createFromDate(2021, 5, 1, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2021, 5, 23, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 12;
        $challenge->openFrom = Carbon::createFromDate(2021, 10, 11, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2021, 11, 1, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 13;
        $challenge->openFrom = Carbon::createFromDate(2022, 2, 7, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2022, 2, 28, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 14;
        $challenge->openFrom = Carbon::createFromDate(2022, 5, 17, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2022, 6, 14, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = false;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 15;
        $challenge->openFrom = Carbon::createFromDate(2022, 10, 1, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2022, 10, 31, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = false;
        $challenge->allowManualInput = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 16;
        $challenge->openFrom = Carbon::createFromDate(2023, 5, 1, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2023, 5, 31, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = true;
        $challenge->isPlogging = true;
        $challenge->allowManualInput = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 17;
        $challenge->openFrom = Carbon::createFromDate(2023, 10, 2, 'Europe/Riga')->setTime(0, 0, 0);
        $challenge->openUntil = Carbon::createFromDate(2023, 10, 31, 'Europe/Riga')->setTime(23, 59, 59);
        $challenge->isWalking = false;
        $challenge->isPlogging = false;
        $challenge->allowManualInput = true;
        $all[] = $challenge;

        $challenge = new ChallengeModel();
        $challenge->id = 18;
        $challenge->openFrom = Carbon::createFromDate(2023, 12, 15, 'Europe/Riga')->setTime(8, 40, 0);
        $challenge->openUntil = Carbon::createFromDate(2023, 12, 21, 'Europe/Riga')->setTime(22, 0, 0);
        $challenge->isWalking = false;
        $challenge->isPlogging = false;
        $challenge->allowManualInput = true;
        $all[] = $challenge;
        
        return $all;
    }
}
