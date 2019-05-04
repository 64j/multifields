<?php
/**.
 * class MultiFields_front
 * @author 64j
 */

class MultiFieldsFront
{
    private static $instance = null;
    protected $evo;
    protected $basePath;
    protected $params;
    protected $data;
    protected $config;

    /**
     * MultiFieldsFront constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->evo = evolutionCMS();
        $this->setParams($params);
        $this->basePath = MODX_BASE_PATH . 'assets/plugins/multifields/';
    }

    /**
     * @param $evo
     * @param array $params
     * @return array|string
     */
    static function getInstance($params = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($params);
        }

        return self::$instance->setParams($params)
            ->renderForm();
    }

    /**
     * @return array|string
     */
    protected function renderForm()
    {
        $out = '';
        if (!empty($this->params['tvid'])) {
            if (!empty($this->getData())) {
                $out = $this->renderData(0, 0, $this->getConfig());
                $out['mf.type'] = 'wrap';
                $out = $this->tpl('tpl', $out);
                $out = $this->tpl('wrap', [
                    'mf.type' => 'wrap',
                    'mf.items' => $out
                ]);
            }
        } else {
            $out = 'tv not found';
        }

        return $out;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $this->data = [];
        if ($this->params['storage'] == 'files') {
            $this->getDataFromFile();
        } else {
            $this->getDataFromBase();
        }

        return $this->data;
    }

    /**
     * @return array|mixed
     */
    protected function getConfig()
    {
        $this->config = [];

        if (file_exists($this->basePath . 'config/' . $this->params['tvid'] . '.php')) {
            $this->config = require_once $this->basePath . 'config/' . $this->params['tvid'] . '.php';
        }

        if (is_array($this->config)) {
            foreach ($this->config as $k => &$v) {
                if (!isset($v['title'])) {
                    $v['title'] = $k;
                }
            }
        }

        return $this->config;
    }

    /**
     *
     */
    protected function getDataFromFile()
    {
        if (file_exists($this->params['file'])) {
            include $this->params['file'];
        }
    }

    /**
     * @return array
     */
    protected function getDataFromBase()
    {
        $this->data = [];

        if (!empty($this->params['docid']) && !empty($this->params['tvid'])) {
            $sql = $this->evo->db->query('
            SELECT id, field_parent, field_id, field_name, field_value
            FROM ' . $this->evo->getFullTableName('multifields') . ' 
            WHERE doc_id=' . $this->params['docid'] . ' AND tv_id=' . $this->params['tvid'] . '
            ORDER BY field_id ASC');

            if ($this->evo->db->getRecordCount($sql)) {
                while ($row = $this->evo->db->getRow($sql)) {
                    $this->data[$row['field_id']] = [
                        'parent' => $row['field_parent'],
                        'name' => $row['field_name'],
                        'value' => $row['field_value']
                    ];
                }
            }
        }

        return $this->data;
    }

    /**
     * @param int $parent
     * @param int $level
     * @param array $config
     * @return array
     */
    protected function renderData($parent = 0, $level = 0, $config = [])
    {
        $out = [];
        $level++;
        $i = 1;
        $result = [];
        foreach ($this->data as $k => $v) {
            $v['mf.parent'] = $v['parent'];
            $v['mf.name'] = $v['name'];
            $v['mf.level'] = $level;

            unset($v['parent']);
            unset($v['name']);

            $prepare = strtolower('prepare_' . $v['mf.name']);
            $tpl = strtolower('tpl_' . $v['mf.name']);

            if ($parent == $v['mf.parent']) {
                $v['mf.iteration'] = $i++;
                if (isset($v['items'])) {
                    $v['mf.items'] = $v['items'];
                }

                $this->findData($v['mf.name'], $config, $result);

                if (isset($this->config[$v['mf.name']])) {
                    $result = $this->config[$v['mf.name']];
                }

                if (!empty($this->params[$prepare])) {
                    $v = $this->prepare($this->params[$prepare], $v);
                } elseif (isset($result['prepare'])) {
                    $v = $this->prepare($result['prepare'], $v);
                }

                if (empty($this->params[$tpl]) && !empty($result['tpl'])) {
                    $this->params[$tpl] = $result['tpl'];
                }

                if ($_ = $this->renderData($k, $level, $result)) {
                    $v = array_merge($v, $_);
                    $out[] = $this->tpl($tpl, $v);
                } else {
                    if (!isset($out[$v['mf.name']])) {
                        $out[$v['mf.name']] = '';
                    }

                    $out[$v['mf.name']] .= $this->tpl($tpl, $v);
                }
            }
        }

        if (!empty($out)) {
            $out['mf.items'] = $this->tpl('', [
                'mf.type' => 'wrap',
                'mf.items' => is_array($out) ? implode($out) : $out,
                'mf.level' => $level
            ]);
        }

        return $out;
    }

    /**
     * @param $searchKey
     * @param $arr
     * @param $result
     */
    protected function findData($searchKey, $arr, &$result)
    {
        if (isset($arr[$searchKey])) {
            $result = $arr[$searchKey];

            return;
        }
        foreach ($arr as $key => $param) {
            if ($key == 'items') {
                $this->findData($searchKey, $param, $result);
            }
        }
    }

    /**
     * @param string $tpl
     * @param array $data
     * @return string
     */
    protected function tpl($tpl = '', $data = [])
    {
        $tpl = strtolower($tpl);
        if ($tpl != '' && isset($this->params[$tpl])) {
            $out = $this->evo->parseText($this->evo->getTpl($this->params[$tpl]), $data);
        } elseif (isset($data['mf.type']) && in_array($data['mf.type'], ['wrap', 'row', 'group'])) {
            $out = $this->evo->parseText($this->evo->getTpl('@CODE:[+mf.items+]'), $data);
        } else {
            $out = $this->evo->parseText($this->evo->getTpl('@CODE:[+value+]'), $data);
        }

        return $out;
    }

    /**
     * @param array $params
     * @return $this
     */
    protected function setParams($params = [])
    {
        $this->params = array_change_key_case($params, CASE_LOWER);

        $pluginParams = [];
        if (!empty($this->evo->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode($this->evo->pluginCache['multifieldsProps'], true);
        }

        $this->params['theme'] = empty($pluginParams['multifields_theme']) ? 'default' : $pluginParams['multifields_theme'];
        $this->params['storage'] = empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'];

        if (empty($this->params['docid'])) {
            $this->params['docid'] = $this->evo->documentIdentifier;
        }

        if (empty($this->params['tvid'])) {
            $this->params['tvid'] = 0;
        }

        if (empty($this->params['prepare'])) {
            $this->params['prepare'] = '';
        }

        $this->params['file'] = $this->basePath . 'data/' . $this->params['docid'] . '__' . $this->params['tvid'] . '.php';

        return $this;
    }

    /**
     * @param $name
     * @param string $default
     * @return string|null
     */
    public function param($name, $default = '')
    {
        $return = null;
        if (isset($this->params[$name])) {
            $return = $this->params[$name];
        } else {
            $return = $default;
        }

        return $return;
    }

    /**
     * @param string $name
     * @param array $data
     * @return array|mixed|string
     */
    protected function prepare($name = 'prepare', $data = [])
    {
        if (!empty($name)) {
            $params = [
                'data' => $data,
                'modx' => $this->evo,
                '_MF' => $this
            ];

            if ((is_object($name)) || is_callable($name)) {
                $data = call_user_func_array($name, $params);
            } else {
                $data = $this->evo->runSnippet($name, $params);
            }
        }

        return $data;
    }
}
