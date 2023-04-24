<?php

namespace App\Services;

use App\Models\ChallengeModel;
use App\Models\TeamModel;
use App\Models\TeamUserModel;
use App\Models\TotalsModel;
use App\Models\UserModel;
use RedBeanPHP\R;

class TeamService
{
    /**
     * @param ChallengeModel $challenge
     * @return TeamModel
     */
    public function addTeam(ChallengeModel $challenge): TeamModel
    {
        $team = new TeamModel;
        $team->challengeId = $challenge->id;
        $team->name = "";
        $team->captainId = 0;
        $team->imageId = 0;
        $team->createdAt = time();
        $team->save();

        $team->name = 'Team #' . $team->id;
        $team->save();

        return $team;
    }

    /**
     * @param ChallengeModel $challenge
     * @return TeamModel[]
     */
    public function getAll(ChallengeModel $challenge): array
    {
        $beans = R::findAll('teams', 'challenge_id = ?', [$challenge->id]);

        return array_map(function ($bean) {
            return TeamModel::fromBean($bean);
        }, $beans);
    }

    /**
     * @param int $teamId
     * @param ChallengeModel $challenge
     * @return TeamModel|null
     */
    public function getById(?int $teamId, ?ChallengeModel $challenge = null): ?TeamModel
    {
        if (!$teamId) {
            return null;
        }

        if ($challenge) {
            $bean = R::findOne('teams', 'id = ? AND challenge_id = ?', [$teamId, $challenge->id]);
        } else {
            $bean = R::findOne('teams', 'id = ?', [$teamId]);
        }

        if ($bean) {
            return TeamModel::fromBean($bean);
        }

        return null;
    }

    /**
     * @param TeamModel $team
     * @param UserModel[] $users
     */
    public function assignUsers(TeamModel $team, array $users)
    {
        foreach ($users as $user) {
            if (TeamUserModel::findOneByChallenge($user->id, $team->challengeId)) {
                // Already in a team for this challenge
                continue;
            }

            $teamUser = new TeamUserModel;
            $teamUser->teamId = $team->id;
            $teamUser->userId = $user->id;
            $teamUser->challengeId = $team->challengeId;
            $teamUser->save();
        }

        $this->recalculateTeamScore($team);
    }

    public function unassignUser(ChallengeModel $challenge, UserModel $user)
    {
        $teamUser = TeamUserModel::findOneByChallenge($user->id, $challenge->id);

        $team = $this->getById($teamUser->teamId);

        $teamUser->delete();

        if ($team) {
            $this->recalculateTeamScore($team);
        }
    }

    public function deleteTeam(TeamModel $team)
    {
        $teamUsers = TeamUserModel::findByTeamId($team->id);
        foreach ($teamUsers as $v) {
            $v->delete();
        }
        $team->delete();
    }

    public function recalculateTeamScore(TeamModel $team)
    {
        $scores = R::getRow(
            '
            SELECT SUM(distance) AS total_distance, SUM(duration) AS total_duration FROM activities a
            JOIN teamsusers tu ON tu.user_id = a.user_id
            WHERE tu.team_id = ? AND a.deleted_at IS NULL AND a.challenge_id = ?
            GROUP BY tu.team_id
        ',
            [$team->id, $team->challengeId]
        );

        $team->totalDistance = (float)($scores['total_distance'] ?? 0);
        $team->totalDuration = (float)($scores['total_duration'] ?? 0);
        $team->save();
    }

    public function getUserLeaderboard(ChallengeModel $challenge, ?TeamModel $team = null)
    {
        $bindings = [
            'challengeId' => $challenge->id,
        ];

        $sql = '1';

        if ($team) {
            $bindings['teamId'] = $team->id;
            $sql = 't.id = :teamId';
        }

        $totalsRaw = R::getAll(
            "
            SELECT 
                   u.id, 
                   u.name AS user_name, 
                   t.name AS team_name,
                   t.image_id AS team_image_id,
                   SUM(distance) AS total_distance, 
                   SUM(duration) AS total_duration, 
                   MAX(a.activity_at) AS last_activity_at,
                   COUNT(a.id) AS activity_count,
                   SUM(a.plogging_bags) AS plogging_bags
            FROM users u
            LEFT JOIN activities a ON a.user_id = u.id AND a.deleted_at IS NULL AND a.challenge_id = :challengeId
            LEFT JOIN teamsusers tu ON tu.user_id = u.id AND tu.challenge_id = :challengeId
            LEFT JOIN teams t ON t.id = tu.team_id 
            WHERE 
                   $sql 
                   AND u.is_participating = 1
            GROUP BY u.id
            ORDER BY total_distance DESC
        ",
            $bindings
        );

        return $this->parseTotals($totalsRaw);
    }

    /**
     * @param null|ChallengeModel $challenge
     * @return TotalsModel[]
     */
    public function getTeamLeaderboard(?ChallengeModel $challenge): array
    {
        if (!$challenge) {
            return [];
        }

        $totalsRaw = R::getAll(
            "
            SELECT 
                   u.id, 
                   t.id AS team_id,
                   u.name AS user_name, 
                   t.name AS team_name,
                   t.image_id AS team_image_id,
                   SUM(distance) AS total_distance, 
                   SUM(duration) AS total_duration, 
                   MAX(a.activity_at) AS last_activity_at,
                   COUNT(a.id) AS activity_count,
                   SUM(a.plogging_bags) AS plogging_bags
            FROM users u
            LEFT JOIN activities a ON a.user_id = u.id AND a.deleted_at IS NULL AND a.challenge_id = :challengeId
            JOIN teamsusers tu ON tu.user_id = u.id AND tu.challenge_id = :challengeId
            JOIN teams t ON t.id = tu.team_id
            WHERE u.is_participating = 1 
            GROUP BY t.id
            ORDER BY total_distance DESC
        ",
            [
                'challengeId' => $challenge->id,
            ]
        );

        return $this->parseTotals($totalsRaw);
    }

    /**
     * @param TeamModel $team
     * @param $newName
     * @param $imagePathname
     */
    public function editTeam(TeamModel $team, string $newName, ?string $imagePathname)
    {
        $team->name = mb_substr(trim($newName), 0, 40) ?: 'Unnamed';
        $team->save();

        if ($imagePathname) {
            $this->uploadImage($team, $imagePathname);
        }
    }

    private function uploadImage(TeamModel $team, ?string $pathname)
    {
        if (!$pathname) {
            return;
        }

        $imageId = (new ImageService)->create($pathname);

        $team->imageId = $imageId;
        $team->save();
    }

    /**
     * @param array $totalsRaw
     * @return TotalsModel[]
     */
    private function parseTotals(array $totalsRaw): array
    {
        return array_map(function ($r) {
            $t = new TotalsModel;
            $t->id = (int)$r['id'];
            $t->userName = $r['user_name'];
            $t->teamName = $r['team_name'];
            $t->distance = (float)$r['total_distance'];
            $t->duration = (int)$r['total_duration'];
            $t->lastActivityAt = (int)$r['last_activity_at'];
            $t->activityCount = (int)$r['activity_count'];
            $t->ploggingBags = (int)$r['plogging_bags'];
            if ($r['team_image_id']) {
                $t->imageUrl = route('image') . '?id=' . $r['team_image_id'];
            }
            return $t;
        }, $totalsRaw);
    }
}