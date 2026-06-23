<?php
session_start();

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value !== false ? trim((string) $value) : $default;
}

function client_ip_address(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $rawValue = trim((string) $_SERVER[$key]);
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = array_map('trim', explode(',', $rawValue));
                $rawValue = (string) ($parts[0] ?? '');
            }
            if (filter_var($rawValue, FILTER_VALIDATE_IP)) {
                return $rawValue;
            }
        }
    }

    return 'unknown';
}

function rate_limit_file_path(string $ipAddress): string
{
    return __DIR__ . '/tmp_rate_limit_' . sha1($ipAddress) . '.json';
}

function is_rate_limited(string $ipAddress, int $limit, int $windowSeconds): bool
{
    $filePath = rate_limit_file_path($ipAddress);
    $now = time();
    $attempts = [];

    if (file_exists($filePath)) {
        $decoded = json_decode((string) file_get_contents($filePath), true);
        if (is_array($decoded)) {
            $attempts = array_values(array_filter($decoded, static fn ($timestamp) => is_int($timestamp) && ($now - $timestamp) < $windowSeconds));
        }
    }

    return count($attempts) >= $limit;
}

function record_rate_limit_attempt(string $ipAddress, int $windowSeconds): void
{
    $filePath = rate_limit_file_path($ipAddress);
    $now = time();
    $attempts = [];

    if (file_exists($filePath)) {
        $decoded = json_decode((string) file_get_contents($filePath), true);
        if (is_array($decoded)) {
            $attempts = array_values(array_filter($decoded, static fn ($timestamp) => is_int($timestamp) && ($now - $timestamp) < $windowSeconds));
        }
    }

    $attempts[] = $now;
    file_put_contents($filePath, json_encode($attempts, JSON_THROW_ON_ERROR));
}

function asset_version(string $path): string
{
    $fullPath = __DIR__ . '/' . ltrim($path, '/');
    return file_exists($fullPath) ? (string) filemtime($fullPath) : (string) time();
}

