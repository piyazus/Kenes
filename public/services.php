<?php include 'includes/header.php'; ?>

<div class="container mt-4">

    <header style="margin: 60px 0 40px; border-bottom: 1px solid var(--color-gray-300); padding-bottom: 24px;">
        <h1 style="font-size: 32px;">Consultation Services</h1>
        <p class="text-secondary">Tailored financial solutions for every stage of business.</p>
    </header>

    <!-- Services Grid -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-bottom: 80px;">

        <!-- Service 1 -->
        <div style="border-top: 2px solid var(--color-green-primary); padding-top: 24px;">
            <h2 style="font-size: 20px; margin-bottom: 12px;">SME Working Capital</h2>
            <p class="text-secondary" style="margin-bottom: 24px;">
                Short-term financing to bridge the gap between payables and receivables. Ideal for inventory
                smoothing.
            </p>
            <ul style="margin-bottom: 24px; font-size: 14px; color: var(--color-gray-700);">
                <li style="margin-bottom: 8px;">• Rates from 14% APR</li>
                <li style="margin-bottom: 8px;">• Terms: 3 - 12 Months</li>
                <li>• Decision in 48 hours</li>
            </ul>
            <a href="register-customer.php" class="btn btn-text">Apply Now →</a>
        </div>

        <!-- Service 2 -->
        <div style="border-top: 2px solid var(--color-gray-300); padding-top: 24px;">
            <h2 style="font-size: 20px; margin-bottom: 12px;">Equipment Leasing</h2>
            <p class="text-secondary" style="margin-bottom: 24px;">
                Acquire the machinery or technology your business needs without draining your cash flow.
            </p>
            <ul style="margin-bottom: 24px; font-size: 14px; color: var(--color-gray-700);">
                <li style="margin-bottom: 8px;">• Rates from 12% APR</li>
                <li style="margin-bottom: 8px;">• Terms: 1 - 5 Years</li>
                <li>• Asset-backed security</li>
            </ul>
            <a href="register-customer.php" class="btn btn-text">Apply Now →</a>
        </div>

        <!-- Service 3 -->
        <div style="border-top: 2px solid var(--color-gray-300); padding-top: 24px;">
            <h2 style="font-size: 20px; margin-bottom: 12px;">Microcredit</h2>
            <p class="text-secondary" style="margin-bottom: 24px;">
                Small scale funding for startups and individual entrepreneurs looking to launch.
            </p>
            <ul style="margin-bottom: 24px; font-size: 14px; color: var(--color-gray-700);">
                <li style="margin-bottom: 8px;">• Rates from 18% APR</li>
                <li style="margin-bottom: 8px;">• Terms: 6 - 24 Months</li>
                <li>• Minimal paperwork</li>
            </ul>
            <a href="register-customer.php" class="btn btn-text">Apply Now →</a>
        </div>

        <!-- Service 4 -->
        <div style="border-top: 2px solid var(--color-gray-300); padding-top: 24px;">
            <h2 style="font-size: 20px; margin-bottom: 12px;">Risk Analysis Report</h2>
            <p class="text-secondary" style="margin-bottom: 24px;">
                A comprehensive financial health check using our proprietary AI model. Get actionable insights.
            </p>
            <ul style="margin-bottom: 24px; font-size: 14px; color: var(--color-gray-700);">
                <li style="margin-bottom: 8px;">• One-time fee</li>
                <li style="margin-bottom: 8px;">• Instant Generation</li>
                <li>• PDF Export</li>
            </ul>
            <a href="register-customer.php" class="btn btn-text">Get Report →</a>
        </div>

    </div>

    <!-- Consultation CTA -->
    <div style="background-color: var(--color-gray-50); padding: 40px; text-align: center; margin-bottom: 60px;">
        <h2 style="font-size: 24px; margin-bottom: 16px;">Not sure what you need?</h2>
        <p class="text-secondary" style="margin-bottom: 24px;">
            Our consultants are ready to help you find the right product.
        </p>
        <a href="register-customer.php" class="btn btn-primary">Book Free Consultation</a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>