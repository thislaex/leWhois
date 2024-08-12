const exchangeRate = 27.00; // USD to TL kurunu buraya girin

function switchCurrency(element) {
    const currencyElement = element.previousElementSibling;
    const currentText = currencyElement.textContent.trim();
    
    if (currentText.endsWith('USD')) {
        const currentPrice = parseFloat(currencyElement.getAttribute('data-price-dolar'));
        const tlPrice = (currentPrice * exchangeRate).toFixed(2);
        
        // Animasyon başlat
        currencyElement.style.transition = 'none'; // Animasyonun başlamadan önce geçici olarak kaldırılır
        currencyElement.textContent = `${tlPrice} TL`;
        
        // Animasyon geçişini başlat
        setTimeout(() => {
            currencyElement.style.transition = 'all 0.5s ease';
        }, 0);
    } else {
        const currentPrice = parseFloat(currencyElement.getAttribute('data-price-dolar'));
        currencyElement.textContent = `${currentPrice} USD`;
    }
}
