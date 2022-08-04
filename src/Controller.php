<?php

namespace App;

use App\Services\ImageService;
use App\Services\MessageService;
use App\Services\TextService;
use App\Services\UserService;
use Carbon\Carbon;
use InvalidArgumentException;

class Controller extends BaseController
{
    public function index()
    {
        return $this->redirect('board');
    }

    public function board()
    {
        return $this->render(
            'my-activities',
            [
                'activities' => $this->activities->getActivities($this->challenge, $this->user),
            ]
        );
    }

    public function rules()
    {
        return $this->render(
            'rules',
            [
                'rules' => (new TextService())->getRulesHtml(),
            ]
        );
    }

    public function myTeam()
    {
        return $this->render(
            'my-team',
            [
                'team' => $this->team,
                'totals' => $this->team ? $this->teams->getUserLeaderboard($this->challenge, $this->team) : [],
                'people' => $this->team ? $this->users->findByTeamId($this->team->id) : [],
            ]
        );
    }

    public function participate()
    {
        if (!$this->challenge || $this->challenge->isOpen()) {
            return $this->redirect('board', "Can't change status, because challenge has started.");
        }

        try {
            $this->users->setParticipating(
                $this->challenge,
                $this->user->id,
                !$this->user->isParticipating
            );
        } catch (InvalidArgumentException $e) {
            return $this->redirect('board', 'You have been assigned a team and cannot change your status now!');
        }

        return $this->redirect('board', 'Participation status updated');
    }

    public function upload()
    {
        if (!$this->activities->canUpload($this->challenge)) {
            return $this->redirect('board', 'Activities cannot be logged at this moment');
        }

        if (!$this->challenge) {
            return $this->redirect('board', 'No active challenges.');
        }

        $type = $_POST['type'] ?? '';
        $isGpx = $type === ActivityUploadTypes::GPX;

        if (!in_array($type, [ActivityUploadTypes::GPX, ActivityUploadTypes::GYM])) {
            return $this->redirect('board', 'Please select an activity type!');
        }

        if ($isGpx && empty($_FILES['gpx']['tmp_name'])) {
            return $this->redirect('board', 'Please select a file!');
        }

        if ($type === ActivityUploadTypes::GYM && !$this->challenge->allowManualInput) {
            return $this->redirect('board', 'Only GPX upload is allowed for this challenge!');
        }

        $gpxPathname = $_FILES['gpx']['tmp_name'] ?? null;
        $photoPathname = $_FILES['photo']['tmp_name'] ?? null;

        if ($photoPathname && !is_uploaded_file($photoPathname)) {
            return $this->redirect('board', 'Bad image selected.');
        }

        if ($isGpx && !is_uploaded_file($gpxPathname)) {
            return $this->redirect('board', 'Bad file selected.');
        }

        if ($type === ActivityUploadTypes::GYM && !$photoPathname) {
            return $this->redirect('board', 'You need to provide a photo proof when logging a gym activity!');
        }

        ini_set('memory_limit', '400M');


        try {
            if ($isGpx) {
                $this->activities->upload(
                    $this->user,
                    $this->challenge,
                    $_FILES['gpx']['name'],
                    $gpxPathname,
                    $_POST['activityUrl'],
                    $_POST['comment'],
                    $photoPathname
                );
            } else {
                $this->activities->uploadGym(
                    $this->user,
                    $this->challenge,
                    $_POST['comment'],
                    $photoPathname,
                    (float)str_replace(',', '.', $_POST['distance'] ?? 0),
                    $_POST['durationHours'] ?? 0,
                    $_POST['durationMinutes'] ?? 0,
                );
            }
        } catch (InvalidArgumentException $e) {
            return $this->redirect('board', $e->getMessage());
        }

        if ($this->team) {
            $this->teams->recalculateTeamScore($this->team);
        }

        return $this->redirect('board', 'Activity logged!');
    }

    public function deleteActivity()
    {
        $wasDeleted = $this->activities->deleteActivity($this->user, $_POST['activityId']);
        if ($wasDeleted && $this->team) {
            $this->teams->recalculateTeamScore($this->team);
        }
        return $this->redirect('board', $wasDeleted ? 'Activity deleted.' : 'Failed to delete an activity.');
    }

    public function editTeam()
    {
        if (!$this->team) {
            return $this->redirect('board', 'You are not in a team!');
        }

        $imagePathname = $_FILES['image'] ? $_FILES['image']['tmp_name'] : null;
        if (!is_uploaded_file($imagePathname)) {
            $imagePathname = null;
        }

        $newName = $_POST['teamName'] ?? '';

        try {
            $this->teams->editTeam($this->team, $newName, $imagePathname);
        } catch (InvalidArgumentException $e) {
            return $this->redirect('my-team', $e->getMessage());
        }

        return $this->redirect('my-team', 'Team information updated');
    }

    public function image()
    {
        $imageId = $_GET['id'] ?? 0;
        $content = (new ImageService())->getImageContent($imageId);

        header('Cache-Control: public, max-age=31536000');
        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($content));

