<?php
/**
 * @var bool $canUpload
 * @var ChallengeModel|null $challenge
 */

use App\Models\ChallengeModel;
use App\Models\UserModel;
use App\Services\Storage;

/**
 * @var ?ChallengeModel $challenge
 * @var UserModel $user
 */

?>

<?php
if ($challenge && !$challenge->isOpen()) {
    ?>
    <div class="card mb-3">
        <div class="card-header text-danger">Challenge has not started yet</div>
        <div class="card-body">
            <p>
                Starts: <strong><?= $challenge->openFrom->format('d.m.Y H:i T'); ?></strong>
            </p>
            <form method="post" action="<?= route('participate'); ?>">
                <?php
                if ($user->isParticipating) { ?>
                    <p class="text-success">
                        You are participating in this challenge.
                    </p>
                    <button type="submit" class="btn btn-sm btn-danger btn-block">
                        I DON'T want to participate
                    </button>
                    <?php
                } else { ?>
                    <p class="text-danger">
                        You are not participating in this challenge.
                    </p>
                    <button type="submit" class="btn btn-sm btn-success btn-block">
                        I want to participate
                    </button>
                    <?php
                } ?>
            </form>
        </div>
    </div>
    <?php
}

if (!$user->isParticipating) {
    return;
}

?>

