    <?php if ($auth->isLoggedIn()): ?>
            </div>
        </main>
    </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
    
    <script src="/assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
