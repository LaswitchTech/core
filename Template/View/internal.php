<?php
use LaswitchTech\Core\ViewGlobals;
ViewGlobals::apply();
?>
<?php if(!$config->get('application','maintenance') || $auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="internal">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>
                <?php if(is_null($request->getParams('GET','forgot'))): ?>
                    <?= $locale->get($this->label()); ?>
                <?php else: ?>
                    <?= $locale->get('Reset Password'); ?>
                <?php endif; ?>
            </title>
            <!-- ======= Load Global CSS ======= -->
            <?= $menu->css(); ?>
            <!-- ======= Load Global JS ======= -->
            <?= $menu->js(); ?>
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
                            <section class="app-internal">
                                <div class="container">
                                    <div class="card shadow">
                                        <?php if(is_null($request->getParams('GET','forgot'))): ?>
                                            <?php require_once $this->view(); ?>
                                        <?php else: $this->interrupt()->Router->render('330'); endif; ?>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
