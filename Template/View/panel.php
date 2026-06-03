<?php
// Global View Context — guaranteed access to $config, $auth, $locale, etc.
use LaswitchTech\Core\ViewGlobals;
ViewGlobals::apply();
?>
<?php if(!$config->get('application','maintenance') || $auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="panel">
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
            <div id="app" class="app d-flex">
                <!-- Controls -->
                <div id="controls" class="d-flex position-fixed bottom-0 end-0 mb-3 me-3" style="z-index:1041;">
                    <!-- Back to Top -->
                    <button type="button" class="back-to-top btn btn-lg btn-primary"><i class="bi bi-arrow-up"></i></button>
                </div>

                <!-- Sidebar -->
                <aside id="sidebar" class="sidebar border-end">
                    <div class="sidebar-header d-flex align-items-center justify-content-end">
                        <button class="btn btn-link d-lg-none mt-3 me-3" id="sidebarClose" aria-label="Close sidebar"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <!-- Branding -->
                    <a class="sidebar-brand d-flex flex-column justify-content-center align-items-center py-4 fs-4 text-decoration-none" href="/">
                        <img class="logo" src="/logo" alt="Logo">
                        <h4 class="brand m-0 mt-2 fs-2"><?= $config->get('application','name') ?></h4>
                    </a>

                    <!-- Navigations -->
                    <?php if($config->get('application','show_nav_title')): ?>
                        <div class="border border-start-0 border-end-0 p-2 px-3 mb-2"><?= $locale->get('Main Navigation') ?></div>
                    <?php endif; ?>
                    <?= $this->Helper->Core->menu($menu->menu('sidebar-main',null,3)); ?>
                    <?php if($auth->isAuthorized("Administration",1)): ?>
                        <?php if($config->get('application','show_nav_title')): ?>
                            <div class="border border-start-0 border-end-0 p-2 px-3 mb-2"><?= $locale->get('Administration') ?></div>
                        <?php endif; ?>
                        <?= $this->Helper->Core->menu($menu->menu('sidebar-admin',null,3)); ?>
                    <?php endif; ?>
                    <?php if($auth->isAuthorized("Development",1)): ?>
                        <?php if($config->get('application','show_nav_title')): ?>
                            <div class="border border-start-0 border-end-0 p-2 px-3 mb-2"><?= $locale->get('Development') ?></div>
                        <?php endif; ?>
                        <?= $this->Helper->Core->menu($menu->menu('sidebar-dev',null,3)); ?>
                    <?php endif; ?>
                </aside>

                <!-- Main Column -->
                <div class="app-main flex-grow-1 d-flex flex-column">
                    <!-- Navbar with sidebar toggle -->
                    <nav class="navbar border-bottom sticky-top shadow">
                        <div class="container-fluid">
                            <!-- Sidebar Toggle Button -->
                            <button id="sidebarToggle" class="btn btn-link" type="button" aria-controls="sidebar" aria-expanded="true" aria-label="Toggle sidebar"><i class="bi bi-list fs-3"></i></button>

                            <!-- Nav - Crumbs -->
                            <div class="nav align-items-center d-none d-lg-flex">
                                <?= $this->Helper->Core->crumbs(); ?>
                            </div>

                            <!-- Nav - Widgets -->
                            <?php require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php'; ?>
                        </div>
                    </nav>

                    <!-- Content -->
                    <main class="content p-0 container-fluid">

                        <!-- Page Title and Breadcrumbs -->
                        <nav class="navbar align-items-center border-bottom shadow">
                            <h2 class="m-0 mt-2 me-auto" id="pageTitle">
                                <i class="bi bi-<?= $this->icon() ?> me-1"></i>
                                <span>
                                    <?php if(is_null($request->getParams('GET','query'))): ?>
                                        <?= $locale->get($this->label()); ?><?php if(!is_null($request->getParams('GET','name'))): ?>: <?= $request->getParams('GET','name') ?><?php elseif(!is_null($request->getParams('GET','id'))): ?>: <?= $request->getParams('GET','id') ?><?php endif; ?>
                                    <?php else: ?>
                                        <?= $locale->get('Search Results'); ?>: <?= $request->getParams('GET','query') ?>
                                    <?php endif; ?>
                                </span>
                            </h2>
                            <nav class="d-none d-lg-flex">
                                <ol id="breadcrumbs" class="breadcrumb user-select-none"></ol>
                            </nav>
                        </nav>

                        <!-- Page Content -->
                        <section class="app-content">
                            <?php if(is_null($request->getParams('GET','query'))): ?>
                                <?php require_once $this->view(); ?>
                            <?php else: $this->interrupt()->Router->render('search'); endif; ?>
                        </section>
                    </main>

                    <footer class="copyright border-top small text-muted py-3 text-center cursor-pointer">
                        <?= $locale->get('Copyright'); ?> &copy; <?= $config->get('application','copyright') ?>-<?= date("Y") ?> <?= $config->get('application','owner')?> <?= $locale->get('All rights reserved'); ?>.
                    </footer>
                </div>

                <!-- Backdrop for mobile drawer behavior -->
                <div class="sidebar-backdrop"></div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
