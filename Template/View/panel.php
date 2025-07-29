<?php if(!$this->Config->get('application','maintenance') || $this->Auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="panel">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>
                <?php if(is_null($this->Request->getParams('GET','query'))): ?>
                    <?= $this->Locale->get($this->label()); ?><?php if(!is_null($this->Request->getParams('GET','name'))): ?>: <?= $this->Request->getParams('GET','name') ?><?php elseif(!is_null($this->Request->getParams('GET','id'))): ?>: <?= $this->Request->getParams('GET','id') ?><?php endif; ?>
                <?php else: ?>
                    <?= $this->Locale->get('Search Results'); ?>: <?= $this->Request->getParams('GET','query') ?>
                <?php endif; ?>
            </title>

            <!-- ======= Load Global CSS ======= -->
            <?= $this->Builder->css(); ?>

            <!-- ======= Load Global JS ======= -->
            <?= $this->Builder->js(); ?>
        </head>
        <body data-bs-spy="scroll" data-bs-target="#main-nav" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true" tabindex="0" class="h-100 w-100">

            <!-- ======= Controls ======= -->
            <div id="controls" class="d-flex position-fixed bottom-0 end-0 mb-3 me-3" style="z-index:1041;">

                <!-- ======= Back to Top ======= -->
                <div class="mx-1 back-to-top">
                    <button type="button" class="d-flex align-items-center justify-content-center btn btn-primary shadow py-2">
                        <i class="bi bi-arrow-up my-1" style="font-size:1em;"></i>
                    </button>
                </div>
                <!-- ======= End Back to Top ======= -->

            </div>
            <!-- ======= End Controls ======= -->

            <!-- ======= Main ======= -->
            <main class="h-100">

                <!-- ======= Panel ======= -->
                <div class="container-fluid h-100">
                    <div class="row overflow-x-hidden h-100 onScroll">

                        <!-- ======= Sidebar ======= -->
                        <div class="sidebar vh-100 px-0 pb-4 position-fixed vh-100 overflow-y collapse collapse-horizontal show" id="sidebar">
                            <div class="container-fluid px-0">
                                <a class="sidebar-brand w-100 d-flex flex-column justify-content-center align-items-center py-4 fs-4 text-decoration-none" href="/">
                                    <img class="" src="<?= $this->Builder->logo(); ?>" alt="Logo" style="width: 200px;">
                                    <h4 class="brand m-0 mt-2 fs-2"><?= $this->Config->get('application','name') ?></h4>
                                </a>
                                <div class="bg-secondary text-white border border-start-0 border-end-0 p-2 px-3"><?= $this->Locale->get('Main Navigation') ?></div>
                                <div class="p-2"><?= $this->Helper->Panel->menu($this->Builder->menu('sidebar-main')); ?></div>
                                <?php if($this->Auth->isAuthorized("Administration",1)): ?>
                                    <div class="bg-secondary text-white border border-start-0 border-end-0 p-2 px-3"><?= $this->Locale->get('Administration') ?></div>
                                    <div class="p-2"><?= $this->Helper->Panel->menu($this->Builder->menu('sidebar-admin')); ?></div>
                                <?php endif; ?>
                                <?php if($this->Auth->isAuthorized("Development",1)): ?>
                                    <div class="bg-secondary text-white border border-start-0 border-end-0 p-2 px-3"><?= $this->Locale->get('Development') ?></div>
                                    <div class="p-2"><?= $this->Helper->Panel->menu($this->Builder->menu('sidebar-dev')); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- ======= End Sidebar ======= -->

                        <!-- ======= Main Content ======= -->
                        <div id="content" class="p-0">

                            <!-- ======= Navbar ======= -->
                            <nav id="navbar" class="navbar navbar-expand-md navbar-light px-3">
                                <div class="container-fluid">

                                    <!-- ======= Sidebar Toggler ======= -->
                                    <a id="sidebarToggle" class="cursor-pointer pe-3" role="button" aria-expanded="true" aria-controls="Toggle Sidebar">
                                        <i class="bi bi-list fs-2"></i>
                                    </a>
                                    <!-- ======= End Sidebar Toggler ======= -->

                                    <!-- ======= Nav ======= -->
                                    <div class="nav nav-pills align-items-center d-md-flex d-none">
                                        <?= $this->Helper->Panel->crumbs(); ?>
                                    </div>
                                    <!-- ======= End Nav ======= -->

                                    <!-- ======= Widgets ======= -->
                                    <?php $this->Model->Widgets->insert(); ?>
                                    <!-- ======= End Widgets ======= -->

                                </div>
                            </nav>
                            <!-- ======= End Navbar ======= -->

                            <!-- ======= Page Title and Breadcrumbs ======= -->
                            <div class="row py-2 px-3 mx-0 d-flex align-items-center">
                                <div class="col-md-6 d-flex align-items-center justify-content-start">
                                    <h1 class="m-0" id="pageTitle">
                                        <i class="bi bi-<?= $this->icon() ?> me-1"></i>
                                        <?php if(is_null($this->Request->getParams('GET','query'))): ?>
                                            <?= $this->Locale->get($this->label()); ?><?php if(!is_null($this->Request->getParams('GET','name'))): ?>: <?= $this->Request->getParams('GET','name') ?><?php elseif(!is_null($this->Request->getParams('GET','id'))): ?>: <?= $this->Request->getParams('GET','id') ?><?php endif; ?>
                                        <?php else: ?>
                                            <?= $this->Locale->get('Search Results'); ?>: <?= $this->Request->getParams('GET','query') ?>
                                        <?php endif; ?>
                                    </h1>
                                </div>
                                <div class="col-md-6 justify-content-end d-md-flex d-none">
                                    <nav aria-label="breadcrumb">
                                        <ol id="breadcrumbs" class="breadcrumb user-select-none"></ol>
                                    </nav>
                                </div>
                            </div>
                            <!-- ======= End Page Title and Breadcrumbs ======= -->

                            <!-- ======= Page Content ======= -->
                            <div class="row mt-4 px-3 mx-0 pb-3">
                                <?php if(is_null($this->Request->getParams('GET','query'))): ?>
                                    <?php require_once $this->view(); ?>
                                <?php else: ?>
                                    <?php $this->interrupt(); ?>
                                    <?php require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'search.php' ; ?>
                                <?php endif; ?>
                            </div>
                            <!-- ======= End Page Content ======= -->

                            <!-- ======= Page Footer ======= -->
                            <div class="d-flex justify-content-end align-items-center mt-1 mb-3">
                                <div id="footerCredit" class="card rounded-end-0 flex-shrink-1 d-block px-4 py-3 link-body-emphasis text-decoration-none cursor-pointer">
                                    <?= $this->Locale->get('Copyright'); ?> &copy; <?= $this->Config->get('application','copyright') ?>-<?= date("Y") ?> <?= $this->Config->get('application','owner')?> <?= $this->Locale->get('All rights reserved'); ?>.
                                </div>
                            </div>
                            <!-- ======= End Page Footer ======= -->

                        </div>
                        <!-- ======= End Main Content ======= -->

                    </div>
                </div>
                <!-- ======= End Panel ======= -->

            </main>
            <!-- ======= End Main ======= -->
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
