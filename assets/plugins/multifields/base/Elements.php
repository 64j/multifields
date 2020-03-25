<?php

namespace Multifields\Base;

use DLTemplate;

class Elements extends \Multifields\Base\Core
{
    protected $_template;
    protected $tpl;
    protected $actions;
    protected $disabled = false;

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
     * @param string $dir
     * @return string
     */
    protected function path($dir = '')
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__) . '/elements/' . strtolower(basename(static::class)) . '/' . $dir);
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
     * @param array $data
     * @return string
     */
    public function render($params = [], $data = [])
    {
        $this->getActions($params);

        return $this->view($params);
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
     * @return bool|false|string
     */
    protected function getTemplate()
    {
        if (empty($this->_template)) {
            if (empty($this->tpl)) {
                $this->_template = $this->tpl = str_replace(DIRECTORY_SEPARATOR, '/', dirname(dirname(__DIR__)) . '/' . strtolower(static::class) . '/' . strtolower(basename(static::class))) . '.tpl';
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
     * @param array $params
     * @return false|string
     */
    public function actionTemplate($params = [])
    {
        $this->init();
        $this->params['tv']['id'] = $params['tvid'];
        $this->params['tv']['name'] = $params['tvname'];

        if (!empty($this->getConfig('templates')[$params['tpl']])) {
            $this->data = $this->fillDataTemplate([$params['tpl'] => $this->getConfig('templates')[$params['tpl']]]);
            $data = $this->fillData($this->data, $this->getConfig('templates'));
            $params['html'] = $this->renderData($data);
        }

        return $this->json_encode($params);
    }

    /**
     * @param array $data
     * @param int $options
     * @param int $depth
     * @return false|string
     */
    protected function json_encode(
        $data = [],
        $options = JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE,
        $depth = 512
    ) {
        return json_encode($data, $options, $depth);
    }
}