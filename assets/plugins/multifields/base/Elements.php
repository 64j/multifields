<?php

namespace Multifields\Base;

use DLTemplate;

class Elements
{
    private static $elements;
    protected $params;
    protected $lexicon;
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
        $this->getLexicon();
    }

    /**
     *
     */
    public function getLexicon()
    {
        if (empty($this->lexicon) && basename(dirname(static::class)) == basename(static::class)) {
            $path = dirname(dirname(__DIR__)) . '/' . strtolower(dirname(str_replace('\\', '/', static::class))) . '/lang/';
            if (is_file($path . evolutionCMS()->getConfig('manager_language') . '.php')) {
                $this->lexicon = require_once $path . evolutionCMS()->getConfig('manager_language') . '.php';
            } elseif (is_file($path . 'english.php')) {
                $this->lexicon = require_once $path . 'english.php';
            }
        }
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
                        $element = $this->element($name);

                        if (!$element) {
                            continue;
                        }

                        if ($files = $element->getStyles()) {
                            if (!isset($styles[$name])) {
                                if (is_array($files)) {
                                    foreach ($files as $style) {
                                        if ($style = $this->setFileUrl($style, $element->path(), true, true)) {
                                            $styles[$name][] = $style;
                                            $this->removeFile($cache_styles, $this->hasFileChanged($style));
                                        }
                                    }
                                } else {
                                    if ($style = $this->setFileUrl($files, $element->path(), true, true)) {
                                        $styles[$name][] = $style;
                                        $this->removeFile($cache_styles, $this->hasFileChanged($style));
                                    }
                                }
                            }
                        }

                        if ($files = $element->getScripts()) {

                            if (!isset($scripts[$name])) {
                                if (is_array($files)) {
                                    foreach ($files as $script) {
                                        if ($script = $this->setFileUrl($script, $element->path(), true, true)) {
                                            $scripts[$name][] = $script;
                                            $this->removeFile($cache_scripts, $this->hasFileChanged($script));
                                        }
                                    }
                                } else {
                                    if ($script = $this->setFileUrl($files, $element->path(), true, true)) {
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
    protected function element($className = null)
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

        if (isset(Elements::$elements[$className])) {
            $element = Elements::$elements[$className];
        } elseif (class_exists($className)) {
            $element = Elements::$elements[$className] = new $className();
            if (Elements::$elements[$className]->disabled) {
                unset(Elements::$elements[$className]);
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
        $this->setAttr();
        $this->setTitle();

        return $this->view();
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
        if (!empty($params)) {
            $this->setParams($params);
        }

        if (!empty($this->lexicon) && is_array($this->lexicon)) {
            foreach ($this->lexicon as $k => $v) {
                $this->params['lang.' . $k] = $v;
            }
        }

        return class_exists('DLTemplate') ? DLTemplate::getInstance(evolutionCMS())->parseChunk('@CODE:' . $this->getTemplate(), $this->params, false, true) : evolutionCMS()->parseText($this->getTemplate(), $this->params);
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
    private function findElements($key = '', $config = [])
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
                        $result = $this->findElements($key, $v['items']);
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
    public function renderData($data = [], $config = [])
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $k = explode('#', $k)[0];

                if (!isset($v['name'])) {
                    $v['name'] = $k;
                }

                $find = $this->findElements($k, $config);

                if (!empty($find)) {
                    $v = array_merge($find, $v);
                }

                if (!isset($find['items'])) {
                    $find['items'] = [];
                }

                if (empty($v['type'])) {
                    $v['type'] = is_numeric($v['name']) ? 'text' : $v['name'];
                }

                if ($this->element($v['type'])) {
                    $this->element($v['type'])->preFillData($v, $config, $find);
                }

                if (!empty($v['items']) && $this->element($v['type'])) {
                    $v['items'] = $this->element($v['type'])->renderData($v['items'], $find['items']);
                }

                $out .= $this->renderFormElement($v);
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
    public function renderFormElement($params = [])
    {
        if (!empty($params['type'])) {
            $params = array_merge([
                'id' => $this->uniqid(),
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

            $element = $this->element($params['type']);

            if (!$element) {
                $this->setTitle();
                $params['class'] = trim('col ' . $params['class']);
                $params['attr'] = 'data-type="' . $params['type'] . '" data-name="' . $params['name'] . '" ' . $params['attr'];

                $params['items'] = renderFormElement($params['type'], $params['id'], $params['default'], $params['elements'], $params['value'], $params['style'], $params);

                if (in_array($params['type'], ['option', 'checkbox'])) {
                    $params['items'] = str_replace(['id="tv', 'for="tv'], ['id="tv' . $params['id'], 'for="tv' . $params['id']], $params['items']);
                }

                if ($params['placeholder'] != '') {
                    $params['item.attr'] .= ' placeholder="' . $params['placeholder'] . '"';
                }

                if ($params['item.attr']) {
                    $params['items'] = str_replace('id="', $params['item.attr'] . ' id="', $params['items']);
                }

                $params['items'] = $params['title'] . $params['items'];
                $params['type'] = 'element';

                $element = $this->element($params['type']);
            }

            $element->setParams($params);

            return $element->render();
        }
    }

    /**
     * @return string
     */
    protected function uniqid()
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
     * @param null $key
     * @return mixed|null
     */
    protected function getParams($key = null)
    {
        return is_null($key) ? $this->params : (isset($this->params[$key]) ? $this->params[$key] : null);
    }

    /**
     * @param array $params
     */
    protected function setParams($params = [])
    {
        $this->params = $params;
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
    protected function setIcon($icon)
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

    protected function setActions()
    {
        if (!empty($this->actions)) {
            $actions = !empty($this->actions) && is_array($this->actions) ? array_flip($this->actions) : [];
            $_actions = isset($this->params['actions']) ? (!empty($this->params['actions']) ? $this->params['actions'] : $actions) : true;

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

            $this->params['actions'] = '';

            if (is_array($this->actions)) {
                foreach ($this->actions as $action) {
                    if (isset($_actions[$action])) {
                        if ($action == 'move') {
                            $this->params['class'] .= ' mf-draggable';
                        }
                        $this->params['actions'] .= $this->element($this->params['type'])->renderAction($action, $this->params['type']);
                    }
                }
            }
        } else {
            $this->params['actions'] = '';
        }

        $class = empty($this->params['actions']) ? ' mf-empty-actions' : '';

        $this->params['actions'] = '<div id="mf-actions-' . $this->params['id'] . '" class="mf-actions' . $class . '">' . $this->params['actions'] . '</div>';
    }

    protected function setTitle()
    {
        if ($this->params['title'] != '') {
            $this->params['title'] = '<div class="mf-title" ' . $this->params['title.attr'] . '>' . $this->params['title'] . '</div>';
        }
    }

    protected function setAttr()
    {
        foreach ($this->params as $k => $param) {
            if (strpos($k, 'mf.') !== false) {
                $this->params['attr'] .= str_replace('mf.', ' data-mf-', strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $k))) . '="' . $param . '"';
            }
        }

        if (!empty($this->params['limit'])) {
            $this->params['attr'] .= ' data-limit="' . (int)$this->params['limit'] . '"';
        }
    }

    protected function setValue()
    {
        if (isset($this->params['value']) && $this->params['value'] !== false) {
            if (is_bool($this->params['value'])) {
                $this->params['value'] = '';
            }

            $this->params['value'] = '
            <div class="mf-value">
                <input type="text" class="form-control" name="' . $this->params['id'] . '_value" value="' . stripcslashes($this->params['value']) . '"' . (isset($this->params['placeholder']) ? ' placeholder="' . $this->params['placeholder'] . '"' : '') . ' data-value>
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
