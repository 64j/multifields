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
    const DEBUG = true;

    private static $instance;
    private static $elements;
    private static $config;
    protected $basePath;
    protected $scripts;
    protected $styles;
    protected $data;
    public $params;

    public function __construct()
    {
        $this->basePath = str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)) . '/';

        $pluginParams = [];
        if (!empty(evolutionCMS()->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode(evolutionCMS()->pluginCache['multifieldsProps'], true);
        }
        $this->params['theme'] = empty($pluginParams['multifields_theme']) ? 'default' : $pluginParams['multifields_theme'];
        $this->params['storage'] = empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'];

        require_once MODX_MANAGER_PATH . 'includes/tmplvars.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.format.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php';
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getStartScripts()
    {
        $this->styles = [
            '@' => [
                $this->setFileUrl('view/css/core.css', dirname(__DIR__) . '/elements/multifields/')
            ]
        ];

        $this->scripts = [
            '@' => [
                $this->setFileUrl('view/js/Sortable.min.js', dirname(__DIR__) . '/elements/multifields/'),
                $this->setFileUrl('view/js/core.js', dirname(__DIR__) . '/elements/multifields/')
            ]
        ];

        if ($elements = glob($this->basePath . 'elements/*', GLOB_ONLYDIR)) {
            foreach ($elements as $element) {
                $name = ucfirst(basename($element));
                $element = self::element('\\Multifields\\Elements\\' . $name);

                if (!$element) {
                    continue;
                }

                if ($styles = $element->getStyles()) {
                    if (!isset($this->styles[$name])) {
                        if (is_array($styles)) {
                            foreach ($styles as $style) {
                                if ($style = $this->setFileUrl($style, $element->path())) {
                                    $this->styles[$name][] = $style;
                                }
                            }
                        } else {
                            if ($style = $this->setFileUrl($styles, $element->path())) {
                                $this->styles[$name][] = $style;
                            }
                        }
                    }
                }

                if ($scripts = $element->getScripts()) {
                    if (!isset($this->scripts[$name])) {
                        if (is_array($scripts)) {
                            foreach ($scripts as $script) {
                                if ($script = $this->setFileUrl($script, $element->path())) {
                                    $this->scripts[$name][] = $script;
                                }
                            }
                        } else {
                            if ($script = $this->setFileUrl($scripts, $element->path())) {
                                $this->scripts[$name][] = $script;
                            }
                        }
                    }
                }
            }
        }

        $out = '';

        if ($this::DEBUG) {
            foreach ($this->styles as $styles) {
                foreach ($styles as $style) {
                    $out .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $style . '"/>';
                }
            }

            foreach ($this->scripts as $scripts) {
                foreach ($scripts as $script) {
                    $out .= "\n" . '<script src="' . $script . '"></script>';
                }
            }

            if (is_file(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css')) {
                unlink(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css');
            }

            if (is_file(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js')) {
                unlink(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js');
            }
        } else {
            if (!is_file(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css') || !is_file(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js')) {
                $__ = '';
                foreach ($this->styles as $styles) {
                    foreach ($styles as $style) {
                        $__ .= file_get_contents($style);
                    }
                }

                file_put_contents(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css', Compress::css($__));

                $__ = '';
                foreach ($this->scripts as $scripts) {
                    foreach ($scripts as $script) {
                        $__ .= ';' . file_get_contents($script);
                    }
                }

                file_put_contents(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js', Compress::js($__));
            }

            $out .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $this->setFileUrl('elements/multifields/view/css/styles.min.css', dirname(__DIR__) . '/') . '"/>';
            $out .= "\n" . '<script src="' . $this->setFileUrl('elements/multifields/view/js/scripts.min.js', dirname(__DIR__) . '/') . '"></script>';
        }

        return $out;
    }

    /**
     * @param string $url
     * @param string $parent
     * @return string
     */
    protected function setFileUrl($url = '', $parent = '')
    {
        if (!empty($url)) {
            $url = trim(str_replace(DIRECTORY_SEPARATOR, '/', $url), '\\/');
            $parent = str_replace(MODX_BASE_PATH, '', trim(str_replace(DIRECTORY_SEPARATOR, '/', $parent), '\\/'));

            $url = $parent . '/' . $url;

            if (is_file(MODX_BASE_PATH . $url)) {
                $url .= '?time=' . filemtime(MODX_BASE_PATH . $url);
            } else {
                $url = '';
            }
        }

        return MODX_SITE_URL . $url;
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
        self::$config = null;
        $this->params['id'] = $id;
        $this->params['tv'] = $row;
        $this->getConfig();
        $this->getData();

        if (empty(self::$config['templates'])) {
            if (self::$config === null) {
                $this->data = 'Must be an array in file for id=' . $this->params['tv']['id'];
            } else {
                $this->data = 'Not found config file for TV id=' . $this->params['tv']['id'];
            }
        } else {
            //$start = microtime(true);
            $values = '';

            if (!empty($this->data)) {
                $values = htmlspecialchars(json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                $this->data = $this->fillData($this->data, self::$config['templates']);
            }

            $this->data = $this->renderData([
                [
                    'type' => 'multifields',
                    'name' => 'multifields',
                    'form.id' => $this->params['storage'] == 'files' ? '-mf-data[' . $this->params['id'] . '__' . $this->params['tv']['id'] . ']' : $this->params['tv']['id'],
                    'tv.id' => $this->params['tv']['id'],
                    'tv.name' => $this->params['tv']['name'],
                    'items' => $this->data,
                    'values' => $values
                ]
            ]);
            //echo microtime(true) - $start . ' s.';

            if (!empty($ResourceManagerLoaded)) {
                $ResourceManagerLoaded = $tmp_ResourceManagerLoaded;
            }
        }

        return $this->data;
    }

    /**
     * @param string $key
     * @return array|null
     */
    protected function getConfig($key = '')
    {
        if (empty(self::$config)) {
            if (!is_dir($this->basePath . 'config')) {
                mkdir($this->basePath . 'config', 0755);
            }

            if (file_exists($this->basePath . 'config/' . $this->params['tv']['name'] . '.php')) {
                self::$config = require $this->basePath . 'config/' . $this->params['tv']['name'] . '.php';
            } elseif (file_exists($this->basePath . 'config/' . $this->params['tv']['id'] . '.php')) {
                self::$config = require $this->basePath . 'config/' . $this->params['tv']['id'] . '.php';
            }

            if (!is_array(self::$config)) {
                self::$config = null;
            } else {
                if (!isset(self::$config['settings'])) {
                    self::$config['settings'] = [];
                }
                if (!isset(self::$config['templates'])) {
                    self::$config['templates'] = [];
                }
                if (!isset(self::$config['items'])) {
                    self::$config['items'] = [];
                }
                //self::$config['templates'] = $this->configNormalize(self::$config['templates']);
            }
        }

        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        return self::$config;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function configNormalize($data = [])
    {
        foreach ($data as $k => &$v) {
            if (!is_array($v)) {
                if (isset(self::$config['templates'][$v])) {
                    $data[$v] = self::$config['templates'][$v];
                }
                unset($data[$k]);
            }
            if (!empty($v['items'])) {
                $v['items'] = $this->configNormalize($v['items']);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $this->data = [];

        switch ($this->params['storage']) {
            case 'files':
                $this->data = $this->fileData();
                break;

            default:
                $this->data = !empty($this->params['tv']['value']) ? json_decode($this->params['tv']['value'], true) : self::$config['items'];
                break;
        }

        return $this->data;
    }

    /**
     * @param int $doc_id
     * @param string $tv_id
     * @return string
     */
    protected function fileData($doc_id = 0, $tv_id = '')
    {
        $data = [];

        if (!is_dir($this->basePath . 'data')) {
            mkdir($this->basePath . 'data', 0755);
        }

        if (empty($doc_id) && !empty($this->params['id'])) {
            $doc_id = $this->params['id'];
        }

        if (empty($tv_id) && isset($this->params['tv']['id'])) {
            $tv_id = $this->params['tv']['id'];
        }

        $file = $this->basePath . 'data/' . $doc_id . '__' . $tv_id . '.json';

        if (file_exists($file)) {
            $data = file_get_contents($file);
            $data = json_decode($data, true);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $config
     * @param array $find
     * @return array
     */
    protected function fillData($data = [], $config = [], $find = [])
    {
        foreach ($data as $k => &$v) {
            $find = $this->findElements($v['name'], $config);
            $v = array_merge($find, $v);

            if (!isset($v['name'])) {
                $v['name'] = $k;
            }

            if (empty($v['type'])) {
                $v['type'] = is_numeric($v['name']) ? 'text' : $v['name'];
            }

            $this->element($v['type'])
                ->preFillData($v, $config, $find);

            if (!empty($v['items'])) {
                if (!empty($find['items']) && (!isset($v['ignoreConfig']) || (isset($v['ignoreConfig']) && !$v['ignoreConfig']))) {
                    foreach ($v['items'] as $key => $item) {
                        if (isset($find['items'][$item['name']])) {
                            $v['items'][$key] = array_merge($find['items'][$item['name']], $item);
                        } else {
                            unset($v['items'][$key]);
                        }
                    }
                    foreach ($find['items'] as $key => $item) {
                        $item['name'] = $key;
                        $_item = false;
                        foreach ($v['items'] as $val) {
                            if ($val['name'] == $key) {
                                $_item = true;
                            }
                        }
                        if (!$_item) {
                            $v['items'][] = $item;
                        }
                    }
                }
                $v['items'] = $this->element($v['type'])
                    ->fillData($v['items'], $find['items'], $find);
            } elseif (!empty($find['items'])) {
                $v['items'] = $this->element($v['type'])
                    ->fillData($find['items'], $find['items'], $find);
            }
        }

        unset($config);

        return $data;
    }

    /**
     * @param array $item
     * @param array $config
     * @param array $find
     */
    protected function preFillData(&$item = [], $config = [], $find = [])
    {

    }

    /**
     * @param string $key
     * @param array $config
     * @return array
     */
    protected function findElements($key = '', $config = [])
    {
        $result = [];

        if (isset($config[$key])) {
            $result = $config[$key];
        } elseif (isset(self::$config['templates'][$key])) {
            $result = self::$config['templates'][$key];
        } else {
            if (is_array($config)) {
                foreach ($config as $k => $v) {
                    if (isset($v['items'])) {
                        $result = $this->findElements($key, $v['items']);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function renderData($data = [])
    {
        $out = '';

        foreach ($data as $k => $v) {
            $_v = $v;
            if (!empty($v['items']) && $this->element($v['type'])) {
                $v['items'] = $this->element($v['type'])
                    ->renderData($v['items']);
            } else {
                unset($v['items']);
            }

            $out .= $this->renderFormElement($v, $_v);
        }

        unset($data);

        return $out;
    }

    /**
     * @param null $className
     * @return object|null
     */
    protected static function element($className = null)
    {
        $element = null;

        if (!is_null($className)) {
            $className = str_replace('!', '', $className);
        }

        if (!isset($className)) {
            $className = get_called_class();
        } elseif (strpos($className, '\\') === false) {
            $className = '\\Multifields\\Elements\\' . ucfirst($className);
        }

        if (isset(self::$elements[$className])) {
            $element = self::$elements[$className];
        } elseif (class_exists($className)) {
            $element = self::$elements[$className] = new $className();
            if (self::$elements[$className]->disabled) {
                unset(self::$elements[$className]);
                $element = null;
            }
        }

        return $element;
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    protected function renderFormElement($params = [], $data = [])
    {
        $out = '';
        $params = array_merge([
            'id' => $this->uniqid(),
            'name' => '',
            'attr' => '',
            'value' => '',
            'title' => '',
            'style' => '',
            'class' => '',
            'default' => '',
            'elements' => '',
            'item.attr' => '',
            'items.class' => '',
            'placeholder' => '',
            'items' => ''
        ], $params);

        if (!empty($params['type'])) {
            if ($this->element($params['type'])) {
                $out = $this->element($params['type'])
                    ->render($params, $data);
            } else {
                $element = renderFormElement($params['type'], $params['id'], $params['default'], $params['elements'], $params['value'], $params['style']);

                if ($params['placeholder']) {
                    $params['item.attr'] .= ' placeholder="' . $params['placeholder'] . '"';
                }

                $element = str_replace('id="', $params['item.attr'] . ' id="', $element);

                if ($params['title']) {
                    $element = '<label for="tv' . $params['id'] . '">' . $params['title'] . '</label>' . $element;
                }

                $out = $this->element('element')
                    ->render([
                        'class' => !empty($params['class']) ? $params['class'] : 'col',
                        'attr' => 'data-type="' . $params['type'] . '" data-name="' . $params['name'] . '" ' . $params['attr'],
                        'items' => $element
                    ]);
            }
        }

        return $out;
    }

    /**
     * @return string
     */
    protected function uniqid()
    {
        return 'id' . time() . rand(0, 99999);
    }

    /**
     *
     */
    public function saveData()
    {
        if (isset($_POST['tv-mf-data']) && $this->params['storage'] == 'files') {
            foreach ($_POST['tv-mf-data'] as $k => $data) {
                list($id, $tvId) = explode('__', $k);
                $this->params['id'] = $id;
                $this->params['tv']['id'] = $tvId;
                $data = evolutionCMS()->removeSanitizeSeed($data);
                $file = $this->basePath . 'data/' . $id . '__' . $tvId . '.json';
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
}
