<?php

namespace App\Services;

use App\Models\ChallengeModel;
use App\Models\TeamUserModel;
use App\Models\UserModel;
use InvalidArgumentException;
use RedBeanPHP\R;

class UserService
{
    public function register($email, $password, $name): UserModel
    {
        $email = trim(mb_strtolower($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$password || !$name) {
            throw new InvalidArgumentException('Bad email, password or name.');
        }

        if ($this->findUser($email)) {
            throw new InvalidArgumentException('Email already registered');
        }

        $user = new UserModel;

        $user->email = $email;
        $user->setPassword($password);
        $user->name = trim($name);
        $user->isAdmin = $this->shouldBeAdmin($email);
        $user->isParticipating = true;
        $user->lastVisitedAt = time();

        $user->save();

        return $user;
    }

    private function shouldBeAdmin(string $email): bool
    {
        $list = trim(mb_strtolower(getenv('ADMIN_EMAILS')));
        return in_array($email, explode(',', $list), true);
    }

    public function findUserByResetKey(?string $resetKey): ?UserModel
    {
        if (!$resetKey) {
            return null;
        }
        $bean = R::findOne('users', 'password_reset_key = ?', [$resetKey]);
        return $bean ? UserModel::fromBean($bean) : null;
    }

    public function findUser($email): ?UserModel
    {
        $bean = R::findOne('users', 'email = ?', [$email]);

        if ($bean) {
            return UserModel::fromBean($bean);
        }

        return null;
    }

    public function findById($id): ?UserModel
    {
        $bean = R::findOne('users', 'id = ?', [$id]);

        if ($bean) {
            return UserModel::fromBean($bean);
        }

        return null;
    }

    public function attemptLogIn(string $name, string $email, string $password, ?string $resetKey)
    {
        $user = $this->findUser($email);

        $name = trim($name);
        $name = $name && $name !== $user->name ? $name : $user->name;
        $shouldBeAdmin = $this->shouldBeAdmin($email);

        if ($resetKey) {
            if ($user->passwordResetKey !== $resetKey) {
                throw new InvalidArgumentException('Reset key is not valid');
            }
            $user->setPassword($password);
            $user->passwordResetKey = null;
            $user->name = $name;
            $user->isAdmin = $shouldBeAdmin;
            $user->save();
            $this->logIn($user);
            return true;
        }

        if ($user->passwordMatches($password)) {
            $user->name = $name;
            $user->lastVisitedAt = time();
            $user->isAdmin = $shouldBeAdmin;
            $user->save();
            $this->logIn($user);
            return true;
        }

        throw new InvalidArgumentException('Password does not match');
    }

    public function logIn(UserModel $user)
    {
        $_SESSION['userId'] = $user->id;
    }

    public function getLoggedIn(): ?UserModel
    {
        $id = $_SESSION['userId'] ?? null;
        if ($id) {
            $user = $this->findById($id);
            if (!empty($_SESSION['impersonator'])) {
                $user->isImpersonating = true;
            }
            return $user;
        }

        return null;
    }

    public function logOut()
    {
        $impersonatorUserId = $_SESSION['impersonator'] ?? null;
        if ($impersonatorUserId) {
            unset($_SESSION['impersonator']);
            $_SESSION['userId'] = $impersonatorUserId;
            return;
        }
        unset($_SESSION['userId']);
    }

    /**
     * @return UserModel[]
     */
    public function getAll(): array
    {
        return array_map(function ($bean) {
            return UserModel::fromBean($bean);
        }, R::findAll('users'));
    }

    /**
     * @param array $userIds
     * @return UserModel[]
     */
    public function findByIds(array $userIds): array
    {
        return array_reduce($userIds, function ($carry, int $userId) {
            $user = $this->findById($userId);
            if ($user) {
                $carry[] = $user;
            }
            return $carry;
        }, []);
    }

    /**
     * @param int $teamId
     * @return UserModel[]
     */
    public function findByTeamId(int $teamId): array
    {
        $teamUsers = TeamUserModel::findByTeamId($teamId);
        return array_map(function (TeamUserModel $tu) {
            return UserModel::findById($tu->userId);
        }, $teamUsers);
    }

    public function impersonate(int $userId)
    {
        $current = $this->getLoggedIn();
        $user = $this->findById($userId);

        $this->logIn($user);

        $_SESSION['impersonator'] = $current->id;

        return $user;
    }

    public function setParticipating(ChallengeModel $challenge, int $userId, bool $isParticipating): bool
    {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }

        if (TeamUserModel::findOneByChallenge($user->id, $challenge->id)) {
            throw new InvalidArgumentException('Cannot change participation because in a team');
        }

        $user->isParticipating = $isParticipating;
        $user->save();
        return true;
    }

    /**
     * @param int $userId
     * @return string Password reset URL
     */
    public function resetPassword(int $userId): string
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        $user->passwordResetKey = md5(uniqid('', true));
        $user->save();

        return $user->getPasswordResetUrl();
    }

    public function setAllAsNotParticipating(ChallengeModel $challenge)
    {
        if ($challenge->openFrom->isPast()) {
            throw new InvalidArgumentException('Challenge has already started');
        }

        $participants = array_filter($this->getAll(), function (UserModel $u) {
            return $u->isParticipating;
        });

        foreach ($participants as $participant) {
            $this->setParticipating($challenge, $participant->id, false);
        }
    }
}