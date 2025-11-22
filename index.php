<?php

/*
 *---------------------------------------------------------------
 * PERFORMANCE NOTE: OPcache Preloading
 *---------------------------------------------------------------
 * For maximum performance, enable OPcache preloading:
 * 1. Enable OPcache in php.ini: opcache.enable=1
 * 2. Set preload script: opcache.preload=/path/to/preload.php
 * 3. See preload.php in project root for configuration
 * 
 * This can improve performance by 20-30% by pre-compiling
 * frequently used classes into memory.
 */

// Check PHP version.
$minPhpVersion = '7.4'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    exit($message);
}

/*
 *---------------------------------------------------------------
 * PERFORMANCE OPTIMIZATION: Early Static File Detection
 *---------------------------------------------------------------
 * Check if request is for static file before bootstrapping.
 * This saves significant processing time for static assets.
 */
// Simple CLI check (is_cli() is defined in bootstrap, so we check manually)
$isCli = php_sapi_name() === 'cli' || (defined('STDIN') && !empty($_SERVER['argv']));

if (!$isCli) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $requestFile = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Extract the actual requested path
    $path = parse_url($requestUri, PHP_URL_PATH);
    $path = $path ?: $requestUri;
    
    // Check for static file extensions
    $staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'pdf', 'zip', 'mp4', 'webm'];
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    
    if (in_array($extension, $staticExtensions, true)) {
        // Try to serve static file directly
        $staticPath = __DIR__ . DIRECTORY_SEPARATOR . 'public' . str_replace('//', '/', $path);
        
        if (is_file($staticPath) && is_readable($staticPath)) {
            // Set appropriate content type
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'pdf' => 'application/pdf',
                'zip' => 'application/zip',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
            ];
            
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            
            // Set cache headers for static files (1 year)
            $lastModified = filemtime($staticPath);
            $etag = md5_file($staticPath);
            
            header('Content-Type: ' . $mimeType);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            header('ETag: "' . $etag . '"');
            header('Cache-Control: public, max-age=31536000, immutable');
            
            // Check if client has cached version
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
                http_response_code(304);
                exit;
            }
            
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
                if ($ifModifiedSince >= $lastModified) {
                    http_response_code(304);
                    exit;
                }
            }
            
            // Output file with compression if available
            if (extension_loaded('zlib') && !ob_get_level()) {
                ob_start('ob_gzhandler');
            }
            
            readfile($staticPath);
            exit;
        }
        
        // Static file not found, return 404 immediately
        http_response_code(404);
        exit;
    }
}

/*
 *---------------------------------------------------------------
 * PERFORMANCE OPTIMIZATION: OPcache Check
 *---------------------------------------------------------------
 * Verify OPcache is enabled for optimal performance.
 * OPcache significantly speeds up PHP execution by caching compiled scripts.
 */
if (function_exists('opcache_get_status') && !$isCli) {
    $opcacheStatus = opcache_get_status(false);
    if ($opcacheStatus === false) {
        // OPcache is disabled - log warning in development only
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log('Performance Warning: OPcache is not enabled. Consider enabling it in php.ini for better performance.');
        }
    }
}

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(FCPATH);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// Load our paths config file
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . 'app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Config\Paths();

// Location of the framework bootstrap file.
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

/*
 *---------------------------------------------------------------
 * PERFORMANCE OPTIMIZATION: Cached Environment Loading
 *---------------------------------------------------------------
 * Cache parsed .env file to avoid re-parsing on every request.
 * Only re-parse if .env file modification time changes.
 * ROOTPATH is now defined from bootstrap.php
 */
require_once SYSTEMPATH . 'Config/DotEnv.php';

// Use ROOTPATH from bootstrap (now defined)
$rootPath = defined('ROOTPATH') ? ROOTPATH : __DIR__ . DIRECTORY_SEPARATOR;
$envCacheDir = defined('WRITEPATH') ? WRITEPATH . 'cache' . DIRECTORY_SEPARATOR : __DIR__ . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
$envCacheFile = $envCacheDir . '.env_cache.php';
$envFile = $rootPath . '.env';

// Check if cache exists and is valid
$useCache = false;
if (is_file($envCacheFile) && is_file($envFile)) {
    $cacheData = @include $envCacheFile;
    if (is_array($cacheData)) {
        $cacheMtime = $cacheData['mtime'] ?? 0;
        $fileMtime = filemtime($envFile);
        
        // Use cache if .env file hasn't changed
        if ($cacheMtime >= $fileMtime && isset($cacheData['vars']) && is_array($cacheData['vars'])) {
            // Restore cached environment variables
            foreach ($cacheData['vars'] as $name => $value) {
                if (!getenv($name, true)) {
                    putenv("{$name}={$value}");
                }
                if (empty($_ENV[$name])) {
                    $_ENV[$name] = $value;
                }
                if (empty($_SERVER[$name])) {
                    $_SERVER[$name] = $value;
                }
            }
            $useCache = true;
        }
    }
}

// Load .env file if cache is not valid
if (!$useCache) {
    $dotEnv = new CodeIgniter\Config\DotEnv($rootPath);
    $vars = $dotEnv->load();
    
    // Save to cache if writable (only if we got valid vars)
    if ($vars !== null && is_array($vars) && is_dir($envCacheDir) && is_writable($envCacheDir)) {
        $cacheContent = "<?php\n";
        $cacheContent .= "// Auto-generated .env cache file\n";
        $cacheContent .= "// Do not edit manually - will be regenerated automatically\n";
        $cacheContent .= "return [\n";
        $cacheContent .= "    'mtime' => " . (is_file($envFile) ? filemtime($envFile) : time()) . ",\n";
        $cacheContent .= "    'vars' => " . var_export($vars, true) . ",\n";
        $cacheContent .= "];\n";
        
        @file_put_contents($envCacheFile, $cacheContent, LOCK_EX);
    }
}

/*
 * ---------------------------------------------------------------
 * GRAB OUR CODEIGNITER INSTANCE
 * ---------------------------------------------------------------
 *
 * The CodeIgniter class contains the core functionality to make
 * the application run, and does all of the dirty work to get
 * the pieces all working together.
 */

$app = Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do its thang.
 */

$app->run();
