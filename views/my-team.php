<?php

use App\Models\TeamModel;
use App\Models\TotalsModel;
use App\Models\UserModel;

$this->layout('dashboard', ['title' => 'My Team'] + $this->data);
$this->push('card');

/**
 * @var TeamModel|null $team
 * @var TotalsModel[] $totals
 * @var UserModel[] $people
 */

if ($team) { ?>
    <table class="table table-sm">
        <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Distance</th>
            <th scope="col">Duration</th>
            <th scope="col">Activities</th>
            <th scope="col">Last Activity</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($totals as $a) { ?>
            <tr>
                <td><?= htmlspecialchars($a->userName); ?></td>
                <td><?= $a->getReadableDistance(); ?></td>
                <td><?= $a->getReadableDuration(); ?></td>
                <td><?= $a->activityCount; ?></td>
                <td><?= $a->getReadableLastActivityAt(); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <h5 class="card-title">You are not in a team</h5>
    <p class="card-text">You will be assigned to a team soon, don't worry!</p>
    <?php
}
$this->end();

if ($team) {
    $this->push('after');
    ?>
    <div class="row mt-4">
        <div class="col-lg-8 mb-4">
            <?php $this->insert('_team-profile', ['team' => $team]); ?>
        </div>
        <div class="col mb-4">
            <?php $this->insert('_team-members', ['people' => $people]); ?>
        </div>
    </div>
    <?php
    $this->end();
}