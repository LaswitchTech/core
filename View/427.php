<div class="row g-0 flex-row overflow-hidden">
    <div class="col-lg-6 p-5" style="background: var(--bs-body-bg);">
        <div class="mb-4">
            <h2 class="h4 mb-1"><?= $this->Locale->get('Enter your authentication code') ?></h2>
            <p class="opacity-50 mb-0"><?= $this->Locale->get('We sent you a code to your mobile and/or email') ?></p>
        </div>
        <form method="POST" action="<?= $this->Request->getHostAddress() . '/' . $this->Request->getUri() ?>" class="needs-validation" autocomplete="off" novalidate>
            <?= $this->CSRF->field(); ?>
            <div class="mb-3">
                <label for="code" class="form-label"><?= $this->Locale->get('Code') ?></label>
                <input type="text" class="form-control" name="code" id="code" autocomplete="off" placeholder="000000" value="<?=$this->Request->getParams('GET','verify')?>" required>
                <div class="invalid-feedback"><?= $this->Locale->get('Please enter a valid pin.') ?></div>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-3 mt-4 mb-2">
                <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();"><i class="bi bi-arrow-left me-1"></i><?= $this->Locale->get('Go Back') ?></button>
                <button type="submit" class="btn btn-primary flex-grow-1" name="reset"><?= $this->Locale->get('Continue') ?></button>
            </div>
        </form>
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

        // Configure Input Mask
        $('input[name="verify"]').inputmask({
            mask: ["999999"],
            placeholder: " ",
            greedy: false,
            showMaskOnHover: false,
            showMaskOnFocus: true
        });
    })();
</script>
