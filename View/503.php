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
                            <div class="container" style="max-width: 760px;">
                                <div class="card shadow">
                                    <div class="card-body p-5">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-exclamation-octagon" style="font-size: calc(var(--bs-body-font-size) * 3); color: var(--bs-orange);"></i>
                                            <h1 class="fw-bold m-0" style="font-size: calc(var(--bs-body-font-size) * 4)">503</h1>
                                        </div>
                                        <div class="ps-3">
                                            <p class="lead mb-1"><?= $this->Locale->get("We're down for maintenance.") ?></p>
                                            <p class="opacity-50 mb-4"><?= $this->Locale->get('Please try again in a few minutes. Thanks for your patience!') ?></p>
                                        </div>
                                        <div class="d-flex justify-content-end mb-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="border-0" viewBox="0 0 465 285" role="img" aria-labelledby="title" style="max-width: 380px;">
                                                <title id="title">A code panel with a traffic cone.</title>
                                                <defs>
                                                    <filter id="drop" x="-20%" y="-20%" width="140%" height="140%">
                                                        <feGaussianBlur in="SourceAlpha" stdDeviation="6"/>
                                                        <feOffset dx="0" dy="6" result="off"/>
                                                        <feColorMatrix in="off" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 .25 0"/>
                                                        <feBlend in="SourceGraphic" mode="normal"/>
                                                    </filter>
                                                </defs>
                                                <style>
                                                    .bob { animation: bob 2.2s ease-in-out infinite; transform-origin: center bottom; }
                                                    .blink { animation: blink 1.4s ease-in-out infinite; }
                                                    @keyframes bob { 0%,100%{ transform: translateY(160)} 50%{ transform: translateY(-8px)} }
                                                    @keyframes blink { 0%,100%{ opacity: .25 } 50%{ opacity: 1 } }
                                                </style>
                                                <g transform="translate(70,0)" filter="url(#drop)">
                                                    <rect x="0" y="0" rx="16" ry="16" width="380" height="240" fill="var(--bs-dark)"/>
                                                    <circle cx="22" cy="20" r="6" fill="var(--bs-danger)"/>
                                                    <circle cx="42" cy="20" r="6" fill="var(--bs-warning)"/>
                                                    <circle cx="62" cy="20" r="6" fill="var(--bs-success)"/>
                                                    <rect x="20" y="48" width="220" height="10" rx="5" fill="var(--bs-secondary)"/>
                                                    <rect x="20" y="70" width="280" height="10" rx="5" fill="var(--bs-secondary)"/>
                                                    <rect x="20" y="92" width="190" height="10" rx="5" fill="var(--bs-primary)"/>
                                                    <rect x="20" y="114" width="300" height="10" rx="5" fill="var(--bs-secondary)"/>
                                                    <rect x="20" y="136" width="160" height="10" rx="5" fill="var(--bs-secondary)"/>
                                                    <rect x="20" y="158" width="260" height="10" rx="5" fill="var(--bs-secondary)"/>
                                                    <rect x="20" y="180" width="140" height="10" rx="5" fill="var(--bs-secondary)"/>
                                                    <text x="340" y="212" font-size="48" fill="#9CA3AF" aria-hidden="true">{ }</text>
                                                    <g class="blink" transform="scale(2) translate(162,10)">
                                                        <path fill="var(--bs-warning)" d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                                        <path fill="var(--bs-warning)" d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                                    </g>
                                                    <g class="bob">
                                                        <g transform="translate(0,180)">
                                                            <ellipse cx="0" cy="75" rx="64" ry="16" fill="#000" opacity=".1"/>
                                                            <path transform="scale(7) translate(-8,-3)" fill="var(--bs-light)" d="M7.03 1.88 c.252-1.01 1.688-1.01 1.94 0 l2.905 11.62 H4.125 Z"/>
                                                            <path transform="scale(7) translate(-8,-3)" fill="var(--bs-orange)" d="m9.97 4.88.953 3.811C10.159 8.878 9.14 9 8 9s-2.158-.122-2.923-.309L6.03 4.88C6.635 4.957 7.3 5 8 5s1.365-.043 1.97-.12m-.245-.978L8.97.88C8.718-.13 7.282-.13 7.03.88L6.275 3.9C6.8 3.965 7.382 4 8 4s1.2-.036 1.725-.098m4.396 8.613a.5.5 0 0 1 .037.96l-6 2a.5.5 0 0 1-.316 0l-6-2a.5.5 0 0 1 .037-.96l2.391-.598.565-2.257c.862.212 1.964.339 3.165.339s2.303-.127 3.165-.339l.565 2.257z"/>
                                                            <circle cx="-10" cy="22" r="4" fill="var(--bs-dark)"/>
                                                            <circle cx="10" cy="22" r="4" fill="var(--bs-dark)"/>
                                                            <circle cx="-9" cy="21" r="1" fill="var(--bs-light)"/>
                                                            <circle cx="11" cy="21" r="1" fill="var(--bs-light)"/>
                                                            <rect x="-6" y="32" width="12" height="3" rx="2" fill="var(--bs-dark)"/>
                                                            <g filter="url(#drop)">
                                                                <path transform="scale(3) translate(-8,-25)" fill="var(--bs-light)" d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                                                                <path transform="scale(3) translate(-8,-25)" fill="var(--bs-light)" d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="d-flex gap-3 justify-content-center">
                                            <button class="btn btn-outline-dark" type="button" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i><?= $this->Locale->get('Retry') ?></button>
                                            <a class="btn btn-outline-dark" href="#"><i class="bi bi-activity me-1"></i><?= $this->Locale->get('Status') ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
