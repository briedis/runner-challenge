<?php

namespace App\Models;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class TeamUserModel
{
    const TABLE = 'teamsusers';

    /** @var int */
    public $id;
    /** @var int */
    public $challengeId;
    /** @var int */
    public $teamId;
    /** @var int */
    public $userId;

    public function save()
    {
        $bean = R::dispense(self::TABLE);

        if ($this->id) {
            $bean->id = $this->id;
        }

        $bean->team_id = $this->teamId;
        $bean->challenge_id = $this->challengeId;
        $bean->user_id = $this->userId;

        R::store($bean);

        $this->id = $bean->id;
    }

    public static function fromBean(OODBBean $bean): TeamUserModel
    {
        $m = new self;

        $m->id = $bean->id;
        $m->challengeId = $bean->challenge_id;
        $m->teamId = $bean->team_id;
        $m->userId = $bean->user_id;

        return $m;
    }

    public function delete()
    {
        R::trash(self::TABLE, $this->id);
    }

    /**
     * @param int $teamId
     * @param int $userId
     * @return TeamUserModel|null
     */
    public static function findOne(int $teamId, int $userId): ?TeamUserModel
    {
        $bean = R::findOne(self::TABLE, 'team_id = ? AND user_id = ?', [$teamId, $userId]);
        if ($bean) {
            return self::fromBean($bean);
        }
        return null;
    }

    /**
     * @param int $teamId
     * @return TeamUserModel[]
     */
    public static function findByTeamId(int $teamId): array
    {
        $beans = R::findAll(self::TABLE, 'team_id = ?', [$teamId]);
        return array_map(function ($bean) {
            return self::fromBean($bean);
        }, $beans);
    }

    /**
     * @param int $userId
     * @param int $challengeId
     * @return TeamUserModel|null
     */
    public static function findOneByChallenge(int $userId, int $challengeId): ?TeamUserModel
    {
        $bean = R::findOne(self::TABLE, 'challenge_id = ? AND user_id = ?', [$challengeId, $userId]);
        if ($bean) {
            return self::fromBean($bean);
        }
        return null;
    }

    public static function findAllByChallenge(int $challengeId): array
    {
        $bean = R::findAll(self::TABLE, 'challenge_id = ?', [$challengeId]);
        if (!$bean) {
            return [];
        }

        return array_map(fn ($b) => self::fromBean($b), $bean);
    }
}