<?= view('public/render', [
    'page' => $page ?? [],
    'isPreview' => $isPreview ?? true,
    'guestbookEntries' => $guestbookEntries ?? [],
]) ?>
<style>
    .aa-fabric-guestbook-control {
        --aa-field-border-color: #cbd5e100 !important;
        border-radius: 10px !important;
        font-size: 14px !important;
    }

    .aa-fabric-selected-sticker {
        display: none !important;
    }
</style>
