<?php
    $conversion = session()->getFlashdata('google_ads_conversion');
    $conversion = is_array($conversion) ? $conversion : [];
    $sendTo = (string) ($conversion['send_to'] ?? 'AW-18262459541/CbuJCLe678YcEJWJnIRE');
    $transactionId = trim((string) ($conversion['transaction_id'] ?? ''));
    $payload = [
        'send_to' => $sendTo,
    ];

    if (isset($conversion['value'])) {
        $payload['value'] = (float) $conversion['value'];
    }

    if (! empty($conversion['currency'])) {
        $payload['currency'] = (string) $conversion['currency'];
    }

    if ($transactionId !== '') {
        $payload['transaction_id'] = $transactionId;
    }
?>
<?php if ($conversion !== []): ?>
    <?= view('components/google_ads_tag') ?>
    <script>
        (function () {
            'use strict';

            if (typeof window.gtag !== 'function') return;

            var payload = <?= json_encode($payload, JSON_UNESCAPED_SLASHES) ?>;
            var transactionId = <?= json_encode($transactionId) ?>;
            var guardKey = transactionId ? 'aa_google_ads_conversion_' + transactionId : '';

            try {
                if (guardKey && sessionStorage.getItem(guardKey) === '1') return;
                if (guardKey) sessionStorage.setItem(guardKey, '1');
            } catch (error) {}

            window.gtag('event', 'conversion', payload);
        })();
    </script>
<?php endif ?>
