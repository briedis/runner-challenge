<?php

use App\Models\ActivityModel;
use App\Models\ChallengeModel;


$this->layout('dashboard', ['title' => 'My Activities'] + $this->data);
$this->push('card');

/**
 * @var ChallengeModel $challenge
 * @var ActivityModel[] $activities
 */

if ($activities) { ?>
    <table class="table table-sm">
        <thead>
        <tr>
            <th scope="col">Distance</th>
            <th scope="col">Duration</th>
            <th scope="col">Average speed</th>
            <?php
            if ($challenge->isPlogging) { ?>
                <th scope="col">Bags of Trash</th>
                <?php
            } ?>
            <th scope="col">Date</th>
            <th scope="col">Uploaded</th>
            <th scope="col">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($activities as $a) {
            $image = $a->getImage();
            ?>
            <tr <?= ($a->deletedAt ? ' style="text-decoration: line-through;" class="text-muted"' : ''); ?>>
                <td class="align-middle">
                    <?php
                    if ($image) { ?>
                        <a href="<?= $image->getLargeUrl(); ?>" class="btn btn-link p-0 mr-2 text-decoration-none"
                           target="_blank">
                            <img src="<?= $image->getSmallUrl(); ?>" alt="" height="30" class="align-middle">
                        </a>
                        <?php
                    } ?>
                    <?= $a->getReadableDistance(); ?>
                </td>
                <td class="align-middle"><?= $a->getReadableDuration(); ?></td>
                <td class="align-middle"><?= $a->getReadableSpeed(); ?></td>
                <?php
                if ($challenge->isPlogging) { ?>
                    <td class="text-center align-middle">
                        <?php
                        if ($a->ploggingBags) { ?>
                            <a href="<?= htmlspecialchars($a->getPloggingImage()->getLargeUrl()); ?>" target="_blank">
                                <?= $a->ploggingBags ?>
                            </a>
                            <?php
                        } ?>
                    </td>
                    <?php
                } ?>
                <td class="align-middle"><a href="<?= htmlspecialchars($a->activityUrl); ?>" target="_blank"
                                            rel="noopener noreferrer"><?= $a->getReadableActivityAt(); ?></a></td>
                <td class="align-middle">
                    <a href="<?= htmlspecialchars(route('gpx')); ?>?activityId=<?= $a->id; ?>"
                       target="_blank"><?= $a->getReadableCreatedAt(); ?></a>
                </td>
                <td class="align-middle text-right px-0">
                    <?php
                    if (!$a->deletedAt) { ?>
                        <form method="post" action="<?= route('delete-activity'); ?>" class="d-inline-block">
                            <input type="hidden" name="activityId" value="<?= $a->id; ?>">
                            <button type="submit" class="btn btn-link p-0 text-decoration-none"
                                    onclick="return confirm('Are you sure?');">❌
                            </button>
                        </form>
                        <?php
                    } ?>
                </td>
            </tr>
            <?php
        } ?>
        </tbody>
    </table>
    <?php
} else { ?>
    <h5 class="card-title">Hello, there!</h5>
    <p class="card-text">When you log an activity, it will show up here!</p>
    <?php
}
$this->end();