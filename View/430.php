<?php if(!$this->Auth->isAuthenticated()): ?>
    <div class="row g-0 flex-row overflow-hidden">
        <div class="col-lg-6 p-5" style="background: var(--bs-body-bg);">
            <div class="mb-4">
                <h2 class="h4 mb-1"><?= $this->Locale->get('Sign in') ?></h2>
                <p class="opacity-50 mb-0"><?= $this->Locale->get('Use your company account') ?></p>
            </div>
            <form method="POST" action="/" class="needs-validation" autocomplete="off" novalidate>
                <?= $this->CSRF->field(); ?>
                <div class="mb-3">
                    <label for="username" class="form-label"><?= $this->Locale->get('Username') ?></label>
                    <input type="email" class="form-control" name="username" id="username" placeholder="username@domain.com" autocomplete="off" required>
                    <div class="invalid-feedback"><?= $this->Locale->get('Please enter a valid email.') ?></div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><?= $this->Locale->get('Password') ?></label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="off" required>
                        <button class="btn btn-outline-secondary rounded-end" type="button" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
                        <div class="invalid-feedback"><?= $this->Locale->get('Password is required.') ?></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember"><?= $this->Locale->get('Keep me signed in') ?></label>
                    </div>
                    <a href="?forgot"><?= $this->Locale->get('Need help?') ?></a>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <button type="submit" class="btn btn-primary flex-grow-1" name="signin"><?= $this->Locale->get('Continue') ?></button>
                </div>
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
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
