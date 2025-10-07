<?php if(!$this->Config->get('application','maintenance') || $this->Route === '503'): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="fullscreen">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= $this->Locale->get($this->label()); ?></title>
            <!-- ======= Load Global CSS ======= -->
            <?= $this->Builder->css(); ?>
            <!-- ======= Load Global JS ======= -->
            <?= $this->Builder->js(); ?>
        </head>
        <body>
            <!-- App Layout -->
            <div id="app" class="app">
                <!-- Main Column -->
                <div class="app-main">
                    <!-- Content -->
                    <main class="content">
                        <!-- Page Content -->
                        <div class="app-content">
                            <section class="app-503">
                                <div class="container">
                                    <div class="card shadow"><?php require_once $this->view(); ?></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
