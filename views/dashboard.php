<?php $this->layout('layout', $this->data);

use App\Models\ChallengeModel; ?>



<?php


/**
 * @var bool $canUpload Is uploading enabled
 * @var ChallengeModel|null $challenge
 */

$links = [
    'board' => 'My Activities',
    'my-team' => 'My Team',
    'team-leaderboard' => 'Teams',
    'people-leaderboard' => 'People',
    'rules' => 'Rules',
];

?>

<div class="row">
    <div class="col-lg-8">
        <div class="card text-center mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <?php
                    foreach ($links as $route => $title) {
                        $url = route($route);
                        $isActive = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $url;
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isActive ? 'active' : ''); ?>" href="<?= $url; ?>">
                                <?= $title; ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="card-body text-left"><?= $this->section('card') ?></div>
        </div>

        <?= $this->section('after') ?>
    </div>

    <div class="col-lg-4">
        <?php $this->insert('_log-activity', ['canUpload' => $canUpload, 'challenge' => $challenge] + $this->data); ?>
    </div>
</div>