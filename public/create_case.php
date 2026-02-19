<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'consultant') {
    header('Location: login.html');
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name']);

// Fetch all customers for the dropdown
$stmt = $pdo->query("SELECT customer_id, full_name, iin_number FROM customer_profiles ORDER BY full_name ASC");
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Case - Consultant Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/forms.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="index.html">K</a>
                <span
                    style="font-size: 12px; background: var(--color-gray-100); padding: 2px 6px; border-radius: 4px; margin-left: 8px; color: var(--color-gray-700);">Consultant</span>
            </div>
            <ul class="navbar-menu navbar-menu-left">
                <li><a href="consultant-dashboard.php">Dashboard</a></li>
            </ul>
            <div class="navbar-user">
                <span style="font-weight: 600; color: var(--color-gray-900);">
                    <?php echo $user_name; ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card" style="max-width: 600px;">
            <div class="auth-header">
                <h1>Create New Case</h1>
                <p class="text-secondary">Submit an application on behalf of a customer.</p>
            </div>

            <form class="needs-validation" action="submit_case.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label" for="customer_id">Select Customer</label>
                    <select class="form-control" id="customer_id" name="customer_id" required>
                        <option value="" selected disabled>Choose a customer...</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['customer_id']; ?>">
                                <?php echo htmlspecialchars($customer['full_name']); ?> (IIN:
                                <?php echo htmlspecialchars($customer['iin_number']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

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
                </div>

                <button type="submit" class="btn btn-primary btn-full">Create Case</button>
            </form>
        </div>
    </div>
</body>

</html>