function gallery_images(string $folder): array
{
    $basePath = __DIR__ . '/' . trim($folder, '/');
    if (!is_dir($basePath)) {
        return [];
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $files = [];

    foreach (scandir($basePath) ?: [] as $fileName) {
        if ($fileName === '.' || $fileName === '..') {
            continue;
        }

        $filePath = $basePath . DIRECTORY_SEPARATOR . $fileName;
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (is_file($filePath) && in_array($extension, $allowedExtensions, true)) {
            $files[] = trim($folder, '/') . '/' . $fileName;
        }
    }

    natsort($files);
    return array_values($files);
}

function image_alt_from_path(string $path, string $fallback): string
{
    $name = pathinfo($path, PATHINFO_FILENAME);
    $name = preg_replace('/[-_]+/', ' ', $name);
    $name = trim((string) preg_replace('/\s+/', ' ', (string) $name));
    return $name !== '' ? ucwords($name) : $fallback;
}

function render_product_carousel(string $carouselId, string $title, string $description, array $images, string $fallbackAlt): void
{
    if (!$images) {
        return;
    }
    $placeholderImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="900" height="900" viewBox="0 0 900 900"%3E%3Crect width="900" height="900" fill="%23f3f7f5"/%3E%3C/svg%3E';
    ?>
    <div class="product-carousel-panel reveal">
      <div class="product-carousel-header">
        <h3><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
        <span><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div id="<?php echo htmlspecialchars($carouselId, ENT_QUOTES, 'UTF-8'); ?>" class="carousel slide" data-bs-ride="false">
        <div class="carousel-inner">
          <?php foreach (array_chunk($images, 4) as $slideIndex => $slideImages): ?>
            <div class="carousel-item <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
              <div class="product-carousel-grid">
                <?php foreach ($slideImages as $imagePath): ?>
                  <?php
                    $imageUrl = $imagePath . '?v=' . asset_version($imagePath);
                    $isInitialSlide = $slideIndex < 2;
                  ?>
                  <div class="product-gallery-item">
                    <img
                      src="<?php echo htmlspecialchars($isInitialSlide ? $imageUrl : $placeholderImage, ENT_QUOTES, 'UTF-8'); ?>"
                      <?php if (!$isInitialSlide): ?>data-src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>
                      data-gallery-src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
                      alt="<?php echo htmlspecialchars(image_alt_from_path($imagePath, $fallbackAlt), ENT_QUOTES, 'UTF-8'); ?>"
                      loading="<?php echo $isInitialSlide ? 'eager' : 'lazy'; ?>"
                      decoding="async"
                    >
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (count($images) > 4): ?>
          <button class="carousel-control-prev product-carousel-control" type="button" data-bs-target="#<?php echo htmlspecialchars($carouselId, ENT_QUOTES, 'UTF-8'); ?>" data-bs-slide="prev" aria-label="Previous <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next product-carousel-control" type="button" data-bs-target="#<?php echo htmlspecialchars($carouselId, ENT_QUOTES, 'UTF-8'); ?>" data-bs-slide="next" aria-label="Next <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$productCategories = [
    'Customized Polos & Jackets',
    'Jerseys & Crew Neck Shirts',
    'Eco Bags & Promotional Giveaways',
    'Digital Prints & Event Support',
];

$collaredImages = gallery_images('assets/images/collared');
$jacketImages = gallery_images('assets/images/jackets');
$sportswearImages = gallery_images('assets/images/sportswear');
$accessoriesImages = gallery_images('assets/images/accessories');
$customizeShirtImages = gallery_images('assets/images/customize shirts');
$clientImages = gallery_images('assets/images/clients');

$smtpConfig = [
    'host' => env_value('GAP_SMTP_HOST', 'smtp.hostinger.com'),
    'port' => (int) env_value('GAP_SMTP_PORT', '465'),
    'username' => env_value('GAP_SMTP_USERNAME', 'sales@greenads.info'),
    'password' => env_value('GAP_SMTP_PASSWORD'),
    'encryption' => env_value('GAP_SMTP_ENCRYPTION', 'ssl'),
    'from_email' => env_value('GAP_FROM_EMAIL', 'sales@greenads.info'),
    'from_name' => env_value('GAP_FROM_NAME', 'Green Ads & Promats Website'),
    'recipient_email' => 'sales@greenads.info',
    'recipient_name' => 'Green Ads Sales',
];

$inquiryForm = [
    'email' => '',
    'contact_number' => '',
    'category' => '',
    'inquiry_details' => '',
    'website' => '',
];

$inquiryErrors = [];
$inquirySuccess = '';
$openInquiryModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'inquiry') {
    $openInquiryModal = true;
    $clientIpAddress = client_ip_address();
    $rateLimitMaxAttempts = 5;
    $rateLimitWindowSeconds = 600;

    $inquiryForm['email'] = trim((string) ($_POST['email'] ?? ''));
    $inquiryForm['contact_number'] = trim((string) ($_POST['contact_number'] ?? ''));
    $inquiryForm['category'] = trim((string) ($_POST['category'] ?? ''));
    $inquiryForm['inquiry_details'] = trim((string) ($_POST['inquiry_details'] ?? ''));
    $inquiryForm['website'] = trim((string) ($_POST['website'] ?? ''));

    if ($inquiryForm['website'] !== '') {
        $inquiryErrors[] = 'Your submission could not be processed. Please try again.';
    }

    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $inquiryErrors[] = 'The form session expired. Please try again.';
    }

    if (is_rate_limited($clientIpAddress, $rateLimitMaxAttempts, $rateLimitWindowSeconds)) {
        $inquiryErrors[] = 'Too many inquiry attempts were sent from this connection. Please wait a few minutes and try again.';
    }

    if (!filter_var($inquiryForm['email'], FILTER_VALIDATE_EMAIL)) {
        $inquiryErrors[] = 'Please enter a valid email address.';
    }

    if ($inquiryForm['contact_number'] === '') {
        $inquiryErrors[] = 'Please enter a contact number.';
    }

    if (!in_array($inquiryForm['category'], $productCategories, true)) {
        $inquiryErrors[] = 'Please choose a product category.';
    }

    if ($inquiryForm['inquiry_details'] === '' || mb_strlen($inquiryForm['inquiry_details']) < 10) {
        $inquiryErrors[] = 'Please enter your inquiry details.';
    }

    if (empty($smtpConfig['password'])) {
        $inquiryErrors[] = 'SMTP password is not configured yet. Set GAP_SMTP_PASSWORD on the server before going live.';
    }

    if (!$inquiryErrors) {
        record_rate_limit_attempt($clientIpAddress, $rateLimitWindowSeconds);

        require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
        require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $smtpConfig['password'];
            $mail->Port = $smtpConfig['port'];

            if ($smtpConfig['encryption'] === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }

            $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
            $mail->addAddress($smtpConfig['recipient_email'], $smtpConfig['recipient_name']);
            $mail->addReplyTo($inquiryForm['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Website Inquiry: ' . $inquiryForm['category'];

            $safeEmail = htmlspecialchars($inquiryForm['email'], ENT_QUOTES, 'UTF-8');
            $safeContact = htmlspecialchars($inquiryForm['contact_number'], ENT_QUOTES, 'UTF-8');
            $safeCategory = htmlspecialchars($inquiryForm['category'], ENT_QUOTES, 'UTF-8');
            $safeDetails = nl2br(htmlspecialchars($inquiryForm['inquiry_details'], ENT_QUOTES, 'UTF-8'));

            $mail->Body = "
                <h2>New Website Inquiry</h2>
                <p><strong>Email:</strong> {$safeEmail}</p>
                <p><strong>Contact Number:</strong> {$safeContact}</p>
                <p><strong>Product Category:</strong> {$safeCategory}</p>
                <p><strong>Inquiry Details:</strong><br>{$safeDetails}</p>
            ";

            $mail->AltBody =
                "New Website Inquiry\n" .
                "Email: {$inquiryForm['email']}\n" .
                "Contact Number: {$inquiryForm['contact_number']}\n" .
                "Product Category: {$inquiryForm['category']}\n\n" .
                "Inquiry Details:\n{$inquiryForm['inquiry_details']}";

            $mail->send();

            $autoReply = new \PHPMailer\PHPMailer\PHPMailer(true);
            $autoReply->isSMTP();
            $autoReply->Host = $smtpConfig['host'];
            $autoReply->SMTPAuth = true;
            $autoReply->Username = $smtpConfig['username'];
            $autoReply->Password = $smtpConfig['password'];
            $autoReply->Port = $smtpConfig['port'];

            if ($smtpConfig['encryption'] === 'tls') {
                $autoReply->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $autoReply->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }

            $autoReply->setFrom($smtpConfig['from_email'], 'Green Ads & Promats Sales');
            $autoReply->addAddress($inquiryForm['email']);
            $autoReply->isHTML(true);
            $autoReply->Subject = 'Thank you for your inquiry';
            $logoPath = __DIR__ . '/assets/images/full_logo.png';
            $logoHtml = '';
            if (file_exists($logoPath)) {
                $autoReply->addEmbeddedImage($logoPath, 'gap_full_logo');
                $logoHtml = '<img src="cid:gap_full_logo" alt="Green Ads &amp; Promats" style="display:block;max-width:280px;width:100%;height:auto;margin:0 auto 24px;">';
            }

            $autoReply->Body = '
                <div style="margin:0;padding:32px 16px;background:#eef6f3;font-family:Arial,sans-serif;color:#18342d;">
                  <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 16px 36px rgba(24,88,76,0.10);">
                    <div style="background:linear-gradient(135deg,#13ca6d,#18584c);padding:28px 24px;text-align:center;">
                      ' . $logoHtml . '
                      <div style="font-size:13px;letter-spacing:3px;text-transform:uppercase;color:#dff8ea;font-weight:bold;">Wear • Promote • Connect</div>
                    </div>
                    <div style="padding:32px 28px;">
                      <h1 style="margin:0 0 16px;font-size:28px;line-height:1.15;color:#1a3932;">Thank you for your inquiry.</h1>
                      <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#45615b;">
                        We have received your message about <strong style="color:#18584c;">' . $safeCategory . '</strong> and our team will keep in touch as soon as possible.
                      </p>
                      <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#45615b;">
                        If you need to add more details, you can reply directly to this email and our sales team will review it.
                      </p>
                      <div style="margin:24px 0 0;padding:18px 20px;border-radius:18px;background:#f4fbf8;border:1px solid #d8efea;">
                        <div style="font-size:14px;line-height:1.7;color:#45615b;"><strong style="color:#1a3932;">Submitted Contact Number:</strong> ' . $safeContact . '</div>
                        <div style="font-size:14px;line-height:1.7;color:#45615b;"><strong style="color:#1a3932;">Submitted Email:</strong> ' . $safeEmail . '</div>
                      </div>
                    </div>
                    <div style="padding:20px 28px;background:#f7fbfa;border-top:1px solid #e1efea;font-size:13px;line-height:1.7;color:#5f6f6a;">
                      Green Ads &amp; Promats Sales Team<br>
                      <a href="mailto:sales@greenads.info" style="color:#18584c;text-decoration:none;">sales@greenads.info</a>
                    </div>
                  </div>
                </div>
            ';
            $autoReply->AltBody = "Thank you for your inquiry with Green Ads & Promats, Inc.\n\nWe have received your message and will keep in touch as soon as possible.\n\nRegards,\nGreen Ads & Promats Sales Team";
            $autoReply->send();

            $inquirySuccess = 'Your inquiry has been sent. The Green Ads sales team will get back to you soon.';
            $openInquiryModal = false;
            $inquiryForm = [
                'email' => '',
                'contact_number' => '',
                'category' => '',
                'inquiry_details' => '',
                'website' => '',
            ];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (\PHPMailer\PHPMailer\Exception $exception) {
            $inquiryErrors[] = 'The inquiry could not be sent right now. ' . $exception->getMessage();
        }
    }
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
      --shadow-xl: 0 18px 42px rgba(18, 58, 49, 0.12);
      --shadow-md: 0 10px 24px rgba(18, 58, 49, 0.08);
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
    section {
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
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(24, 88, 76, 0.08);
      box-shadow: 0 8px 22px rgba(18, 58, 49, 0.06);
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
        border: 1px solid rgba(255, 255, 255, 0.72);
        box-shadow: 0 16px 30px rgba(18, 58, 49, 0.1);
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
    }

    .hero {
      position: relative;
      padding-top: 9rem;
      padding-bottom: 5rem;
      background:
       
        url("assets/images/gap_hero2.jpg?v=<?php echo asset_version('assets/images/gap_hero2.jpg'); ?>") center / cover no-repeat;
      overflow: hidden;
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
      color: white;
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

    .page-feedback {
      position: sticky;
      top: 5.25rem;
      z-index: 1025;
      padding-top: 0.75rem;
    }

    .page-feedback .alert {
      border: 0;
      border-radius: 18px;
      box-shadow: var(--shadow-md);
    }

    .metric-card,
    .glass-card,
    .info-card,
    .product-card,
    .contact-card {
      background: var(--glass);
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
      background: rgba(0, 0, 0, 0.3);
      border-color: rgba(255, 255, 255, 0.42);
      color: #ffffff;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }

    .floating-panel strong {
      display: block;
      margin-bottom: 0.35rem;
      font-size: 1.1rem;
      color: #000000;
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
      box-shadow: 0 8px 18px rgba(18, 58, 49, 0.06);
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
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    .about-split-card {
      min-height: 100%;
      background: rgba(255, 255, 255, 0.72);
    }

    .about-split-card img {
      height: auto;
      min-height: 0;
      aspect-ratio: 16 / 10;
    }

    .about-split-content {
      padding: 1.45rem;
    }

    .about-split-content h3 {
      margin-bottom: 0.65rem;
      font-size: 1.15rem;
      line-height: 1.2;
    }

    .about-split-content .section-copy {
      font-size: 0.92rem;
      line-height: 1.65;
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
      box-shadow: 0 8px 18px rgba(18, 58, 49, 0.06);
      position: relative;
      z-index: 1;
    }

    .product-media-item img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      object-position: center top;
      display: block;
      transition: transform 0.35s ease;
    }

    .product-media-item:hover img {
      transform: scale(1.04);
    }

    .product-media-item:hover {
      z-index: 2;
    }

    .product-card .content {
      padding: 1.4rem 1.35rem 1.55rem;
    }

    .product-card h3 {
      font-size: 1.25rem;
      margin-bottom: 0.7rem;
    }

    .product-showcase {
      display: grid;
      gap: 2rem;
    }

    .product-carousel-panel {
      padding: 1.1rem;
      border-radius: 28px;
      background: rgba(255, 255, 255, 0.62);
      border: 1px solid rgba(255, 255, 255, 0.7);
      box-shadow: var(--shadow-md);
    }

    .product-carousel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
      padding: 0 0.25rem;
    }

    .product-carousel-header h3 {
      margin: 0;
      font-size: 1.35rem;
    }

    .product-carousel-header span {
      color: var(--muted);
      font-size: 0.92rem;
    }

    .product-carousel-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 0.9rem;
    }

    .product-gallery-item {
      position: relative;
      overflow: hidden;
      border-radius: 20px;
      background: #fff;
      border: 1px solid rgba(24, 88, 76, 0.08);
      aspect-ratio: 4 / 5;
    }

    .product-gallery-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center top;
      display: block;
      transition: transform 0.35s ease;
    }

    .product-gallery-item:hover img {
      transform: scale(1.04);
    }

    .product-carousel-control {
      width: 3rem;
      height: 3rem;
      top: 50%;
      transform: translateY(-50%);
      border-radius: 999px;
      background: rgba(24, 88, 76, 0.74);
      opacity: 1;
    }

    .product-carousel-control.carousel-control-prev {
      left: 0.75rem;
    }

    .product-carousel-control.carousel-control-next {
      right: 0.75rem;
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
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
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
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      opacity: 1;
      background-size: 1.05rem;
      background-position: center;
      background-repeat: no-repeat;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='white' stroke-linecap='round' stroke-width='2'%3e%3cpath d='M3 3l10 10M13 3L3 13'/%3e%3c/svg%3e");
    }

    .image-lightbox-nav {
      position: fixed;
      bottom: 1.25rem;
      z-index: 1060;
      width: 3.25rem;
      height: 3.25rem;
      border: 1px solid rgba(255, 255, 255, 0.28);
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.14);
      color: #fff;
      font-size: 1.8rem;
      line-height: 1;
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      transition: background 180ms ease, transform 180ms ease;
    }

    .image-lightbox-nav:hover,
    .image-lightbox-nav:focus {
      background: rgba(255, 255, 255, 0.24);
      color: #fff;
      transform: scale(1.04);
    }

    .image-lightbox-prev {
      left: calc(50% - 4rem);
    }

    .image-lightbox-next {
      right: calc(50% - 4rem);
    }

    @media (max-width: 575.98px) {
      .image-lightbox-modal .modal-body {
        padding: 1rem;
      }

      .image-lightbox-modal img {
        max-height: calc(100vh - 2rem);
      }

      .image-lightbox-nav {
        width: 2.75rem;
        height: 2.75rem;
        font-size: 1.45rem;
      }
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

    .inquiry-modal .modal-content {
      border: 0;
      border-radius: 28px;
      box-shadow: var(--shadow-xl);
      background: rgba(255, 255, 255, 0.96);
    }

    .inquiry-modal .modal-header,
    .inquiry-modal .modal-footer {
      border-color: rgba(24, 88, 76, 0.08);
    }

    .inquiry-modal .form-control,
    .inquiry-modal .form-select {
      border-radius: 14px;
      padding: 0.9rem 1rem;
      border-color: rgba(24, 88, 76, 0.14);
      box-shadow: none;
    }

    .inquiry-modal .form-control:focus,
    .inquiry-modal .form-select:focus {
      border-color: rgba(34, 171, 104, 0.55);
      box-shadow: 0 0 0 0.2rem rgba(34, 171, 104, 0.14);
    }

    .hp-field {
      position: absolute !important;
      left: -9999px !important;
      width: 1px !important;
      height: 1px !important;
      overflow: hidden !important;
      opacity: 0 !important;
      pointer-events: none !important;
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
      transition: opacity 0.45s ease, transform 0.45s ease;
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

      #about .split-highlight {
        min-height: auto;
        margin-bottom: 0.5rem;
      }

      #about .split-highlight img {
        height: auto;
        min-height: 360px;
      }

      .product-carousel-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .reveal {
        opacity: 1;
        transform: none;
        transition: none;
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
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
      }

      .floating-panel,
      .floating-panel strong,
      .floating-panel p,
      .floating-panel .text-white {
        color: #fff !important;
      }

      #about .split-highlight {
        border-radius: 24px;
      }

      #about .split-highlight img {
        min-height: 320px;
      }

      #about .split-highlight .overlay-card {
        position: relative;
        left: auto;
        right: auto;
        bottom: auto;
        margin: 0;
        border-radius: 0 0 24px 24px;
      }

      #about .about-split-card img {
        aspect-ratio: 4 / 3;
      }

      .navbar {
        padding: 0.85rem 0;
      }

      .glass-nav {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
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

      .product-carousel-panel {
        padding: 0.85rem;
        border-radius: 22px;
      }

      .product-carousel-header {
        align-items: flex-start;
        flex-direction: column;
      }

      .product-carousel-control {
        width: 2.55rem;
        height: 2.55rem;
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

    @media (hover: none), (pointer: coarse) {
      .product-card:hover {
        transform: none;
        box-shadow: var(--shadow-md);
      }

      .product-media-item:hover img {
        transform: none;
      }

      .product-gallery-item:hover img {
        transform: none;
      }

      .product-media-item:hover {
        z-index: 1;
      }

      .btn-brand:hover {
        transform: none;
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
            <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
              <button class="btn btn-brand js-inquiry-trigger" type="button" data-bs-toggle="modal" data-bs-target="#inquiryModal">Request a Quote</button>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <?php if ($inquiryErrors): ?>
      <div class="page-feedback">
        <div class="container">
          <div class="alert alert-danger mb-0" role="alert">
            <?php foreach ($inquiryErrors as $error): ?>
              <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

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
                <button class="btn btn-brand d-inline-flex align-items-center gap-2 js-inquiry-trigger" type="button" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                  Start Your Project <i class="bi bi-arrow-up-right-circle"></i>
                </button>
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
                  <img src="assets/images/hero3.jpg?v=<?php echo asset_version('assets/images/hero3.jpg'); ?>" alt="Green Ads and Promats hero visual">
                </div>
                <div class="floating-panel glass-card">
                  <strong>Built for Business.</strong>
                  <p class="mb-0 text-secondary">From custom polos and jackets to event banners and promotional giveaways, the product mix is designed for branding, activations, uniforms, and corporate campaigns.</p>
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
                  <div class="split-highlight about-split-card reveal">
                    <img src="assets/images/mission_img.jpg?v=<?php echo asset_version('assets/images/mission_img.jpg'); ?>" alt="Green Ads and Promats mission display">
                    <div class="about-split-content">
                      <h3>Mission</h3>
                      <p class="mb-0 section-copy">Provide excellent promotional apparel at the most reasonable cost while generating healthy sales, fair compensation, and a comfortable, productive work environment.</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="split-highlight about-split-card reveal">
                    <img src="assets/images/vision_img.jpg?v=<?php echo asset_version('assets/images/vision_img.jpg'); ?>" alt="Green Ads and Promats customized giveaway samples">
                    <div class="about-split-content">
                      <h3>Vision</h3>
                      <p class="mb-0 section-copy">Become a leading supplier of customized promotional apparel in the Philippines through consistency, quality, and long-term client trust.</p>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="split-highlight about-split-card reveal">
                    <img src="assets/images/beginnings.jpg?v=<?php echo asset_version('assets/images/beginnings.jpg'); ?>" alt="Green Ads and Promats early exhibit booth">
                    <div class="about-split-content">
                      <h3>Our Beginnings</h3>
                      <p class="section-copy mb-3">
                        Green Ads &amp; Promats, Inc. was formerly known as MBM Giftshoppe, conceptualized in 2002 to serve a growing demand for corporate and promotional materials. In 2007, the founders formalized the business and focused on garments and giveaways, moving into direct manufacturing to reduce cost, meet deadlines, and ensure better quality control.
                      </p>
                    <!--  <p class="section-copy mb-0">
                        That evolution remains the core advantage today: a company that understands both promotion and production, and can translate branding requirements into finished products that are practical, presentable, and scalable.
                      </p> -->
                    </div>
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
              <h2 class="section-title reveal">Browse our apparel work.</h2>
            </div>
            <div class="col-lg-4">
              <p class="section-copy reveal mb-0">Swipe or click through our collections, then open any image fullscreen.</p>
            </div>
          </div>
          <div class="product-showcase">
            <?php render_product_carousel('collaredCarousel', 'Collared Shirts', 'Custom collared apparel samples', $collaredImages, 'Collared shirt sample'); ?>
            <?php render_product_carousel('jacketsCarousel', 'Jackets', 'Custom jacket and outerwear samples', $jacketImages, 'Jacket sample'); ?>
            <?php render_product_carousel('sportswearCarousel', 'Sportswear', 'Jerseys, activewear, and team apparel samples', $sportswearImages, 'Sportswear sample'); ?>
            <?php render_product_carousel('accessoriesCarousel', 'Accessories & Giveaways', 'Promotional accessories, bags, and giveaway samples', $accessoriesImages, 'Promotional accessory sample'); ?>
            <?php render_product_carousel('customizeShirtsCarousel', 'Customized Shirts', 'Custom shirt designs and production samples', $customizeShirtImages, 'Customized shirt sample'); ?>
            <?php render_product_carousel('clientsCarousel', 'Client Work', 'Completed projects and client sample gallery', $clientImages, 'Client project sample'); ?>
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
                 Equipped with specialized sewing and printing equipment, Green Ads & Promats delivers complete in-house solutions for apparel construction, cutting, finishing, heat transfer, and promotional printing, ensuring quality, efficiency, and faster project completion.
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
              <p class="section-copy reveal mb-0">Our portfolio spans a diverse range of industries, including manufacturing, telecommunications, food and beverage, entertainment, finance, hospitality, and logistics. This broad client base reflects our ability to deliver effective solutions tailored to the unique needs of various sectors.</p>
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
                    <a href="tel:+63279441063">(632) 79441063</a><br>
                    <a href="tel:+639228337960">(63) 922.8337960</a><br>
                    <a href="tel:+639460605443">(63) 946.0605443</a><br>
                    <a href="tel:+639455249782">(63) 945.5249782</a><br>
                    <a href="tel:+639994503766">(63) 999.4503766</a>
                  </div>
                </div>
                <div class="contact-line">
                  <i class="bi bi-envelope-fill"></i>
                  <div>
                    <strong>Email</strong><br>
                    <a href="mailto:gap.promats@gmail.com">gap.promats@gmail.com</a><br>
                    <a href="mailto:sales@greenads.info">sales@greenads.info</a>
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
                  <button class="btn btn-brand js-inquiry-trigger" type="button" data-bs-toggle="modal" data-bs-target="#inquiryModal">Email the Team</button>
                  <a class="btn btn-ghost" href="tel:+63279441063">Call the Office</a>
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

  <div class="modal fade inquiry-modal" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form method="post" action="">
          <input type="hidden" name="form_type" value="inquiry">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
          <div class="modal-header">
            <div>
              <h2 class="modal-title fs-4 mb-1" id="inquiryModalLabel">Send an Inquiry</h2>
              <p class="mb-0 text-secondary">Tell Green Ads &amp; Promats what you need and the sales team will respond at sales@greenads.info.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="hp-field" aria-hidden="true">
                <label for="inquiryWebsite">Website</label>
                <input id="inquiryWebsite" name="website" type="text" tabindex="-1" autocomplete="off" value="<?php echo htmlspecialchars($inquiryForm['website'], ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="inquiryEmail">Email</label>
                <input class="form-control" id="inquiryEmail" name="email" type="email" required value="<?php echo htmlspecialchars($inquiryForm['email'], ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="inquiryContact">Contact Number</label>
                <input class="form-control" id="inquiryContact" name="contact_number" type="text" required value="<?php echo htmlspecialchars($inquiryForm['contact_number'], ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div class="col-12">
                <label class="form-label" for="inquiryCategory">Product Category</label>
                <select class="form-select" id="inquiryCategory" name="category" required>
                  <option value="">Select a category</option>
                  <?php foreach ($productCategories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $inquiryForm['category'] === $category ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="inquiryDetails">Inquiry Details</label>
                <textarea class="form-control" id="inquiryDetails" name="inquiry_details" rows="6" required><?php echo htmlspecialchars($inquiryForm['inquiry_details'], ENT_QUOTES, 'UTF-8'); ?></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-brand">Send Inquiry</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade inquiry-modal" id="inquirySuccessModal" tabindex="-1" aria-labelledby="inquirySuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title fs-4 mb-0" id="inquirySuccessModalLabel">Inquiry Sent</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0"><?php echo htmlspecialchars($inquirySuccess ?: 'Your inquiry has been sent successfully.', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-brand" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade image-lightbox-modal" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageLightboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <button type="button" class="btn-close image-lightbox-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <button type="button" class="image-lightbox-nav image-lightbox-prev" id="imageLightboxPrev" aria-label="Previous image">
          <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="image-lightbox-nav image-lightbox-next" id="imageLightboxNext" aria-label="Next image">
          <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </button>
        <div class="modal-body">
          <img id="imageLightboxTarget" src="" alt="">
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
    const revealItems = document.querySelectorAll(".reveal");
    if (window.innerWidth > 991 && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
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
    } else {
      revealItems.forEach((item) => item.classList.add("is-visible"));
    }

    const lightboxModalElement = document.getElementById("imageLightboxModal");
    const lightboxTarget = document.getElementById("imageLightboxTarget");
    const lightboxPrev = document.getElementById("imageLightboxPrev");
    const lightboxNext = document.getElementById("imageLightboxNext");
    const productCarousels = document.querySelectorAll(".product-carousel-panel .carousel");

    const loadCarouselSlideImages = (slide, preloadOnly = false) => {
      if (!slide) {
        return;
      }

      slide.querySelectorAll("img[data-src]").forEach((image) => {
        const source = image.getAttribute("data-src");
        if (!source) {
          return;
        }

        if (preloadOnly) {
          const preloader = new Image();
          preloader.src = source;
        }

        image.setAttribute("src", source);
        image.removeAttribute("data-src");
      });
    };

    const scheduleCarouselPreload = (callback) => {
      if ("requestIdleCallback" in window) {
        window.requestIdleCallback(callback, { timeout: 1200 });
        return;
      }

      window.setTimeout(callback, 250);
    };

    const getWrappedCarouselSlide = (slides, index) => {
      if (!slides.length) {
        return null;
      }

      return slides[(index + slides.length) % slides.length];
    };

    const primeCarouselSlides = (carousel, activeSlide) => {
      const slides = Array.from(carousel.querySelectorAll(".carousel-item"));
      const activeIndex = Math.max(slides.indexOf(activeSlide), 0);

      loadCarouselSlideImages(getWrappedCarouselSlide(slides, activeIndex));
      loadCarouselSlideImages(getWrappedCarouselSlide(slides, activeIndex + 1));

      scheduleCarouselPreload(() => {
        loadCarouselSlideImages(getWrappedCarouselSlide(slides, activeIndex + 2), true);
        loadCarouselSlideImages(getWrappedCarouselSlide(slides, activeIndex + 3), true);
      });
    };

    productCarousels.forEach((carousel) => {
      primeCarouselSlides(carousel, carousel.querySelector(".carousel-item.active"));
      carousel.addEventListener("slide.bs.carousel", (event) => {
        primeCarouselSlides(carousel, event.relatedTarget);
      });
    });

    if (lightboxModalElement && lightboxTarget && window.bootstrap) {
      const lightboxModal = new bootstrap.Modal(lightboxModalElement);
      const lightboxImages = Array.from(document.querySelectorAll("main img"));
      let activeLightboxIndex = 0;

      const showLightboxImage = (index) => {
        if (!lightboxImages.length) {
          return;
        }

        activeLightboxIndex = (index + lightboxImages.length) % lightboxImages.length;
        const image = lightboxImages[activeLightboxIndex];
        const source = image.getAttribute("data-gallery-src") || image.getAttribute("data-src") || image.getAttribute("src");
        const alt = image.getAttribute("alt") || "Expanded image";

        lightboxTarget.setAttribute("src", source);
        lightboxTarget.setAttribute("alt", alt);
      };

      const showAdjacentLightboxImage = (direction) => {
        showLightboxImage(activeLightboxIndex + direction);
      };

      lightboxImages.forEach((image, index) => {
        image.addEventListener("click", () => {
          showLightboxImage(index);
          lightboxModal.show();
        });
      });

      if (lightboxPrev) {
        lightboxPrev.addEventListener("click", () => showAdjacentLightboxImage(-1));
      }

      if (lightboxNext) {
        lightboxNext.addEventListener("click", () => showAdjacentLightboxImage(1));
      }

      lightboxModalElement.addEventListener("keydown", (event) => {
        if (event.key === "ArrowLeft") {
          showAdjacentLightboxImage(-1);
        }

        if (event.key === "ArrowRight") {
          showAdjacentLightboxImage(1);
        }
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

    const inquiryModalElement = document.getElementById("inquiryModal");
    if (inquiryModalElement && window.bootstrap) {
      const inquiryModal = new bootstrap.Modal(inquiryModalElement);
      if (<?php echo $openInquiryModal ? 'true' : 'false'; ?>) {
        inquiryModal.show();
      }
    }

    const inquirySuccessModalElement = document.getElementById("inquirySuccessModal");
    if (inquirySuccessModalElement && window.bootstrap && <?php echo $inquirySuccess ? 'true' : 'false'; ?>) {
      const inquirySuccessModal = new bootstrap.Modal(inquirySuccessModalElement);
      inquirySuccessModal.show();
    }
  </script>
</body>
</html>
