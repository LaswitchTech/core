<?php if(!$this->Config->get('application','maintenance') || $this->Auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="index">
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
        <body data-bs-spy="scroll" data-bs-target="#page-nav" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true">

            <!-- App Layout -->
            <div id="app" class="app">

                <!-- Controls -->
                <div id="controls" class="d-flex position-fixed bottom-0 end-0 mb-3 me-3" style="z-index:1041;">
                    <!-- Back to Top -->
                    <button type="button" class="back-to-top btn btn-lg btn-primary"><i class="bi bi-arrow-up"></i></button>
                </div>

                <!-- Main Column -->
                <div class="app-main">

                    <!-- Content -->
                    <main class="content">

                        <!-- ======= Header ======= -->
                        <div class="sticky-top shadow">
                            <!-- Navbar -->
                            <nav class="navbar border-bottom text-bg-primary px-5">
                                <div class="container-fluid">
                                    <!-- Main Navigation -->
                                    <ul class="nav me-auto my-2">
                                        <?php foreach($this->Builder->menu('topbar') as $route => $nav): ?>
                                            <?php if($route === $this->Route): ?>
                                                <li class="nav-item"><a href="<?= $nav['link'] ?>" class="nav-link px-2 link-body-emphasis rounded rounded-pill text-bg-light active" aria-current="page"><?= $this->Locale->get($nav['label']); ?></a></li>
                                            <?php else: ?>
                                                <li class="nav-item"><a href="<?= $nav['link'] ?>" class="nav-link px-2 link-body-emphasis"><?= $this->Locale->get($nav['label']); ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>

                                    <!-- Nav - Widgets -->
                                    <?php require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php'; ?>
                                </div>
                            </nav>
                            <header class="d-flex flex-wrap justify-content-center align-items-center px-5 py-4 border-bottom text-bg-dark">

                                <!-- Branding -->
                                <a href="/" class="d-flex justify-content-center align-items-center mb-3 mb-md-0 me-md-auto link-light text-decoration-none">
                                    <img class="me-2" src="/logo" alt="Logo" style="height:64px;">
                                    <h1 class="display-5 fw-lighter m-0"><?php echo $this->Config->get('application','name') ?></h1>
                                </a>

                                <!-- Page Navigation -->
                                <ul id="page-nav" class="nav nav-pills my-1">
                                    <?php $first = true; ?>
                                    <?php foreach($this->Builder->menu('topnav', $this->Route) as $route => $nav): ?>
                                        <?php if($first): ?>
                                            <li class="nav-item"><a href="<?= $route ?>" class="nav-link active" aria-current="page"><?= $this->Locale->get($nav['label']); ?></a></li>
                                        <?php else: ?>
                                            <li class="nav-item"><a href="<?= $route ?>" class="nav-link"><?= $this->Locale->get($nav['label']); ?></a></li>
                                        <?php endif; ?>
                                        <?php $first = false; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </header>
                        </div>

                        <!-- Page Content -->
                        <div class="app-content">
                            <?php if(is_null($this->Request->getParams('GET','query'))): ?>
                                <?php require_once $this->view(); ?>
                            <?php else: $this->interrupt()->Router->render('search'); endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
