<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/plugins/custom/vis-timeline/vis-timeline.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
    rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/theme-overrides.css') }}" rel="stylesheet" type="text/css" />

<style>
    .file-upload-box {
        height: 100px;
    }

    ._image-box {
        margin: 0 auto;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--kt-primary-light);
        border: 2px dashed var(--kt-primary);
        font-size: 32px;
        border-radius: var(--ui-radius, 0.75rem);
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }

    ._image-box:hover {
        border-color: var(--kt-primary-active);
    }

    .image-box {
        width: 100%;
        overflow: hidden;
        border-radius: var(--ui-radius, 0.75rem);
    }

    .image-box img {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .btn-remove-image {
        border-radius: 50%;
        font-size: 8px;
        text-align: center;
        position: absolute;
        border: none;
        right: 26px;
        top: 10px;
        z-index: 2;
    }
</style>
