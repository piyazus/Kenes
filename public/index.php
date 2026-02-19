<?php include 'includes/header.php'; ?>

<header class="container" style="
        min-height: 80vh; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center; 
        text-align: center;
        max-width: 900px;
        margin-bottom: 80px;
    ">
    <h1 style="font-size: clamp(48px, 8vw, 84px); margin-bottom: 40px; color: #000;">
        Kenes turns businesses<br>
        into <span style="font-style: italic; font-family: var(--font-family-serif);">formidable</span> enterprises.
    </h1>

    <div style="max-width: 600px; margin: 0 auto; text-align: left;">
        <p
            style="font-size: 18px; line-height: 1.6; color: #333; font-style: italic; border-left: 2px solid #ccc; padding-left: 20px;">
            [1] “A formidable enterprise is one who seems like they'll get what they want, regardless of whatever
            obstacles are in the way.”
        </p>
        <p style="text-align: right; margin-top: 10px; font-size: 14px;">— Kenes Philosophy</p>
    </div>
</header>

<section class="container" style="margin-bottom: 100px;">
    <div style="text-align: left; margin-bottom: 40px;">
        <h2 style="font-size: 14px; text-transform: uppercase; color: var(--color-gray-500); letter-spacing: 1px;">
            Our Approach</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 60px;">
        <div>
            <h3 style="font-size: 24px; margin-bottom: 16px;">Resilience First</h3>
            <p style="line-height: 1.6;">
                We don't just optimize for profit; we optimize for survival. Our financial structures are designed
                to
                withstand market volatility, ensuring your business stays standing when others fall.
            </p>
        </div>
        <div>
            <h3 style="font-size: 24px; margin-bottom: 16px;">Data-Driven Rigor</h3>
            <p style="line-height: 1.6;">
                Intuition is good; data is better. We use advanced AI modeling to stress-test your business plans
                against real-world scenarios before a single dollar is deployed.
            </p>
        </div>
        <div>
            <h3 style="font-size: 24px; margin-bottom: 16px;">Human Expertise</h3>
            <p style="line-height: 1.6;">
                Technology is a tool, not a replacement. Our consultants are seasoned experts who understand the
                nuances of the local market that algorithms miss.
            </p>
        </div>
    </div>
</section>


<section class="container" style="margin-bottom: 120px;">
    <div style="text-align: left; margin-bottom: 40px;">
        <h2 style="font-size: 14px; text-transform: uppercase; color: var(--color-gray-500); letter-spacing: 1px;">
            Products & Services</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">

        <a href="services.php" class="glass-card" style="text-decoration: none; display: block; height: 100%;">
            <div style="color: var(--color-primary-solid); margin-bottom: 16px; font-size: 24px;">Growth</div>
            <h3 style="font-size: 20px; font-weight: 600;">SME Working Capital</h3>
            <p style="font-size: 15px; margin-bottom: 0;">Short-term financing to bridge cash flow gaps and seize
                immediate opportunities.</p>
        </a>

        <a href="services.php" class="glass-card" style="text-decoration: none; display: block; height: 100%;">
            <div style="color: var(--color-primary-solid); margin-bottom: 16px; font-size: 24px;">Assets</div>
            <h3 style="font-size: 20px; font-weight: 600;">Equipment Leasing</h3>
            <p style="font-size: 15px; margin-bottom: 0;">Acquire essential machinery and technology without heavy
                upfront capital expenditure.</p>
        </a>


        <a href="services.php" class="glass-card" style="text-decoration: none; display: block; height: 100%;">
            <div style="color: var(--color-primary-solid); margin-bottom: 16px; font-size: 24px;">Intelligence</div>
            <h3 style="font-size: 20px; font-weight: 600;">Risk Analysis</h3>
            <p style="font-size: 15px; margin-bottom: 0;">Comprehensive AI-powered financial health checks to
                identify vulnerabilities.</p>
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>