<?php

use App\Models\ChallengeModel;
use App\Models\TeamModel;
use App\Models\TeamUserModel;
use App\Models\UserModel;

/**
 * @var TeamModel[] $teams
 * @var UserModel[] $users
 * @var bool $canUpload
 * @var string $rules
 * @var ChallengeModel|null $challenge
 */

/**
 * @param TeamModel $team
 * @param bool|null $isParticipating null - ignore, true/false for participation status
 * @return UserModel[]
 */
$findUsers = function (?TeamModel $team, ?bool $isParticipating) use ($users, $challenge) {
    if (!$challenge) {
        return [];
    }
    return array_filter($users, function (UserModel $user) use ($team, $isParticipating, $challenge) {
        if ($isParticipating !== null && $user->isParticipating !== $isParticipating) {
            return false;
        }
        $teamUser = TeamUserModel::findOneByChallenge($user->id, $challenge->id);
        if (!$team && !$teamUser) {
            return true;
        }
        return ($team && $teamUser) && ($team->id == $teamUser->teamId);
    });
};

$this->layout('layout', ['title' => 'Admin'] + $this->data);
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                Teams
                <form action="<?= route('add-team'); ?>" method="post" class="float-right">
                    <button type="submit" class="btn btn-primary btn-sm">Create a team</button>
                </form>
            </div>
            <div class="card-body">
                <div class="card-columns">
                    <?php foreach ($teams as $team) { ?>
                        <div class="card">
                            <div class="card-header p-1 pl-2">
                                <?= htmlspecialchars($team->name); ?>
                                (<?= $team->getReadableDistance(); ?>)
                                <form method="post" action="<?= route('delete-team'); ?>" class="float-right">
                                    <input type="hidden" name="teamId" value="<?= $team->id; ?>">
                                    <button type="submit" class="btn btn-link btn-sm m-0 p-0 mr-2">❌</button>
                                </form>
                            </div>
                            <div class="card-body p-1 pl-2">
                                <?php
                                $teamUsers = $findUsers($team, null);
                                if (!$teamUsers) {
                                    ?><em class="text-muted">&ndash;</em><?php
                                } else {
                                    ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($teamUsers as $u) { ?>
                                            <li class="list-group-item p-0">
                                                <span title="<?= htmlspecialchars($u->email); ?>">
                                                    <?= htmlspecialchars($u->name); ?>
                                                </span>
                                                <a href="javascript:" class="impersonate" data-id="<?= $u->id; ?>"></a>
                                                <a href="javascript:" class="password" data-id="<?= $u->id; ?>"></a>
                                                <form method="post" action="<?= route('unassign-team'); ?>"
                                                      class="float-right">
                                                    <input type="hidden" name="userId" value="<?= $u->id; ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm m-0 p-0 mr-2">
                                                        ❌
                                                    </button>
                                                </form>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header">Rules</div>
            <div class="card-body">
                <form action="<?= route('edit-rules'); ?>" method="post">
                    <div class="form-group">
                        <label for="rules">
                            Rules (<a href="https://www.markdownguide.org/basic-syntax/"
                                      target="_blank" rel="nofollow noopener">markdown syntax</a>):
                        </label>
                        <textarea id="rules" name="html" class="form-control p-3 text-monospace"
                        ><?= htmlspecialchars($rules); ?></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                    <script>
                        autosize(document.getElementById('rules'));
                    </script>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card <?= ($canUpload ? 'bg-success' : 'bg-danger'); ?> text-white mb-3">
            <div class="card-header p-3">Activity upload</div>
            <div class="card-body p-3">
                <form method="post" action="<?= route('enable-upload'); ?>">
                    <input type="hidden" name="canUpload" value="<?= ((int)!$canUpload); ?>">
                    <?php if ($canUpload) { ?>
                        <p>Activity upload is enabled.</p>
                        <button type="submit" class="btn p-1 btn-danger btn-block">Disable upload</button>
                        <?php if ($challenge) { ?>
                            <div class="pt-3">
                                <small>
                                    From: <?= $challenge->openFrom->format('d.m.Y H:i T'); ?><br>
                                    Until: <?= $challenge->openUntil->format('d.m.Y H:i T'); ?><br>
                                </small>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p>Activity upload is disabled.</p>
                        <button type="submit" class="btn p-1 btn-success btn-block">Enable upload</button>
                    <?php } ?>
                </form>
            </div>
        </div>

        <?php $this->insert('admin/_announcement'); ?>

        <div class="card mb-3">
            <?php
            $teamlessUsers = $findUsers(null, true);
            ?>
            <div class="card-header">Without teams (<?= count($teamlessUsers); ?>)</div>
            <div class="card-body p-1">
                <?php
                if (!$teamlessUsers) {
                    ?><em class="text-muted">&ndash;</em><?php
                } else {
                    ?>
                    <form action="<?= route('assign-team'); ?>" method="post">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($teamlessUsers as $u) { ?>
                                <li class="list-group-item p-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="userIds[]"
                                               id="user<?= $u->id; ?>" value="<?= $u->id; ?>">
                                        <label class="custom-control-label d-block" for="user<?= $u->id; ?>">
                                            <span title="<?= htmlspecialchars($u->email); ?>">
                                                <?= htmlspecialchars($u->name); ?>
                                            </span>
                                            <a href="javascript:" class="impersonate" data-id="<?= $u->id; ?>"></a>
                                            <a href="javascript:" class="password" data-id="<?= $u->id; ?>"></a>
                                            <a href="javascript:" class="participating" data-id="<?= $u->id; ?>"></a>
                                        </label>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                        <select name="teamId" class="custom-select custom-select-sm mt-2">
                            <option value="0">-- Select a team or action --</option>
                            <option value="-1">Set as not participating</option>
                            <?php foreach ($teams as $team) { ?>
                                <option value="<?= $team->id; ?>">Add to "<?= htmlspecialchars($team->name); ?>"</option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm btn-block mt-2">Assign to a team
                        </button>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>

        <div class="card">
            <?php
            $notParticipating = $findUsers(null, false);
            ?>
            <div class="card-header">Not participating (<?= count($notParticipating); ?>)</div>
            <div class="card-body p-1">
                <?php
                if (!$notParticipating) {
                    ?><em class="text-muted">&ndash;</em><?php
                } else {
                    ?>
                    <form action="<?= route('set-participating'); ?>" method="post">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notParticipating as $u) { ?>
                                <li class="list-group-item p-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="userIds[]"
                                               id="user<?= $u->id; ?>" value="<?= $u->id; ?>">
                                        <label class="custom-control-label d-block" for="user<?= $u->id; ?>">
                                            <span title="<?= htmlspecialchars($u->email); ?>">
                                                <?= htmlspecialchars($u->name); ?>
                                            </span>
                                            <a href="javascript:" class="impersonate" data-id="<?= $u->id; ?>"></a>
                                            <a href="javascript:" class="password" data-id="<?= $u->id; ?>"></a>
                                        </label>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                        <input type="hidden" name="isParticipating" value="1">
                        <button type="submit" class="btn btn-primary btn-sm btn-block mt-2">
                            Set as participating
                        </button>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<form class="invisible" method="post" action="<?= route('impersonate'); ?>" id="form-impersonate">
    <input type="hidden" name="userId" id="impersonate">
</form>

<form class="invisible" method="post" action="<?= route('set-participating'); ?>" id="form-participating">
    <input type="hidden" name="userIds[]" value="0" id="set-participating">
    <input type="hidden" name="isParticipating" value="0">
</form>

<form class="invisible" method="post" action="<?= route('reset-password'); ?>" id="form-password">
    <input type="hidden" name="userId" id="password">
</form>

<script>
    document.querySelectorAll('a.impersonate').forEach(function (a) {
        a.className += ' btn btn-link btn-sm p-0 m-0 ml-1';
        a.innerHTML = '👮';
        a.title = 'Impersonate';
        a.style.textDecoration = 'none';

        a.addEventListener('click', function (event) {
            if (confirm('Impersonate?')) {
                document.getElementById('impersonate').value = event.target.dataset.id;
                document.getElementById('form-impersonate').submit();
            }
        });
    });

    document.querySelectorAll('a.participating').forEach(function (a) {
        a.className += ' btn btn-link btn-sm p-0 m-0 ml-1';
        a.innerHTML = '⏸️';
        a.title = 'Set as not participating';
        a.style.textDecoration = 'none';

        a.addEventListener('click', function (event) {
            document.getElementById('set-participating').value = event.target.dataset.id;
            document.getElementById('form-participating').submit();
        });
    });

    document.querySelectorAll('a.password').forEach(function (a) {
        a.className += ' btn btn-link btn-sm p-0 m-0 ml-1';
        a.innerHTML = '🔓️';
        a.title = 'Reset password';
        a.style.textDecoration = 'none';

        a.addEventListener('click', function (event) {
            if (!confirm('Reset password?')) {
                return;
            }
            document.getElementById('password').value = event.target.dataset.id;
            document.getElementById('form-password').submit();
        });
    });
</script>