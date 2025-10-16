let cryptoPrices = {};
let searchTimeout;
const COINGECKO_API = 'https://api.coingecko.com/api/v3';

async function refreshPrices() {
    try {
        showToast('info', 'Refreshing', 'Refreshing cryptocurrency prices...');
        
        const symbols = [];
        document.querySelectorAll('[data-crypto-id]').forEach(el => {
            const cryptoId = el.getAttribute('data-crypto-id');
            if (cryptoId) symbols.push(cryptoId);
        });
        
        if (symbols.length === 0) return;
        
        const uniqueSymbols = [...new Set(symbols)];
        const response = await fetch(`${COINGECKO_API}/simple/price?ids=${uniqueSymbols.join(',')}&vs_currencies=usd&include_24hr_change=true`);
        const data = await response.json();
        
        cryptoPrices = data;
        updatePortfolioPrices();
        updateAlertPrices();
        updateStats();
        
        showToast('success', 'Success', 'Prices updated successfully!');
    } catch (error) {
        console.error('Error fetching prices:', error);
        showToast('error', 'Error', 'Failed to fetch prices. Please try again.');
    }
}

function updatePortfolioPrices() {
    let totalValue = 0;
    let totalInvested = 0;
    
    document.querySelectorAll('#portfolio-table tr[data-crypto-id]').forEach(row => {
        const cryptoId = row.getAttribute('data-crypto-id');
        const holdingId = row.getAttribute('data-holding-id');
        const amount = parseFloat(row.children[1].textContent.replace(/,/g, ''));
        const purchasePrice = parseFloat(row.children[2].textContent.replace(/[$,]/g, ''));
        
        if (cryptoPrices[cryptoId]) {
            const currentPrice = cryptoPrices[cryptoId].usd;
            const value = amount * currentPrice;
            const invested = amount * purchasePrice;
            const pnl = value - invested;
            const pnlPercent = invested > 0 ? ((pnl / invested) * 100).toFixed(2) : 0;
            
            totalValue += value;
            totalInvested += invested;
            
            row.querySelector('.current-price').textContent = '$' + currentPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            row.querySelector('.current-value').textContent = '$' + value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            const pnlCell = row.querySelector('.pnl-value');
            pnlCell.innerHTML = `<span class="${pnl >= 0 ? 'text-success' : 'text-danger'}">
                ${pnl >= 0 ? '+' : ''}$${pnl.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${pnlPercent}%)
            </span>`;
        }
    });
    
    const totalPnl = totalValue - totalInvested;
    const totalPnlPercent = totalInvested > 0 ? ((totalPnl / totalInvested) * 100).toFixed(2) : 0;
    
    document.getElementById('total-holdings').textContent = '$' + totalValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('total-pnl').innerHTML = `<span class="${totalPnl >= 0 ? 'text-success' : 'text-danger'}">
        ${totalPnl >= 0 ? '+' : ''}$${totalPnl.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
    </span>`;
}

function updateAlertPrices() {
    document.querySelectorAll('#alerts-table tr[data-alert-id]').forEach(row => {
        const cryptoId = row.getAttribute('data-crypto-id');
        const priceCell = row.querySelector('.alert-current-price');
        
        if (!cryptoId) {
            priceCell.textContent = 'Error: No ID';
            priceCell.style.color = 'var(--danger)';
            return;
        }
        
        if (cryptoPrices[cryptoId]) {
            const currentPrice = cryptoPrices[cryptoId].usd;
            priceCell.textContent = '$' + currentPrice.toFixed(2);
            priceCell.style.color = '';
        } else {
            priceCell.textContent = 'Price unavailable';
            priceCell.style.color = 'var(--text-light)';
        }
    });
}

function updateStats() {
}

