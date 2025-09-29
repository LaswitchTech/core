<div class="nav nav-pills flex-grow-1 align-items-center justify-content-end widgets">
    <!-- Search Field -->
    <div id="searchField" class="ms-3 <?php if(is_null($this->Request->getParams('GET','query'))){ echo 'd-none'; } ?> nav-item flex-grow-1">
        <form class="d-flex" method="get" autocomplete="on" novalidate>
            <input type="text" name="query" class="form-control search" placeholder="<?= $this->Locale->get('Search'); ?>..." aria-label="<?= $this->Locale->get('Search'); ?>" value="<?= $this->Request->getParams('GET','query') ?>">
        </form>
    </div>
    <div id="searchBtn" class="nav-item">
        <button type="button" class="nav-link text-decoration-none animate-pulse-hover">
            <i class="bi bi-search fs-4"></i>
        </button>
    </div>

    <!-- FullScreen -->
    <div class="nav-item d-md-block d-none">
        <button id="fullscreenToggle" type="button" class="nav-link text-decoration-none animate-pulse-hover">
            <i class="bi bi-fullscreen fs-4"></i>
        </button>
    </div>

    <!-- Plugin's Widgets -->
    <?php $this->hook("widgets"); ?>

    <!-- App Drawer -->
    <?php if($this->Auth && $this->Auth->isAuthenticated()): ?>
        <?php if(count($this->Builder->menu('apps')) > 0): ?>
            <div class="nav-item app-drawer">
                <div class="dropdown">
                    <button class="nav-link text-decoration-none py-2 animate-pulse-hover" type="button" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fs-4 bi bi-grid"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <div>
                            <h5 class="py-2 px-3 m-0 cursor-default d-flex justify-content-center align-items-center">
                                <span><?= $this->Locale->get('Apps') ?></span>
                            </h5>
                        </div>
                        <div class="row row-cols-3 m-0 p-2">
                            <?php foreach($this->Builder->menu('apps') as $route => $nav): ?>
                                <?php if (strpos($route, '.') !== false) { $url = 'https://'.$nav['link']; } else { $url = $nav['link']; } ?>
                                <div class="col">
                                    <a href="<?= $url ?>">
                                        <i class="bi bi-<?= $nav['icon'] ?> fs-3"></i>
                                        <span class="text-wrap text-center"><?= $this->Locale->get($nav['label']); ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Profile -->
    <?php if($this->Auth && $this->Auth->isAuthenticated()): ?>
        <div class="nav-item">
            <div class="dropdown profile-drawer">
                <button id="profileMenu" type="button" class="nav-link text-decoration-none ms-2 p-0 animate-pulse-hover" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="/avatar?username=<?= $this->Auth->user()->username ?>" alt="avatar" width="48" height="48" class="rounded-circle">
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileMenu" style="min-width:350px;max-width:500px;">
                    <div class="py-2">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <div class="position-relative rounded-circle border border-3 my-2">
                                <img src="/avatar?username=<?= $this->Auth->user()->username ?>" alt="avatar" class="rounded-circle" style="max-height: 122px; max-width: 122px; height: 122px; width: 122px; object-fit: contain; object-position: center;">
                            </div>
                            <div>
                                <h5><?= $this->Auth->user()->vcard['name'] ?></h5>
                            </div>
                        </div>
                    </div>
                    <?php foreach($this->Builder->menu('user') as $route => $nav): ?>
                        <li>
                            <a class="dropdown-item" href="<?= $nav['link'] ?>">
                                <i class="bi bi-<?= $nav['icon'] ?> me-1"></i>
                                <span><?= $this->Locale->get($nav['label']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="dropstart">
                        <button class="dropdown-item" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
                            <i class="bi bi-circle-half me-2" aria-hidden="true"></i>
                            <span><?= $this->Locale->get('Dark Mode') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                                    <i class="bi bi-sun-fill me-2" aria-hidden="true"></i>
                                    <span>Light</span>
                                    <i class="bi bi-check2 ms-auto d-none" aria-hidden="true"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                                    <i class="bi bi-moon-stars-fill me-2" aria-hidden="true"></i>
                                    <span>Dark</span>
                                    <i class="bi bi-check2 ms-auto d-none" aria-hidden="true"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="true">
                                    <i class="bi bi-circle-half me-2" aria-hidden="true"></i>
                                    <span>Auto</span>
                                    <i class="bi bi-check2 ms-auto d-none" aria-hidden="true"></i>
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li class="dropstart">
                        <button class="dropdown-item" type="button" aria-expanded="false" data-bs-toggle="dropdown">
                            <i class="bi bi-globe-americas me-2" aria-hidden="true"></i>
                            <span><?= $this->Locale->get('Language') ?></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach($this->Locale->list() as $locale => $language): ?>
                                <li>
                                    <a href="?locale=<?= $locale ?>" class="dropdown-item d-flex align-items-center <?php if($locale === $this->Locale->current('name')){ echo 'active'; } ?>" aria-pressed="false">
                                        <i class="bi bi-globe-americas me-2" aria-hidden="true"></i>
                                        <span><?= $language ?></span>
                                        <i class="bi bi-check2 ms-auto <?php if($locale !== $this->Locale->current('name')){ echo 'd-none'; } ?>" aria-hidden="true"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <div><hr class="dropdown-divider"></div>
                    <li>
                        <a class="dropdown-item" href="?signout">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            <span><?= $this->Locale->get('Sign Out'); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sign In -->
    <?php if($this->Auth && !$this->Auth->isAuthenticated()): ?>
        <div class="nav-item ms-2">
            <a href="/signin?redirect=<?= $this->Request->getNamespace() ?>" class="btn btn-outline-light my-1 px-2"><?= $this->Locale->get('Sign in'); ?></a>
        </div>
    <?php endif; ?>
</div>
