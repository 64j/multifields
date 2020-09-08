<?php

namespace Multifields\Base;

use DLTemplate;

class Elements
{
    private static $elements;
    protected static $lang;
    protected $tpl;
    protected $actions;
    protected $template;
    protected $scripts;
    protected $styles;
    protected $disabled = false;
    protected $file_has_changed = null;

    /**
     * Elements constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param null $key
     * @return mixed
     */
    public static function lang($key = null)
    {
        if (empty(self::$lang)) {
            $path = dirname(dirname(__DIR__)) . '/' . strtolower(dirname(str_replace('\\', '/', static::class))) . '/lang/';
            if (is_file($path . evolutionCMS()->getConfig('manager_language') . '.php')) {
                self::$lang = require_once $path . evolutionCMS()->getConfig('manager_language') . '.php';
            } elseif (is_file($path . 'english.php')) {
                self::$lang = require_once $path . 'english.php';
            }
        }

        if (isset(self::$lang[$key])) {
            return self::$lang[$key];
        }

        return self::$lang;
    }

    /**
     * @return string
     */
    public function getStartScripts()
    {
        $out = '';
        $cache_styles = dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css';
        $cache_scripts = dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js';

        $styles = [
            '@' => [
                $this->setFileUrl('view/css/core.css', dirname(__DIR__) . '/elements/multifields/', true, true)
            ]
        ];

        $this->removeFile($cache_styles, $this->hasFileChanged($styles['@'][0]));

        $scripts = [
            '@' => [
                $this->setFileUrl('view/js/Sortable.min.js', dirname(__DIR__) . '/elements/multifields/'),
                $this->setFileUrl('view/js/core.js', dirname(__DIR__) . '/elements/multifields/', true, true)
            ]
        ];

        $this->removeFile($cache_scripts, $this->hasFileChanged($scripts['@'][1]));

        if ($elements = glob(Core::getParams('basePath') . 'elements/*', GLOB_ONLYDIR)) {
            foreach ($elements as $element) {
                if ($elements_elements = glob($element . '/*.php')) {
                    $namespace = ucfirst(basename($element));

                    foreach ($elements_elements as $elements_element) {
                        $name = rtrim(basename($elements_element), '.php');

                        $name = $namespace . ':' . $name;

                        if (!self::element($name)) {
                            continue;
                        }

                        if ($files = self::element($name)
                            ->getStyles()) {
                            if (!isset($styles[$name])) {
                                if (is_array($files)) {
                                    foreach ($files as $style) {
                                        if ($style = $this->setFileUrl($style, self::element($name)
                                            ->path(), true, true)) {
                                            $styles[$name][] = $style;
                                            $this->removeFile($cache_styles, $this->hasFileChanged($style));
                                        }
                                    }
                                } else {
                                    if ($style = $this->setFileUrl($files, self::element($name)
                                        ->path(), true, true)) {
                                        $styles[$name][] = $style;
                                        $this->removeFile($cache_styles, $this->hasFileChanged($style));
                                    }
                                }
                            }
                        }

                        if ($files = self::element($name)
                            ->getScripts()) {

                            if (!isset($scripts[$name])) {
                                if (is_array($files)) {
                                    foreach ($files as $script) {
                                        if ($script = $this->setFileUrl($script, self::element($name)
                                            ->path(), true, true)) {
                                            $scripts[$name][] = $script;
                                            $this->removeFile($cache_scripts, $this->hasFileChanged($script));
                                        }
                                    }
                                } else {
                                    if ($script = $this->setFileUrl($files, self::element($name)
                                        ->path(), true, true)) {
                                        $scripts[$name][] = $script;
                                        $this->removeFile($cache_scripts, $this->hasFileChanged($script));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (Core::getParams('debug')) {
            foreach ($styles as $files) {
                foreach ($files as $style) {
                    $out .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $style . '"/>';
                }
            }

            foreach ($scripts as $files) {
                foreach ($files as $script) {
                    $out .= "\n" . '<script src="' . $script . '"></script>';
                }
            }

            $this->removeFile($cache_styles);
            $this->removeFile($cache_scripts);
        } else {
            if (!is_file($cache_styles) || !is_file($cache_scripts)) {

                $__ = '';
                foreach ($styles as $files) {
                    foreach ($files as $style) {
                        $__ .= file_get_contents($style);
                    }
                }

                file_put_contents($cache_styles, Compress::css($__));

                $__ = '';
                foreach ($scripts as $files) {
                    foreach ($files as $script) {
                        $__ .= ';' . file_get_contents($script);
                    }
                }

                file_put_contents($cache_scripts, Compress::js($__));
            }

            $out .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $this->setFileUrl($cache_styles, dirname(__DIR__) . '/') . '"/>';
            $out .= "\n" . '<script src="' . $this->setFileUrl($cache_scripts, dirname(__DIR__) . '/') . '"></script>';
        }

        return $out;
    }

    /**
     * @param string $url
     * @param string $parent
     * @param bool $timestamp
     * @param bool $check_cache
     * @return string
     */
    protected function setFileUrl($url = '', $parent = '', $timestamp = true, $check_cache = false)
    {
        if (!empty($url)) {
            $url = str_replace(dirname(__DIR__), '', $url);
            $url = trim(str_replace(DIRECTORY_SEPARATOR, '/', $url), '\\/');
            $parent = trim(str_replace(MODX_BASE_PATH, '', str_replace(DIRECTORY_SEPARATOR, '/', $parent)), '\\/');

            $url = $parent . '/' . $url;

            if (is_file(MODX_BASE_PATH . $url) && $timestamp) {
                if (is_bool($timestamp)) {
                    $timestamp = filemtime(MODX_BASE_PATH . $url);
                }
                if ($check_cache) {
                    if (Core::getParams('debug')) {
                        $this->removeFile(MODX_BASE_PATH . $url . '.cache');
                    } else {
                        $this->file_has_changed[MODX_SITE_URL . $url] = !is_file(MODX_BASE_PATH . $url . '.cache') || (is_file(MODX_BASE_PATH . $url . '.cache') && $timestamp != file_get_contents(MODX_BASE_PATH . $url . '.cache'));
                        if ($this->file_has_changed[MODX_SITE_URL . $url]) {
                            file_put_contents(MODX_BASE_PATH . $url . '.cache', $timestamp);
                        }
                    }
                }
                if (!Core::getParams('debug')) {
                    $url .= '?time=' . $timestamp;
                }
            } else {
                $url = '';
            }
        }

        $url = MODX_SITE_URL . $url;

        return $url;
    }

    /**
     * @param $file
     * @param bool $remove
     */
    protected function removeFile($file, $remove = true)
    {
        if ($remove && is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @param $url
     * @return bool
     */
    protected function hasFileChanged($url)
    {
        return !empty($this->file_has_changed[explode('?', $url)[0]]);
    }

    /**
     * @param null $className
     * @return object|null
     */
    public static function element($className = null)
    {
        $element = null;
        $name = null;

        if (isset($className)) {
            list($className, $name) = explode(':', $className . ':');
            if (empty($name)) {
                $name = $className;
            }
        }

        if (substr($name, 0, 5) == 'Front') {
            return null;
        }

        if (!isset($className)) {
            $className = get_called_class();
        } elseif (strpos($className, '\\') === false) {
            $className = '\\Multifields\\Elements\\' . ucfirst($className) . '\\' . ucfirst($name);
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
    public function render($params = [], $data = [])
    {
        $this->getActions($params);

        foreach ($params as $k => $param) {
            if (substr($k, 0, 3) == 'mf.') {
                $params['attr'] .= str_replace('mf.', ' data-mf-', strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $k))) . '="' . $param . '"';
            }
        }

        return $this->view($params);
    }

    /**
     * @param $params
     */
    protected function getActions(&$params)
    {
        if (!empty($this->actions)) {
            $actions = !empty($this->actions) && is_array($this->actions) ? array_flip($this->actions) : [];
            $_actions = isset($params['actions']) ? (!empty($params['actions']) ? $params['actions'] : $actions) : true;

            if (is_array($_actions)) {
                $_actions = array_flip($_actions);
                $_actions = array_intersect_key($actions, $_actions);
            } else {
                if (is_null($_actions) || $_actions === true) {
                    $_actions = $actions;
                } else {
                    $_actions = [];
                }
            }

            $params['actions'] = '';

            if (is_array($this->actions)) {
                foreach ($this->actions as $action) {
                    if (isset($_actions[$action])) {
                        if ($action == 'move') {
                            $params['class'] .= ' mf-draggable';
                        }
                        $params['actions'] .= self::element($params['type'])
                            ->renderAction($action, $params['type']);
                    }
                }
            }
        } else {
            $params['actions'] = '';
        }

        $class = empty($params['actions']) ? ' mf-empty-actions' : '';

        $params['actions'] = '<div id="mf-actions-' . $params['id'] . '" class="mf-actions' . $class . '">' . $params['actions'] . '</div>';
    }

    /**
     * @param $action
     * @param $type
     * @return string
     */
    protected function renderAction($action, $type)
    {
        return '<i class="mf-actions-' . $action . ' fa" onclick="Multifields.elements[\'' . $type . '\'].action' . ucfirst($action) . '(event);"></i>';
    }

    /**
     * @param array $params
     * @return string
     */
    protected function view($params = [])
    {
        $lang = self::lang();

        if (is_array($lang)) {
            foreach ($lang as $k => $v) {
                $params['lang.' . $k] = $v;
            }
        }

        return class_exists('DLTemplate') ? DLTemplate::getInstance(evolutionCMS())
            ->parseChunk('@CODE:' . $this->getTemplate(), $params, false, true) : evolutionCMS()->parseText($this->getTemplate(), $params);
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        if (empty($this->template)) {
            if (empty($this->tpl)) {
                $name = str_replace('\\', '/', static::class);
                $this->template = $this->tpl = dirname(dirname(__DIR__)) . '/' . strtolower(dirname($name) . '/' . basename($name)) . '.tpl';
            } else {
                $this->template = dirname(dirname(__DIR__)) . '/' . strtolower(dirname(str_replace('\\', '/', static::class))) . '/' . trim($this->tpl, '/');
            }

            if (is_file($this->template)) {
                $this->template = file_get_contents($this->template);
            } else {
                $this->template = 'Error: Could not load template ' . $this->tpl . ' in class ' . static::class . '!<br>';
            }
        }

        return $this->template;
    }

    /**
     * @param array $params
     * @return false|string
     */
    public function actionTemplate($params = [])
    {
        Core::getInstance();
        Core::setParams([
            'tv' => [
                'id' => $params['tvid'],
                'name' => $params['tvname']
            ]
        ]);

        if (!empty(Core::getConfig('templates')[$params['tpl']])) {
            $data = $this->fillData($this->fillDataTemplate([$params['tpl'] => Core::getConfig('templates')[$params['tpl']]]), Core::getConfig('templates'));
            $params['html'] = $this->renderData($data);
        }

        return json_encode($params, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $data
     * @param array $config
     * @param array $find
     * @return array
     */
    public static function fillData($data = [], $config = [], $find = [])
    {
        if (is_array($data)) {
            foreach ($data as $k => &$v) {
                if (!isset($v['name'])) {
                    $v['name'] = $k;
                }

                $find = self::findElements($v['name'], $config);

                if (!isset($find['items'])) {
                    $find['items'] = [];
                }

                if (!empty($find)) {
                    $v = array_merge($find, $v);
                }

                if (empty($v['type'])) {
                    $v['type'] = is_numeric($v['name']) ? 'text' : $v['name'];
                }

                if (self::element($v['type'])) {
                    self::element($v['type'])
                        ->preFillData($v, $config, $find);
                }

                if (!empty($v['items'])) {
                    $v['items'] = self::element($v['type'])
                        ->fillData($v['items'], $find['items'], $find);
                } elseif (!empty($find['items'])) {
                    $v['items'] = self::element($v['type'])
                        ->fillData($find['items'], $find['items'], $find);
                }
            }
        }

        unset($config);

        return $data;
    }

    /**
     * @param string $key
     * @param array $config
     * @return array
     */
    protected static function findElements($key = '', $config = [])
    {
        $result = [];

        if (isset($config[$key])) {
            $result = $config[$key];
        } elseif (isset(Core::getConfig('templates')[$key])) {
            $result = Core::getConfig('templates')[$key];
        } else {
            if (is_array($config)) {
                foreach ($config as $k => $v) {
                    if (isset($v['items'])) {
                        $result = self::findElements($key, $v['items']);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function fillDataTemplate($data = [])
    {
        foreach ($data as $k => &$v) {
            if (is_array($v)) {
                $v['name'] = $k;
                unset($v['type']);
                if (isset($v['items'])) {
                    $v['items'] = $this->fillDataTemplate($v['items']);
                }
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return string
     */
    public static function renderData($data = [])
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $_v = $v;
                if (!empty($v['items']) && self::element($v['type'])) {
                    $v['items'] = self::element($v['type'])
                        ->renderData($v['items']);
                } else {
                    unset($v['items']);
                }

                $out .= self::renderFormElement($v, $_v);
            }
            unset($data);
        } else {
            $out = $data;
        }

        return $out;
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    protected static function renderFormElement($params = [], $data = [])
    {
        $out = '';

        $params = array_merge([
            'id' => self::uniqid(),
            'name' => '',
            'attr' => '',
            'value' => '',
            'title' => '',
            'label' => '',
            'style' => '',
            'class' => '',
            'default' => '',
            'elements' => '',
            'label.attr' => '',
            'item.attr' => '',
            'items.class' => '',
            'placeholder' => '',
            'items' => ''
        ], $params);

        if (!empty($params['type'])) {
            if (self::element($params['type'])) {
                $out = self::element($params['type'])
                    ->render($params, $data);
            } else {
                $element = renderFormElement($params['type'], $params['id'], $params['default'], $params['elements'], $params['value'], $params['style']);

                if ($params['placeholder']) {
                    $params['item.attr'] .= ' placeholder="' . $params['placeholder'] . '"';
                }

                if (in_array($params['type'], ['option', 'checkbox'])) {
                    $element = str_replace(['id="tv', 'for="tv'], ['id="tv' . $params['id'], 'for="tv' . $params['id']], $element);
                }

                $element = str_replace('id="', $params['item.attr'] . ' id="', $element);

                if ($params['label'] != '') {
                    $element = '<label for="tv' . $params['id'] . '" ' . $params['label.attr'] . '>' . $params['label'] . '</label>' . $element;
                }

                $out = self::element('element')
                    ->render([
                        'id' => $params['id'],
                        'class' => 'col ' . $params['class'],
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
    protected static function uniqid()
    {
        return 'id' . time() . rand(0, 99999);
    }

    /**
     * @param string $dir
     * @return string
     */
    protected function path($dir = '')
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__) . '/elements/' . strtolower($this->classBasename()) . '/' . $dir);
    }

    /**
     * @return string
     */
    protected function classBasename()
    {
        $className = explode('\\', static::class);

        return $className[count($className) - 2];
    }

    /**
     * @return array
     */
    protected function getStyles()
    {
        return $this->styles;
    }

    /**
     * @return array
     */
    protected function getScripts()
    {
        return $this->scripts;
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
     * @param null $str
     * @param null $str2
     * @param bool $exit
     */
    protected function dd($str = null, $str2 = null, $exit = false)
    {
        $class = 'col-6';

        if ($str == null || $str2 == null) {
            //$class = 'col-xs-12';
        }
        print '<div class="row">';
        print '<div class="' . $class . '">';
        print '<pre class="alert alert-info">';
        print_r($str);
        print '</pre>';
        print '</div>';

        print '<div class="' . $class . '">';
        print '<pre class="alert alert-warning">';
        print_r($str2);
        print '</pre>';
        print '</div>';

        print '</div>';
        if ($exit) {
            exit;
        }
    }
}
