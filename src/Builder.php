<?php

// Declaring namespace
namespace LaswitchTech\Core;

// Import additionnal class into the global namespace
use Exception;

class Builder {

    // Constants

    // Global Properties
    protected $Config;
    protected $CSRF;
    protected $Auth;

    // Properties
    protected $Routes;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Global Variables
        global $CONFIG, $CSRF, $AUTH;

        // Set Global Properties
        $this->Config = $CONFIG;
        $this->CSRF = $CSRF;
        $this->Auth = $AUTH;

        // Configure Globals
        $this->Config->add('css')->add('js')->add('routes');
    }

    /**
     * Get the routes
     *
     * @return array
     */
    public function routes(): array
    {
        if ($this->Routes === null) {
            $this->Routes = $this->Config->get('routes') ?? [];

            $pluginsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
            if (is_dir($pluginsPath)) {
                foreach (array_diff(scandir($pluginsPath), ['.', '..', '.DS_Store']) as $plugin) {
                    $cfg = $pluginsPath . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'routes.cfg';
                    if (is_file($cfg)) {
                        $pluginRoutes = json_decode(file_get_contents($cfg), true) ?: [];
                        foreach ($pluginRoutes as $r => $p) {
                            if (!isset($this->Routes[$r])) $this->Routes[$r] = $p;
                        }
                    }
                }
            }

            ksort($this->Routes, SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $this->Routes;
    }

    /**
     * Build a menu from routes with depth control.
     *
     * Behavior:
     *  - $maxDepth === 1: return a flat list of ALL descendants under the chosen parent(s)
     *                     (or global roots if $parent is null); no nesting, no wrapper key.
     *  - $maxDepth >= 2:  return a nested tree up to $maxDepth levels; items deeper than $maxDepth are omitted.
     *
     * Output node fields: label, icon, color, items, link
     *
     * @param string            $location  e.g. 'sidebar-main'
     * @param string|array|null $parent    e.g. '/crm'. If null, build from global roots (no eligible parent).
     * @param int               $maxDepth  depth cap (1 = flat)
     * @return array
     */
    public function menu($location = 'sidebar', $parent = null, int $maxDepth = 2): array
    {
        global $AUTH;

        $maxDepth = max(1, (int)$maxDepth);

        // 1) Collect eligible routes
        $all = $this->routes();
        if (!$all) return [];

        $eligible = []; // route => ['label','icon','color','link','parents'=>[]]
        foreach ($all as $route => $p) {
            // normalize parents
            $parents = [];
            if (isset($p['parent']) && $p['parent'] !== null) {
                $parents = is_array($p['parent']) ? $p['parent'] : [$p['parent']];
                $parents = array_values(array_filter($parents, fn($x) => is_string($x) && $x !== ''));
            }

            // location filter
            if (!isset($p['location'])) continue;
            if (is_string($p['location'])) {
                if ($p['location'] !== $location) continue;
            } elseif (is_array($p['location'])) {
                if (!in_array($location, $p['location'], true)) continue;
            } else continue;

            // auth/public filter
            $isPublic = $p['public'] ?? false;
            $level    = $p['level']  ?? 0;
            if (!$isPublic && (!$AUTH || !$AUTH->isAuthenticated())) continue;
            if (!$isPublic && (!$AUTH || !$AUTH->isAuthorized('Route>' . $route, $level))) continue;

            $eligible[$route] = [
                'label'   => $p['label'] ?? '',
                'icon'    => $p['icon']  ?? null,
                'color'   => $p['color'] ?? null,
                'link'    => $route,
                'parents' => $parents,
            ];
        }
        if (!$eligible) return [];

        // --- NEW: helper to promote parents to nearest eligible ancestor(s) ---
        $resolveParents = function(array $declared) use ($all, $eligible): array {
            $result = [];
            $queue  = $declared;
            $seen   = [];
            while ($queue) {
                $cur = array_shift($queue);
                if (!is_string($cur) || $cur === '' || isset($seen[$cur])) continue;
                $seen[$cur] = true;

                if (isset($eligible[$cur])) {
                    // found an eligible ancestor for this branch
                    $result[$cur] = true;
                    continue;
                }

                // climb up if we know about this route in $all
                if (!isset($all[$cur])) continue;
                $pp = $all[$cur]['parent'] ?? null;
                if ($pp === null) continue;

                foreach (is_array($pp) ? $pp : [$pp] as $up) {
                    if (is_string($up) && $up !== '') $queue[] = $up;
                }
            }
            return array_keys($result);
        };

        // 2) Build parent->children map among eligible routes (with promotion)
        $children = []; // parentRoute => [childRoute...]
        foreach ($eligible as $r => $_) $children[$r] = [];

        // keep a promoted-parents cache to avoid recomputing
        $effParentCache = [];

        foreach ($eligible as $child => $node) {
            $declared = $node['parents'];
            $eff = $effParentCache[$child] ?? $resolveParents($declared);
            $effParentCache[$child] = $eff;

            foreach ($eff as $parEff) {
                // only link to eligible parents
                if (isset($children[$parEff])) $children[$parEff][] = $child;
            }
        }

        $sortKeys = function(array &$arr) { ksort($arr, SORT_NATURAL | SORT_FLAG_CASE); };
        $sortList = function(array &$list) { sort($list, SORT_NATURAL | SORT_FLAG_CASE); };
        foreach ($children as &$lst) $sortList($lst);
        unset($lst);

        // 3) Determine start nodes
        $starts = [];
        if ($parent === null) {
            // global roots = nodes that have no eligible (promoted) parent
            foreach ($eligible as $route => $node) {
                $eff = $effParentCache[$route] ?? $resolveParents($node['parents']);
                if (empty($eff)) $starts[] = $route;
            }
            $sortList($starts);
        } else {
            // pick descendants under the requested parent, even if that parent itself is not eligible
            $want = is_array($parent) ? $parent : [$parent];
            $seen = [];

            // If the requested parent is eligible, we can directly use $children
            foreach ($want as $p) {
                if (isset($children[$p])) {
                    foreach ($children[$p] as $c) $seen[$c] = true;
                }

                // Also include any eligible node that has $p in its ancestry chain (promoted)
                foreach ($eligible as $route => $node) {
                    // Check if $p appears in ancestry by climbing from declared parents
                    $queue = $node['parents'];
                    $visited = [];
                    $found = false;
                    while ($queue && !$found) {
                        $cur = array_shift($queue);
                        if (isset($visited[$cur])) continue;
                        $visited[$cur] = true;
                        if ($cur === $p) { $found = true; break; }
                        if (!isset($all[$cur])) continue;
                        $pp = $all[$cur]['parent'] ?? null;
                        if ($pp === null) continue;
                        foreach (is_array($pp) ? $pp : [$pp] as $up) {
                            if (is_string($up) && $up !== '') $queue[] = $up;
                        }
                    }
                    if ($found) $seen[$route] = true;
                }
            }

            $starts = array_keys($seen);
            $sortList($starts);
        }

        // Helpers
        $makeLeaf = function(string $route) use ($eligible): array {
            return [
                'label' => $eligible[$route]['label'],
                'icon'  => $eligible[$route]['icon'],
                'color' => $eligible[$route]['color'],
                'items' => [],
                'link'  => $eligible[$route]['link'],
            ];
        };

        // 4a) Depth = 1: FLAT list of ALL descendants of the start set
        if ($maxDepth === 1) {
            $out = [];
            $queue = $starts;
            $visited = [];
            while ($queue) {
                $cur = array_shift($queue);
                if (isset($visited[$cur])) continue;
                $visited[$cur] = true;
                $out[$cur] = $makeLeaf($cur);
                foreach ($children[$cur] ?? [] as $ch) $queue[] = $ch;
            }
            $sortKeys($out);
            return $out;
        }

        // 4b) Depth >= 2: build nested tree up to $maxDepth; deeper nodes are omitted
        $buildTree = function(string $route, int $depth) use (&$buildTree, $maxDepth, $children, $makeLeaf): array {
            $node = $makeLeaf($route);
            if ($depth >= $maxDepth) return $node;
            $items = [];
            foreach ($children[$route] ?? [] as $ch) {
                $items[$ch] = $buildTree($ch, $depth + 1);
            }
            if ($items) {
                ksort($items, SORT_NATURAL | SORT_FLAG_CASE);
                $node['items'] = $items;
            }
            return $node;
        };

        $result = [];
        foreach ($starts as $s) {
            $result[$s] = $buildTree($s, 1);
        }
        $sortKeys($result);
        return $result;
    }

    /**
     * Create Crumbs
     *
     * @param string $location
     * @param string $parent
     * @return array
     */
    public function crumbs()
    {
        // Import Global Variables
        global $REQUEST;

        // Initialize the crumbs
        $crumbs = [];

        // Retrieve the routes
        $routes = $this->Config->get('routes');

        // Load Plugins Routes
        $pluginsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';
        foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
            $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
            if(is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')){
                foreach(json_decode(file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg'),true) as $route => $param){
                    if(!isset($routes[$route])){
                        $routes[$route] = $param;
                    }
                }
            }
        }

        // Retrieve the current url
        $url = $REQUEST->getUri();
        if($REQUEST->getQueryString()){
            $url .= '?' . $REQUEST->getQueryString();
        }

        // Create Base URL
        $base = '';
        $parts = explode('/', $url);
        foreach($parts as $part){
            $base = rtrim($base,'/') . '/' . rtrim($part,'/');
            $route = explode('?', $base)[0];
            if(isset($routes[$route])){
                $crumbs[$route] = $routes[$route];
                $crumbs[$route]['link'] = $base;
            }
        }

        return $crumbs;
    }

    /**
     * Generate the HTML tags for the CSS files
     */
    public function css()
    {
        $html = '';

        // Load Core CSS
        $path = $this->Config->root() . '/vendor/laswitchtech/core/assets/css';
        if(is_file($path.'.cfg')){
            if(is_dir($path)){
                $assets = json_decode(file_get_contents($path.'.cfg') ?? '[]',true);
                foreach($assets as $file){
                    if(is_file($path.'/'.$file)){
                        $html .= '<link rel="stylesheet" type="text/css" href="/assets/core/css/'.trim($file,'/').'">' . PHP_EOL;
                    }
                }
            }
        }

        // Load Plugins Assets CSS
        $path = $this->Config->root() . '/lib/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $extension){
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            $html .= '<link rel="stylesheet" type="text/css" href="/assets/plugins/'.$extension.'/assets/css/'.trim($asset,'/').'">' . PHP_EOL;
                        }
                    }
                }
            }
        }

        // Load Themes Assets CSS
        $path = $this->Config->root() . '/lib/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $extension){
                if(($this->Config->get('application','theme') ?? $this->Config->get('installer','theme')) !== $extension) continue;
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            $html .= '<link rel="stylesheet" type="text/css" href="/assets/themes/'.$extension.'/assets/css/'.trim($asset,'/').'">' . PHP_EOL;
                        }
                    }
                }
            }
        }

        // Load Global CSS
        $css = $this->Config->get('css');
        foreach($css as $file){
            if(is_file($this->Config->root().'/webroot/assets/css/'.trim($file,'/'))){
                $html .= '<link rel="stylesheet" type="text/css" href="/assets/css/'.trim($file,'/').'">' . PHP_EOL;
            } elseif(is_file($this->Config->root().'/webroot/assets/js/'.trim($file,'/'))){
                $html .= '<link rel="stylesheet" type="text/css" href="/assets/js/'.trim($file,'/').'">' . PHP_EOL;
            }
        }

        // Load Plugins CSS
        $path = $this->Config->root() . '/webroot/assets/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $file){
                $filePath = $file . '/styles.css';
                if(is_file($path.'/'.$filePath)){
                    $html .= '<link rel="stylesheet" type="text/css" href="/assets/plugins/'.trim($filePath,'/').'" data-plugin="'.$file.'">' . PHP_EOL;
                }
            }
        }

        // Load Less CSS
        $html .= '<link rel="stylesheet" href="/css">' . PHP_EOL;

        // Load Theme CSS
        $path = $this->Config->root() . '/webroot/assets/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $file){
                $filePath = $file . '/styles.css';
                if(is_file($path.'/'.$filePath)){
                    if(($this->Config->get('application','theme') ?? $this->Config->get('installer','theme')) == $file){
                        $html .= '<link rel="stylesheet" type="text/css" href="/assets/themes/'.trim($filePath,'/').'" data-theme="'.$file.'">' . PHP_EOL;
                    }
                }
            }
        }

        // Return the HTML
        return $html;
    }

    /**
     * Generate the HTML tags for the JS files
     */
    public function js()
    {
        $html = '';

        // Load Constants JS
        $html .= '<script>' . PHP_EOL;
        $html .= '' . PHP_EOL;
        $html .= '    // Global Variables' . PHP_EOL;
        $html .= '    const CSRF_KEY = "' . $this->CSRF->key() . '"' . PHP_EOL;
        $html .= '    var CSRF_TOKEN = "' . $this->CSRF->token() . '"' . PHP_EOL;
        $html .= '    const LOGO = "' . $this->logo() . '"' . PHP_EOL;
        $html .= '    const PUBLIC = ' . ($this->Config->get('application','public') ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const AUTHENTICATED = ' . ($this->Auth->isAuthenticated() ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const USER_ID = ' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->id : 0) . ';' . PHP_EOL;
        $html .= '    const USER_USERNAME = "' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->username : '') . '";' . PHP_EOL;
        $html .= '    const USER_ORGANIZATION = ' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->organization()->id : 'null') . ';' . PHP_EOL;
        $html .= '    const USER_TOKEN = "' . ($this->Auth->isAuthenticated() ? $this->Auth->user()->token() : '') . '";' . PHP_EOL;
        $html .= '    const USER_ROLES = ' . json_encode($this->Auth->isAuthenticated() ? $this->Auth->user()->roles() : null) . ';' . PHP_EOL;
        $html .= '    const MAINTENANCE_MODE = ' . ($this->Config->get('application','maintenance') ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const DEV_MODE = ' . ($this->Auth->isAuthorized('Developer', 1) ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '    const ADMIN_MODE = ' . ($this->Auth->isAuthorized('Administrator', 1) ? 'true' : 'false') . ';' . PHP_EOL;
        $html .= '</script>' . PHP_EOL;

        // Load Core JS
        $path = $this->Config->root() . '/vendor/laswitchtech/core/assets/js';
        if(is_file($path.'.cfg')){
            if(is_dir($path)){
                $assets = json_decode(file_get_contents($path.'.cfg') ?? '[]',true);
                foreach($assets as $file){
                    if(is_file($path.'/'.$file)){
                        if(str_ends_with($file, '.mjs')){
                            $html .= '<script type="module" src="/assets/core/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                        } else {
                            $html .= '<script src="/assets/core/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                        }
                    }
                }
            }
        }

        // Load Themes JS
        $path = $this->Config->root() . '/lib/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $file){
                $filePath = $file . '/library.js';
                if(is_file($path.'/'.$filePath)){
                    if(($this->Config->get('application','theme') ?? $this->Config->get('installer','theme')) == $file){
                        $html .= '<script src="/assets/themes/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                    }
                }
            }
            foreach($themes as $file){
                $filePath = $file . '/script.js';
                if(is_file($path.'/'.$filePath)){
                    if(($this->Config->get('application','theme') ?? $this->Config->get('installer','theme')) == $file){
                        $html .= '<script src="/assets/themes/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                    }
                }
            }
        }

        // Load Themes Assets JS
        $path = $this->Config->root() . '/lib/themes';
        if(is_dir($path)){
            $themes = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($themes as $extension){
                if(($this->Config->get('application','theme') ?? $this->Config->get('installer','theme')) !== $extension) continue;
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            if(str_ends_with($asset, '.mjs')){
                                $html .= '<script type="module" src="/assets/themes/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            } else {
                                $html .= '<script src="/assets/themes/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }

        // Load Plugins Assets JS
        $path = $this->Config->root() . '/lib/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $extension){
                $assetsPath = $path . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js';
                if(is_file($assetsPath.'.cfg')){
                    if(is_dir($assetsPath)){
                        $assets = json_decode(file_get_contents($assetsPath.'.cfg') ?? '[]',true);
                        foreach($assets as $asset){
                            if(str_ends_with($asset, '.mjs')){
                                $html .= '<script type="module" src="/assets/plugins/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            } else {
                                $html .= '<script src="/assets/plugins/'.$extension.'/assets/js/'.trim($asset,'/').'"></script>' . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }

        // Load Global JS
        $js = $this->Config->get('js');
        foreach($js as $file){
            if(is_file($this->Config->root().'/webroot/assets/js/'.trim($file,'/'))){
                // Check if the file is a module by looking for the file extension
                if(str_ends_with($file, '.mjs')){
                    $html .= '<script type="module" src="/assets/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                } else {
                    $html .= '<script src="/assets/js/'.trim($file,'/').'"></script>' . PHP_EOL;
                }
            }
        }

        // Load Plugins JS
        $path = $this->Config->root() . '/lib/plugins';
        if(is_dir($path)){
            $plugins = array_diff(scandir($path), array('..', '.','.DS_Store'));
            foreach($plugins as $file){
                $filePath = $file . '/library.js';
                if(is_file($path.'/'.$filePath)){
                    $html .= '<script src="/assets/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                }
            }
            foreach($plugins as $file){
                $filePath = $file . '/script.js';
                if(is_file($path.'/'.$filePath)){
                    $html .= '<script src="/assets/plugins/'.trim($filePath,'/').'"></script>' . PHP_EOL;
                }
            }
        }

        // Return the HTML
        return $html;
    }

    /**
     * Get the logo path
     *
     * @return string
     */
    public function logo()
    {
        $src = '/assets/img/logo.svg';
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.jpg';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.gif';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.webp';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/img/logo.png';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.svg';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.jpg';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.gif';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.webp';
        }
        if(!is_file($this->Config->root() . '/webroot' . $src)){
            $src = '/assets/core/img/logo.png';
        }
        return $src;
    }
}
