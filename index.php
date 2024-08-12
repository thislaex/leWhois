<!-- 
Developed by laex - Volkan 

leWhois system v0.0.1

-->


<?php
function getWhois(string $domain): string
{
  $domain = trim($domain);
  if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
    return "error: Geçersiz alan adı: $domain";
  }

  $whoisServer = "whois.verisign-grs.com";
  if (preg_match('/\.tr$/i', $domain)) {
    $whoisServer = "whois.nic.tr";
  }

  $conn = @fsockopen($whoisServer, 43, $errno, $errstr, 10);

  if (!$conn) {
    return "error: Bağlantı başarısız: $errstr ($errno)";
  }

  fwrite($conn, $domain . "\r\n");
  $response = '';
  while (!feof($conn)) {
    $response .= fgets($conn, 128);
  }
  fclose($conn);

  if (empty($response) || stripos($response, 'No match for') !== false) {
    return "error: Alan adı bulunamadı: $domain";
  }

  return $response;
}

function getDomainPrices(string $domain): array
{
  return [
    [
      'firma' => 'GoDaddy',
      'fiyat' => 9.99,
      'url' => 'https://www.godaddy.com/domainsearch/find?checkAvail=1&tmskey=&domainToCheck=' . urlencode($domain)
    ],
    [
      'firma' => 'Namecheap',
      'fiyat' => 8.88,
      'url' => 'https://www.namecheap.com/domains/registration/results/?domain=' . urlencode($domain)
    ],
    [
      'firma' => 'Bluehost',
      'fiyat' => 12.99,
      'url' => 'https://www.bluehost.com/domains/domain-name-search?search=' . urlencode($domain)
    ],
  ];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $domains = $_POST['domains'] ?? '';
  $domains = explode(',', $domains);
}
?>


<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WHOIS Sorgulama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="#">WHOIS Sorgulama</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="#">Ana Sayfa</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Hakkımızda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">İletişim</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>


  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <h2 class="text-center mb-4">WHOIS Sorgulama</h2>
        <form method="post" class="input-group mb-3 card p-4 shadow-sm bg-dark text-white border-0 rounded-3">
          <input type="text color-white" class="form-control" id="domains" name="domains"
            placeholder="Birden fazla alan adı girebilirsiniz, her birini virgül ile ayırın." required>
          <button class="btn btn-primary" type="submit">Sorgula</button>
        </form>
        <div class="alert alert-info d-none" id="result">
          <strong>Sonuç:</strong> <span id="resultText"></span>
        </div>
      </div>
    </div>
  </div>

  <?php if (isset($domains) && !empty($domains)): ?>
    <div class="row mt-5">
      <div class="col-md-8 offset-md-2">
        <div class="card bg-le shadow-sm p-4">
          <h3 class="card-title">WHOIS Bilgileri:</h3>

          <?php foreach ($domains as $domain): ?>
            <?php
            $domain = trim($domain);
            $whoisData = getWhois($domain);
            ?>

            <?php if (strpos($whoisData, 'error:') === 0): ?>
              <?php
              $domainName = htmlspecialchars($domain);
              ?>
              <div class="alert alert-danger mt-3">Alan adı bulunamadı: <?php echo $domainName; ?></div>

              <?php
              // Alan adı kayıt fiyatlarını al ve listele
              $prices = getDomainPrices($domainName);
              ?>
              <div class="mt-3">
                <h5><?php echo htmlspecialchars($domainName); ?> için popüler firmalardaki fiyatlar:</h5>
                <ul class="list-group">
                  <?php foreach ($prices as $price): ?>
                    <li class="list-group-item">
                      <div class="price-container">
                        <span><?php echo htmlspecialchars($price['firma']); ?>:</span>
                        <span class="currency"
                          data-price-dolar="<?php echo htmlspecialchars($price['fiyat']); ?>"><?php echo htmlspecialchars($price['fiyat']); ?>
                          USD</span>
                        <span class="currency-switcher text-primary" onclick="switchCurrency(this)">₺</span>
                        <a href="<?php echo htmlspecialchars($price['url']); ?>" class="btn btn-sm btn-success ms-3"
                          target="_blank">Satın Al</a>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php else: ?>
              <div class="mt-3">
                <h5><?php echo htmlspecialchars($domain); ?>:</h5>
                <pre class="bg-le p-3 border"><?php echo htmlspecialchars($whoisData); ?></pre>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>

        </div>
      </div>
    </div>
  <?php endif; ?>
  </div>


  <div class="footer">
    <div class="container">
      <div class="row justify-content-between">
        <div class="col-md-6">© 2024 leWhois v0.0.1 - laex.com.tr</div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>

</html>