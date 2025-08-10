<?= $this->extend('layouts/tenant') ?>

<?= $this->section('title') ?>Submit Maintenance Request<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools"></i> Submit Maintenance Request
        </h1>
        <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-secondary">
            <i class="fas fa-list"></i> View All Requests
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Maintenance Request Form -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus"></i> New Maintenance Request
                    </h6>
                </div>
                <div class="card-body">
                    <form action="<?= site_url('tenant/maintenance/store') ?>" method="post" enctype="multipart/form-data" id="maintenanceForm">
                        <?= csrf_field() ?>
                        
                        <!-- Validation Errors -->
                        <?php if (session()->getFlashdata('validation')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Property Information -->
                        <?php if ($lease): ?>
                            <div class="alert alert-info">
                                <h6><i class="fas fa-home"></i> Property Information</h6>
                                <p class="mb-1"><strong><?= esc($lease['property_name']) ?></strong></p>
                                <p class="mb-0"><?= esc($lease['property_address']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Request Details -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading"></i> Request Title *
                                    </label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           placeholder="Brief description of the issue (e.g., Leaky faucet in kitchen)" 
                                           value="<?= old('title') ?>" required maxlength="200">
                                    <small class="form-text text-muted">Keep it short and descriptive</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="priority" class="form-label">
                                        <i class="fas fa-exclamation-triangle"></i> Priority Level *
                                    </label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>
                                            Low - Cosmetic issues, non-urgent
                                        </option>
                                        <option value="normal" <?= old('priority') === 'normal' ? 'selected' : '' ?>>
                                            Normal - General maintenance
                                        </option>
                                        <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>
                                            High - Appliance issues, minor leaks
                                        </option>
                                        <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>
                                            Urgent - Safety hazards, no heat/AC
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i> Detailed Description *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="5" 
                                      placeholder="Please provide a detailed description of the problem, including:&#10;- What exactly is wrong?&#10;- When did you first notice it?&#10;- Have you tried anything to fix it?&#10;- Any other relevant details..." 
                                      required><?= old('description') ?></textarea>
                            <small class="form-text text-muted">The more details you provide, the better we can help you</small>
                        </div>

                        <!-- Location/Category -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="location" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i> Location in Property
                                    </label>
                                    <select class="form-select" id="location" name="location">
                                        <option value="">Select Location</option>
                                        <option value="kitchen" <?= old('location') === 'kitchen' ? 'selected' : '' ?>>Kitchen</option>
                                        <option value="bathroom" <?= old('location') === 'bathroom' ? 'selected' : '' ?>>Bathroom</option>
                                        <option value="living_room" <?= old('location') === 'living_room' ? 'selected' : '' ?>>Living Room</option>
                                        <option value="bedroom" <?= old('location') === 'bedroom' ? 'selected' : '' ?>>Bedroom</option>
                                        <option value="basement" <?= old('location') === 'basement' ? 'selected' : '' ?>>Basement</option>
                                        <option value="garage" <?= old('location') === 'garage' ? 'selected' : '' ?>>Garage</option>
                                        <option value="exterior" <?= old('location') === 'exterior' ? 'selected' : '' ?>>Exterior</option>
                                        <option value="other" <?= old('location') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label">
                                        <i class="fas fa-tags"></i> Category
                                    </label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">Select Category</option>
                                        <option value="plumbing" <?= old('category') === 'plumbing' ? 'selected' : '' ?>>Plumbing</option>
                                        <option value="electrical" <?= old('category') === 'electrical' ? 'selected' : '' ?>>Electrical</option>
                                        <option value="hvac" <?= old('category') === 'hvac' ? 'selected' : '' ?>>HVAC</option>
                                        <option value="appliances" <?= old('category') === 'appliances' ? 'selected' : '' ?>>Appliances</option>
                                        <option value="flooring" <?= old('category') === 'flooring' ? 'selected' : '' ?>>Flooring</option>
                                        <option value="windows_doors" <?= old('category') === 'windows_doors' ? 'selected' : '' ?>>Windows/Doors</option>
                                        <option value="pest_control" <?= old('category') === 'pest_control' ? 'selected' : '' ?>>Pest Control</option>
                                        <option value="other" <?= old('category') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Availability -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="preferred_date" class="form-label">
                                        <i class="fas fa-calendar"></i> Preferred Date
                                    </label>
                                    <input type="date" class="form-control" id="preferred_date" name="preferred_date" 
                                           min="<?= date('Y-m-d') ?>" value="<?= old('preferred_date') ?>">
                                    <small class="form-text text-muted">When would you prefer the maintenance to be done?</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="preferred_time" class="form-label">
                                        <i class="fas fa-clock"></i> Preferred Time
                                    </label>
                                    <select class="form-select" id="preferred_time" name="preferred_time">
                                        <option value="">Any Time</option>
                                        <option value="morning" <?= old('preferred_time') === 'morning' ? 'selected' : '' ?>>Morning (8AM - 12PM)</option>
                                        <option value="afternoon" <?= old('preferred_time') === 'afternoon' ? 'selected' : '' ?>>Afternoon (12PM - 5PM)</option>
                                        <option value="evening" <?= old('preferred_time') === 'evening' ? 'selected' : '' ?>>Evening (5PM - 8PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Upload -->
                        <div class="form-group mb-3">
                            <label for="images" class="form-label">
                                <i class="fas fa-camera"></i> Upload Photos (Optional)
                            </label>
                            <input type="file" class="form-control" id="images" name="images[]" 
                                   accept="image/*" multiple>
                            <small class="form-text text-muted">
                                Upload photos to help us understand the issue better. Max 5 photos, 5MB each.
                            </small>
                            <div id="image-preview" class="mt-2"></div>
                        </div>

                        <!-- Access Instructions -->
                        <div class="form-group mb-3">
                            <label for="access_instructions" class="form-label">
                                <i class="fas fa-key"></i> Access Instructions
                            </label>
                            <textarea class="form-control" id="access_instructions" name="access_instructions" rows="3" 
                                      placeholder="Special instructions for accessing the property or specific areas (e.g., key location, pet information, parking instructions)..."><?= old('access_instructions') ?></textarea>
                        </div>

                        <!-- Contact Preference -->
                        <div class="form-group mb-4">
                            <label class="form-label">
                                <i class="fas fa-phone"></i> How would you like to be contacted?
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="contact_email" name="contact_methods[]" value="email" checked>
                                <label class="form-check-label" for="contact_email">Email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="contact_phone" name="contact_methods[]" value="phone">
                                <label class="form-check-label" for="contact_phone">Phone</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="contact_text" name="contact_methods[]" value="text">
                                <label class="form-check-label" for="contact_text">Text Message</label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                            <a href="<?= site_url('tenant/maintenance') ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Sidebar -->
        <div class="col-lg-4">
            <!-- Priority Guide -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Priority Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-danger fs-6 mb-1">URGENT</span>
                        <p class="small mb-2">Safety hazards, no heat/AC, major water leaks, electrical issues, security problems</p>
                        <small class="text-muted">Response: Same day</small>
                    </div>
                    
                    <div class="mb-3">
                        <span class="badge bg-warning fs-6 mb-1">HIGH</span>
                        <p class="small mb-2">Appliance failures, minor leaks, broken locks, heating/cooling issues</p>
                        <small class="text-muted">Response: 1-2 days</small>
                    </div>
                    
                    <div class="mb-3">
                        <span class="badge bg-info fs-6 mb-1">NORMAL</span>
                        <p class="small mb-2">General repairs, painting, caulking, minor fixture issues</p>
                        <small class="text-muted">Response: 3-5 days</small>
                    </div>
                    
                    <div>
                        <span class="badge bg-secondary fs-6 mb-1">LOW</span>
                        <p class="small mb-2">Cosmetic issues, non-essential repairs, aesthetic improvements</p>
                        <small class="text-muted">Response: 1-2 weeks</small>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Emergency Contact
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">For immediate emergencies that pose safety risks:</p>
                    <div class="text-center">
                        <a href="tel:+1234567890" class="btn btn-danger btn-lg">
                            <i class="fas fa-phone"></i> (123) 456-7890
                        </a>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Available 24/7 for true emergencies only
                    </small>
                </div>
            </div>

            <!-- Tips -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb"></i> Helpful Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-camera text-info"></i>
                            <small>Include photos when possible - they help diagnose issues faster</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning"></i>
                            <small>Be specific about when the issue occurs (morning, evening, etc.)</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt text-success"></i>
                            <small>Provide exact location details within your unit</small>
                        </li>
                        <li>
                            <i class="fas fa-tools text-primary"></i>
                            <small>Mention any steps you've already tried to fix the issue</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview functionality
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (e.target.files.length > 5) {
        alert('Maximum 5 images allowed');
        e.target.value = '';
        return;
    }
    
    Array.from(e.target.files).forEach((file, index) => {
        if (file.size > 5 * 1024 * 1024) {
            alert(`Image ${index + 1} is too large. Maximum size is 5MB.`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'col-3 mb-2';
            div.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 80px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                            onclick="removeImage(this, ${index})" style="padding: 2px 6px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

function removeImage(button, index) {
    button.closest('.col-3').remove();
    // Note: This is a simplified version. In a real application, you'd need to 
    // properly manage the file input to remove the specific file.
}

// Priority change handler
document.getElementById('priority').addEventListener('change', function() {
    const priority = this.value;
    const form = document.getElementById('maintenanceForm');
    
    // Remove existing priority classes
    form.classList.remove('urgent-request', 'high-request');
    
    // Add visual indicator for urgent/high priority
    if (priority === 'urgent') {
        form.classList.add('urgent-request');
        if (!document.querySelector('.urgent-warning')) {
            const warning = document.createElement('div');
            warning.className = 'alert alert-danger urgent-warning';
            warning.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Urgent Request:</strong> For immediate safety hazards, please call our emergency line at (123) 456-7890
            `;
            form.insertBefore(warning, form.firstChild);
        }
    } else if (priority === 'high') {
        form.classList.add('high-request');
        const urgentWarning = document.querySelector('.urgent-warning');
        if (urgentWarning) {
            urgentWarning.remove();
        }
    } else {
        const urgentWarning = document.querySelector('.urgent-warning');
        if (urgentWarning) {
            urgentWarning.remove();
        }
    }
});

// Form validation
document.getElementById('maintenanceForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const priority = document.getElementById('priority').value;
    
    if (!title || !description || !priority) {
        e.preventDefault();
        alert('Please fill in all required fields (Title, Description, and Priority)');
        return;
    }
    
    if (title.length < 5) {
        e.preventDefault();
        alert('Please provide a more descriptive title (at least 5 characters)');
        document.getElementById('title').focus();
        return;
    }
    
    if (description.length < 10) {
        e.preventDefault();
        alert('Please provide more details in the description (at least 10 characters)');
        document.getElementById('description').focus();
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    // For urgent requests, show confirmation
    if (priority === 'urgent') {
        e.preventDefault();
        if (confirm('This is marked as URGENT. Have you considered calling our emergency line at (123) 456-7890 for immediate assistance?')) {
            // Restore button and submit
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            this.submit();
        } else {
            // Restore button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
});

// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const maxLength = 1000;
    const currentLength = this.value.length;
    
    let counter = document.getElementById('desc-counter');
    if (!counter) {
        counter = document.createElement('small');
        counter.id = 'desc-counter';
        counter.className = 'form-text text-muted';
        this.parentNode.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} characters`;
    
    if (currentLength > maxLength * 0.9) {
        counter.className = 'form-text text-warning';
    } else {
        counter.className = 'form-text text-muted';
    }
});

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);

// Preferred date validation (not in the past)
document.getElementById('preferred_date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        alert('Please select a date that is today or in the future');
        this.value = '';
    }
});
</script>

<style>
.urgent-request {
    border-left: 5px solid #dc3545;
}

.high-request {
    border-left: 5px solid #ffc107;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

#image-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.position-relative .btn-danger {
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<?= $this->endSection() ?>