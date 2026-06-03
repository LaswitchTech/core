<?php
use LaswitchTech\Core\ViewGlobals;
ViewGlobals::apply();
?>
<?php if(!$config->get('application','maintenance') || $auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="fullscreen">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>
                <?php if(is_null($request->getParams('GET','query'))): ?>
                    <?= $locale->get($this->label()); ?><?php if(!is_null($request->getParams('GET','name'))): ?>: <?= $request->getParams('GET','name') ?><?php elseif(!is_null($request->getParams('GET','id'))): ?>: <?= $request->getParams('GET','id') ?><?php endif; ?>
                <?php else: ?>
                    <?= $locale->get('Search Results'); ?>: <?= $request->getParams('GET','query') ?>
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

                <!-- Controls -->
                <div id="controls" class="d-flex position-fixed bottom-0 end-0 mb-3 me-3" style="z-index:1041;">
                    <!-- Back to Top -->
                    <button type="button" class="back-to-top btn btn-lg btn-primary"><i class="bi bi-arrow-up"></i></button>
                </div>

                <!-- Main Column -->
                <div class="app-main">
                    <!-- Navbar -->
                    <nav class="navbar border-bottom sticky-top shadow">
                        <div class="container-fluid">
                            <!-- Branding -->
                            <a class="brand d-flex justify-content-start align-items-center text-decoration-none" href="/">
                                <img class="logo me-1" src="/logo" alt="Logo">
                                <h4 class="brand m-0 ms-2 fs-2"><?= $config->get('application','name') ?></h4>
                            </a>

                            <!-- Nav - Widgets -->
                            <?php require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php'; ?>
                        </div>
                    </nav>
                    <!-- Content -->
                    <main class="content">
                        <!-- Page Content -->
                        <div class="app-content">
                            <?php if(is_null($request->getParams('GET','query'))): ?>
                                <?php require_once $this->view(); ?>
                            <?php else: $this->interrupt()->Router->render('search'); endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