<div class="card">
    <div class="card-header">Log an activity</div>
    <div class="card-body pt-3">
        <?php
        if ($challenge->allowManualInput) { ?>
            <div class="mb-3">
                <a href="javascript:" onclick="setWorkoutType('gpx');" id="js--btn-gpx"
                   class="btn btn-block btn-outline-secondary">GPX Upload (Strava, etc.)</a>
                <a href="javascript:" onclick="setWorkoutType('gym');" id="js--btn-gym"
                   class="btn btn-block btn-outline-secondary mt-2">Gym Workout<br>(distance and duration)</a>
            </div>
            <?php
        }
        ?>
        <form action="<?= route('upload'); ?>" method="post" enctype="multipart/form-data" id="gpx-form">
            <div class="p-3 <?= ($challenge->allowManualInput ? 'd-none' : ''); ?>" id="js--form-gpx"
                 style="border:1px solid #cccccc;">
                <h5>Upload a GPX file from your activity app</h5>
                <div class="form-group">
                    <label for="customFile">Activity file (GPX):</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="gpx" id="file-gpx">
                        <label class="custom-file-label" id="file-gpx-label" for="file-gpx">
                            Select a file...
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        How to export from
                        <a href="https://support.strava.com/hc/en-us/articles/216918437-Exporting-your-Data-and-Bulk-Export#GPX"
                           rel="nofollow noopener" target="_blank">Strava</a>
                    </small>
                </div>
                <div class="form-group mb-0">
                    <label for="activityUrl">Activity URL:</label>
                    <input type="url" class="form-control" name="activityUrl" id="activityUrl">
                    <small class="form-text text-muted">Strava activity page (only if activity is public).</small>
                </div>
            </div>
            <?php
            if ($challenge->allowManualInput) { ?>
                <div class="p-3 mt-3 d-none" id="js--form-gym"
                     style="border:1px solid #cccccc;">
                    <h5>Manually enter the distance (Gym workout)</h5>
                    <div class="form-group">
                        <label for="distance">Distance in <u>kilometers</u>:</label>
                        <input type="number" step="0.01" class="form-control" name="distance" id="distance">
                        <small class="form-text text-muted">If you don't have a GPX file, you can enter distance
                            here</small>
                    </div>
                    <label>Duration of the workout:</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <select name="durationHours" id="duration-hours" class="form-control">
                                    <option value="0">Hours</option>
                                    <?php
                                    for ($i = 0; $i < 24; $i++) { ?>
                                        <option value="<?= $i; ?>"><?= $i; ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <select name="durationMinutes" id="duration-minutes" class="form-control">
                                    <option value="0">Minutes</option>
                                    <?php
                                    for ($i = 0; $i < 60; $i++) { ?>
                                        <option value="<?= $i; ?>"><?= $i; ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } ?>
            <div class="p-3 mt-3 mb-3" style="border:1px solid #cccccc;">
                <h5>add some additional info for others to see</h5>
                <div class="form-group">
                    <label for="comment">Short comment (optional):</label>
                    <input type="text" class="form-control" name="comment" id="comment">
                    <small class="form-text text-muted">Others will see this, make it motivational!</small>
                </div>
                <div class="form-group">
                    <label for="file-photo">
                        Upload a photo
                        (optional, max size <?= Storage::getMaxUploadSize(); ?>M):
                    </label>
                    <div class="custom-file overflow-hidden">
                        <input type="file" class="custom-file-input" name="photo" id="file-photo">
                        <label class="custom-file-label" id="file-photo-label" for="file-photo">
                            Select a photo...
                        </label>
                    </div>
                </div>
            </div>
            <?php
            if ($challenge->isPlogging) { ?>
                <div id="js--block-plogging" class="p-3 mt-3 mb-3 d-none" style="border:1px solid #cccccc;">
                    <h5 class="d-flex justify-content-between align-items-baseline">
                        <span>Plogging</span>
                        <span class="small">Optional</span>
                    </h5>
                    <div class="form-group">
                        <label for="plogging-bags">Gathered amount in shopping bags:</label>
                        <input type="number" step="1" min="0" value="0" class="form-control" name="plogging-bags" id="plogging-bags">
                        <small class="form-text text-muted"></small>
                    </div>

                    <div class="form-group">
                        <label for="plogging-photo">
                            Upload a photo of proof
                            (max size <?= Storage::getMaxUploadSize(); ?>M):
                        </label>
                        <div class="custom-file overflow-hidden">
                            <input type="file" class="custom-file-input" name="plogging-photo" id="plogging-photo">
                            <label class="custom-file-label" id="plogging-photo-label" for="plogging-photo">
                                Select a photo...
                            </label>
                        </div>
                    </div>
                </div>
                <?php
            }
            if ($canUpload) { ?>
                <div class="text-right">
                    <button id="gpx-upload-button" type="submit" class="btn btn-success btn-block">Log Activity</button>
                </div>
                <?php
            } else { ?>
                <div class="alert alert-danger p-2 text-center mb-0">
                    <small>Activities cannot be logged at this moment!</small>
                </div>
                <?php
            } ?>
            <input type="hidden" name="type" id="js--type" value="<?= ($challenge->allowManualInput ? '' : 'gpx'); ?>"/>
        </form>
        <script>
            const inputUploadType = document.getElementById('js--type');
            const inputFile = document.getElementById('file-photo');

            // Disable the upload button during submit to prevent accidental double-submits
            document.getElementById('gpx-form').onsubmit = function () {

                if (inputUploadType.value === 'gym' && !inputFile.value) {
                    alert('You need to provide a proof photo of your gym activity!')
                    return false;
                }


                document.getElementById('gpx-upload-button').setAttribute('disabled', 'true');
                document.getElementById('gpx-upload-button').classList.add('disabled');
                document.getElementById('gpx-upload-button').innerHTML = 'Uploading…';
            };

            document.getElementById('file-gpx').onchange = function () {
                document.getElementById('file-gpx-label').textContent = this.files[0].name;
            };

            document.getElementById('file-photo').onchange = function () {
                document.getElementById('file-photo-label').textContent = this.files[0].name;
            };

            document.getElementById('plogging-photo').onchange = function () {
                document.getElementById('plogging-photo-label').textContent = this.files[0].name;
            };

            function setWorkoutType(type) {
                const btnGpx = document.getElementById('js--btn-gpx');
                const btnGym = document.getElementById('js--btn-gym');
                const formGpx = document.getElementById('js--form-gpx');
                const formGym = document.getElementById('js--form-gym');
                const blockPlogging = document.getElementById('js--block-plogging');

                inputUploadType.value = type;

                btnGpx.classList.remove('btn-primary');
                btnGym.classList.remove('btn-primary');
                blockPlogging.classList.remove('d-none');

                formGpx.classList.remove('d-none');
                formGym.classList.remove('d-none');

                if (type === 'gpx') {
                    btnGpx.classList.add('btn-primary');
                    formGym.classList.add('d-none');
                    blockPlogging.classList.remove('d-none');
                }
                if (type === 'gym') {
                    btnGym.classList.add('btn-primary');
                    formGpx.classList.add('d-none');
                    blockPlogging.classList.add('d-none');
                }
            }
        </script>
    </div>
</div>