<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

function isValidDomain($domain) {
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain)
            && preg_match("/^.{1,253}$/", $domain)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain));
}

$domain = $_GET['domain'] ?? null;
if ($domain) {
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = strtolower(trim($domain));
    $domain = explode('/', $domain)[0];

    if (!isValidDomain($domain)) {
        echo "invalid domain";
        exit;
    }

    $whoisServers = [
        "com" => "whois.verisign-grs.com",
        "net" => "whois.verisign-grs.com",
        "org" => "whois.pir.org",
        "info" => "whois.afilias.net",
        "biz" => "whois.biz",
        "io" => "whois.nic.io",
        "com.tr" => "whois.nic.tr",
        "tr" => "whois.nic.tr"
    ];

    $parts = explode(".", $domain);
    $tld = implode(".", array_slice($parts, -2));

    if (!isset($whoisServers[$tld])) {
        $tld = end($parts);
        if (!isset($whoisServers[$tld])) {
            echo "unsupported tld";
            exit;
        }
    }

    $conn = fsockopen($whoisServers[$tld], 43, $errno, $errstr, 10);
    if (!$conn) {
        echo "connection error";
        exit;
    }

    fputs($conn, $domain . "\r\n");
    $response = "";
    while (!feof($conn)) {
        $response .= fgets($conn, 128);
    }
    fclose($conn);

    if ($tld === "com.tr" || $tld === "tr") {
        $response = mb_convert_encoding($response, 'UTF-8', 'ISO-8859-9');
        if (stripos($response, '% No match found') !== false || 
            stripos($response, 'Not found') !== false) {
            echo "not registered";
            exit;
        }
    } else {
        if (empty($response) || stripos($response, 'No match for') !== false) {
            echo "not registered";
            exit;
        }
    }
    
    echo $response;
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>leWhois - Advanced Domain Lookup</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .gradient-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .loading-ring {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }
        .loading-ring div {
            box-sizing: border-box;
            display: block;
            position: absolute;
            width: 64px;
            height: 64px;
            margin: 8px;
            border: 8px solid #fff;
            border-radius: 50%;
            animation: loading-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            border-color: #fff transparent transparent transparent;
        }
        @keyframes loading-ring {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex flex-col relative">
    <div id="particles-js"></div>
    
    <header class="glass-effect py-4 fixed w-full top-0 z-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-white floating">
                    <i class="fas fa-globe mr-2"></i>leWhois
                </h1>
                <nav>
                    <ul class="flex space-x-8">
                        <li><a href="#" class="text-white hover:text-purple-200 transition-all duration-300">
                            <i class="fas fa-home mr-1"></i>Anasayfa</a>
                        </li>
                        <li><a href="#" class="text-white hover:text-purple-200 transition-all duration-300">
                            <i class="fas fa-info-circle mr-1"></i>Hakkında</a>
                        </li>
                        <li><a href="#" class="text-white hover:text-purple-200 transition-all duration-300">
                            <i class="fas fa-envelope mr-1"></i>İletişim</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1 container mx-auto px-4 pt-24 relative z-10">
        <div class="max-w-4xl mx-auto mt-20" data-aos="fade-up">
            <div class="glass-effect p-10 rounded-3xl">
                <h2 class="text-5xl font-bold text-white text-center mb-8">
                    Domain Sorgulama
                    <div class="text-lg font-normal mt-2 text-white/70">Hızlı ve Detaylı WHOIS Bilgisi</div>
                </h2>
                
                <form id="whoisForm" onsubmit="return false;" class="space-y-6">
                    <div class="flex gap-4">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-white/70"></i>
                            <input type="text" 
                                   id="domain" 
                                   class="w-full px-12 py-4 rounded-xl bg-white/10 placeholder-white/70 border border-white/20 focus:border-white/40 focus:outline-none transition-all text-lg"
                                   placeholder="Domain adı giriniz..."
                                   required>
                        </div>
                        <button onclick="lookupDomain()" 
                                class="px-8 py-4 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-search"></i>
                            <span>Sorgula</span>
                        </button>
                    </div>
                </form>
                
                <div id="loader" class="hidden flex justify-center items-center flex-col mt-8">
                    <div class="loading-ring"><div></div></div>
                    <div class="text-white mt-4">Sorgulanıyor...</div>
                </div>
                
                <div id="error" class="hidden mt-6 p-4 bg-red-500/20 border border-red-500/40 text-white rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                        <span class="error-message"></span>
                    </div>
                </div>
                
                <div id="success" class="hidden mt-6 p-4 bg-green-500/20 border border-green-500/40 text-white rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-xl"></i>
                        <span class="success-message"></span>
                    </div>
                </div>
                
                <div id="resultContainer" class="hidden mt-6">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-white text-xl">WHOIS Sonucu</h3>
                        <button onclick="copyResult()" class="text-white/70 hover:text-white transition-all">
                            <i class="fas fa-copy"></i> Kopyala
                        </button>
                    </div>
                    <pre id="result" class="p-6 bg-white/10 text-white rounded-xl overflow-auto max-h-96"></pre>
                </div>
            </div>
        </div>
    </main>

    <footer class="glass-effect py-6 mt-20 relative z-10">
        <div class="container mx-auto px-6 text-center text-white/80">
            <p>&copy; 2024 leWhois. Tüm hakları saklıdır.</p>
            <div class="flex justify-center gap-4 mt-4">
                <a href="https://github.com/thislaex" class="text-white/70 hover:text-white transition-all">
                    <i class="fab fa-github"></i>
                </a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        
        // Particle.js initialization
        particlesJS("particles-js", {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#ffffff" },
                opacity: { value: 0.2 },
                size: { value: 3 },
                line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.1, width: 1 }
            }
        });

        // Copy result function
        function copyResult() {
            const result = document.getElementById('result').textContent;
            navigator.clipboard.writeText(result).then(() => {
                alert('Sonuç panoya kopyalandı!');
            });
        }

        async function lookupDomain() {
            const domain = document.getElementById('domain').value;
            const loader = document.getElementById('loader');
            const error = document.getElementById('error');
            const success = document.getElementById('success');
            const result = document.getElementById('result');
            const resultContainer = document.getElementById('resultContainer');

            error.classList.add('hidden');
            success.classList.add('hidden');
            resultContainer.classList.add('hidden');
            loader.classList.remove('hidden');

            try {
                const response = await fetch(`?domain=${encodeURIComponent(domain)}`);
                const data = await response.text();

                loader.classList.add('hidden');

                if (data === 'not registered') {
                    success.querySelector('.success-message').textContent = 'Domain kullanılabilir! ✨';
                    success.classList.remove('hidden');
                } else if (data === 'invalid domain' || data === 'unsupported tld' || data === 'connection error') {
                    error.querySelector('.error-message').textContent = `Hata: ${data}`;
                    error.classList.remove('hidden');
                } else {
                    result.textContent = data;
                    resultContainer.classList.remove('hidden');
                }
            } catch (err) {
                loader.classList.add('hidden');
                error.querySelector('.error-message').textContent = 'İstek işlenirken bir hata oluştu';
                error.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
