<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$vehicles = $db->fetchAll("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Vehicle Maintenance';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-car text-primary"></i>
                Vehicle Maintenance
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track vehicle information and maintenance history</p>
        </div>
        <button onclick="showAddVehicleModal()" class="btn btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Add Vehicle
        </button>
    </div>

    <?php if (empty($vehicles)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-car text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No vehicles added yet. Add your first vehicle to start tracking maintenance!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($vehicles as $vehicle): 
                $maintenanceCount = $db->fetchColumn("SELECT COUNT(*) FROM vehicle_maintenance WHERE vehicle_id = ?", [$vehicle['id']]);
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold"><?php echo sanitize($vehicle['make'] . ' ' . $vehicle['model']); ?></h2>
                            <p class="text-blue-100"><?php echo $vehicle['year']; ?></p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editVehicle(<?php echo $vehicle['id']; ?>)" class="text-white hover:bg-white/20 p-2 rounded">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" class="text-white hover:bg-white/20 p-2 rounded">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">License Plate</p>
                            <p class="font-semibold"><?php echo sanitize($vehicle['license_plate'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Current Mileage</p>
                            <p class="font-semibold"><?php echo number_format($vehicle['current_mileage'] ?? 0); ?> mi</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">VIN</p>
                            <p class="font-semibold text-xs"><?php echo sanitize($vehicle['vin'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Color</p>
                            <p class="font-semibold"><?php echo sanitize($vehicle['color'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold">Maintenance Records</h3>
                            <span class="text-sm text-gray-500"><?php echo $maintenanceCount; ?> records</span>
                        </div>
                        <button onclick="viewMaintenance(<?php echo $vehicle['id']; ?>)" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                            View History
                        </button>
                        <button onclick="addMaintenance(<?php echo $vehicle['id']; ?>)" class="w-full mt-2 px-4 py-2 bg-primary text-white rounded hover:bg-blue-600">
                            <i class="fas fa-wrench mr-2"></i>Add Service
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="addVehicleModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Add Vehicle</h2>
        </div>
        <form id="vehicleForm" class="p-6 space-y-4">
            <input type="hidden" id="vehicleId">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Make *</label>
                    <input type="text" id="make" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Model *</label>
                    <input type="text" id="model" required class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Year</label>
                    <input type="number" id="year" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Color</label>
                    <input type="text" id="color" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">VIN</label>
                <input type="text" id="vin" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">License Plate</label>
                <input type="text" id="licensePlate" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Current Mileage</label>
                <input type="number" id="currentMileage" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddVehicleModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save Vehicle</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
