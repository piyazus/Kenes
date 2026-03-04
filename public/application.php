<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/auth_guard.php';
requireAuth('customer');

// Fetch services for step 1
$stmt = $pdo->query("SELECT * FROM services WHERE active = 1 ORDER BY id");
$services = $stmt->fetchAll();

require_once 'includes/config.php';
$extra_css = '<link rel="stylesheet" href="' . $base_url . '/css/forms.css"><link rel="stylesheet" href="' . $base_url . '/css/dashboard.css">';
include 'includes/header.php';
?>

<main class="container" style="padding: 48px 24px 80px;">
    <div class="wizard">
        <!-- Step indicators -->
        <div class="wizard-steps">
            <div class="wizard-step active" id="indicator-1">
                <span class="wizard-step-num">1</span>
                <span class="hidden-mobile"><?= __('app.step1') ?></span>
            </div>
            <div class="wizard-connector" id="connector-1"></div>
            <div class="wizard-step" id="indicator-2">
                <span class="wizard-step-num">2</span>
                <span class="hidden-mobile"><?= __('app.step2') ?></span>
            </div>
            <div class="wizard-connector" id="connector-2"></div>
            <div class="wizard-step" id="indicator-3">
                <span class="wizard-step-num">3</span>
                <span class="hidden-mobile"><?= __('app.step3') ?></span>
            </div>
        </div>

        <form id="applicationForm" action="submit_application.php" method="POST" enctype="multipart/form-data">
            <?php csrfField(); ?>

            <!-- STEP 1: Select Service -->
            <div class="wizard-panel active" id="step-1">
                <h2><?= __('app.step1') ?></h2>
                <p class="text-secondary"><?= __('app.select_service') ?></p>
                <div class="service-select-grid" style="margin-top: 20px;">
                    <?php foreach ($services as $svc): ?>
                        <div class="service-select-card" data-service-id="<?= $svc['id'] ?>"
                            onclick="selectService(this, <?= $svc['id'] ?>)">
                            <h4><?= htmlspecialchars($svc['name']) ?></h4>
                            <?php if ($svc['interest_rate']): ?>
                                <p style="color: var(--yc-orange); font-weight: 600;">from <?= $svc['interest_rate'] ?>%</p>
                            <?php endif; ?>
                            <p><?= htmlspecialchars(substr($svc['description'] ?? '', 0, 80)) ?>...</p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="service_id" id="serviceId" required>

                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label" for="amount">Requested Amount (KZT) *</label>
                    <input type="number" class="form-control" id="amount" name="amount" required min="100000"
                        placeholder="e.g. 5000000">
                </div>

                <div class="wizard-actions">
                    <a href="customer-dashboard.php" class="btn btn-ghost"><?= __('app.cancel') ?></a>
                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next →</button>
                </div>
            </div>

            <!-- STEP 2: Upload Documents -->
            <div class="wizard-panel" id="step-2">
                <h2><?= __('app.step2') ?></h2>
                <p class="text-secondary"><?= __('app.upload_docs') ?></p>

                <!-- Document upload slots -->
                <div style="margin-top: 20px;">
                    <?php
                    $docTypes = [
                        'business_plan' => __('doc.business_plan'),
                        'financial' => __('doc.financial'),
                        'iin_cert' => __('doc.iin_cert'),
                        'bank_statement' => __('doc.bank_statement'),
                        'other' => __('doc.other')
                    ];
                    foreach ($docTypes as $key => $label):
                        ?>
                        <div class="upload-slot">
                            <div>
                                <div class="upload-slot-label"><?= $label ?></div>
                                <span class="upload-slot-status" id="status-<?= $key ?>">No file chosen</span>
                            </div>
                            <input type="file" name="documents[<?= $key ?>]" id="file-<?= $key ?>"
                                accept=".pdf,.jpg,.jpeg,.png,.docx" style="display: none;"
                                onchange="fileSelected('<?= $key ?>', this)">
                            <button type="button" class="btn btn-ghost btn-sm"
                                onclick="document.getElementById('file-<?= $key ?>').click()">Choose File</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- General drag-drop zone -->
                <div class="upload-zone" id="dropZone" style="margin-top: 16px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    <p><?= __('app.drag_drop') ?></p>
                    <span class="upload-hint"><?= __('app.file_types') ?></span>
                    <input type="file" name="extra_documents[]" id="extraFiles" multiple
                        accept=".pdf,.jpg,.jpeg,.png,.docx" style="display: none;">
                </div>
                <div class="file-list" id="extraFileList"></div>

                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost" onclick="prevStep(1)">← Back</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next →</button>
                </div>
            </div>

            <!-- STEP 3: Review & Submit -->
            <div class="wizard-panel" id="step-3">
                <h2><?= __('app.step3') ?></h2>
                <p class="text-secondary"><?= __('app.review') ?></p>

                <div class="card" style="margin: 20px 0;">
                    <h4>Selected Service</h4>
                    <p id="reviewService" style="font-weight: 600; color: var(--yc-orange);">—</p>
                    <h4>Requested Amount</h4>
                    <p id="reviewAmount" style="font-weight: 600;">—</p>
                    <h4>Documents Uploaded</h4>
                    <div id="reviewDocs">No documents uploaded</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes"><?= __('app.notes') ?></label>
                    <textarea class="form-control" id="notes" name="notes"
                        placeholder="Any additional information for your consultant..."></textarea>
                </div>

                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost" onclick="prevStep(2)">← Back</button>
                    <button type="submit" class="btn btn-primary btn-lg"><?= __('app.submit') ?></button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    var selectedServiceId = null;
    var selectedServiceName = '';

    function selectService(el, id) {
        document.querySelectorAll('.service-select-card').forEach(function (c) { c.classList.remove('selected'); });
        el.classList.add('selected');
        selectedServiceId = id;
        selectedServiceName = el.querySelector('h4').textContent;
        document.getElementById('serviceId').value = id;
    }

    function fileSelected(type, input) {
        var status = document.getElementById('status-' + type);
        if (input.files.length > 0) {
            status.textContent = input.files[0].name;
            status.classList.add('uploaded');
        }
    }

    function nextStep(step) {
        if (step === 2 && !selectedServiceId) {
            alert('Please select a service first.');
            return;
        }
        if (step === 2 && !document.getElementById('amount').value) {
            alert('Please enter a requested amount.');
            return;
        }

        // Update review panel
        if (step === 3) {
            document.getElementById('reviewService').textContent = selectedServiceName;
            document.getElementById('reviewAmount').textContent = Number(document.getElementById('amount').value).toLocaleString() + ' KZT';

            var docs = [];
            document.querySelectorAll('.upload-slot-status.uploaded').forEach(function (s) {
                docs.push(s.textContent);
            });
            document.getElementById('reviewDocs').innerHTML = docs.length ? docs.map(function (d) { return '<div style="font-size: 13px; padding: 2px 0;">✓ ' + d + '</div>'; }).join('') : 'No documents uploaded';
        }

        showStep(step);
    }

    function prevStep(step) { showStep(step); }

    function showStep(step) {
        document.querySelectorAll('.wizard-panel').forEach(function (p) { p.classList.remove('active'); });
        document.getElementById('step-' + step).classList.add('active');

        for (var i = 1; i <= 3; i++) {
            var ind = document.getElementById('indicator-' + i);
            ind.classList.remove('active', 'done');
            if (i < step) ind.classList.add('done');
            if (i === step) ind.classList.add('active');

            if (i < 3) {
                var conn = document.getElementById('connector-' + i);
                conn.classList.toggle('done', i < step);
            }
        }
    }

    // Drag and drop
    var dropZone = document.getElementById('dropZone');
    var extraFiles = document.getElementById('extraFiles');

    dropZone.addEventListener('click', function () { extraFiles.click(); });

    dropZone.addEventListener('dragover', function (e) { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', function () { dropZone.classList.remove('drag-over'); });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        extraFiles.files = e.dataTransfer.files;
        showExtraFiles();
    });
    extraFiles.addEventListener('change', showExtraFiles);

    function showExtraFiles() {
        var list = document.getElementById('extraFileList');
        list.innerHTML = '';
        for (var i = 0; i < extraFiles.files.length; i++) {
            var div = document.createElement('div');
            div.className = 'file-item';
            div.innerHTML = '<span class="file-item-name">' + extraFiles.files[i].name + '</span><span style="color: var(--text-muted); font-size: 12px;">' + (extraFiles.files[i].size / 1024 / 1024).toFixed(1) + ' MB</span>';
            list.appendChild(div);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>