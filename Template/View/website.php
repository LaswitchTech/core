<?php if(!$this->Config->get('application','maintenance') || $this->Auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="website">
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
                        <div class="app-header">

                            <!-- Navbar -->
                            <nav class="navbar">
                                <div class="container-fluid">
                                    <!-- Main Navigation -->
                                    <ul class="nav">
                                        <?php foreach($this->Builder->menu('topbar') as $route => $nav): ?>
                                            <?php if($route === $this->Route): ?>
                                                <li class="nav-item"><a href="<?= $nav['link'] ?>" class="nav-link active" aria-current="page"><?= $this->Locale->get($nav['label']); ?></a></li>
                                            <?php else: ?>
                                                <li class="nav-item"><a href="<?= $nav['link'] ?>" class="nav-link"><?= $this->Locale->get($nav['label']); ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>

                                    <!-- Nav - Widgets -->
                                    <?php require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'widgets.php'; ?>
                                </div>
                            </nav>
                            <header>

                                <!-- Branding -->
                                <a href="/">
                                    <img src="/logo" alt="Logo">
                                    <h1><?php echo $this->Config->get('application','name') ?></h1>
                                </a>

                                <!-- Page Navigation -->
                                <ul id="page-nav" class="nav nav-pills">
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

                        <!--  Footer  -->
                        <footer class="app-footer">
                            <div class="row">
                                <?php foreach($this->Builder->menu('topbar') as $route => $nav): ?>
                                    <?php $menu = $this->Builder->menu('topnav', $route); ?>
                                    <?php if(empty($menu)): continue; endif; ?>
                                    <div class="col-12 col-md-6 col-lg-2">
                                        <h5><?= $this->Locale->get($nav['label']); ?></h5>
                                        <ul class="nav">
                                            <?php foreach($menu as $lroute => $lnav): ?>
                                                <li class="nav-item"><a href="<?= $route ?><?= $lroute ?>" class="nav-link"><?= $this->Locale->get($lnav['label']); ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <h5><?= $this->Locale->get("Subscribe to our newsletter"); ?></h5>
                                    <p class="text-white-50"><?= $this->Locale->get("Monthly figest of what's new and exciting from us."); ?></p>
                                    <form>
                                        <div class="input-group">
                                            <input type="email" class="form-control" placeholder="<?= $this->Locale->get("Email address"); ?>" aria-label="email">
                                            <button type="submit" class="btn btn-primary"><?= $this->Locale->get("Subscribe"); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center border-top border-secondary p-4 gap-4">
                                    <div class="d-flex flex-column justify-content-center align-items-start flex-grow-1">
                                        <p class="text-white-50"><a href="/copyright"><?= $this->Locale->get('Copyright'); ?></a> &copy; <?= $this->Config->get('application','copyright') ?>-<?= date("Y") ?> <?= $this->Config->get('application','owner')?> <?= $this->Locale->get('All rights reserved'); ?>.</p>
                                        <a href="/" class="d-flex align-items-center mb-3 link-light text-decoration-none">
                                            <img class="me-2" src="/logo" alt="Logo" style="height:64px;">
                                            <h1 class="display-5 fw-lighter m-0"><?php echo $this->Config->get('application','name') ?></h1>
                                        </a>
                                    </div>
                                    <!--  Locale  -->
                                    <div class="dropdown flex-shrink-1">
                                        <button class="btn btn-link py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?= $this->Locale->locale($this->Locale->current())->language(); ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php foreach($this->Locale->list() as $locale => $language): ?>
                                                <?php if($locale === $this->Locale->current()){ continue; } ?>
                                                <li><a class="dropdown-item" href="?locale=<?= $locale ?>"><i class="bi bi-globe-americas me-2"></i><?= $language ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <!--  End Locale  -->
                                    <!-- Socials  -->
                                    <div class="socials d-flex justify-content-center align-items-center gap-3 flex-shrink-1">
                                        <?php $socials = $this->Config->get('application','social'); ?>
                                        <?php foreach($socials as $social => $link): ?>
                                            <a href="<?= $link ?>"><i class="bi bi-<?= $social ?> fs-4"></i></a>
                                        <?php endforeach; ?>
                                    </div>
                                    <!-- End Socials  -->
                                </div>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
