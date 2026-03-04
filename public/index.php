<?php
require_once 'includes/db.php';

// Fetch services for preview
$stmt = $pdo->query("SELECT * FROM services WHERE active = 1 ORDER BY id LIMIT 3");
$services = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    .landing-hero {
        text-align: center;
        padding: 100px 24px 80px;
        background: linear-gradient(180deg, var(--bg-white) 0%, var(--bg-body) 100%);
    }

    .landing-hero h1 {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1.15;
        max-width: 700px;
        margin: 0 auto 20px;
        letter-spacing: -0.02em;
    }

    .landing-hero h1 .text-orange {
        color: var(--yc-orange);
    }

    .landing-hero p {
        font-size: 1.15rem;
        max-width: 560px;
        margin: 0 auto 32px;
        color: var(--text-secondary);
    }

    .hero-ctas {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .stats-bar {
        background: var(--bg-white);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 40px 0;
    }

    .stats-bar-inner {
        max-width: 900px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 32px;
        text-align: center;
    }

    .stats-bar-num {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--yc-orange);
        line-height: 1;
        margin-bottom: 8px;
    }

    .stats-bar-label {
        font-size: 14px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .how-section {
        padding: 80px 24px;
        text-align: center;
    }

    .how-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        max-width: 1000px;
        margin: 48px auto 0;
    }

    .how-card {
        padding: 24px 16px;
    }

    .how-num {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--yc-orange);
        color: #fff;
        font-weight: 700;
        margin: 0 auto 16px;
    }

    .how-card h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .how-card p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
    }

    .services-preview {
        padding: 80px 24px;
        background: var(--bg-white);
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        max-width: 1000px;
        margin: 40px auto 0;
    }

    .service-card {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 24px;
        transition: var(--transition);
    }

    .service-card:hover {
        border-color: var(--yc-orange);
        box-shadow: var(--shadow-sm);
    }

    .service-card h3 {
        font-size: 1.1rem;
        margin-bottom: 8px;
    }

    .service-card .rate {
        display: inline-block;
        background: var(--yc-orange-light);
        color: var(--yc-orange);
        padding: 2px 10px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .service-card p {
        font-size: 14px;
        margin-bottom: 16px;
    }

    .why-section {
        padding: 80px 24px;
    }

    .why-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        max-width: 1000px;
        margin: 40px auto 0;
    }

    .why-card {
        padding: 24px;
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
    }

    .why-card h3 {
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .why-card p {
        font-size: 14px;
        margin: 0;
    }

    .why-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--yc-orange-light);
        border-radius: var(--radius);
        color: var(--yc-orange);
        flex-shrink: 0;
    }

    .testimonials-section {
        padding: 80px 24px;
        background: var(--bg-white);
    }

    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        max-width: 1000px;
        margin: 40px auto 0;
    }

    .testimonial-card {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 24px;
    }

    .testimonial-card p {
        font-size: 14px;
        font-style: italic;
        margin-bottom: 16px;
    }

    .testimonial-author {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .testimonial-role {
        font-size: 12px;
        color: var(--text-muted);
    }

    .cta-banner {
        background: var(--yc-orange);
        padding: 64px 24px;
        text-align: center;
    }

    .cta-banner h2 {
        color: #fff;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .cta-banner .btn {
        background: #fff;
        color: var(--yc-orange);
        font-weight: 700;
        border: none;
        padding: 14px 32px;
        font-size: 16px;
    }

    .cta-banner .btn:hover {
        background: #f0f0f0;
    }

    @media (max-width: 768px) {
        .landing-hero h1 {
            font-size: 2rem;
        }

        .how-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .why-grid {
            grid-template-columns: 1fr;
        }

        .stats-bar-inner {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>

<main>
    <!-- HERO -->
    <section class="landing-hero">
        <h1><?= __('hero.headline') ?></h1>
        <p><?= __('hero.sub') ?></p>
        <div class="hero-ctas">
            <a href="register-customer.php" class="btn btn-primary btn-lg"><?= __('hero.cta_start') ?></a>
            <a href="#how-it-works" class="btn btn-ghost btn-lg"><?= __('hero.cta_how') ?></a>
        </div>
    </section>

    <!-- STATS BAR -->
    <section class="stats-bar">
        <div class="stats-bar-inner">
            <div>
                <div class="stats-bar-num">200+</div>
                <div class="stats-bar-label"><?= __('stats.applications') ?></div>
            </div>
            <div>
                <div class="stats-bar-num">78%</div>
                <div class="stats-bar-label"><?= __('stats.approval') ?></div>
            </div>
            <div>
                <div class="stats-bar-num">3×</div>
                <div class="stats-bar-label"><?= __('stats.faster') ?></div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-section" id="how-it-works">
        <h2><?= __('how.title') ?></h2>
        <div class="how-grid">
            <div class="how-card">
                <div class="how-num">1</div>
                <h4><?= __('how.step1') ?></h4>
                <p>Create your account and upload your business documents securely.</p>
            </div>
            <div class="how-card">
                <div class="how-num">2</div>
                <h4><?= __('how.step2') ?></h4>
                <p>Our AI engine scores your financial profile and risk level instantly.</p>
            </div>
            <div class="how-card">
                <div class="how-num">3</div>
                <h4><?= __('how.step3') ?></h4>
                <p>A dedicated Kenes consultant reviews and prepares your bank proposal.</p>
            </div>
            <div class="how-card">
                <div class="how-num">4</div>
                <h4><?= __('how.step4') ?></h4>
                <p>Your application is submitted and you receive the bank's decision.</p>
            </div>
        </div>
    </section>

    <!-- SERVICES PREVIEW -->
    <section class="services-preview">
        <div class="text-center">
            <h2><?= __('services.title') ?></h2>
        </div>
        <div class="services-grid">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $svc): ?>
                    <div class="service-card">
                        <h3><?= htmlspecialchars($svc['name']) ?></h3>
                        <?php if ($svc['interest_rate']): ?>
                            <span class="rate">from <?= $svc['interest_rate'] ?>% APR</span>
                        <?php endif; ?>
                        <p><?= htmlspecialchars($svc['description']) ?></p>
                        <a href="services.php" class="btn btn-text"><?= __('services.learn_more') ?> →</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="service-card">
                    <h3>SME Working Capital</h3>
                    <span class="rate">from 14% APR</span>
                    <p>Short-term financing for small and medium enterprises.</p>
                    <a href="services.php" class="btn btn-text"><?= __('services.learn_more') ?> →</a>
                </div>
                <div class="service-card">
                    <h3>Equipment Leasing</h3>
                    <span class="rate">from 12% APR</span>
                    <p>Acquire machinery without draining your cash flow.</p>
                    <a href="services.php" class="btn btn-text"><?= __('services.learn_more') ?> →</a>
                </div>
                <div class="service-card">
                    <h3>Microcredit</h3>
                    <span class="rate">from 18% APR</span>
                    <p>Small-scale funding for startups and entrepreneurs.</p>
                    <a href="services.php" class="btn btn-text"><?= __('services.learn_more') ?> →</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- WHY KENES -->
    <section class="why-section">
        <div class="text-center">
            <h2><?= __('why.title') ?></h2>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <h3>
                    <span class="why-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                        </svg>
                    </span>
                    <?= __('why.speed') ?>
                </h3>
                <p><?= __('why.speed_desc') ?></p>
            </div>
            <div class="why-card">
                <h3>
                    <span class="why-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </span>
                    <?= __('why.expertise') ?>
                </h3>
                <p><?= __('why.expertise_desc') ?></p>
            </div>
            <div class="why-card">
                <h3>
                    <span class="why-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </span>
                    <?= __('why.transparency') ?>
                </h3>
                <p><?= __('why.transparency_desc') ?></p>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="testimonials-section">
        <div class="text-center">
            <h2><?= __('testimonials.title') ?></h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <p>"Kenes helped us secure a 15M KZT loan in just 2 weeks. The AI analysis was incredibly accurate and
                    our consultant guided us through every step."</p>
                <div class="testimonial-author">Alikhan Serikbayev</div>
                <div class="testimonial-role">CEO, TechSteppe LLC — Almaty</div>
            </div>
            <div class="testimonial-card">
                <p>"Before Kenes, I spent 3 months collecting documents manually. They automated everything and I had my
                    Damu loan approved in 10 days."</p>
                <div class="testimonial-author">Dinara Mukhamejanova</div>
                <div class="testimonial-role">Founder, GreenFarm KH — Kostanay</div>
            </div>
            <div class="testimonial-card">
                <p>"The proposal document they generated was so professional the bank said it was the best-prepared
                    application they'd ever received."</p>
                <div class="testimonial-author">Nurlan Orazov</div>
                <div class="testimonial-role">Director, Atlas Import TOO — Astana</div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section class="cta-banner">
        <h2><?= __('cta.ready') ?></h2>
        <a href="register-customer.php" class="btn btn-lg"><?= __('cta.button') ?></a>
    </section>
</main>

<?php include 'includes/footer.php'; ?>