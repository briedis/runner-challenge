<?php
/**
 * @var TeamModel $team
 */

use App\Models\TeamModel; ?>
<div class="card">
    <div class="card-header">Team Profile</div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body text-center p-1 pt-2">
                        <h4 class="card-title"
                            style="text-transform: none;"><?= htmlspecialchars($team->name); ?></h4>
                    </div>
                    <?php if ($team->imageId) { ?>
                        <img class="card-img-top" src="<?= htmlspecialchars($team->getImageUrl()); ?>"
                             alt="">
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-7">
                <form action="<?= route('edit-team'); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="team-name">
                            Team name:
                        </label>
                        <input id="team-name" name="teamName" class="form-control" maxlength="40"
                               value="<?= htmlspecialchars($team->name); ?>">
                        <small class="form-text text-muted">Up to 40 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="team-name">Image:</label>

                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image" id="team-image">
                            <label class="custom-file-label" id="team-image-label" for="team-image">
                                Image...
                            </label>
                        </div>
                        <small class="form-text text-muted">A square will be cropped.</small>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
                <script>
                    document.getElementById('team-image').onchange = function () {
                        document.getElementById('team-image-label').textContent = this.files[0].name;
                    };
                </script>
            </div>
        </div>
    </div>
</div>
