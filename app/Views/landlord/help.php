<?= $this->extend('layouts/landlord') ?>

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
                        <strong>Quick Response:</strong> We typically respond to messages within 24 hours during
                        business days. For urgent matters, please select "High" priority.
                    </div>

                    <?= form_open('landlord/send-admin-message', ['id' => 'contactForm']) ?>
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a topic...</option>
                                <option value="Property Management">Property Management</option>
                                <option value="Payment Problems">Payment Problems</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Account Issues">Account Issues</option>
                                <option value="Feature Request">Feature Request</option>
                                <option value="Bug Report">Bug Report</option>
                                <option value="Billing Questions">Billing Questions</option>
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

                    <div class="mb-3" id="customSubjectDiv" style="display: none;">
                        <label for="custom_subject" class="form-label">Custom Subject *</label>
                        <input type="text" class="form-control" id="custom_subject" name="custom_subject"
                            placeholder="Please specify your subject..." maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6"
                            placeholder="Please describe your issue or question in detail..." required
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
                        <!-- FAQ Item 1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                    <i class="fas fa-home me-2 text-primary"></i>
                                    How do I add a new property?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Go to <strong>Properties</strong> → <strong>Add New Property</strong>. Fill in all
                                    required information including property details, ownership percentages, and
                                    management information. The system will automatically calculate rent distributions
                                    based on ownership percentages.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    <i class="fas fa-dollar-sign me-2 text-warning"></i>
                                    How are payments tracked and distributed?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The system automatically tracks all payments and calculates your share based on
                                    ownership percentages. You can view payment history, generate reports, and export
                                    financial data from the <strong>Payments</strong> and <strong>Reports</strong>
                                    sections.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    <i class="fas fa-tools me-2 text-info"></i>
                                    How do I handle maintenance requests?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    All maintenance requests from landlords appear in your <strong>Maintenance</strong>
                                    dashboard. You can review requests, assign priorities, and track progress through
                                    the platform.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 5 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    <i class="fas fa-chart-bar me-2 text-secondary"></i>
                                    What reports are available?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="faq5"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The <strong>Reports</strong> section provides comprehensive financial reports,
                                    occupancy analytics, maintenance cost summaries, and custom date range reports. You
                                    can export all reports to PDF formats.
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
                            <a href="mailto:support@landlordpanel.com">support@landlordpanel.com</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock text-info me-2"></i>
                            <strong>Response Time:</strong><br>
                            <small class="text-muted">
                                • Normal: 24-48 hours<br>
                                • High: 4-12 hours<br>
                                • Urgent: 1-4 hours
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
                        <a href="<?= site_url('landlord/dashboard') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                        </a>
                        <a href="<?= site_url('landlord/properties') ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-building me-2"></i> Manage Properties
                        </a>
                        <a href="<?= site_url('landlord/payments') ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-dollar-sign me-2"></i> View Payments
                        </a>
                        <a href="<?= site_url('landlord/reports') ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar me-2"></i> Generate Reports
                        </a>
                        <a href="<?= site_url('landlord/profile') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user me-2"></i> Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle subject selection
    document.getElementById('subject').addEventListener('change', function () {
        const customSubjectDiv = document.getElementById('customSubjectDiv');
        const customSubjectInput = document.getElementById('custom_subject');

        if (this.value === 'Other') {
            customSubjectDiv.style.display = 'block';
            customSubjectInput.required = true;
        } else {
            customSubjectDiv.style.display = 'none';
            customSubjectInput.required = false;
            customSubjectInput.value = '';
        }
    });

    // Character counter for message
    document.getElementById('message').addEventListener('input', function () {
        const charCount = document.getElementById('charCount');
        const currentLength = this.value.length;
        charCount.textContent = currentLength;

        if (currentLength > 1800) {
            charCount.style.color = '#e74a3b';
        } else if (currentLength > 1500) {
            charCount.style.color = '#f6c23e';
        } else {
            charCount.style.color = '#858796';
        }
    });

    // Form submission
    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;

        // Validate custom subject if "Other" is selected
        const subject = document.getElementById('subject').value;
        const customSubject = document.getElementById('custom_subject').value;

        if (subject === 'Other' && !customSubject.trim()) {
            showNotification('Please specify a custom subject when "Other" is selected.', 'error');
            return;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitBtn.disabled = true;

        // Submit form
        const formData = new FormData(this);

        fetch('<?= site_url('landlord/send-admin-message') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    this.reset();
                    document.getElementById('customSubjectDiv').style.display = 'none';
                    document.getElementById('charCount').textContent = '0';
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error sending message: ' + error.message, 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Notification system
    function showNotification(message, type) {
        // Remove existing notifications
        const existingAlerts = document.querySelectorAll('.notification-alert');
        existingAlerts.forEach(alert => alert.remove());

        const alertClass = type === 'success' ? 'alert-success' :
            type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

        const icon = type === 'success' ? 'fa-check-circle' :
            type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show notification-alert position-fixed`;
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
        <i class="fas ${icon}"></i>
        <strong>${type === 'error' ? 'Error!' : type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : 'Info!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(alertDiv);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script>

<style>
    .contact-info .contact-item {
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: background-color 0.2s ease;
    }

    .contact-info .contact-item:hover {
        background-color: #f8f9fc;
    }

    .status-item {
        padding: 0.25rem 0;
    }

    .accordion-button:not(.collapsed) {
        color: #1cc88a;
        background-color: rgba(28, 200, 138, 0.1);
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(28, 200, 138, 0.25);
    }

    .notification-alert {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: none;
    }

    /* Character counter styling */
    #charCount {
        font-weight: 600;
    }

    /* Custom subject animation */
    #customSubjectDiv {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
            padding: 0;
            margin: 0;
        }

        to {
            opacity: 1;
            max-height: 100px;
            padding: inherit;
            margin: inherit;
        }
    }
</style>

<?= $this->endSection() ?>