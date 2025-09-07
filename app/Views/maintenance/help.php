<?= $this->extend('layouts/maintenance') ?>

<?= $this->section('title') ?>Help & Support<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-life-ring"></i> Help & Support
        </h1>
        <small class="text-muted">Need assistance? We're here to help!</small>
    </div>

    <div class="row">
        <!-- Contact Admin Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope"></i> Contact Administrator
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Quick Response:</strong> We typically reply within 24 hours during business days.
                        For urgent site hazards, choose <strong>Urgent</strong>.
                    </div>

                    <?= form_open('maintenance/help/send', ['id' => 'contactForm']) ?>
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a topic...</option>
                                <option value="Work Order Question">Work Order Question</option>
                                <option value="Scheduling Conflict">Scheduling Conflict</option>
                                <option value="Urgent Site/Property Hazard">Urgent Site/Property Hazard</option>
                                <option value="App Bug / Technical Issue">App Bug / Technical Issue</option>
                                <option value="Account / Password">Account / Password</option>
                                <option value="Feature Request">Feature Request</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="customSubjectDiv" style="display:none;">
                        <label for="custom_subject" class="form-label">Custom Subject *</label>
                        <input type="text" class="form-control" id="custom_subject" name="custom_subject"
                            placeholder="Please specify your subject..." maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6"
                            placeholder="Describe your issue or question in detail..." required
                            maxlength="2000"></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/2000 characters
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-question-circle"></i> Frequently Asked Questions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <!-- FAQ 1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                    <i class="fas fa-clipboard-list me-2 text-primary"></i>
                                    How do I accept a pending work order?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Open <strong>Maintenance → Work Orders</strong>, go to the
                                    <strong>Pending Queue</strong> tab, click <em>View</em> to review details,
                                    then click <strong>Accept</strong> and enter the approved cost.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    <i class="fas fa-play me-2 text-info"></i>
                                    When can I click "Start Work"?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    After accepting (status becomes <strong>Approved</strong>), go to
                                    <strong>My Work</strong> and click <strong>Start</strong>. Enter duration;
                                    the system will block your calendar for those days.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    <i class="fas fa-check me-2 text-warning"></i>
                                    What do I need to finish a work order?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    After starting (status <strong>In Progress</strong>), click
                                    <strong>Complete</strong>,
                                    add completion notes, and upload one or more completion images. That’s it.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                    What if an order needs to be cancelled?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    If you’ve accepted an order but can’t proceed, use <strong>Cancel</strong> from
                                    <strong>My Work</strong>. It moves back to <strong>Pending</strong> and remains
                                    listed as <strong>Cancelled</strong> in your work history.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    <i class="fas fa-wallet me-2 text-secondary"></i>
                                    How is the approved cost validated?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="faq5"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    If the landlord set an estimate, your approved cost must be
                                    <strong>≤ estimate + 150 SAR</strong>. If no estimate exists, any cost is allowed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Information Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-envelope"></i> Contact Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="contact-item mb-3">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <strong>Email Support:</strong><br>
                            <a href="mailto:support@maintenancepanel.com">support@maintenancepanel.com</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock text-info me-2"></i>
                            <strong>Response Time:</strong><br>
                            <small class="text-muted">
                                • Normal: 24–48 hours<br>
                                • High: 4–12 hours<br>
                                • Urgent: 1–4 hours
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= site_url('maintenance/dashboard') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                        </a>
                        <a href="<?= site_url('maintenance/requests') ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-list me-2"></i> Work Orders
                        </a>
                        <a href="<?= site_url('maintenance/schedule') ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-calendar-alt me-2"></i> My Schedule
                        </a>
                        <a href="<?= site_url('maintenance/profile') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user-cog me-2"></i> My Profile
                        </a>
                        <a href="<?= site_url('maintenance/help') ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-life-ring me-2"></i> Help $ Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Subject -> Show custom subject if 'Other'
    document.getElementById('subject').addEventListener('change', function () {
        const div = document.getElementById('customSubjectDiv');
        const input = document.getElementById('custom_subject');
        if (this.value === 'Other') {
            div.style.display = 'block';
            input.required = true;
        } else {
            div.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    });

    // Character counter
    document.getElementById('message').addEventListener('input', function () {
        const n = this.value.length;
        const el = document.getElementById('charCount');
        el.textContent = n;
        el.style.color = n > 1800 ? '#e74a3b' : (n > 1500 ? '#f6c23e' : '#858796');
    });

    // Submit via AJAX
    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;

        // Validate custom subject when "Other"
        const subject = document.getElementById('subject').value;
        const customSubject = document.getElementById('custom_subject').value;
        if (subject === 'Other' && !customSubject.trim()) {
            showNotification('Please specify a custom subject when "Other" is selected.', 'error');
            return;
        }

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch('<?= site_url('maintenance/help/send') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showNotification(d.message, 'success');
                    this.reset();
                    document.getElementById('customSubjectDiv').style.display = 'none';
                    document.getElementById('charCount').textContent = '0';
                } else {
                    showNotification(d.message || 'Failed to send message', 'error');
                }
            })
            .catch(err => showNotification('Error: ' + err.message, 'error'))
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Notification helper (matches your styling)
    function showNotification(message, type) {
        document.querySelectorAll('.notification-alert').forEach(el => el.remove());
        const cls = type === 'success' ? 'alert-success' :
            type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'success' ? 'fa-check-circle' :
            type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        const div = document.createElement('div');
        div.className = `alert ${cls} alert-dismissible fade show notification-alert position-fixed`;
        div.style.top = '20px'; div.style.right = '20px'; div.style.zIndex = '9999'; div.style.minWidth = '300px';
        div.innerHTML = `<i class="fas ${icon}"></i> <strong>${type === 'error' ? 'Error' : type === 'success' ? 'Success' : 'Info'}</strong> ${message}
                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 5000);
    }
</script>

<style>
    .contact-info .contact-item {
        padding: .5rem;
        border-radius: .375rem;
        transition: background-color .2s ease;
    }

    .contact-info .contact-item:hover {
        background-color: #f8f9fc;
    }

    .accordion-button:not(.collapsed) {
        color: #1cc88a;
        background-color: rgba(28, 200, 138, .1);
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 .25rem rgba(28, 200, 138, .25);
    }

    .notification-alert {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        border: none;
    }

    #charCount {
        font-weight: 600;
    }

    #customSubjectDiv {
        animation: slideDown .3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
            padding: 0;
            margin: 0
        }

        to {
            opacity: 1;
            max-height: 100px;
            padding: inherit;
            margin: inherit
        }
    }
</style>

<?= $this->endSection() ?>