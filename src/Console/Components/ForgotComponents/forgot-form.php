<?php include('header.php') ?>

<main class="form-signin">
    <form action="<?= url('forgot.post'); ?>" method="POST">
        <h1 class="mb-3 fw-normal"><?= $title ?></h1>

        <?= csrf_token() ?>

        <?php if ($msg) : ?>
            <div class="alert alert-info" role="alert">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="form-floating">
            <input type="email" class="form-control" id="floatingInput" name="email" placeholder="name@example.com">
            <label for="floatingInput">Email address</label>
        </div>

        <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">
            <span class="material-icons">send</span> Send
        </button>

        <p class="mt-5 mb-3 text-muted">&copy; 2020 - <?= date('Y') ?></p>
    </form>
</main>

<?php include('footer.php') ?>