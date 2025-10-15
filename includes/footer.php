    <?php if (isset($auth) && $auth->isLoggedIn()): ?>
            </div>
        </main>
    </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="/assets/js/charts.js"></script>
    <script src="/assets/js/main.js"></script>
    <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
