<div class="card-body p-5">
    <div class="d-flex flex-column align-items-center justify-content-center">
        <h1 class="fw-bold m-0" style="font-size: calc(var(--bs-body-font-size) * 5)">423</h1>
    </div>
    <div class="d-flex align-items-center justify-content-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="border-0" viewBox="0 0 465 210" role="img" aria-labelledby="title">
            <title id="title">Bob found this resource locked.</title>
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
                    <path transform="scale(4) translate(-8,-25)" fill="var(--bs-primary)" d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                    <g transform="scale(2) translate(-8,-45)">
                        <path fill="var(--bs-secondary)" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>
                        <g transform="translate(0,3)">
                            <path fill="var(--bs-light)" d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/>
                        </g>
                    </g>
                </g>
            </g>
        </svg>
    </div>
    <div class="d-flex flex-column align-items-center justify-content-center">
        <p class="lead mb-1"><?= $this->Locale->get("This resource is locked.") ?></p>
        <p class="opacity-50 text-center mb-4">
            <?= $this->Locale->get("Bob can't make changes right now. It may be locked by another user or a workflow rule.") ?>
        </p>
    </div>
    <div class="d-flex gap-3 justify-content-center">
        <button class="btn btn-outline-orange" type="button" onclick="window.history.back();"><i class="bi bi-arrow-left me-1"></i><?= $this->Locale->get('Go Back') ?></button>
        <button class="btn btn-outline-primary" type="button" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i><?= $this->Locale->get('Retry') ?></button>
        <a class="btn btn-outline-secondary" href="/"><i class="bi bi-house me-1"></i><?= $this->Locale->get('Home') ?></a>
        <a class="btn btn-outline-dark" href="/support"><i class="bi bi-life-preserver me-1"></i><?= $this->Locale->get('Contact Support') ?></a>
    </div>
</div>
