<div class="card-body p-5">
    <div class="d-flex flex-column align-items-center justify-content-center">
        <h1 class="fw-bold m-0" style="font-size: calc(var(--bs-body-font-size) * 5)">422</h1>
    </div>
    <div class="d-flex align-items-center justify-content-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="border-0" viewBox="0 0 465 210" role="img" aria-labelledby="title">
            <title id="title">Bob found issues with the submitted content.</title>
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
                    <path transform="scale(4) translate(-8,-25)" fill="var(--bs-danger)" d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                    <g transform="scale(2) translate(-8,-45)">
                        <path fill="var(--bs-secondary)" d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                        <path fill="var(--bs-secondary)" d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                        <g transform="scale(0.5) translate(24,16)">
                            <path fill="var(--bs-danger)" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                        </g>
                    </g>
                </g>
            </g>
        </svg>
    </div>
    <div class="d-flex flex-column align-items-center justify-content-center">
        <p class="lead mb-1"><?= $this->Locale->get("We couldn't process that content.") ?></p>
        <p class="opacity-50 text-center mb-4">
            <?= $this->Locale->get("Bob found validation issues. Please review the fields and try again.") ?>
        </p>
    </div>
    <div class="d-flex gap-3 justify-content-center">
        <button class="btn btn-outline-orange" type="button" onclick="window.history.back();"><i class="bi bi-arrow-left me-1"></i><?= $this->Locale->get('Go Back') ?></button>
        <button class="btn btn-outline-primary" type="button" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i><?= $this->Locale->get('Retry') ?></button>
        <a class="btn btn-outline-secondary" href="/"><i class="bi bi-house me-1"></i><?= $this->Locale->get('Home') ?></a>
        <a class="btn btn-outline-dark" href="/support"><i class="bi bi-life-preserver me-1"></i><?= $this->Locale->get('Contact Support') ?></a>
    </div>
</div>
