<?php

use App\Models\TotalsModel;

$this->layout('dashboard', ['title' => 'People'] + $this->data);
$this->push('card');

/**
 * @var TotalsModel[] $totals
 */

$i = 0;
?>
    <table class="table table-sm">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th colspan="2" scope="col">Name</th>
            <th scope="col">Distance</th>
            <th scope="col">Activities</th>
            <th scope="col">Last Activity</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($totals as $a) { ?>
            <tr>
                <td class="align-middle"><strong><?= ++$i; ?>.</strong></td>
                <td class="pt-2 pb-2 pl-0 pr-0">
                    <?php if ($a->imageUrl) { ?>
                        <a href="<?= htmlspecialchars($a->imageUrl); ?>" target="_blank">
                            <img src="<?= htmlspecialchars($a->imageUrl); ?>" class="img-fluid" width="50">
                        </a>
                    <?php } ?>
                </td>
                <td>
                    <b><?= htmlspecialchars($a->userName); ?></b>
                    <br>
                    <span class="badge p-0"><?= htmlspecialchars($a->teamName); ?></span>
                </td>
                <td><?= $a->getReadableDistance(); ?></td>
                <td class="text-center"><?= $a->activityCount; ?></td>
                <td><?= $a->getReadableLastActivityAt(); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php
$this->end();