<?php
/**
 * Multifields
 *
 * Custom fields for documents
 *
 * @author 64j
 */

namespace Multifields\Base;

class Core
{
    const VERSION = '2.0.1';

    private static $instance;
    private $params = [];
    private $config = [];
    private $data = [];
    protected $file_has_changed = null;

    private function __construct($params = [])
    {
        $pluginParams = [];
        if (!empty(evolutionCMS()->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode(evolutionCMS()->pluginCache['multifieldsProps'], true);
        }

        $this->setParams(array_merge([
            'basePath' => str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)) . '/',
            'storage' => empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'],
            'debug' => empty($pluginParams['multifields_debug']) ? false : ($pluginParams['multifields_debug'] == 'no' ? false : true),
        ], $params));

        if (!is_dir($this->getCacheFolder())) {
            mkdir($this->getCacheFolder(), 0755);
            file_put_contents($this->getCacheFolder() . '.htaccess', "order deny,allow\nallow from all\n");
        }

        require_once MODX_MANAGER_PATH . 'includes/tmplvars.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.format.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php';
    }

    /**
     * @param array $params
     * @return static
     */
    public static function getInstance($params = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($params);
        } else {
            self::$instance->setParams($params);
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    protected function getCacheFolder()
    {
        return MODX_BASE_PATH . 'assets/cache/multifields/';
    }

    /**
     * @return string
     */
    public function getStartScripts()
    {
        $evo = evolutionCMS();

        $out = '
        <script>
        if (!evo) {
            var evo = {};
        }
        if (!evo.config) {
            evo.config = {};
        }
        if (typeof evo.MGR_DIR === \'undefined\') {
            evo.MGR_DIR = \'' . MGR_DIR . '\';
        }
        if (typeof evo.MODX_MANAGER_URL === \'undefined\') {
            evo.MODX_MANAGER_URL = \'' . MODX_MANAGER_URL . '\';
        }
        if (typeof evo.config.which_browser === \'undefined\') {
            evo.config.which_browser = \'' . ($evo->configGlobal['which_browser'] ? $evo->configGlobal['which_browser'] : $evo->config['which_browser']) . '\';
        }
        </script>';

        $cache_styles = $this->getCacheFolder() . 'multifields.styles.min.css';
        $cache_scripts = $this->getCacheFolder() . 'multifields.scripts.min.js';

        $styles = [
            '@' => [
                $this->setFileUrl('view/css/core.css', dirname(__DIR__) . '/elements/multifields/', false, true)
            ]
        ];

        $this->removeFile($cache_styles, $this->hasFileChanged($styles['@'][0]));

        $scripts = [
            '@' => [
                $this->setFileUrl('view/js/Sortable.min.js', dirname(__DIR__) . '/elements/multifields/'),
                $this->setFileUrl('view/js/core.js', dirname(__DIR__) . '/elements/multifields/', false, true)
            ]
        ];

        $this->removeFile($cache_scripts, $this->hasFileChanged($scripts['@'][1]));

        if ($elements = glob($this->getParams('basePath') . 'elements/*', GLOB_ONLYDIR)) {
            foreach ($elements as $path) {
                if ($elements_elements = glob($path . '/*.php')) {
                    $namespace = ucfirst(basename($path));

                    foreach ($elements_elements as $elements_element) {
                        $name = rtrim(basename($elements_element), '.php');
                        $name = $namespace . ':' . $name;
                        $element = (new Elements)->element($name);

                        if (!$element) {
                            continue;
                        }

                        $files = $element->getStyles();

                        if (!empty($files) && !isset($styles[$name])) {
                            if (is_array($files)) {
                                foreach ($files as $style) {
                                    if ($style = $this->setFileUrl($style, $path, false, true)) {
                                        $styles[$name][] = $style;
                                        $this->removeFile($cache_styles, $this->hasFileChanged($style));
                                    }
                                }
                            } else {
                                if ($style = $this->setFileUrl($files, $path, false, true)) {
                                    $styles[$name][] = $style;
                                    $this->removeFile($cache_styles, $this->hasFileChanged($style));
                                }
                            }
                        }

                        $files = $element->getScripts();

                        if (!empty($files) && !isset($scripts[$name])) {
                            if (is_array($files)) {
                                foreach ($files as $script) {
                                    if ($script = $this->setFileUrl($script, $path, false, true)) {
                                        $scripts[$name][] = $script;
                                        $this->removeFile($cache_scripts, $this->hasFileChanged($script));
                                    }
                                }
                            } else {
                                if ($script = $this->setFileUrl($files, $path, false, true)) {
                                    $scripts[$name][] = $script;
                                    $this->removeFile($cache_scripts, $this->hasFileChanged($script));
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($this->getParams('debug')) {
            foreach ($styles as $files) {
                foreach ($files as $style) {
                    $out .= "\n" . '<link rel="stylesheet" type="text/css" href="../' . $style . '"/>';
                }
            }

            foreach ($scripts as $files) {
                foreach ($files as $script) {
                    $out .= "\n" . '<script src="../' . $script . '"></script>';
                }
            }

            $this->removeFile($cache_styles);
            $this->removeFile($cache_scripts);
        } else {
            if (!is_file($cache_styles) || !is_file($cache_scripts)) {
                $__ = '';
                foreach ($styles as $files) {
                    foreach ($files as $style) {
                        $__ .= file_get_contents(MODX_BASE_PATH . $style);
                    }
                }

                file_put_contents($cache_styles, Compress::css($__));

                $__ = '';
                foreach ($scripts as $files) {
                    foreach ($files as $script) {
                        $__ .= ';' . file_get_contents(MODX_BASE_PATH . $script);
                    }
                }

                file_put_contents($cache_scripts, Compress::js($__));
            }

            $out .= "\n" . '<link rel="stylesheet" type="text/css" href="../' . $this->setFileUrl($cache_styles, '', true, true) . '"/>';
            $out .= "\n" . '<script src="../' . $this->setFileUrl($cache_scripts, '', true, true) . '"></script>';
        }

        return $out;
    }

    /**
     * @param string $url
     * @param string $parent
     * @param bool $check_timestamp
     * @param bool $check_cache
     * @return string
     */
    private function setFileUrl($url = '', $parent = '', $check_timestamp = false, $check_cache = false)
    {
        if (!empty($url)) {
            $url = str_replace(MODX_BASE_PATH, '', $url);
            $url = trim(str_replace(DIRECTORY_SEPARATOR, '/', $url), '\\/');
            $parent = trim(str_replace(MODX_BASE_PATH, '', str_replace(DIRECTORY_SEPARATOR, '/', $parent)), '\\/');

            $url = ltrim($parent . '/' . $url, '/');
            $timestamp = '';

            if (is_file(MODX_BASE_PATH . $url)) {
                $timestamp = filemtime(MODX_BASE_PATH . $url);
                if ($check_cache) {
                    $fileCache = $this->getCacheFolder() . 'multifields.' . str_replace(['\\', '/'], '.', $url) . '.cache';
                    if ($this->getParams('debug')) {
                        $this->removeFile($fileCache);
                    } else {
                        $this->file_has_changed[$url] = !is_file($fileCache) || (is_file($fileCache) && $timestamp != file_get_contents($fileCache));
                        if ($this->file_has_changed[$url]) {
                            file_put_contents($fileCache, $timestamp);
                        }
                    }
                }
            }

            if (!empty($check_timestamp) && !empty($timestamp)) {
                $url .= '?time=' . $timestamp;
            }
        }

        return $url;
    }

    /**
     * @param $file
     * @param bool $remove
     */
    private function removeFile($file, $remove = true)
    {
        if ($remove && is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @param $url
     * @return bool
     */
    private function hasFileChanged($url)
    {
        return !empty($this->file_has_changed[explode('?', $url)[0]]);
    }

    /**
     * @param int $id
     * @param array $row
     * @return string
     */
    public function render($id = 0, $row = [])
    {
        global $ResourceManagerLoaded;

        $tmp_ResourceManagerLoaded = $ResourceManagerLoaded;

        $this->setParams([
            'id' => $id,
            'tv' => $row
        ]);

        $this->setConfig(null);
        $this->setData(null);

        if (empty($this->getConfig('templates'))) {
            if ($this->getConfig()) {
                $out = 'Must be an array in file for id=' . $this->getParams('tv')['id'];
            } else {
                $out = 'Not found config file for TV id=' . $this->getParams('tv')['id'];
            }
        } else {
            $start = microtime(true);

            $values = '';

            if (!empty($this->getData())) {
                $values = htmlspecialchars(json_encode($this->getData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
            }

            $elements = new Elements();

            $out = $elements->renderFormElement([
                'type' => 'multifields',
                'name' => 'multifields',
                'form.id' => $this->getParams('storage') == 'files' ? 'tv-mf-data[' . $this->getParams('id') . '__' . $this->getParams('tv')['id'] . ']' : 'tv' . $this->getParams('tv')['id'],
                'tv.id' => $this->getParams('tv')['id'],
                'tv.name' => $this->getParams('tv')['name'],
                'items' => $elements->renderData($this->getData()),
                'values' => $values
            ]);

            if ($this->getParams('debug')) {
                echo microtime(true) - $start . ' s.';
            }

            if (!empty($ResourceManagerLoaded)) {
                $ResourceManagerLoaded = $tmp_ResourceManagerLoaded;
            }
        }

        return $out;
    }

    /**
     *
     */
    public function saveData()
    {
        if (isset($_POST['tv-mf-data']) && $this->getParams('storage') == 'files') {
            foreach ($_POST['tv-mf-data'] as $k => $data) {
                list($id, $tvId) = explode('__', $k);
                $this->setParams([
                    'id' => $id,
                    'tv' => [
                        'id' => $tvId
                    ]
                ]);
                $data = evolutionCMS()->removeSanitizeSeed($data);
                $file = $this->getParams('basePath') . 'data/' . $id . '__' . $tvId . '.json';
                if ($data == '') {
                    if (is_file($file)) {
                        unlink($file);
                    }
                } else {
                    file_put_contents($file, $data);
                }
            }
        }
    }

    /**
     *
     */
    public function deleteData()
    {
    }

    /**
     * @param array $data
     * @return array
     */
    public function setParams($data = [])
    {
        if (empty($this->params)) {
            $this->params = [];
        }

        return $this->params = array_merge($this->params, $data);
    }

    /**
     * @param null $key
     * @return array
     */
    public function getParams($key = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $this->params;
    }

    /**
     * @param array $data
     * @return array
     */
    public function setConfig($data = [])
    {
        return $this->config = $data;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getConfig($key = null)
    {
        if (empty($this->config)) {
            if (!is_dir($this->getParams('basePath') . 'config')) {
                mkdir($this->getParams('basePath') . 'config', 0755);
            }

            if (file_exists($this->getParams('basePath') . 'config/' . $this->getParams('tv')['name'] . '.php')) {
                $this->config = require $this->getParams('basePath') . 'config/' . $this->getParams('tv')['name'] . '.php';
            } elseif (file_exists($this->getParams('basePath') . 'config/' . $this->getParams('tv')['id'] . '.php')) {
                $this->config = require $this->getParams('basePath') . 'config/' . $this->getParams('tv')['id'] . '.php';
            }

            if (!is_array($this->config)) {
                $this->config = null;
            } else {
                if (!isset($this->config['settings'])) {
                    $this->config['settings'] = [];
                }
                if (!isset($this->config['templates'])) {
                    $this->config['templates'] = [];
                }
                if (!isset($this->config['items'])) {
                    $this->config['items'] = [];
                }
                $this->config['templates'] = $this->configNormalize($this->config['templates']);
            }
        }

        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @param array $data
     * @return array
     */
    private function configNormalize($data = [])
    {
        if (is_array($data)) {
            foreach ($data as $k => &$v) {
                if (!is_array($v)) {
                    if (isset($this->config['templates'][$v])) {
                        $data[$v] = $this->config['templates'][$v];
                    }
                    unset($data[$k]);
                }
                if (!empty($v['templates'])) {
                    if (is_array($v['templates'])) {
                        $v['@templates'] = [];
                        foreach ($v['templates'] as $key => $val) {
                            $v['@templates'][] = is_array($val) ? $key : $val;
                        }
                    }
                    $v['templates'] = $this->configNormalize($v['templates']);
                }
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function setData($data = [])
    {
        return $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (empty($this->data)) {
            switch ($this->getParams('storage')) {
                case 'files':
                    $this->data = $this->fileData();
                    break;

                default:
                    $this->data = !empty($this->getParams('tv')['value']) ? json_decode($this->getParams('tv')['value'], true) : $this->getConfig('items');
                    break;
            }
        }

        return $this->data;
    }

    /**
     * @param int $doc_id
     * @param null $tv_id
     * @return array
     */
    private function fileData($doc_id = 0, $tv_id = null)
    {
        $this->data = [];

        if (!is_dir($this->getParams('basePath') . 'data')) {
            mkdir($this->getParams('basePath') . 'data', 0755);
        }

        if (empty($doc_id) && !empty($this->getParams('id'))) {
            $doc_id = $this->getParams('id');
        }

        if (empty($tv_id) && isset($this->getParams('tv')['id'])) {
            $tv_id = $this->getParams('tv')['id'];
        }

        $file = $this->getParams('basePath') . 'data/' . $doc_id . '__' . $tv_id . '.json';

        if (file_exists($file)) {
            $this->data = file_get_contents($file);
            $this->data = json_decode($this->data, true);
        }

        return $this->data;
    }
}
