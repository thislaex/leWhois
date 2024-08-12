<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modern Whois Kontrol</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/index.css">
</head>

<body class="bg-gradient-to-br from-purple-600 to-blue-500 min-h-screen flex flex-col">

  <header class="bg-white shadow-md py-4">
    <div class="container mx-auto px-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">leWhois</h1>
      <nav>
        <ul class="flex space-x-6">
          <li><a href="#" class="text-gray-600 hover:text-purple-500 transition duration-300">Anasayfa</a></li>
          <li><a href="#" class="text-gray-600 hover:text-purple-500 transition duration-300">Hakkında</a></li>
          <li><a href="#" class="text-gray-600 hover:text-purple-500 transition duration-300">İletişim</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <div class="flex-1 container mx-auto px-4 mt-10 fade-in">
    <div class="max-w-xl mx-auto p-8 bg-white shadow-lg rounded-2xl transform transition duration-500 hover:scale-105">
      <h2 class="text-4xl font-extrabold text-gray-800 mb-8 text-center">Whois Kontrol</h2>
      <?php
      function sanitizeDomain(string $domain): string
      {
        return strtolower(trim($domain));
      }

      function isValidDomain(string $domain): bool
      {
        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
      }

      function getWhois(string $domain): string
      {
        $domain = sanitizeDomain($domain);

        if (!isValidDomain($domain)) {
          return "error: Geçersiz alan adı.";
        }

        $whoisServer = "whois.verisign-grs.com";
        if (preg_match('/\.tr$/i', $domain)) {
          $whoisServer = "whois.nic.tr";
        }

        $conn = @fsockopen($whoisServer, 43, $errno, $errstr, 5);

        if (!$conn) {
          return "error: WHOIS sunucusuna bağlanılamadı.";
        }

        fwrite($conn, $domain . "\r\n");
        $response = '';
        while (!feof($conn)) {
          $response .= fgets($conn, 128);
        }
        fclose($conn);

        if (empty($response) || stripos($response, 'No match for') !== false) {
          return "not registered";
        }

        return htmlspecialchars($response);
      }

      function getDomainPrices(string $domain): array
      {
        $domain = sanitizeDomain($domain);

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

      $results = [];

      if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $domains = $_POST['domains'] ?? '';
        $domainArray = array_map('sanitizeDomain', explode(',', $domains));

        foreach ($domainArray as $domain) {
          $whoisResult = getWhois($domain);
          $prices = ($whoisResult === "not registered") ? getDomainPrices($domain) : [];
          $results[] = [
            'domain' => $domain,
            'whois' => $whoisResult,
            'prices' => $prices
          ];
        }
      }
      ?>


      <form method="POST" action="">
        <div class="mb-6">
          <label for="domains" class="block text-gray-600 text-lg font-semibold mb-2">Alan Adları (Virgülle
            Ayırın):</label>
          <input type="text" id="domains" name="domains" placeholder="example.com, example.org"
            class="w-full px-5 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-4 focus:ring-purple-500 transition duration-300"
            value="<?php echo isset($domains) ? htmlspecialchars($domains) : ''; ?>">
        </div>

        <button type="submit"
          class="w-full py-3 bg-gradient-to-r from-pink-500 to-purple-500 text-white text-lg font-semibold rounded-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-105">Sorgula</button>
      </form>

      <?php if (!empty($results)): ?>
        <?php foreach ($results as $result): ?>
          <div class="mt-10">
            <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($result['domain']); ?> Whois
              Sonucu:</h3>
            <div class="result-box">
              <p class="text-lg font-semibold">
                <?php echo htmlspecialchars($result['whois'] === "not registered" ? "Domain kayıtlı değil" : $result['whois']); ?>
              </p>
            </div>

            <?php if ($result['whois'] === "not registered"): ?>
              <h4 class="text-xl font-bold text-gray-800 mt-6 mb-3">Domain Fiyatları:</h4>
              <ul class="space-y-3">
                <?php foreach ($result['prices'] as $price): ?>
                  <li class="bg-white p-4 rounded-lg shadow-md hover:bg-gray-50 transition duration-300">
                    <a href="<?php echo htmlspecialchars($price['url']); ?>" target="_blank"
                      class="text-purple-500 font-semibold">
                      <?php echo htmlspecialchars($price['firma']); ?>: $<?php echo htmlspecialchars($price['fiyat']); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-white shadow-md py-4 mt-10">
    <div class="container mx-auto px-4 flex justify-between items-center">
      <p class="text-gray-600">© 2024 leWhois. All rights reserved. It was developed by laex as open source.</p>
      <a href="https://laex.com.tr" class="text-purple-500 hover:underline">laex.com.tr</a>
    </div>
  </footer>

</body>

</html>