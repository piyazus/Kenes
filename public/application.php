<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$extra_css = '<link rel="stylesheet" href="css/forms.css">';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card" style="max-width: 600px;">
        <div class="auth-header">
            <h1>New Application</h1>
            <p class="text-secondary">Submit a new request for funding.</p>
        </div>

        <form class="needs-validation" novalidate action="submit_application.php" method="POST"
            enctype="multipart/form-data">

            <div class="form-group">
                <label class="form-label" for="service_type">Loan Type</label>
                <select class="form-control" id="service_type" name="service_type" required>
                    <option value="" selected disabled>Select a loan type</option>
                    <option value="sme_business">SME Business Loan</option>
                    <option value="microcredit">Microcredit</option>
                    <option value="working_capital">Working Capital</option>
                    <option value="investment">Investment Loan</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="amount">Requested Amount (KZT)</label>
                <input type="number" class="form-control" id="amount" name="amount" required min="100000"
                    placeholder="e.g. 5000000">
            </div>

            <div class="form-group">
                <label class="form-label" for="purpose">Purpose of Loan</label>
                <textarea class="form-control" id="purpose" name="purpose" required
                    placeholder="Describe how you will use the funds..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="documents">Documents (PDF, Images)</label>
                <input type="file" class="form-control" id="documents" name="documents[]" multiple
                    accept=".pdf,.jpg,.jpeg,.png">
                <p class="text-secondary" style="font-size: 12px; margin-top: 4px;">Upload financial statements,
                    business plan, etc.</p>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Submit Application</button>

            <div class="auth-footer">
                <p><a href="customer-dashboard.php">Cancel & Return to Dashboard</a></p>
            </div>
        </form>
    </div>
</div>

<script src="js/form-validation.js"></script>

<?php include 'includes/footer.php'; ?>