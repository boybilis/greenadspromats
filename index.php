<?php
function asset_version(string $path): string
{
    $fullPath = __DIR__ . '/' . ltrim($path, '/');
    return file_exists($fullPath) ? (string) filemtime($fullPath) : (string) time();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Green Ads &amp; Promats, Inc. | Wear • Promote • Connect</title>
  <meta name="description" content="Green Ads &amp; Promats, Inc. manufactures customized apparel, promotional giveaways, banners, and branded merchandise for businesses in the Philippines.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --brand-50: #eff6f5;
      --brand-100: #d8efea;
      --brand-300: #63a095;
      --brand-500: #22ab68;
      --brand-600: #13ca6d;
      --brand-700: #18584c;
      --brand-900: #1a3932;
      --ink: #0f1716;
      --muted: #5f6f6a;
      --line: rgba(24, 88, 76, 0.14);
      --glass: rgba(255, 255, 255, 0.58);
      --glass-strong: rgba(255, 255, 255, 0.76);
      --shadow-xl: 0 30px 80px rgba(18, 58, 49, 0.18);
      --shadow-md: 0 18px 40px rgba(18, 58, 49, 0.12);
      --radius-xl: 32px;
      --radius-lg: 24px;
      --radius-md: 18px;
    }

    html {
      scroll-behavior: smooth;
      overflow-x: clip;
    }

    body {
      margin: 0;
      color: var(--ink);
      background:
        radial-gradient(circle at top left, rgba(19, 202, 109, 0.16), transparent 30%),
        radial-gradient(circle at 85% 10%, rgba(24, 88, 76, 0.18), transparent 28%),
        linear-gradient(180deg, #f7fbfa 0%, #eef6f3 100%);
      font-family: "Manrope", sans-serif;
      overflow-x: clip;
      width: 100%;
    }

    .site-shell,
    main,
    section,
    .container,
    .row {
      max-width: 100%;
    }

    .site-shell {
      width: 100%;
      overflow-x: clip;
    }

    .row > * {
      min-width: 0;
    }

    h1, h2, h3, h4, .brand-title {
      font-family: "Sora", sans-serif;
      letter-spacing: -0.03em;
    }

    section {
      position: relative;
      padding: 5.5rem 0;
    }

    section[id] {
      scroll-margin-top: 5.75rem;
    }

    .section-title {
      font-size: clamp(1.6rem, 3.2vw, 2.7rem);
      line-height: 1.02;
      margin-bottom: 1rem;
    }

    .section-copy {
      font-size: 1.05rem;
      line-height: 1.8;
      color: var(--muted);
    }

    .site-shell::before,
    .site-shell::after {
      content: "";
      position: fixed;
      inset: auto;
      width: 22rem;
      height: 22rem;
      border-radius: 50%;
      filter: blur(30px);
      z-index: -1;
      opacity: 0.55;
      pointer-events: none;
    }

    .site-shell::before {
      top: 3rem;
      left: -7rem;
      background: rgba(19, 202, 109, 0.18);
    }

    .site-shell::after {
      right: -8rem;
      bottom: 10rem;
      background: rgba(24, 88, 76, 0.16);
    }

    .glass-nav {
      background: rgba(255, 255, 255, 0.68);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      border-bottom: 1px solid rgba(24, 88, 76, 0.08);
      box-shadow: 0 10px 30px rgba(18, 58, 49, 0.08);
    }

    .navbar {
      padding: 1rem 0;
    }

    .navbar > .container {
      flex-wrap: nowrap;
      gap: 0.75rem;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      color: var(--ink);
      font-weight: 800;
      margin-right: auto;
      min-width: 0;
    }

    .brand-mark {
      width: min(100%, 340px);
      height: auto;
      flex: 0 0 auto;
      display: block;
    }

    .navbar .nav-link {
      color: #23463f;
      font-weight: 600;
      padding-inline: 0.8rem;
      white-space: nowrap;
    }

    .navbar .btn-brand {
      padding: 0.78rem 1.1rem;
      font-size: 0.95rem;
      line-height: 1;
      white-space: nowrap;
    }

    .navbar .nav-link:hover,
    .navbar .nav-link:focus {
      color: var(--brand-500);
    }

    .navbar-toggler {
      position: relative;
      z-index: 3;
      flex: 0 0 auto;
    }

    .navbar-collapse {
      z-index: 2;
    }

    @media (max-width: 1199.98px) {
      .glass-nav .container {
        position: relative;
      }

      .navbar-collapse {
        position: absolute;
        top: calc(100% + 0.7rem);
        right: 0.75rem;
        left: 0.75rem;
        padding: 1rem;
        border-radius: 24px;
        background: rgb(255, 255, 255);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.72);
        box-shadow: 0 24px 48px rgba(18, 58, 49, 0.16);
      }

      .navbar-collapse:not(.show) {
        display: none;
      }

      .navbar-collapse .navbar-nav {
        gap: 0.2rem;
      }

      .navbar-collapse .nav-link {
        padding: 0.9rem 1rem;
        border-radius: 14px;
      }

      .navbar-collapse .nav-link:hover,
      .navbar-collapse .nav-link:focus {
        background: rgba(34, 171, 104, 0.08);
      }

      .navbar-collapse .nav-item.ms-lg-2 {
        margin-left: 0 !important;
      }

      .navbar-collapse .nav-item.mt-3 {
        margin-top: 0.75rem !important;
      }

      .navbar-collapse .btn-brand {
        width: 100%;
        justify-content: center;
      }
    }

    .btn-brand {
      background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
      color: #fff;
      border: 0;
      border-radius: 999px;
      padding: 0.9rem 1.5rem;
      font-weight: 700;
      box-shadow: 0 12px 28px rgba(24, 88, 76, 0.28);
    }

    .btn-brand:hover,
    .btn-brand:focus {
      color: #fff;
      transform: translateY(-2px);
    }

    .btn-ghost {
      border-radius: 999px;
      padding: 0.9rem 1.5rem;
      font-weight: 700;
      border: 1px solid rgba(24, 88, 76, 0.16);
      background: rgba(255, 255, 255, 0.72);
      color: var(--brand-900);
      backdrop-filter: blur(12px);
    }

    .hero {
      padding-top: 9rem;
      padding-bottom: 5rem;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.6rem 0.95rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.75);
      border: 1px solid rgba(24, 88, 76, 0.1);
      color: var(--brand-700);
      font-size: 0.8rem;
      font-weight: 800;
      letter-spacing: 0.18em;
      text-transform: uppercase;
    }

    .hero h1 {
      font-size: clamp(2rem, 4.8vw, 4rem);
      line-height: 1.02;
      margin: 1.25rem 0 1.4rem;
    }

    .hero-lead {
      font-size: 1.15rem;
      line-height: 1.85;
      color: var(--muted);
      max-width: 42rem;
    }

    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.9rem;
      margin-top: 2rem;
    }

    .hero-metrics {
      margin-top: 2.2rem;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 1rem;
    }

    .metric-card,
    .glass-card,
    .info-card,
    .product-card,
    .contact-card {
      background: var(--glass);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.62);
      box-shadow: var(--shadow-md);
    }

    .metric-card {
      border-radius: 22px;
      padding: 1.25rem;
    }

    .metric-card strong {
      display: block;
      font-size: 1.85rem;
      color: var(--brand-900);
      margin-bottom: 0.35rem;
    }

    .hero-visual-wrap {
      position: relative;
      padding: 1.1rem;
      border-radius: var(--radius-xl);
      background: linear-gradient(140deg, rgba(255, 255, 255, 0.6), rgba(34, 171, 104, 0.12));
      box-shadow: var(--shadow-xl);
    }

    .hero-visual-wrap::before {
      content: "";
      position: absolute;
      inset: 0.9rem;
      border-radius: calc(var(--radius-xl) - 10px);
      border: 1px solid rgba(255, 255, 255, 0.7);
      pointer-events: none;
    }

    .hero-visual {
      width: 100%;
      min-height: 620px;
      border-radius: 26px;
      overflow: hidden;
      background: #f8fbfa;
      position: relative;
    }

    .hero-visual img {
      width: 100%;
      height: 100%;
      min-height: 620px;
      object-fit: cover;
      object-position: center center;
      display: block;
    }

    .floating-panel {
      position: absolute;
      right: -1rem;
      bottom: 2.2rem;
      max-width: 260px;
      padding: 1.2rem;
      border-radius: 24px;
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.42);
      color: #ffffff;
    }

    .floating-panel strong {
      display: block;
      margin-bottom: 0.35rem;
      font-size: 1.1rem;
      color: #ffffff;
    }

    .floating-panel,
    .floating-panel p,
    .floating-panel .text-secondary {
      color: #ffffff !important;
    }

    .trust-strip {
      padding-top: 1.3rem;
      padding-bottom: 0;
    }

    .trust-card {
      border-radius: var(--radius-lg);
      padding: 1rem 1.25rem;
      background: rgba(255, 255, 255, 0.66);
      border: 1px solid rgba(24, 88, 76, 0.1);
      box-shadow: 0 10px 28px rgba(18, 58, 49, 0.08);
    }

    .trust-card span {
      display: block;
      font-size: 0.83rem;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.18em;
      margin-bottom: 0.35rem;
    }

    .trust-card strong {
      font-size: 1.15rem;
      color: var(--brand-900);
    }

    .info-card,
    .contact-card {
      border-radius: var(--radius-lg);
      padding: 1.8rem;
      height: 100%;
    }

    .info-card .icon-badge,
    .contact-card .icon-badge,
    .product-card .icon-badge {
      width: 3rem;
      height: 3rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 16px;
      background: linear-gradient(135deg, rgba(19, 202, 109, 0.18), rgba(24, 88, 76, 0.18));
      color: var(--brand-700);
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }

    #about {
      background:
        linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)),
        url("assets/images/cover.jpg?v=<?php echo asset_version('assets/images/cover.jpg'); ?>") center center / cover no-repeat;
    }

    .split-highlight {
      border-radius: var(--radius-xl);
      overflow: hidden;
      position: relative;
      min-height: 100%;
      box-shadow: var(--shadow-xl);
      background: linear-gradient(180deg, rgba(24, 88, 76, 0.12), rgba(24, 88, 76, 0.2));
    }

    .split-highlight img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center center;
      min-height: 520px;
      display: block;
    }

    .split-highlight .overlay-card {
      position: absolute;
      left: 1.4rem;
      right: 1.4rem;
      bottom: 1.4rem;
      z-index: 2;
      border-radius: 22px;
      padding: 1.15rem;
      background: rgba(13, 28, 25, 0.55);
      color: #fff;
      backdrop-filter: blur(16px);
    }

    .equipment-panel {
      padding: 2rem;
      border-radius: var(--radius-xl);
      background:
        linear-gradient(135deg, rgba(24, 88, 76, 0.08), rgba(255, 255, 255, 0.8)),
        rgba(255, 255, 255, 0.68);
      box-shadow: var(--shadow-xl);
    }

    .equipment-shot {
      border-radius: 26px;
      overflow: hidden;
      border: 1px solid rgba(24, 88, 76, 0.12);
      background: #fff;
    }

    .equipment-shot img,
    .clients-shot img {
      width: 100%;
      display: block;
    }

    .feature-list {
      list-style: none;
      padding: 0;
      margin: 1.5rem 0 0;
    }

    .feature-list li {
      display: flex;
      gap: 0.9rem;
      align-items: flex-start;
      padding: 0.95rem 0;
      border-top: 1px solid rgba(24, 88, 76, 0.08);
      color: var(--muted);
    }

    .feature-list i {
      color: var(--brand-500);
      font-size: 1rem;
      margin-top: 0.15rem;
    }

    .product-card {
      border-radius: 28px;
      overflow: hidden;
      height: 100%;
      transition: transform 0.35s ease, box-shadow 0.35s ease;
    }

    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 25px 50px rgba(18, 58, 49, 0.18);
    }

    .product-media {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem;
      padding: 1rem 1rem 0;
    }

    .product-media-item {
      overflow: hidden;
      border-radius: 22px;
      background: rgba(255, 255, 255, 0.72);
      border: 1px solid rgba(24, 88, 76, 0.08);
      box-shadow: 0 12px 30px rgba(18, 58, 49, 0.08);
    }

    .product-media-item img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      object-position: center top;
      display: block;
    }

    .product-card .content {
      padding: 1.4rem 1.35rem 1.55rem;
    }

    .product-card h3 {
      font-size: 1.25rem;
      margin-bottom: 0.7rem;
    }

    .pill {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      border-radius: 999px;
      padding: 0.45rem 0.8rem;
      background: rgba(19, 202, 109, 0.14);
      color: var(--brand-700);
      font-size: 0.8rem;
      font-weight: 700;
      margin: 0.25rem 0.35rem 0 0;
    }

    .clients-shot {
      overflow: hidden;
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-xl);
      background: rgba(255, 255, 255, 0.74);
      border: 1px solid rgba(255, 255, 255, 0.72);
    }

    main img {
      cursor: zoom-in;
    }

    .image-lightbox-modal {
      background: rgba(0, 0, 0, 0.58);
    }

    .image-lightbox-modal .modal-dialog {
      margin: 0;
    }

    .image-lightbox-modal .modal-content {
      background: transparent;
      border: 0;
      border-radius: 0;
      min-height: 100vh;
    }

    .image-lightbox-modal .modal-body {
      min-height: 100vh;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .image-lightbox-modal img {
      max-width: 100%;
      max-height: calc(100vh - 4rem);
      width: auto;
      height: auto;
      display: block;
      object-fit: contain;
      margin: 0 auto;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
    }

    .image-lightbox-close {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 1060;
      width: 3rem;
      height: 3rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.14);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      opacity: 1;
      background-size: 1.05rem;
      background-position: center;
      background-repeat: no-repeat;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='white' stroke-linecap='round' stroke-width='2'%3e%3cpath d='M3 3l10 10M13 3L3 13'/%3e%3c/svg%3e");
    }

    .contact-card a {
      color: var(--brand-900);
      text-decoration: none;
    }

    .contact-line {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
      padding: 0.95rem 0;
      border-top: 1px solid rgba(24, 88, 76, 0.08);
    }

    .contact-line > div {
      min-width: 0;
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .contact-line a {
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .contact-line:first-of-type {
      border-top: 0;
      padding-top: 0;
    }

    .contact-line i {
      color: var(--brand-500);
      font-size: 1.1rem;
      margin-top: 0.15rem;
    }

    .contact-cta {
      border-radius: var(--radius-xl);
      padding: 2rem;
      background:
        linear-gradient(135deg, rgba(24, 88, 76, 0.92), rgba(19, 202, 109, 0.84)),
        #17473d;
      color: #fff;
      box-shadow: var(--shadow-xl);
    }

    .contact-cta .btn-ghost {
      background: rgba(255, 255, 255, 0.16);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.18);
    }

    footer {
      padding: 1.7rem 0 2.5rem;
      color: var(--muted);
    }

    .footer-brand {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      color: var(--ink);
      font-weight: 700;
    }

    .footer-brand img {
      width: min(100%, 320px);
      height: auto;
      display: block;
    }

    .reveal {
      opacity: 0;
      transform: translateY(26px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .reveal.is-visible {
      opacity: 1;
      transform: none;
    }

    @media (max-width: 1199.98px) {
      .hero-visual {
        min-height: 520px;
      }

      .hero-visual img {
        min-height: 520px;
      }

      .floating-panel {
        position: static;
        max-width: none;
        margin-top: 1rem;
      }
    }

    @media (max-width: 991.98px) {
      section {
        padding: 4.6rem 0;
      }

      .hero {
        padding-top: 6.8rem;
      }

      .hero-metrics {
        grid-template-columns: 1fr;
      }

      .hero-visual {
        min-height: 420px;
      }

      .hero-visual img {
        min-height: 420px;
      }

      .split-highlight img {
        min-height: 400px;
      }
    }

    @media (max-width: 767.98px) {
      .brand-mark {
        width: min(100%, 200px);
      }

      .container {
        padding-left: 1rem;
        padding-right: 1rem;
      }

      .row {
        margin-left: 0;
        margin-right: 0;
      }

      .row > * {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
      }

      .hero-metrics {
        display: none;
      }

      .floating-panel {
        background: rgba(14, 20, 18, 0.78);
        border-color: rgba(255, 255, 255, 0.14);
      }

      .floating-panel,
      .floating-panel strong,
      .floating-panel p,
      .floating-panel .text-white {
        color: #fff !important;
      }

      .navbar {
        padding: 0.85rem 0;
      }

      section[id] {
        scroll-margin-top: 5rem;
      }

      .navbar-collapse {
        left: 1rem;
        right: 1rem;
      }

      .navbar-toggler {
        flex: 0 0 auto;
        padding: 0.35rem 0.5rem;
      }

      .hero h1 {
        font-size: clamp(1.75rem, 8vw, 2.6rem);
      }

      .hero-lead,
      .section-copy {
        font-size: 1rem;
      }

      .hero-actions {
        flex-direction: column;
      }

      .hero-actions .btn-brand,
      .hero-actions .btn-ghost,
      .contact-cta .btn-brand,
      .contact-cta .btn-ghost {
        width: 100%;
        justify-content: center;
      }

      .product-media {
        grid-template-columns: 1fr;
      }

      .product-media-item img {
        height: 300px;
      }

      .contact-line {
        gap: 0.75rem;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      html {
        scroll-behavior: auto;
      }

      .reveal,
      .product-card,
      .btn-brand {
        transition: none;
      }
    }
  </style>
</head>
<body>
  <div class="site-shell">
    <nav class="navbar navbar-expand-xl fixed-top glass-nav">
      <div class="container">
        <a class="navbar-brand" href="#top" aria-label="Green Ads and Promats home">
          <img class="brand-mark" src="assets/images/full_logo.png?v=<?php echo asset_version('assets/images/full_logo.png'); ?>" alt="Green Ads and Promats full logo">
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
          <ul class="navbar-nav ms-auto align-items-lg-center">
            <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
            <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
            <li class="nav-item"><a class="nav-link" href="#equipment">Equipment</a></li>
            <li class="nav-item"><a class="nav-link" href="#clients">Clients</a></li>
            <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
              <a class="btn btn-brand" href="#contact">Request a Quote</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main id="top">
      <section class="hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="eyebrow reveal">
                <i class="bi bi-stars"></i>
                Garment Manufacturing + Promotional Materials
              </div>
              <h1 class="reveal">Wear. Promote. Connect.</h1>
              <p class="hero-lead reveal">
                <b>Green Ads & Promats, Inc.</b> is a garment manufacturing company, Dedicated to
provide excellent promotional apparel at the most reasonable cost for our
customers. 
              </p>
              <div class="hero-actions reveal">
                <a class="btn btn-brand d-inline-flex align-items-center gap-2" href="mailto:gap.promats@gmail.com">
                  Start Your Project <i class="bi bi-arrow-up-right-circle"></i>
                </a>
                <a class="btn btn-ghost d-inline-flex align-items-center gap-2" href="#products">
                  Explore Products <i class="bi bi-grid"></i>
                </a>
              </div>
              <div class="hero-metrics reveal">
                <div class="metric-card">
                  <strong>2007</strong>
                  <span>Year incorporated as Green Ads &amp; Promats, Inc.</span>
                </div>
                <div class="metric-card">
                  <strong>7+</strong>
                  <span>Core product lines from uniforms to giveaway kits and banners.</span>
                </div>
                <div class="metric-card">
                  <strong>20+</strong>
                  <span>Recognized client brands featured in the company profile.</span>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual-wrap reveal">
                <div class="hero-visual">
                  <img src="assets/images/hero3.png?v=<?php echo asset_version('assets/images/hero3.png'); ?>" alt="Green Ads and Promats hero visual">
                </div>
                <div class="floating-panel glass-card">
                  <strong>Built for Business.</strong>
                  <p class="mb-0 text-black">From custom polos and jackets to event banners and promotional giveaways, the product mix is designed for branding, activations, uniforms, and corporate campaigns.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<!--
      <section class="trust-strip">
        <div class="container">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="trust-card reveal">
                <span>Mission</span>
                <strong>Excellent promotional apparel at reasonable cost.</strong>
              </div>
            </div>
            <div class="col-md-4">
              <div class="trust-card reveal">
                <span>Vision</span>
                <strong>Be a leading supplier of customized promotional apparel in the Philippines.</strong>
              </div>
            </div>
            <div class="col-md-4">
              <div class="trust-card reveal">
                <span>Origins</span>
                <strong>Started in 2002 and grew into a dedicated garments and giveaways manufacturer.</strong>
              </div>
            </div>
          </div>
        </div>
      </section>
-->
      <section id="about">
        <div class="container">
          <div class="row g-4 align-items-stretch">
		 
            <div class="col-lg-5">
			  <div class="eyebrow reveal mb-4">
                <i class="bi bi-stars"></i>
               About Us
              </div>
              <div class="split-highlight reveal">
                <img src="assets/images/bg_about.jpg?v=<?php echo asset_version('assets/images/bg_about.jpg'); ?>" alt="Green Ads and Promats about section cover">
                <div class="overlay-card">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-patch-check-fill"></i>
                    <strong>Manufacturing with brand intent</strong>
                  </div>
                  <p class="mb-0">The company evolved from promotional sourcing into direct manufacturing to control quality, improve turnaround, and deliver better value to clients.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="info-card reveal">
                    <div class="icon-badge"><i class="bi bi-bullseye"></i></div>
                    <h3>Mission</h3>
                    <p class="mb-0 section-copy">Provide excellent promotional apparel at the most reasonable cost while generating healthy sales, fair compensation, and a comfortable, productive work environment.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-card reveal">
                    <div class="icon-badge"><i class="bi bi-eye"></i></div>
                    <h3>Vision</h3>
                    <p class="mb-0 section-copy">Become a leading supplier of customized promotional apparel in the Philippines through consistency, quality, and long-term client trust.</p>
                  </div>
                </div>
                <div class="col-12">
                  <div class="info-card reveal">
                    <div class="icon-badge"><i class="bi bi-clock-history"></i></div>
                    <h3>Our Beginnings</h3>
                    <p class="section-copy mb-3">
                      Green Ads &amp; Promats, Inc. was formerly known as MBM Giftshoppe, conceptualized in 2002 to serve a growing demand for corporate and promotional materials. In 2007, the founders formalized the business and focused on garments and giveaways, moving into direct manufacturing to reduce cost, meet deadlines, and ensure better quality control.
                    </p>
                    <p class="section-copy mb-0">
                      That evolution remains the core advantage today: a company that understands both promotion and production, and can translate branding requirements into finished products that are practical, presentable, and scalable.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="products">
        <div class="container">
          <div class="row justify-content-between align-items-end mb-4">
            <div class="col-lg-7">
              <p class="eyebrow reveal mb-3"><i class="bi bi-grid-1x2"></i> Product Range</p>
              <h2 class="section-title reveal">Promotional apparel, merchandise, and print products under one roof.</h2>
            </div>
            <div class="col-lg-4">
              <p class="section-copy reveal mb-0">The brochure highlights a broad catalog for internal uniforms, event kits, retail merchandise, and campaign collateral, combining garment manufacturing with print and giveaway production.</p>
            </div>
          </div>
          <div class="row g-4">
            <div class="col-md-6">
              <article class="product-card reveal">
                <div class="product-media">
                  <div class="product-media-item">
                    <img src="assets/images/customized polo.png?v=<?php echo asset_version('assets/images/customized polo.png'); ?>" alt="Customized polo shirts">
                  </div>
                  <div class="product-media-item">
                    <img src="assets/images/customized jacket.png?v=<?php echo asset_version('assets/images/customized jacket.png'); ?>" alt="Customized jackets">
                  </div>
                </div>
                <div class="content">
                  <div class="icon-badge"><i class="bi bi-person-badge"></i></div>
                  <h3>Customized Polos &amp; Jackets</h3>
                  <p class="section-copy mb-3">Branded polo shirts, corporate outerwear, hoodies, and custom jackets tailored for uniforms, events, and promotional programs.</p>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Corporate uniforms</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Event apparel</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Team outerwear</span>
                </div>
              </article>
            </div>
            <div class="col-md-6">
              <article class="product-card reveal">
                <div class="product-media">
                  <div class="product-media-item">
                    <img src="assets/images/jerseys.png?v=<?php echo asset_version('assets/images/jerseys.png'); ?>" alt="Customized jerseys">
                  </div>
                  <div class="product-media-item">
                    <img src="assets/images/crew necks.png?v=<?php echo asset_version('assets/images/crew necks.png'); ?>" alt="Crew neck shirts">
                  </div>
                </div>
                <div class="content">
                  <div class="icon-badge"><i class="bi bi-trophy"></i></div>
                  <h3>Jerseys &amp; Crew Neck Shirts</h3>
                  <p class="section-copy mb-3">Sublimated jerseys, activewear-inspired tops, and statement crew necks for organizations, sports, campaigns, and retail-ready runs.</p>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Sports jerseys</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Campaign shirts</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Retail runs</span>
                </div>
              </article>
            </div>
            <div class="col-md-6">
              <article class="product-card reveal">
                <div class="product-media">
                  <div class="product-media-item">
                    <img src="assets/images/ecobag.png?v=<?php echo asset_version('assets/images/ecobag.png'); ?>" alt="Ecobags and drawstrings">
                  </div>
                  <div class="product-media-item">
                    <img src="assets/images/promotional.png?v=<?php echo asset_version('assets/images/promotional.png'); ?>" alt="Promotional giveaways">
                  </div>
                </div>
                <div class="content">
                  <div class="icon-badge"><i class="bi bi-gift"></i></div>
                  <h3>Eco Bags &amp; Promotional Giveaways</h3>
                  <p class="section-copy mb-3">Reusable bags, drawstrings, and branded giveaway items suited for activations, employee kits, merchandise packs, and promotional campaigns.</p>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Eco bags</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Lanyards</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Giveaway kits</span>
                </div>
              </article>
            </div>
            <div class="col-md-6">
              <article class="product-card reveal">
                <div class="product-media">
                  <div class="product-media-item">
                    <img src="assets/images/digital.png?v=<?php echo asset_version('assets/images/digital.png'); ?>" alt="Digital printed banners">
                  </div>
                  <div class="product-media-item">
                    <img src="assets/images/sublimation.jpg?v=<?php echo asset_version('assets/images/sublimation.jpg'); ?>" alt="Promotional giveaways and event items">
                  </div>
                </div>
                <div class="content">
                  <div class="icon-badge"><i class="bi bi-easel2"></i></div>
                  <h3>Digital Prints &amp; Event Support</h3>
                  <p class="section-copy mb-3">Dye-sublimation banners, flags, display materials, and supporting promotional items for exhibits, launches, fairs, and on-ground campaigns.</p>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Event banners</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Flags &amp; exhibits</span>
                  <span class="pill"><i class="bi bi-check2-circle"></i> Campaign freebies</span>
                </div>
              </article>
            </div>
          </div>
        </div>
      </section>

      <section id="equipment">
        <div class="container">
          <div class="equipment-panel reveal">
            <div class="row g-4 align-items-center">
              <div class="col-lg-5">
                <p class="eyebrow mb-3"><i class="bi bi-gear-wide-connected"></i> Production Capability</p>
                <h2 class="section-title">Equipment that supports both sewing and print production.</h2>
                <p class="section-copy">
                  The company profile features specialized sewing machines and print equipment that allow Green Ads &amp; Promats to handle apparel construction, finishing, cutting, heat transfer, and promotional print output within the same workflow.
                </p>
                <ul class="feature-list">
                  <li><i class="bi bi-check2-circle"></i><span>Industrial sewing coverage including single needle, button hole, piping, overlock, bar tack, waistband, double needle, flat seam, zigzag, and cutting equipment.</span></li>
                  <li><i class="bi bi-check2-circle"></i><span>Printing support with sublimation printer, vinyl cutter plotter, roller heat press, single heat press, and mugs heat press.</span></li>
                  <li><i class="bi bi-check2-circle"></i><span>A direct-manufacturing setup that helps protect quality, control lead time, and keep product output consistent.</span></li>
                </ul>
              </div>
              <div class="col-lg-7">
                <div class="equipment-shot">
                  <img src="assets/images/equipments.png?v=<?php echo asset_version('assets/images/equipments.png'); ?>" alt="Green Ads and Promats manufacturing equipment">
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="clients">
        <div class="container">
          <div class="row align-items-end mb-4">
            <div class="col-lg-7">
              <p class="eyebrow reveal mb-3"><i class="bi bi-buildings"></i> Client Portfolio</p>
              <h2 class="section-title reveal">Trusted by recognizable brands and organizations.</h2>
            </div>
            <div class="col-lg-5">
              <p class="section-copy reveal mb-0">The company profile lists clients from manufacturing, telecom, food, entertainment, finance, hospitality, and logistics, reinforcing the breadth of industries already served.</p>
            </div>
          </div>
          <div class="clients-shot reveal">
            <img src="assets/images/clients.jpg?v=<?php echo asset_version('assets/images/clients.jpg'); ?>" alt="Logos of Green Ads and Promats clients">
          </div>
        </div>
      </section>

      <section id="contact">
        <div class="container">
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="contact-card reveal">
                <div class="icon-badge"><i class="bi bi-geo-alt"></i></div>
                <h2 class="section-title fs-1">Company Details</h2>
                <div class="contact-line">
                  <i class="bi bi-geo-alt-fill"></i>
                  <div>
                    <strong>Address</strong><br>
                    17 Bodoni St., Fourth Estate Subdv., Parañaque City
                  </div>
                </div>
                <div class="contact-line">
                  <i class="bi bi-telephone-fill"></i>
                  <div>
                    <strong>Contact Numbers</strong><br>
                    <a href="tel:+6328204844">(632) 820 4844</a> / <a href="tel:+6328253663">825 36 63</a> / <a href="tel:+6329441063">944 10 63</a>
                  </div>
                </div>
                <div class="contact-line">
                  <i class="bi bi-envelope-fill"></i>
                  <div>
                    <strong>Email</strong><br>
                    <a href="mailto:gap.promats@gmail.com">gap.promats@gmail.com</a><br>
                    <a href="mailto:gap.promats@yahoo.com">gap.promats@yahoo.com</a>
                  </div>
                </div>
                <div class="contact-line">
                  <i class="bi bi-facebook"></i>
                  <div>
                    <strong>Facebook</strong><br>
                    <a href="https://facebook.com/greenadsandpromats" target="_blank" rel="noreferrer">facebook.com/greenadsandpromats</a>
                  </div>
                </div>
                <div class="contact-line">
                  <i class="bi bi-instagram"></i>
                  <div>
                    <strong>Instagram</strong><br>
                    <a href="https://instagram.com/green_ads_and_promats_inc" target="_blank" rel="noreferrer">@green_ads_and_promats_inc</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="contact-cta reveal h-100 d-flex flex-column justify-content-between">
                <div>
                  <p class="eyebrow text-white bg-transparent border border-light-subtle mb-3"><i class="bi bi-lightning-charge-fill"></i> Ready to brief your project?</p>
                  <h2 class="section-title text-white">Promotional apparel that looks sharp, feels considered, and arrives ready to represent your brand.</h2>
                  <p class="mb-4 text-white-50 fs-5">For uniforms, giveaways, branded apparel, or event graphics, Green Ads &amp; Promats can help translate your concept into production-ready output.</p>
                </div>
                <div class="d-flex flex-wrap gap-3">
                  <a class="btn btn-brand" href="mailto:gap.promats@gmail.com?subject=Project%20Inquiry%20for%20Green%20Ads%20%26%20Promats">Email the Team</a>
                  <a class="btn btn-ghost" href="tel:+6328204844">Call the Office</a>
                </div>
                <div class="mt-4 pt-4 border-top border-light border-opacity-25">
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <div class="small text-white-50 text-uppercase mb-1">Officer</div>
                      <div class="fw-semibold fs-5">Raoul C. Navarrete</div>
                      <div>Director</div>
                    </div>
                    <div class="col-sm-6">
                      <div class="small text-white-50 text-uppercase mb-1">Officer</div>
                      <div class="fw-semibold fs-5">Kristine N. Navarrete</div>
                      <div>Director</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>© 2026 Green Ads &amp; Promats, Inc. All rights reserved.</div>
        <div></div>
      </div>
    </footer>
  </div>

  <div class="modal fade image-lightbox-modal" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageLightboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <button type="button" class="btn-close image-lightbox-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-body">
          <img id="imageLightboxTarget" src="" alt="">
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
    const revealItems = document.querySelectorAll(".reveal");
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.14 });

    revealItems.forEach((item, index) => {
      item.style.transitionDelay = `${Math.min(index * 35, 240)}ms`;
      observer.observe(item);
    });

    const lightboxModalElement = document.getElementById("imageLightboxModal");
    const lightboxTarget = document.getElementById("imageLightboxTarget");

    if (lightboxModalElement && lightboxTarget && window.bootstrap) {
      const lightboxModal = new bootstrap.Modal(lightboxModalElement);
      const lightboxImages = document.querySelectorAll("main img");

      lightboxImages.forEach((image) => {
        image.addEventListener("click", () => {
          const source = image.getAttribute("src");
          const alt = image.getAttribute("alt") || "Expanded image";

          lightboxTarget.setAttribute("src", source);
          lightboxTarget.setAttribute("alt", alt);
          lightboxModal.show();
        });
      });

      lightboxModalElement.addEventListener("hidden.bs.modal", () => {
        lightboxTarget.setAttribute("src", "");
        lightboxTarget.setAttribute("alt", "");
      });
    }

    const navCollapseElement = document.getElementById("siteNav");
    if (navCollapseElement && window.bootstrap) {
      const navCollapseInstance = bootstrap.Collapse.getOrCreateInstance(navCollapseElement, { toggle: false });
      const navLinks = navCollapseElement.querySelectorAll(".nav-link, .btn-brand");

      navLinks.forEach((link) => {
        link.addEventListener("click", () => {
          if (window.innerWidth < 1200 && navCollapseElement.classList.contains("show")) {
            navCollapseInstance.hide();
          }
        });
      });
    }
  </script>
</body>
</html>
