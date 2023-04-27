<?php

namespace App\Services;

use App\Models\ActivityModel;
use App\Models\ChallengeModel;
use App\Models\TeamModel;
use App\Models\TeamUserModel;
use App\Models\UserModel;
use InvalidArgumentException;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use RedBeanPHP\RedException\SQL;
use Throwable;

class ActivityService
{
    public function upload(
        UserModel $user,
        ChallengeModel $challenge,
        string $filename,
        string $pathname,
        string $activityUrl,
        string $comment,
        ?string $photoPathname,
        ?int $ploggingBags,
        ?string $ploggingPhotoPathname,
    ): bool {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension != 'gpx') {
            throw new InvalidArgumentException('Invalid file extension. Has to be GPX');
        }

        if (!filter_var($activityUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                'Invalid activity URL. Make sure to copy the full URL from your browser.'
            );
        }

        $imageId = $photoPathname ? $this->getPhotoId($photoPathname) : null;

        try {
            $gpxStats = (new GpxParser())->parse($pathname, (bool)$challenge->isWalking);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The activity file could not be read.');
        }

        $content = file_get_contents($pathname);
        $md5 = md5($content);

        $isDebug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN);
        if (R::findOne('files', 'md5 = ?', [$md5]) && !$isDebug) {
            throw new InvalidArgumentException('This activity has already been uploaded');
        }

        $file = $this->storeFile($challenge, $content, $md5, $user, $filename);

        $activity = new ActivityModel();
        $activity->challengeId = $challenge->id;
        $activity->userId = $user->id;
        $activity->fileId = $file->id;
        $activity->activityUrl = $activityUrl;
        $activity->imageId = $imageId;
        $activity->comment = mb_substr(trim($comment), 0, 500);
        $activity->distance = $gpxStats->distance;
        $activity->averageSpeed = $gpxStats->getAverageSpeedKmh();
        $activity->maxSpeed = 0;
        $activity->duration = $gpxStats->duration;
        $activity->activityAt = $gpxStats->startTime;
        $activity->createdAt = time();
        $activity->deletedAt = null;

        if ($challenge->isPlogging && $ploggingBags && $ploggingPhotoPathname) {
            $activity->ploggingBags = $ploggingBags;
            $activity->ploggingImageId = $this->getPhotoId($ploggingPhotoPathname);
        }

        $activity->save();

        $teamUser = TeamUserModel::findOneByChallenge($user->id, $challenge->id);
        $team = null;
        if ($teamUser) {
            $team = (new TeamService())->getById($teamUser->teamId);
        }

        $this->notifyAboutActivity($user, $activity, $team);

        return true;
    }

    private function getPhotoId(string $pathname): int
    {
        return (new ImageService())->upload($pathname, 'images')->id;
    }

    public function uploadGym(
        UserModel $user,
        ChallengeModel $challenge,
        string $comment,
        string $photoPathname,
        float $distanceKilometers,
        int $durationHours,
        int $durationMinutes
    ) {
        if (!$distanceKilometers || $distanceKilometers < 0 || $distanceKilometers > 100) {
            throw new InvalidArgumentException('Check the distance field. It should be in kilometers!');
        }

        $durationSeconds = $durationHours * 3600 + $durationMinutes * 60;

        if ($durationSeconds <= 0) {
            throw new InvalidArgumentException('Check the duration field!');
        }

        $imageId = null;
        if ($photoPathname) {
            $imageId = (new ImageService())->upload($photoPathname, 'images')->id;
        }

        $activity = new ActivityModel();
        $activity->challengeId = $challenge->id;
        $activity->userId = $user->id;
        $activity->fileId = '';
        $activity->activityUrl = '';
        $activity->imageId = $imageId;
        $activity->comment = mb_substr(trim($comment), 0, 500);
        $activity->distance = (int)($distanceKilometers * 1000);
        $activity->averageSpeed = $distanceKilometers / ($durationSeconds / 3600);
        $activity->maxSpeed = 0;
        $activity->duration = $durationSeconds;
        $activity->activityAt = time();
        $activity->createdAt = time();
        $activity->deletedAt = null;

        $activity->save();

        $teamUser = TeamUserModel::findOneByChallenge($user->id, $challenge->id);
        $team = null;
        if ($teamUser) {
            $team = (new TeamService())->getById($teamUser->teamId);
        }

        $this->notifyAboutActivity($user, $activity, $team, true);
    }

    /**
     * @param ChallengeModel $challenge
     * @param UserModel $user
     * @return ActivityModel[]
     */
    public function getActivities(ChallengeModel $challenge, UserModel $user): array
    {
        $beans = R::findLike('activities', [
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
        ], 'ORDER BY created_at DESC');

        return array_map(function ($bean) {
            return ActivityModel::fromBean($bean);
        }, $beans);
    }

    /**
     * Is uploading enabled
     *
     * @param ChallengeModel $challenge
     * @return bool
     */
    public function canUpload(?ChallengeModel $challenge): bool
    {
        if ($challenge && !$challenge->isOpen()) {
            return false;
        }

        return (new SettingsService())->get('can_upload', false);
    }

    /**
     * Enable / Disable uploading
     * @param bool $canUpload
     * @return bool
     */
    public function setUpload(bool $canUpload)
    {
        return (new SettingsService())->set('can_upload', $canUpload);
    }

    private function notifyAboutActivity(
        UserModel $user,
        ActivityModel $activity,
        ?TeamModel $team,
        bool $skipPhoto = false
    ) {
        $teamName = $team ? " _({$team->name})_" : '';

        $message = ":fire: *{$user->name}*{$teamName} just logged *{$activity->getReadableDistance()}* in *{$activity->getReadableDuration()}*.";
        $message .= " {$this->getQuote()}";

        [$name] = explode(' ', $user->name);

        $attachments = [];

        if ($activity->comment) {
            $attachments[] = [
                'text' => "{$name} says: _{$activity->comment}_",
                'color' => "good",
            ];
        }

        $image = $activity->getImage();
        if (!$skipPhoto && $image) {
            $attachments[] = [
                "fallback" => "$name also uploaded a photo - " . $image->getLargeUrl(),
                "title" => "$name also uploaded a photo!",
                "title_link" => $image->getLargeUrl(),
                "text" => " ",
                "image_url" => $image->getLargeUrl(),
                "color" => "#764FA5",
            ];
        }

        (new Slack())->send($message, $attachments);
    }

    private function getQuote(): string
    {
        $q = [
            'Strong is what happens when you run out of weak.',
            'The hardest part is walking out the front door.',
            'The Price of Excellence is discipline.',
            'Too fit to quit.',
            'Use it or lose it.',
            'Willpower knows no obstacles.',
            'Why put off feeling good?',
            'Get a jump on your day.',
            'Your body hears everything that your mind says.',
            'Fitness is not a destination it is a way of life.',
            'Get in. Get fit, and get on with life.',
            'It never gets easier. You just get strong.',
            'Love yourself enough to work harder.',
            'Make yourself stronger than your excuses.',
            'No Pain. No Gain.',
            'Move it or lose it.',
            'No one ever drowned in sweat.',
            'Your sweat is your fat crying. Keep it up.',
            'All it takes is all you got.',
        ];

        shuffle($q);

        return array_pop($q);
    }

    public function deleteActivity(UserModel $user, int $activityId): bool
    {
        $bean = R::findOne('activities', 'id = ? AND user_id = ?', [(int)$activityId, (int)$user->id]);

        if (!$bean) {
            throw new InvalidArgumentException('Entry not found');
        }

        $activity = ActivityModel::fromBean($bean);
        $activity->delete();

        return true;
    }

    public function getActivity(UserModel $user, int $activityId): ?ActivityModel
    {
        $activity = ActivityModel::getById($activityId);
        if ($activity && $activity->userId == $user->id) {
            return $activity;
        }
        return null;
    }

    /**
     * @param ActivityModel $activity
     * @return string
     */
    public function getGpx(ActivityModel $activity): ?string
    {
        $file = R::findOne('files', 'id = ?', [$activity->fileId]);
        if ($file) {
            return $file->content;
        }

        return null;
    }

    /**
     * @param ChallengeModel $challenge
     * @param bool|string $content
     * @param string $md5
     * @param UserModel $user
     * @param string $filename
     * @return array|OODBBean
     * @throws SQL
     */
    private function storeFile(
        ChallengeModel $challenge,
        bool|string $content,
        string $md5,
        UserModel $user,
        string $filename
    ): OODBBean|array {
        $file = R::dispense('files');
        $file->challenge_id = $challenge->id;
        $file->content = $content;
        $file->md5 = $md5;
        $file->user_id = $user->id;
        $file->created_at = time();
        $file->original_filename = $filename;

        R::store($file);

        return $file;
    }
}