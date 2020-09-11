<?php

namespace Multifields\Base;

use DLTemplate;

class Elements
{
    private static $elements;
    protected static $params;
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
    private function setFileUrl($url = '', $parent = '', $timestamp = true, $check_cache = false)
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
     * @return string
     */
    public function render()
    {
        self::element(self::$params['type'])
            ->setAttr();

        self::element(self::$params['type'])
            ->setTitle();

        return $this->view();
    }

    protected function setActions()
    {
        if (!empty($this->actions)) {
            $actions = !empty($this->actions) && is_array($this->actions) ? array_flip($this->actions) : [];
            $_actions = isset(self::$params['actions']) ? (!empty(self::$params['actions']) ? self::$params['actions'] : $actions) : true;

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

            self::$params['actions'] = '';

            if (is_array($this->actions)) {
                foreach ($this->actions as $action) {
                    if (isset($_actions[$action])) {
                        if ($action == 'move') {
                            self::$params['class'] .= ' mf-draggable';
                        }
                        self::$params['actions'] .= self::element(self::$params['type'])
                            ->renderAction($action, self::$params['type']);
                    }
                }
            }
        } else {
            self::$params['actions'] = '';
        }

        $class = empty(self::$params['actions']) ? ' mf-empty-actions' : '';

        self::$params['actions'] = '<div id="mf-actions-' . self::$params['id'] . '" class="mf-actions' . $class . '">' . self::$params['actions'] . '</div>';
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
        self::$params = array_merge(is_array(self::$params) ? self::$params : [], $params);

        if (is_array($lang)) {
            foreach ($lang as $k => $v) {
                self::$params['lang.' . $k] = $v;
            }
        }

        return class_exists('DLTemplate') ? DLTemplate::getInstance(evolutionCMS())
            ->parseChunk('@CODE:' . $this->getTemplate(), self::$params, false, true) : evolutionCMS()->parseText($this->getTemplate(), self::$params);
    }

    /**
     * @return string
     */
    private function getTemplate()
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
     * @param string $key
     * @param array $config
     * @return array
     */
    private static function findElements($key = '', $config = [])
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

        if (isset($result['tpl'])) {
            unset($result['tpl']);
        }

        if (isset($result['prepare'])) {
            unset($result['prepare']);
        }

        return $result;
    }

    /**
     * @param array $data
     * @param array $config
     * @return string
     */
    public static function renderData($data = [], $config = [])
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $k = explode('#', $k)[0];

                if (!isset($v['name'])) {
                    $v['name'] = $k;
                }

                $find = self::findElements($k, $config);

                if (!empty($find)) {
                    $v = array_merge($find, $v);
                }

                if (!isset($find['items'])) {
                    $find['items'] = [];
                }

                if (empty($v['type'])) {
                    $v['type'] = is_numeric($v['name']) ? 'text' : $v['name'];
                }

                if (self::element($v['type'])) {
                    self::element($v['type'])
                        ->preFillData($v, $config, $find);
                }

                if (!empty($v['items']) && self::element($v['type'])) {
                    $v['items'] = self::element($v['type'])
                        ->renderData($v['items'], $find['items']);
                }

                $out .= self::renderFormElement($v);
            }
        } else {
            $out = $data;
        }

        unset($data);

        return $out;
    }

    /**
     * @param array $params
     * @return string
     */
    protected static function renderFormElement($params = [])
    {
        if (!empty($params['type'])) {
            self::$params = array_merge([
                'id' => self::uniqid(),
                'name' => '',
                'attr' => '',
                'value' => '',
                'label' => '',
                'title' => '',
                'title.attr' => '',
                'placeholder' => '',
                'style' => '',
                'class' => '',
                'default' => '',
                'elements' => '',
                'item.attr' => '',
                'items' => '',
                'items.class' => '',
                'items.attr' => ''
            ], $params);

            $element = self::element(self::$params['type']);

            if (!$element) {
                self::setTitle();
                self::$params['class'] = trim('col ' . self::$params['class']);
                self::$params['attr'] = 'data-type="' . self::$params['type'] . '" data-name="' . self::$params['name'] . '" ' . self::$params['attr'];

                self::$params['items'] = renderFormElement(self::$params['type'], self::$params['id'], self::$params['default'], self::$params['elements'], self::$params['value'], self::$params['style']);

                if (in_array(self::$params['type'], ['option', 'checkbox'])) {
                    self::$params['items'] = str_replace(['id="tv', 'for="tv'], ['id="tv' . self::$params['id'], 'for="tv' . self::$params['id']], self::$params['items']);
                }

                if (self::$params['placeholder'] != '') {
                    self::$params['item.attr'] .= ' placeholder="' . self::$params['placeholder'] . '"';
                }

                if (self::$params['item.attr']) {
                    self::$params['items'] = str_replace('id="', self::$params['item.attr'] . ' id="', self::$params['items']);
                }

                self::$params['items'] = self::$params['title'] . self::$params['items'];
                self::$params['type'] = 'element';

                $element = self::element(self::$params['type']);
            }

            return $element->render();
        }
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
    private function path($dir = '')
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__) . '/elements/' . strtolower($this->classBasename()) . '/' . $dir);
    }

    /**
     * @return string
     */
    private function classBasename()
    {
        $className = explode('\\', static::class);

        return $className[count($className) - 2];
    }

    /**
     * @return array
     */
    private function getStyles()
    {
        return $this->styles;
    }

    /**
     * @return array
     */
    private function getScripts()
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
     * @param $icon
     * @return string
     */
    protected static function setIcon($icon)
    {
        if (!empty($icon)) {
            $attr = '';
            if ($icon[0] == '<') {
                $attr .= ' class="mf-icon mf-icon-image"';
            } elseif (stripos($icon, '/') !== false) {
                $attr .= ' class="mf-icon mf-icon-image"';
                $attr .= ' style="background-image: url(\'' . $icon . '\');"';
                $icon = '';
            } elseif ($icon) {
                $attr .= ' class="mf-icon ' . $icon . '"';
                $icon = '';
            }

            $icon = '<div' . $attr . '>' . $icon . '</div>';
        }

        return $icon;
    }

    protected static function setTitle()
    {
        if (self::$params['title'] != '') {
            self::$params['title'] = '<div class="mf-title" ' . self::$params['title.attr'] . '>' . self::$params['title'] . '</div>';
        }
    }

    protected static function setAttr()
    {
        foreach (self::$params as $k => $param) {
            if (strpos($k, 'mf.') !== false) {
                self::$params['attr'] .= str_replace('mf.', ' data-mf-', strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $k))) . '="' . $param . '"';
            }
        }

        if (!empty(self::$params['limit'])) {
            self::$params['attr'] .= ' data-limit="' . (int)self::$params['limit'] . '"';
        }
    }

    protected static function setValue()
    {
        if (isset(self::$params['value']) && self::$params['value'] !== false) {
            if (is_bool(self::$params['value'])) {
                self::$params['value'] = '';
            }

            self::$params['value'] = '
            <div class="mf-value">
                <input type="text" class="form-control" name="' . self::$params['id'] . '_value" value="' . stripcslashes(self::$params['value']) . '"' . (isset(self::$params['placeholder']) ? ' placeholder="' . self::$params['placeholder'] . '"' : '') . ' data-value>
            </div>';
        }
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
            $params['html'] = $this->renderData([
                $params['tpl'] => Core::getConfig('templates')[$params['tpl']]
            ]);
        }

        return json_encode($params, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
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