        return $content;
    }

    public function register()
    {
        if ($this->user) {
            return $this->redirect('board');
        }

        $resetKey = $_GET['resetKey'] ?? ($_POST['resetKey'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';

        $user = $resetKey ? $this->users->findUserByResetKey($resetKey) : $this->users->findUser($email);

        if ($resetKey && !$user) {
            return $this->redirect('register', 'Password reset URL is not valid');
        }

        if (!$email || !$password) {
            return $this->render(
                'login',
                [
                    'resetKey' => $resetKey,
                    'email' => $user ? $user->email : '',
                ]
            );
        }

        if ($user) {
            try {
                $this->users->attemptLogIn($name, $email, $password, $resetKey);
            } catch (InvalidArgumentException $e) {
                return $this->redirect('register', $e->getMessage());
            }
            return $this->redirect('board', 'Welcome back, ' . htmlspecialchars($user->name));
        }

        if (!is_ip_whitelisted()) {
            return $this->redirect('register', 'Registration is not allowed from this IP address');
        }

        try {
            $user = $this->users->register($email, $password, $name);
            $this->users->logIn($user);
        } catch (InvalidArgumentException $e) {
            return $this->redirect('register', $e->getMessage());
        }

        return $this->redirect('board', 'Successfully registered!');
    }

    public function leaderboardTeams()
    {
        return $this->render(
            'leaderboard-teams',
            [
                'totals' => $this->teams->getTeamLeaderboard($this->challenge),
            ]
        );
    }

    public function leaderboardPeople()
    {
        return $this->render(
            'leaderboard-people',
            [
                'totals' => $this->teams->getUserLeaderboard($this->challenge),
            ]
        );
    }

    public function logout()
    {
        $this->users->logOut();
        return $this->redirect('register');
    }

    public function admin()
    {
        return $this->render(
            'admin',
            [
                'canUpload' => $this->activities->canUpload(null),
                'teams' => $this->challenge ? $this->teams->getAll($this->challenge) : [],
                'users' => $this->users->getAll(),
                'rules' => (new TextService())->getRules(),
                'challenge' => $this->challenge,
            ]
        );
    }

    public function addTeam()
    {
        $team = $this->teams->addTeam($this->challenge);
        return $this->redirect('admin', 'Team "' . $team->name . '" was added');
    }

    public function assignTeam()
    {
        $teamId = (int)$_POST['teamId'];

        $team = $this->teams->getById($teamId);
        $users = $this->users->findByIds($_POST['userIds'] ?? []);

        if ($teamId === -1) {
            foreach ($users as $user) {
                (new UserService())->setParticipating($this->challenge, $user->id, false);
            }
            return $this->redirect('admin', 'People have been set as NOT participating.');
        }

        if (!$team) {
            $this->redirect('admin', 'Team was not found');
        }

        $this->teams->assignUsers($team, $users);

        return $this->redirect('admin', 'People have been assigned to a team.');
    }

    public function unassignTeam()
    {
        $user = $this->users->findById($_POST['userId']);
        $this->teams->unassignUser($this->challenge, $user);
        return $this->redirect('admin', 'A person has been unassigned from a team.');
    }

    public function deleteTeam()
    {
        $team = $this->teams->getById($_POST['teamId']);
        $this->teams->deleteTeam($team);
        return $this->redirect('admin', 'Team has been delete');
    }

    public function impersonate()
    {
        $user = $this->users->impersonate($_POST['userId']);
        return $this->redirect('board', 'You are now impersonating ' . htmlspecialchars($user->name));
    }

    public function enableUpload()
    {
        $this->activities->setUpload((bool)$_POST['canUpload']);
        return $this->redirect('admin');
    }

    public function editRules()
    {
        (new TextService())->setRules($_POST['html'] ?? '');
        return $this->redirect('admin', 'Rules saved');
    }

    public function setParticipating()
    {
        try {
            foreach ($_POST['userIds'] as $userId) {
                (new UserService())->setParticipating($this->challenge, $userId, (bool)$_POST['isParticipating']);
            }
        } catch (InvalidArgumentException $e) {
            return $this->redirect('admin', $e->getMessage());
        }

        return $this->redirect('admin', 'Participation status changed');
    }

    public function resetPassword()
    {
        $newPassword = (new UserService())->resetPassword($_POST['userId']);

        return $this->redirect('admin', 'URL to reset password: <code>' . $newPassword . '</code>');
    }

    public function announcement()
    {
        $result = (new MessageService())->send($_POST['message']);
        return $this->redirect('admin', $result ? 'Announcement sent!' : 'Failed to send the message!');
    }

    public function migrate()
    {
        die('No migrations');
    }

    public function downloadGpx()
    {
        $activityId = $_GET['activityId'];
        $activity = $this->activities->getActivity($this->user, $activityId);

        if (!$activity) {
            return $this->redirect('board', 'Activity was not found');
        }

        $gpx = $this->activities->getGpx($activity);

        if (!$gpx) {
            return $this->redirect('board', 'GPX file was not found');
        }

        header('Cache-Control: public, max-age=31536000');
        header('Content-Type: application/gpx+xml');
        header('Content-Length: ' . strlen($gpx));
        $filename = 'activity-' . Carbon::createFromTimestamp($activity->createdAt)->format('Ymd-His') . '.gpx';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        return $gpx;
    }
}
