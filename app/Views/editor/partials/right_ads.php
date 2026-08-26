<?php
    return;
    $editorAds = array_values(array_filter((array) ($editorAds ?? []), static fn ($ad): bool => is_array($ad) && ! empty($ad['image_path'])));
?>
<?php if ($editorAds !== []): ?>
    <section class="aa-editor-ads-card" data-editor-ads>
        <div class="aa-editor-ads-slider">
            <?php foreach ($editorAds as $index => $ad): ?>
                <?php
                    $imageUrl = base_url(ltrim((string) ($ad['image_path'] ?? ''), '/'));
                    $title = (string) ($ad['title'] ?? 'Info AdaAcara');
                    $linkUrl = trim((string) ($ad['link_url'] ?? ''));
                ?>
                <article class="aa-editor-ad-slide <?= $index === 0 ? 'is-active' : '' ?>" data-editor-ad-slide="<?= esc((string) $index, 'attr') ?>">
                    <?php if ($linkUrl !== ''): ?>
                        <a href="<?= esc($linkUrl, 'attr') ?>" target="_blank" rel="noopener" aria-label="<?= esc($title, 'attr') ?>">
                            <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" loading="lazy">
                        </a>
                    <?php else: ?>
                        <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" loading="lazy">
                    <?php endif ?>
                </article>
            <?php endforeach ?>

            <?php if (count($editorAds) > 1): ?>
                <div class="aa-editor-ads-dots" aria-label="Slide iklan editor">
                    <?php foreach ($editorAds as $index => $ad): ?>
                        <button class="<?= $index === 0 ? 'is-active' : '' ?>" type="button" data-editor-ad-dot="<?= esc((string) $index, 'attr') ?>" aria-label="Tampilkan iklan <?= esc((string) ($index + 1), 'attr') ?>"></button>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>
    </section>
    <script>
    (function() {
        var root = document.currentScript ? document.currentScript.previousElementSibling : document.querySelector('[data-editor-ads]');
        if (!root || root.dataset.editorAdsBound === '1') return;
        root.dataset.editorAdsBound = '1';

        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-editor-ad-slide]'));
        var dots = Array.prototype.slice.call(root.querySelectorAll('[data-editor-ad-dot]'));
        if (slides.length <= 1) return;

        var activeIndex = 0;
        var timer = null;

        function showSlide(index) {
            activeIndex = (index + slides.length) % slides.length;
            slides.forEach(function(slide, slideIndex) {
                slide.classList.toggle('is-active', slideIndex === activeIndex);
            });
            dots.forEach(function(dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === activeIndex);
            });
        }

        function start() {
            window.clearInterval(timer);
            timer = window.setInterval(function() {
                showSlide(activeIndex + 1);
            }, 3000);
        }

        dots.forEach(function(dot, index) {
            dot.addEventListener('click', function() {
                showSlide(index);
                start();
            });
        });

        start();
    })();
    </script>
<?php endif ?>
