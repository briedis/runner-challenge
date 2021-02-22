<?php
/**
 * @var UserModel[] $people
 */

use App\Models\UserModel;

?>
<div class="card">
    <div class="card-header">Members</div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php foreach ($people as $person) { ?>
                <li class="list-group-item"><?= htmlspecialchars($person->name); ?></li>
            <?php } ?>
        </ul>
    </div>
</div>