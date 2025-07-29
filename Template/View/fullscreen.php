<?php if(!$this->Config->get('application','maintenance') || $this->Auth->isAuthorized('Administrator',1)): ?>
    <!doctype html>
    <html lang="en" class="h-100 w-100" data-bs-theme="auto" data-bs-template="fullscreen">
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
        <body>
            <?php require_once $this->view(); ?>
        </body>
    </html>
<?php else: $this->interrupt()->Router->render('503'); endif; ?>
