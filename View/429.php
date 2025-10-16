<div class="card-body p-5">
    <div class="d-flex flex-column align-items-center justify-content-center">
        <h1 class="fw-bold m-0" style="font-size: calc(var(--bs-body-font-size) * 5)">429</h1>
    </div>
    <div class="d-flex align-items-center justify-content-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="border-0" viewBox="0 0 465 210" role="img" aria-labelledby="title">
            <title id="title">Bob asks you to slow down.</title>
            <defs>
                <filter id="drop" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="6"/>
                    <feOffset dx="0" dy="6" result="off"/>
                    <feColorMatrix in="off" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 .25 0"/>
                    <feBlend in="SourceGraphic" mode="normal"/>
                </filter>
                <pattern id="stripes" width="20" height="20" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                    <rect width="10" height="20" fill="var(--bs-warning)"/>
                    <rect x="10" width="10" height="20" fill="var(--bs-dark)"/>
                </pattern>
            </defs>
            <g transform="translate(232,110)" filter="url(#drop)">
                <g class="bob">
                    <ellipse cx="0" cy="75" rx="64" ry="16" fill="#000" opacity=".1"/>
                    <path transform="scale(7) translate(-8,-3)" fill="var(--bs-light)" d="M7.03 1.88 c.252-1.01 1.688-1.01 1.94 0 l2.905 11.62 H4.125 Z"/>
                    <path transform="scale(7) translate(-8,-3)" fill="var(--bs-orange)" d="m9.97 4.88.953 3.811C10.159 8.878 9.14 9 8 9s-2.158-.122-2.923-.309L6.03 4.88C6.635 4.957 7.3 5 8 5s1.365-.043 1.97-.12m-.245-.978L8.97.88C8.718-.13 7.282-.13 7.03.88L6.275 3.9C6.8 3.965 7.382 4 8 4s1.2-.036 1.725-.098m4.396 8.613a.5.5 0 0 1 .037.96l-6 2a.5.5 0 0 1-.316 0l-6-2a.5.5 0 0 1 .037-.96l2.391-.598.565-2.257c.862.212 1.964.339 3.165.339s2.303-.127 3.165-.339l.565 2.257z"/>
                    <circle cx="-10" cy="22" r="4" fill="var(--bs-dark)"/>
                    <circle cx="10" cy="22" r="4" fill="var(--bs-dark)"/>
                    <circle cx="-9" cy="21" r="1" fill="var(--bs-light)"/>
                    <circle cx="11" cy="21" r="1" fill="var(--bs-light)"/>
                    <rect x="-6" y="32" width="12" height="3" rx="2" fill="var(--bs-dark)"/>
                </g>
                <g class="bubble">
                    <path transform="scale(4) translate(-8,-25)" fill="var(--bs-warning)" d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                    <g transform="scale(2) translate(-8,-45)">
                        <path fill="var(--bs-secondary)" d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/>
                    </g>
                </g>
            </g>
        </svg>
    </div>
    <div class="d-flex flex-column align-items-center justify-content-center">
        <p class="lead mb-1"><?= $this->Locale->get("Easy there! You've hit the speed limit.") ?></p>
        <p class="opacity-50 text-center mb-4">
            <?= $this->Locale->get("Bob says you've sent too many requests in a short time. Please wait a moment and try again.") ?>
        </p>
    </div>
    <div class="d-flex gap-3 justify-content-center">
        <button class="btn btn-outline-orange" type="button" onclick="window.history.back();"><i class="bi bi-arrow-left me-1"></i><?= $this->Locale->get('Go Back') ?></button>
        <button class="btn btn-outline-primary" type="button" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i><?= $this->Locale->get('Retry') ?></button>
        <a class="btn btn-outline-secondary" href="/"><i class="bi bi-house me-1"></i><?= $this->Locale->get('Home') ?></a>
        <a class="btn btn-outline-dark" href="/support"><i class="bi bi-life-preserver me-1"></i><?= $this->Locale->get('Contact Support') ?></a>
    </div>
</div>
