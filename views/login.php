<?php
/**
 * @var string $resetKey
 * @var string $email
 */
?>
<?php $this->layout('layout', ['title' => 'Register or Sign in'] + $this->data) ?>

<div class="row mt-5">
    <div class="col"></div>
    <div class="col">
        <h2>Registration</h2>
        <form method="post" action="<?= route('register'); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email); ?>"
                       id="email"
                       aria-describedby="emailHelp"
                       placeholder="Email"
                    <?= ($email ? 'readonly' : ''); ?>
                >
                <small id="emailHelp" class="form-text text-muted">Won't be shown to others.</small>
            </div>
            <div class="form-group">
                <label for="password"><?= ($resetKey ? 'Your new Password' : 'Password'); ?>:</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
            </div>
            <?php if (!$resetKey) { ?>
                <div class="form-group">
                    <label for="name">Your name (if you here the first time):</label>
                    <input type="text" class="form-control" name="name" id="name" aria-describedby="emailHelp"
                           placeholder="Your name">
                    <small class="form-text text-muted">Will be visible to others.</small>
                </div>
            <?php } ?>
            <input type="hidden" name="resetKey" value="<?= htmlspecialchars($resetKey); ?>">
            <button type="submit" class="btn btn-success btn-block">
                <?= ($resetKey ? 'Set password' : 'Sign In or Register'); ?>
            </button>
        </form>
    </div>
    <div class="col"></div>
</div>

<!--
<? htmlspecialchars(print_r($_GET)); ?>
-->