async function loadTopCryptos() {
    try {
        const response = await fetch(`${COINGECKO_API}/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=12&page=1&sparkline=false&price_change_percentage=24h`);
        const coins = await response.json();
        
        const grid = document.getElementById('top-cryptos-grid');
        grid.innerHTML = '';
        
        coins.forEach(coin => {
            const changeClass = coin.price_change_percentage_24h >= 0 ? 'positive' : 'negative';
            const changeSign = coin.price_change_percentage_24h >= 0 ? '+' : '';
            
            const card = document.createElement('div');
            card.className = 'crypto-card';
            card.innerHTML = `
                <div class="crypto-header">
                    <img src="${coin.image}" alt="${coin.name}" class="crypto-icon">
                    <div class="crypto-info">
                        <h4>${coin.symbol.toUpperCase()}</h4>
                        <span class="crypto-name">${coin.name}</span>
                    </div>
                </div>
                <div class="crypto-price">$${coin.current_price.toLocaleString()}</div>
                <div class="crypto-change ${changeClass}">
                    ${changeSign}${coin.price_change_percentage_24h.toFixed(2)}%
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading top cryptos:', error);
    }
}

function showAddCryptoModal() {
    document.getElementById('addCryptoModal').style.display = 'flex';
    setupCryptoSearch('crypto-search', 'crypto-search-results', function(coin) {
        document.getElementById('crypto_id').value = coin.id;
        document.getElementById('crypto_symbol').value = coin.symbol;
        document.getElementById('crypto_name').value = coin.name;
        document.getElementById('crypto-search').value = `${coin.symbol.toUpperCase()} - ${coin.name}`;
        document.getElementById('crypto-search-results').classList.remove('active');
    });
}

function showCreateAlertModal() {
    document.getElementById('createAlertModal').style.display = 'flex';
    setupCryptoSearch('alert-crypto-search', 'alert-search-results', function(coin) {
        document.getElementById('alert_crypto_id').value = coin.id;
        document.getElementById('alert_crypto_symbol').value = coin.symbol;
        document.getElementById('alert-crypto-search').value = `${coin.symbol.toUpperCase()} - ${coin.name}`;
        document.getElementById('alert-search-results').classList.remove('active');
    });
}

function setupCryptoSearch(inputId, resultsId, selectCallback) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    
    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            results.classList.remove('active');
            return;
        }
        
        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`${COINGECKO_API}/search?query=${query}`);
                const data = await response.json();
                
                results.innerHTML = '';
                
                if (data.coins.length === 0) {
                    results.innerHTML = '<div class="search-result-item">No results found</div>';
                } else {
                    data.coins.slice(0, 5).forEach(coin => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <strong>${coin.symbol.toUpperCase()}</strong> - ${coin.name}
                        `;
                        item.onclick = () => selectCallback(coin);
                        results.appendChild(item);
                    });
                }
                
                results.classList.add('active');
            } catch (error) {
                console.error('Error searching:', error);
            }
        }, 300);
    });
}

async function addCrypto(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/crypto.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Cryptocurrency added to portfolio!');
            closeModal('addCryptoModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to add cryptocurrency');
        }
    } catch (error) {
        console.error('Error adding crypto:', error);
        showToast('error', 'Error', 'Failed to add cryptocurrency');
    }
}

async function createAlert(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/crypto.php?action=create_alert', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Price alert created successfully!');
            closeModal('createAlertModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to create alert');
        }
    } catch (error) {
        console.error('Error creating alert:', error);
        showToast('error', 'Error', 'Failed to create alert');
    }
}

async function deleteHolding(id) {
    if (!confirm('Are you sure you want to remove this cryptocurrency from your portfolio?')) return;
    
    try {
        const response = await fetch(`/api/crypto.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Cryptocurrency removed from portfolio');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to remove cryptocurrency');
        }
    } catch (error) {
        console.error('Error deleting holding:', error);
        showToast('error', 'Error', 'Failed to remove cryptocurrency');
    }
}

async function deleteAlert(id) {
    if (!confirm('Are you sure you want to delete this price alert?')) return;
    
    try {
        const response = await fetch(`/api/crypto.php?action=delete_alert&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Alert deleted successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to delete alert');
        }
    } catch (error) {
        console.error('Error deleting alert:', error);
        showToast('error', 'Error', 'Failed to delete alert');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadTopCryptos();
    
    setTimeout(() => {
        refreshPrices();
    }, 500);
    
    setInterval(refreshPrices, 60000);
});
