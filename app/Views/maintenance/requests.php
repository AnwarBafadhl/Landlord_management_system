<?= $this->extend('layouts/maintenance') ?>
<?= $this->section('title') ?>Page Title<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <h1>Page Coming Soon</h1>
    <p>This feature is under development.</p>
    <a href="<?= site_url('maintenance/dashboard') ?>" class="btn btn-primary">Back to Dashboard</a>
</div>
<?= $this->endSection() ?>