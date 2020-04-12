<?php

namespace Multifields\Base;

use DLTemplate;

class Elements extends Core
{
    private static $elements;
    protected $tpl;
    protected $actions;
    protected $_template;
    protected $scripts;
    protected $styles;
    protected $disabled = false;

    /**
     * @return string
     */
    public function getStartScripts()
    {
        $styles = [
            '@' => [
                $this->setFileUrl('view/css/core.css', dirname(__DIR__) . '/elements/multifields/')
            ]
        ];

        $scripts = [
            '@' => [
                $this->setFileUrl('view/js/Sortable.min.js', dirname(__DIR__) . '/elements/multifields/'),
                $this->setFileUrl('view/js/core.js', dirname(__DIR__) . '/elements/multifields/')
            ]
        ];

        if ($elements = glob(Core::getParams('basePath') . 'elements/*', GLOB_ONLYDIR)) {
            foreach ($elements as $element) {
                $name = ucfirst(basename($element));

                if (!self::element($name)) {
                    continue;
                }

                if ($files = self::element($name)
                    ->getStyles()) {
                    if (!isset($styles[$name])) {
                        if (is_array($files)) {
                            foreach ($files as $style) {
                                if ($style = $this->setFileUrl($style, self::element($name)
                                    ->path())) {
                                    $styles[$name][] = $style;
                                }
                            }
                        } else {
                            if ($style = $this->setFileUrl($files, self::element($name)
                                ->path())) {
                                $styles[$name][] = $style;
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
                                    ->path())) {
                                    $scripts[$name][] = $script;
                                }
                            }
                        } else {
                            if ($script = $this->setFileUrl($files, self::element($name)
                                ->path())) {
                                $scripts[$name][] = $script;
                            }
                        }
                    }
                }
            }
        }

        $out = '';

        if (Core::DEBUG) {
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

            if (is_file(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css')) {
                unlink(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css');
            }

            if (is_file(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js')) {
                unlink(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js');
            }
        } else {
            if (!is_file(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css') || !is_file(dirname(__DIR__) . '/elements/multifields/view/js/scripts.min.js')) {
                $__ = '';
                foreach ($styles as $files) {
                    foreach ($files as $style) {
                        $__ .= file_get_contents($style);
                    }
                }

                file_put_contents(dirname(__DIR__) . '/elements/multifields/view/css/styles.min.css', Compress::css($__));

                $__ = '';
                foreach ($scripts as $files) {
                    foreach ($files as $script) {
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
            $parent = trim(str_replace(MODX_BASE_PATH, '', str_replace(DIRECTORY_SEPARATOR, '/', $parent)), '\\/');

            $url = $parent . '/' . $url;

            if (is_file(MODX_BASE_PATH . $url)) {
                //$url .= '?time=' . filemtime(MODX_BASE_PATH . $url);
            } else {
                $url = '';
            }
        }

        return MODX_SITE_URL . $url;
    }

    /**
     * @param null $className
     * @return object|null
     */
    public static function element($className = null)
    {
        $element = null;

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
    public function render($params = [], $data = [])
    {
        $this->getActions($params);

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
                        $params['actions'] .= '<i class="mf-actions-' . $action . ' fa" onclick="Multifields.elements.' . $params['type'] . '.action' . ucfirst($action) . '(event);"></i>';
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
     * @param array $params
     * @return string
     */
    protected function view($params = [])
    {
        return class_exists('DLTemplate') ? DLTemplate::getInstance(evolutionCMS())
            ->parseChunk('@CODE:' . $this->getTemplate(), $params, false, true) : evolutionCMS()->parseText($this->getTemplate(), $params);
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        if (empty($this->_template)) {
            if (empty($this->tpl)) {
                $this->_template = $this->tpl = str_replace(DIRECTORY_SEPARATOR, '/', dirname(dirname(__DIR__)) . '/' . strtolower($this->classBasename()) . '/' . strtolower($this->classBasename())) . '.tpl';
            } else {
                $this->_template = trim($this->tpl, '/');
                $this->_template = $this->path() . $this->_template;
            }
            if (is_file($this->_template)) {
                $this->_template = file_get_contents($this->_template);
            } else {
                $this->_template = 'Error: Could not load template ' . $this->tpl . ' in class ' . static::class . '!<br>';
            }
        }

        return $this->_template;
    }

    /**
     * @return string
     */
    protected function classBasename()
    {
        return basename(str_replace('\\', '/', static::class));
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
     * @param array $params
     * @return false|string
     */
    public function actionTemplate($params = [])
    {
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
    protected static function fillData($data = [], $config = [], $find = [])
    {
        foreach ($data as $k => &$v) {
            $find = self::findElements($v['name'], $config);
            $v = array_merge($find, $v);

            if (!isset($v['name'])) {
                $v['name'] = $k;
            }

            if (empty($v['type'])) {
                $v['type'] = is_numeric($v['name']) ? 'text' : $v['name'];
            }

            if (self::element($v['type'])) {
                self::element($v['type'])
                    ->preFillData($v, $config, $find);
            }

            if (!empty($v['items'])) {
                //                if (!empty($find['items']) && (!isset($v['ignoreConfig']) || (isset($v['ignoreConfig']) && !$v['ignoreConfig']))) {
                //                    foreach ($v['items'] as $key => $item) {
                //                        if (isset($find['items'][$item['name']])) {
                //                            $v['items'][$key] = array_merge($find['items'][$item['name']], $item);
                //                        } else {
                //                            unset($v['items'][$key]);
                //                        }
                //                    }
                //                    foreach ($find['items'] as $key => $item) {
                //                        $item['name'] = $key;
                //                        $_item = false;
                //                        foreach ($v['items'] as $val) {
                //                            if ($val['name'] == $key) {
                //                                $_item = true;
                //                            }
                //                        }
                //                        if (!$_item) {
                //                            $v['items'][] = $item;
                //                        }
                //                    }
                //                }
                $v['items'] = self::element($v['type'])
                    ->fillData($v['items'], $find['items'], $find);
            } elseif (!empty($find['items'])) {
                $v['items'] = self::element($v['type'])
                    ->fillData($find['items'], $find['items'], $find);
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
    protected static function renderData($data = [])
    {
        $out = '';

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
            if (self::element($params['type'])) {
                $out = self::element($params['type'])
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

                $out = self::element('element')
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
    protected static function uniqid()
    {
        return 'id' . time() . rand(0, 99999);
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
}
