<?php include('view-error-header.php') ?>

<body>
    <section>
        <h1><?= $component ?> - Exception thrown</h1>
        <hr>

        <div class="row">
            <div class="column-1"></div>

            <div class="column-2">
                <p class="desc-1">
                    <span>Type:</span> <?= self::$statusMessageList[$code] ?>
                </p>

                <p class="desc-1">
                    <span>Code:</span> <?= $code ?>
                </p>
            </div>

            <div class="column-3">
                <p class="<?= ($description == "" ? "desc-1" : "") ?>">
                    <span>Message:</span> <?= $msg ?>
                </p>

                <?php if ($description != "") : ?>
                    <p>
                        <span>Description:</span>
                    </p>
                    <p><?= $description ?></p>
                <?php endif; ?>
            </div>

            <div class="column-4"></div>
        </div>
    </section>
</body>

</html>