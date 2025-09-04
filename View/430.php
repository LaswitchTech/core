<?php if($this->Config->get('application','installed') && !$this->Auth->isAuthenticated()): ?>
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
                            <section class="app-430">
                                <div class="container">
                                    <div class="row g-0 shadow card flex-row overflow-hidden" style="max-width: 960px; margin-inline: auto;">
                                        <div class="col-lg-6 p-5" style="background: var(--bs-body-bg);">
                                            <div class="mb-4">
                                                <h2 class="h4 mb-1"><?= $this->Locale->get('Sign in') ?></h2>
                                                <p class="opacity-50 mb-0"><?= $this->Locale->get('Use your company account') ?></p>
                                            </div>
                                            <form method="POST" action="/" class="needs-validation" novalidate>
                                                <?= $this->CSRF->field(); ?>
                                                <div class="mb-3">
                                                    <label for="username" class="form-label"><?= $this->Locale->get('Username') ?></label>
                                                    <input type="email" class="form-control" name="username" id="username" placeholder="username@domain.com" required>
                                                    <div class="invalid-feedback"><?= $this->Locale->get('Please enter a valid email.') ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password" class="form-label"><?= $this->Locale->get('Password') ?></label>
                                                    <div class="input-group">
                                                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                                                        <button class="btn btn-outline-secondary rounded-end" type="button" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
                                                        <div class="invalid-feedback"><?= $this->Locale->get('Password is required.') ?></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                                        <label class="form-check-label" for="remember"><?= $this->Locale->get('Keep me signed in') ?></label>
                                                    </div>
                                                    <a href="#"><?= $this->Locale->get('Need help?') ?></a>
                                                </div>
                                                <input class="btn btn-primary w-100" name="signin" value="<?= $this->Locale->get('Continue') ?>" type="submit">
                                            </form>
                                            <hr class="my-4">
                                            <p class="mb-0"><span class="opacity-50"><?= $this->Locale->get('New to the platform?') ?></span> <a href="#"><?= $this->Locale->get('Create an account') ?></a></p>
                                        </div>
                                        <div class="col-lg-6 p-5 d-flex flex-column text-white" style="background: linear-gradient(120deg, var(--bs-primary), var(--bs-dark));">
                                            <div class="d-flex align-items-center gap-2 mb-4">
                                                <img src="/logo" alt="<?= $this->Config->get('application','name'); ?>" style="max-height: 4rem; max-width: 4rem;">
                                                <h3 class="fw-lighter m-0"><?= $this->Config->get('application','name'); ?></h3>
                                            </div>
                                            <div class="mt-auto">
                                                <h3 class="h4 fw-light"><?= $this->Locale->get($this->Config->get('application','slogan') ?? ''); ?></h3>
                                                <p class="opacity-50"><?= $this->Locale->get($this->Config->get('application','tagline') ?? ''); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <script>
                                // Bootstrap validation
                                (() => {
                                    const forms = document.querySelectorAll('.needs-validation');
                                    Array.from(forms).forEach(form => {
                                        form.addEventListener('submit', event => {
                                        if (!form.checkValidity()) {
                                            event.preventDefault();
                                            event.stopPropagation();
                                        }
                                        form.classList.add('was-validated');
                                        }, false);
                                    });
                                })();
                                // Password visibility toggle helper
                                function togglePassword(inputId, btn) {
                                    const input = document.getElementById(inputId);
                                    const icon = btn.querySelector('i');
                                    if (input.type === 'password') {
                                        input.type = 'text';
                                        icon.classList.remove('bi-eye');
                                        icon.classList.add('bi-eye-slash');
                                    } else {
                                        input.type = 'password';
                                        icon.classList.remove('bi-eye-slash');
                                        icon.classList.add('bi-eye');
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
