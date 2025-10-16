    <?php if (isset($auth) && $auth->isLoggedIn()): ?>
        </main>
    </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="/assets/js/charts.js"></script>
    <script src="/assets/js/main.js"></script>
    
    <!-- Global Search Script -->
    <script>
    let searchTimeout;
    
    function openGlobalSearch() {
        document.getElementById('globalSearchModal').classList.remove('hidden');
        document.getElementById('globalSearchInput').focus();
    }
    
    function closeGlobalSearch() {
        document.getElementById('globalSearchModal').classList.add('hidden');
        document.getElementById('globalSearchInput').value = '';
        document.getElementById('globalSearchResults').innerHTML = '';
    }
    
    // Keyboard shortcut Ctrl+K
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openGlobalSearch();
        }
        if (e.key === 'Escape') {
            closeGlobalSearch();
        }
    });
    
    // Search input handler
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('globalSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length < 2) {
                    document.getElementById('globalSearchResults').innerHTML = '';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    fetch(`/api/global_search.php?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            const resultsDiv = document.getElementById('globalSearchResults');
                            if (data.success && data.results.length > 0) {
                                resultsDiv.innerHTML = data.results.map(result => `
                                    <a href="${result.url}" class="block p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                                <i class="fas ${result.icon} text-primary text-sm"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-gray-900 dark:text-white">${result.title}</span>
                                                    <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">${result.type}</span>
                                                </div>
                                                ${result.description ? `<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${result.description}</p>` : ''}
                                                ${result.meta ? `<p class="text-xs text-gray-500 mt-1">${result.meta}</p>` : ''}
                                            </div>
                                        </div>
                                    </a>
                                `).join('');
                            } else {
                                resultsDiv.innerHTML = '<div class="text-center py-8 text-gray-500">No results found</div>';
                            }
                        });
                }, 300);
            });
        }
    });
    </script>
    
    <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
