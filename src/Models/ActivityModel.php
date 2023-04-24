<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class ActivityModel
{
    public $id;
    public $challengeId;
    public $userId;
    public $fileId;
    public $activityUrl;
    public $comment;
    /** @var float Meters */
    public $distance;
    /** @var float km/h */
    public $averageSpeed;
    public $maxSpeed;
    /** @var int Seconds */
    public $duration;
    public $imageId;
    public $activityAt;
    public $createdAt;
    public $deletedAt;

    /** How many shopping bags of trash were gathered */
    public ?int $ploggingBags = null;
    /** Proof for plogging gathered bags */
    public ?int $ploggingImageId = null;

    public static function fromBean(OODBBean $bean): ActivityModel
    {
        $m = new self;

        $m->id = $bean->id;
        $m->challengeId = $bean->challenge_id;
        $m->userId = $bean->user_id;
        $m->fileId = $bean->file_id;
        $m->activityUrl = $bean->activity_url;
        $m->comment = $bean->comment;
        $m->distance = $bean->distance;
        $m->averageSpeed = $bean->average_speed;
        $m->maxSpeed = $bean->max_speed;
        $m->duration = $bean->duration;
        $m->imageId = $bean->image_id;
        $m->activityAt = $bean->activityAt;
        $m->createdAt = $bean->created_at;
        $m->deletedAt = $bean->deleted_at;
        $m->ploggingBags = (int)$bean->plogging_bags ?: null;
        $m->ploggingImageId = (int)$bean->plogging_image_id ?: null;

        return $m;
    }

    public static function getById(int $activityId): ?ActivityModel
    {
        $bean = R::findOne('activities', 'id = ?', [$activityId]);

        if ($bean) {
            return self::fromBean($bean);
        }

        return null;
    }

    public function save()
    {
        $bean = R::dispense('activities');

        if ($this->id) {
            $bean->id = $this->id;
        }

        $bean->challenge_id = $this->challengeId;
        $bean->user_id = $this->userId;
        $bean->file_id = $this->fileId;
        $bean->activity_url = $this->activityUrl;
        $bean->comment = $this->comment;
        $bean->distance = $this->distance;
        $bean->average_speed = $this->averageSpeed;
        $bean->max_speed = $this->maxSpeed;
        $bean->duration = $this->duration;
        $bean->image_id = $this->imageId;
        $bean->activity_at = $this->activityAt;
        $bean->created_at = $this->createdAt;
        $bean->deleted_at = $this->deletedAt;
        $bean->plogging_bags = $this->ploggingBags;
        $bean->plogging_image_id = $this->ploggingImageId;

        $this->id = R::store($bean);
    }

    public function delete()
    {
        $this->deletedAt = time();
        $this->save();
    }

    public function getReadableActivityAt(): string
    {
        return Carbon::createFromTimestamp($this->activityAt)->diffForHumans();
    }

    public function getReadableCreatedAt(): string
    {
        return Carbon::createFromTimestamp($this->createdAt)->diffForHumans();
    }

    public function getReadableDistance(): string
    {
        return round($this->distance / 1000, 2) . ' km';
    }

    public function getReadableDuration(): string
    {
        $hrs = floor($this->duration / 3600);
        $min = floor(($this->duration - $hrs * 3600) / 60);

        return CarbonInterval::hours($hrs)->minutes($min)->forHumans(['short' => true]);
    }

    public function getReadableSpeed(): string
    {
        return round($this->averageSpeed, 2) . ' km/h';
    }

    public function getImage(): ?ImageModel
    {
        if ($this->imageId) {
            return ImageModel::getById($this->imageId);
        }
        return null;
    }

    public function getPloggingImage(): ?ImageModel
    {
        if ($this->ploggingImageId) {
            return ImageModel::getById($this->ploggingImageId);
        }
        return null;
    }
}