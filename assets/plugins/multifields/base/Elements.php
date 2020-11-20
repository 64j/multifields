<?php

namespace Multifields\Base;

use DLTemplate;

class Elements
{
    private static $elements;
    protected $params;
    protected $lexicon;
    protected $tpl;
    protected $actions = ['add', 'move', 'del'];
    protected $template;
    protected $scripts;
    protected $styles;
    protected $disabled = false;

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
     * @param null $className
     * @return object|null
     */
    public function element($className = null)
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

        return class_exists('DLTemplate') ? DLTemplate::getInstance(evolutionCMS())
            ->parseChunk('@CODE:' . $this->getTemplate(), $this->params, false, true) : evolutionCMS()->parseText($this->getTemplate(), $this->params);
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
        } elseif (isset(mfc()->getConfig('templates')[$key])) {
            $result = mfc()->getConfig('templates')[$key];
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

                if (is_string($v)) {
                    $k = $v;
                    $v = [];
                }

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

                if (isset($find['value'])) {
                    if ($find['value'] === false) {
                        $v['value'] = false;
                    } elseif (!is_bool($find['value'])) {
                        $v['value'] = $find['value'];
                    }
                } elseif (isset($find['default']) && $v['value'] == '') {
                    $v['value'] = $find['default'];
                }

                if (empty($v['type'])) {
                    $v['type'] = is_numeric($v['name']) ? 'text' : $v['name'];
                }

                //$this->syncWithConfig($v, $find);

                if ($this->element($v['type'])) {
                    $this->element($v['type'])
                        ->preFillData($v, $config, $find);
                }

                if (!empty($v['items']) && $this->element($v['type'])) {
                    $v['items'] = $this->element($v['type'])
                        ->renderData($v['items'], $find['items']);
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
     * @param array $value
     * @param array $find
     */
    protected function syncWithConfig(&$value = [], $find = [])
    {
        if (!empty($value['items']) && !isset($find['templates'])) {
            $__items = [];
            foreach ($find['items'] as $key => $item) {
                if (isset($item['items'])) {
                    continue;
                }
                $__items[$key] = isset($value['items'][$key]) ? array_merge($item, $value['items'][$key]) : $item;
                $__items[$key]['type'] = $item['type'];
            }
            if (!empty($__items)) {
                $value['items'] = $__items;
            }
        }
    }

    /**
     * @param array $params
     * @return string
     */
    public function renderFormElement($params = [])
    {
        if (!empty($params['type'])) {
            $this->params = array_merge([
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
                'item.class' => '',
                'item.attr' => '',
                'items' => '',
                'items.class' => '',
                'items.attr' => '',
                'actions' => null
            ], $params);

            $element = $this->element($this->params['type']);

            if (!$element) {
                $this->setTitle();
                $this->params['class'] = trim('col ' . $this->params['class']);
                $this->params['attr'] = 'data-type="' . $this->params['type'] . '" data-name="' . $this->params['name'] . '" ' . $this->params['attr'];

                $this->params['items'] = renderFormElement($this->params['type'], $this->params['id'], $this->params['default'], $this->params['elements'], $this->params['value'], $this->params['style'], $this->params);

                if (in_array($this->params['type'], ['option', 'checkbox'])) {
                    $this->params['items'] = str_replace(['id="tv', 'for="tv'], ['id="tv' . $this->params['id'], 'for="tv' . $this->params['id']], $this->params['items']);
                }

                if ($this->params['placeholder'] != '') {
                    $this->params['item.attr'] .= ' placeholder="' . $this->params['placeholder'] . '"';
                }

                if ($this->params['item.attr']) {
                    $this->params['items'] = str_replace('id="tv', $this->params['item.attr'] . ' id="tv', $this->params['items']);
                }

                $this->params['items'] = $this->params['title'] . $this->params['items'];
                $this->params['type'] = 'element';

                $element = $this->element($this->params['type']);
            } else {
                if (!is_bool($this->params['value'])) {
                    $this->params['value'] = htmlspecialchars($this->params['value'], ENT_QUOTES, 'UTF-8');
                }
            }

            $element->setParams($this->params);

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
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @return array
     */
    public function getScripts()
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
                        $this->params['actions'] .= $this->setAction($action, $this->params['type']);
                    }
                }
            }
        } else {
            $this->params['actions'] = '';
        }

        $class = empty($this->params['actions']) ? ' mf-empty-actions' : '';

        $this->params['actions'] = '<div id="mf-actions-' . $this->params['id'] . '" class="mf-actions' . $class . '">' . $this->params['actions'] . '</div>';
    }

    /**
     * @param $action
     * @param $type
     * @return string
     */
    protected function setAction($action, $type)
    {
        if (!$this->element($this->params['type'])) {
            $type = 'elements';
        }
        return '<i class="mf-actions-' . $action . ' fa" onclick="Multifields.elements[\'' . $type . '\'].action' . ucfirst($action) . '(event);"></i>';
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
                <input type="text" class="form-control" name="' . $this->params['id'] . '_value" value="' . $this->params['value'] . '"' . (isset($this->params['placeholder']) ? ' placeholder="' . $this->params['placeholder'] . '"' : '') . ' data-value>
            </div>';
        }
    }

    /**
     * @param array $params
     * @return false|string
     */
    public function actionTemplate($params = [])
    {
        mfc([
            'tv' => [
                'id' => $params['tvid'],
                'name' => $params['tvname']
            ]
        ]);

        if (!empty(mfc()->getConfig('templates')[$params['tpl']])) {
            $params['html'] = $this->renderData([
                $params['tpl'] => mfc()->getConfig('templates')[$params['tpl']]
            ]);
            $params['type'] = mfc()->getConfig('templates')[$params['tpl']]['type'];
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
