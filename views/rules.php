<?php

$this->layout('dashboard', ['title' => 'Rules'] + $this->data);
$this->push('card');

/**
 * @var string $rules
 */


?>
    <div class="text-dark"><?= $rules; ?></div>
<?php

$this->end();