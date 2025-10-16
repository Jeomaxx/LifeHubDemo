<?php
require_once __DIR__ . '/db.php';

class RBAC {
    private $db;
    private $userPermissions = null;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function assignRoleToUser($userId, $roleName) {
        $role = $this->db->fetchOne("SELECT id FROM roles WHERE name = ?", [$roleName]);
        if (!$role) {
            return false;
        }
        
        $existing = $this->db->fetchOne(
            "SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", 
            [$userId, $role['id']]
        );
        
        if ($existing) {
            return true;
        }
        
        return $this->db->execute(
            "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)",
            [$userId, $role['id']]
        );
    }
    
    public function removeRoleFromUser($userId, $roleName) {
        $role = $this->db->fetchOne("SELECT id FROM roles WHERE name = ?", [$roleName]);
        if (!$role) {
            return false;
        }
        
        return $this->db->execute(
            "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $role['id']]
        );
    }
    
    public function getUserRoles($userId) {
        return $this->db->fetchAll(
            "SELECT r.* FROM roles r 
             JOIN user_roles ur ON ur.role_id = r.id 
             WHERE ur.user_id = ?",
            [$userId]
        );
    }
    
    public function getUserPermissions($userId) {
        if ($this->userPermissions !== null) {
            return $this->userPermissions;
        }
        
        $permissions = $this->db->fetchAll(
            "SELECT DISTINCT p.* FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?",
            [$userId]
        );
        
        $this->userPermissions = array_column($permissions, 'name');
        return $this->userPermissions;
    }
    
    public function hasPermission($userId, $permissionName) {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permissionName, $permissions);
    }
    
    public function hasAnyPermission($userId, array $permissionNames) {
        $permissions = $this->getUserPermissions($userId);
        return count(array_intersect($permissions, $permissionNames)) > 0;
    }
    
    public function hasAllPermissions($userId, array $permissionNames) {
        $permissions = $this->getUserPermissions($userId);
        return count(array_intersect($permissions, $permissionNames)) === count($permissionNames);
    }
    
    public function isAdmin($userId) {
        $roles = $this->getUserRoles($userId);
        foreach ($roles as $role) {
            if ($role['name'] === 'admin') {
                return true;
            }
        }
        return false;
    }
    
    public function getAllRoles() {
        return $this->db->fetchAll("SELECT * FROM roles ORDER BY name");
    }
    
    public function getAllPermissions() {
        return $this->db->fetchAll("SELECT * FROM permissions ORDER BY module, name");
    }
    
    public function getRolePermissions($roleId) {
        return $this->db->fetchAll(
            "SELECT p.* FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?",
            [$roleId]
        );
    }
    
    public function assignPermissionToRole($roleId, $permissionId) {
        $existing = $this->db->fetchOne(
            "SELECT * FROM role_permissions WHERE role_id = ? AND permission_id = ?",
            [$roleId, $permissionId]
        );
        
        if ($existing) {
            return true;
        }
        
        return $this->db->execute(
            "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
            [$roleId, $permissionId]
        );
    }
    
    public function removePermissionFromRole($roleId, $permissionId) {
        return $this->db->execute(
            "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?",
            [$roleId, $permissionId]
        );
    }
    
    public function createRole($name, $description = '') {
        return $this->db->insert('roles', [
            'name' => $name,
            'description' => $description
        ]);
    }
    
    public function createPermission($name, $description = '', $module = '') {
        return $this->db->insert('permissions', [
            'name' => $name,
            'description' => $description,
            'module' => $module
        ]);
    }
    
    public function ensureUserHasRole($userId) {
        $roles = $this->getUserRoles($userId);
        if (empty($roles)) {
            $this->assignRoleToUser($userId, 'user');
        }
    }
}

function requirePermission($permissionName) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
    
    $rbac = new RBAC();
    if (!$rbac->hasPermission($_SESSION['user_id'], $permissionName)) {
        http_response_code(403);
        die('Access Denied: You do not have permission to access this resource.');
    }
}

function checkPermission($userId, $permissionName) {
    $rbac = new RBAC();
    return $rbac->hasPermission($userId, $permissionName);
}
