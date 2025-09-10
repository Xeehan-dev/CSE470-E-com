<?php
// System-Independent Configuration for NovaShop E-Commerce
// This file automatically detects and configures for Windows, Mac, and Linux

// Detect operating system
function get_operating_system() {
    $os = strtoupper(PHP_OS);
    if (strpos($os, 'WIN') === 0) {
        return 'WINDOWS';
    } elseif (strpos($os, 'DARWIN') === 0) {
        return 'MAC';
    } elseif (strpos($os, 'LINUX') === 0) {
        return 'LINUX';
    } else {
        return 'UNKNOWN';
    }
}

// Get system-specific paths
function get_system_paths() {
    $os = get_operating_system();
    
    switch ($os) {
        case 'WINDOWS':
            return [
                'xampp_path' => 'C:/xampp',
                'htdocs_path' => 'C:/xampp/htdocs',
                'mysql_path' => 'C:/xampp/mysql',
                'apache_path' => 'C:/xampp/apache',
                'php_path' => 'C:/xampp/php',
                'temp_path' => sys_get_temp_dir(),
                'separator' => '\\'
            ];
            
        case 'MAC':
            return [
                'xampp_path' => '/Applications/XAMPP',
                'htdocs_path' => '/Applications/XAMPP/xamppfiles/htdocs',
                'mysql_path' => '/Applications/XAMPP/xamppfiles/var/mysql',
                'apache_path' => '/Applications/XAMPP/xamppfiles/etc',
                'php_path' => '/Applications/XAMPP/xamppfiles/bin',
                'temp_path' => '/tmp',
                'separator' => '/'
            ];
            
        case 'LINUX':
            return [
                'xampp_path' => '/opt/lampp',
                'htdocs_path' => '/opt/lampp/htdocs',
                'mysql_path' => '/opt/lampp/var/mysql',
                'apache_path' => '/opt/lampp/etc',
                'php_path' => '/opt/lampp/bin',
                'temp_path' => '/tmp',
                'separator' => '/'
            ];
            
        default:
            return [
                'xampp_path' => '',
                'htdocs_path' => '',
                'mysql_path' => '',
                'apache_path' => '',
                'php_path' => '',
                'temp_path' => sys_get_temp_dir(),
                'separator' => DIRECTORY_SEPARATOR
            ];
    }
}

// Get database configuration based on system
function get_database_config() {
    $os = get_operating_system();
    
    switch ($os) {
        case 'WINDOWS':
            return [
                'host' => 'localhost',
                'port' => 3306,
                'socket' => null,
                'connection_type' => 'tcp'
            ];
            
        case 'MAC':
            return [
                'host' => 'localhost',
                'port' => 3306,
                'socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
                'connection_type' => 'socket'
            ];
            
        case 'LINUX':
            return [
                'host' => 'localhost',
                'port' => 3306,
                'socket' => '/opt/lampp/var/mysql/mysql.sock',
                'connection_type' => 'socket'
            ];
            
        default:
            return [
                'host' => 'localhost',
                'port' => 3306,
                'socket' => null,
                'connection_type' => 'tcp'
            ];
    }
}

// Cross-platform path functions
function get_system_path($path) {
    $paths = get_system_paths();
    return str_replace('/', $paths['separator'], $path);
}

function is_system($system) {
    return get_operating_system() === strtoupper($system);
}

function is_windows() {
    return is_system('WINDOWS');
}

function is_mac() {
    return is_system('MAC');
}

function is_linux() {
    return is_system('LINUX');
}

// Cross-platform file operations
function system_file_exists($path) {
    $system_path = get_system_path($path);
    return file_exists($system_path);
}

function system_is_readable($path) {
    $system_path = get_system_path($path);
    return is_readable($system_path);
}

function system_is_writable($path) {
    $system_path = get_system_path($path);
    return is_writable($system_path);
}

function system_mkdir($path, $mode = 0755, $recursive = true) {
    $system_path = get_system_path($path);
    return mkdir($system_path, $mode, $recursive);
}

function system_file_get_contents($path) {
    $system_path = get_system_path($path);
    return file_get_contents($system_path);
}

function system_file_put_contents($path, $data) {
    $system_path = get_system_path($path);
    return file_put_contents($system_path, $data);
}

// Cross-platform include/require operations
function system_include($path) {
    $system_path = get_system_path($path);
    return include($system_path);
}

function system_require($path) {
    $system_path = get_system_path($path);
    return require($system_path);
}

function system_require_once($path) {
    $system_path = get_system_path($path);
    return require_once($system_path);
}

// System-specific database connection
function get_system_db_connection($host, $user, $pass, $dbname) {
    $db_config = get_database_config();
    
    if ($db_config['connection_type'] === 'socket' && $db_config['socket']) {
        // Try socket connection first
        try {
            $conn = new mysqli($db_config['host'], $user, $pass, $dbname, $db_config['port'], $db_config['socket']);
            if (!$conn->connect_error) {
                return $conn;
            }
        } catch (Exception $e) {
            // Fall back to TCP connection
        }
    }
    
    // Use TCP connection
    return new mysqli($db_config['host'], $user, $pass, $dbname, $db_config['port']);
}

// System-specific session path
function get_system_session_path() {
    $paths = get_system_paths();
    return $paths['temp_path'] . $paths['separator'] . 'php_sessions';
}

// System-specific error logging
function system_error_log($message) {
    $os = get_operating_system();
    error_log("[$os] " . $message);
}

// System-specific URL generation
function get_system_app_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    return $protocol . '://' . $host . $path;
}

// Initialize system-specific settings
function initialize_system_config() {
    $os = get_operating_system();
    $paths = get_system_paths();
    
    // Set session path
    $session_path = get_system_session_path();
    if (!is_dir($session_path)) {
        system_mkdir($session_path);
    }
    
    // Set error reporting based on system
    if ($os === 'WINDOWS') {
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        if ($paths['php_path']) {
            ini_set('error_log', $paths['php_path'] . $paths['separator'] . 'logs' . $paths['separator'] . 'php_error.log');
        }
    }
    
    system_error_log("System configuration initialized for $os");
}

// Auto-initialize when this file is included
initialize_system_config();
?>
