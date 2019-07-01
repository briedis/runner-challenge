<?php

namespace App\Services;

use App\Models\ActivityModel;
use App\Models\ChallengeModel;
use App\Models\UserModel;
use Exception;
use InvalidArgumentException;
use RedBeanPHP\R;
use Waddle\Parsers\GPXParser;

class ActivityService
{
    public function upload(
        UserModel $user,
        ChallengeModel $challenge,
        string $filename,
        string $pathname,
        string $activityUrl,
        string $comment
    ): bool {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, ['gpx'])) {
            throw new InvalidArgumentException('Invalid file extension. Has to be or GPX');
        }

        if (!filter_var($activityUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid activity URL. Make sure to copy the full URL from your browser.');
        }

        try {
            /** @noinspection PhpParamsInspection */
            $result = (new GPXParser)->parse($pathname);
        } catch (Exception $e) {
            throw new InvalidArgumentException('The activity file could not be read.');
        }

        $content = file_get_contents($pathname);
        $md5 = md5($content);

        if (R::findOne('files', 'md5 = ?', [$md5])) {
            throw new InvalidArgumentException('This activity has already been uploaded');
        }

        $file = R::dispense('files');
        $file->challenge_id = $challenge->id;
        $file->content = $content;
        $file->md5 = $md5;
        $file->user_id = $user->id;
        $file->created_at = time();
        $file->original_filename = $filename;
        R::store($file);

        $activity = R::dispense('activities');
        $activity->challenge_id = $challenge->id;
        $activity->user_id = $user->id;
        $activity->file_id = $file->id;
        $activity->activity_url = $activityUrl;
        $activity->comment = mb_substr(trim($comment), 0, 500);
        $activity->distance = $result->getTotalDistance();
        $activity->average_speed = $result->getAverageSpeedInKPH();
        $activity->max_speed = $result->getMaxSpeedInKPH();
        $activity->duration = $result->getTotalDuration();
        $activity->activity_at = $result->getStartTime('U');
        $activity->created_at = time();
        $activity->deleted_at = null;
        R::store($activity);

        return true;
    }

    /**
     * @param UserModel $user
     * @return ActivityModel[]
     */
    public function getActivities(UserModel $user): array
    {
        $beans = R::findLike('activities', [
            'user_id' => $user->id,
        ], 'ORDER BY created_at DESC');

        return array_map(function ($bean) {
            return ActivityModel::fromBean($bean);
        }, $beans);
    }

    /**
     * Is uploading enabled
     *
     * @return bool
     */
    public function canUpload(): bool
    {
        return (new SettingsService)->get('can_upload', false);
    }

    /**
     * Enable / Disable uploading
     * @param bool $canUpload
     * @return bool
     */
    public function setUpload(bool $canUpload)
    {
        return (new SettingsService)->set('can_upload', $canUpload);
    }

}