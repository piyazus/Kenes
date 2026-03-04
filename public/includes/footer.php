<footer
    style="background: var(--bg-white); border-top: 1px solid var(--border); padding: 48px 0 24px; margin-top: 80px;">
    <div class="container">
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; margin-bottom: 32px;">
            <!-- Brand -->
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <span
                        style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: var(--yc-orange); color: #fff; font-weight: 800; border-radius: 4px;">K</span>
                    <span style="font-weight: 700; font-size: 18px;">Kenes</span>
                </div>
                <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                    Damu loan consulting for Kazakhstani businesses. AI-powered, consultant-driven.
                </p>
            </div>

            <!-- Links -->
            <div>
                <h4
                    style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; color: var(--text-muted);">
                    Platform</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><a href="<?= $base_url ?>/services.php"
                            style="display: block; padding: 4px 0; font-size: 14px; color: var(--text-secondary);">
                            <?= __('nav.services') ?>
                        </a></li>
                    <li><a href="<?= $base_url ?>/about.php"
                            style="display: block; padding: 4px 0; font-size: 14px; color: var(--text-secondary);">
                            <?= __('nav.about') ?>
                        </a></li>
                    <li><a href="<?= $base_url ?>/careers.php"
                            style="display: block; padding: 4px 0; font-size: 14px; color: var(--text-secondary);">
                            <?= __('nav.careers') ?>
                        </a></li>
                </ul>
            </div>

            <div>
                <h4
                    style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; color: var(--text-muted);">
                    Legal</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><a href="<?= $base_url ?>/privacy.php"
                            style="display: block; padding: 4px 0; font-size: 14px; color: var(--text-secondary);">
                            <?= __('footer.privacy') ?>
                        </a></li>
                    <li><a href="<?= $base_url ?>/terms.php"
                            style="display: block; padding: 4px 0; font-size: 14px; color: var(--text-secondary);">
                            <?= __('footer.terms') ?>
                        </a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4
                    style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; color: var(--text-muted);">
                    Contact</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="font-size: 14px; color: var(--text-secondary); padding: 4px 0;">info@kenes.kz</li>
                    <li style="font-size: 14px; color: var(--text-secondary); padding: 4px 0;">+7 (727) 123-4567</li>
                    <li style="padding: 8px 0; display: flex; gap: 12px;">
                        <!-- LinkedIn -->
                        <a href="#" style="color: var(--text-muted);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                        <!-- Telegram -->
                        <a href="#" style="color: var(--text-muted);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div
            style="border-top: 1px solid var(--border); padding-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">&copy;
                <?= date('Y') ?> Kenes.
                <?= __('footer.rights') ?>
            </p>
            <div class="lang-switcher">
                <a href="?lang=en" class="<?= ($current_lang ?? 'en') === 'en' ? 'active-lang' : '' ?>">EN</a>
                <a href="?lang=kz" class="<?= ($current_lang ?? 'en') === 'kz' ? 'active-lang' : '' ?>">KZ</a>
                <a href="?lang=ru" class="<?= ($current_lang ?? 'en') === 'ru' ? 'active-lang' : '' ?>">RU</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= $base_url ?>/js/navbar.js"></script>
</body>

</html>