<?php

namespace App\Models;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class UserModel
{
    public $id;
    public $password;
    public $email;
    /** @var ?int */
    public $teamId;
    public $name;
    public $isAdmin;

    /** @var bool Is currently impersonating this user */
    public $isImpersonating;

    public static function fromBean(?OODBBean $bean): UserModel
    {
        $m = new self;

        if (!$bean) {
            return $m;
        }

        $m->id = $bean->id;
        $m->password = $bean->password;
        $m->email = $bean->email;
        $m->teamId = ((int)$bean->team_id) ?: null;
        $m->name = $bean->name;
        $m->isAdmin = (bool)$bean->is_admin;

        return $m;
    }

    /**
     * @param int $teamId
     * @return UserModel[]
     */
    public static function findByTeam(int $teamId): array
    {
        $beans = R::findAll('users', 'team_id = ?', [$teamId]);

        return array_map(function ($bean) {
            return self::fromBean($bean);
        }, $beans);
    }

    public function passwordMatches($password): bool
    {
        return password_verify($password, $this->password);
    }

    public function save()
    {
        $bean = R::dispense('users');

        if ($this->id) {
            $bean->id = $this->id;
        }

        $bean->password = $this->password;
        $bean->email = $this->email;
        $bean->team_id = $this->teamId;
        $bean->name = $this->name;
        $bean->is_admin = (int)$this->isAdmin;

        R::store($bean);

        $this->id = $bean->id;
    }
}