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
        if (!empty($this->params['tvId']) || !empty($this->params['tvName']) || isset($this->params['data'])) {
            if (!empty($this->getData())) {
                $api = isset($this->params['api']) ? $this->params['api'] : '';
                switch ($api) {
                    case '0':
                        $out = $this->data;
                        break;

                    case '1':
                        $out = $this->fillData($this->data);
                        break;

                    case 'json':
                        $out = json_encode($this->fillData($this->data), JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                        break;

                    default:
                        $out = $this->renderData(0, 0, $this->getConfig());
                        $out['mf.type'] = 'wrap';
                        $out = $this->tpl('tpl', $out);
                        $out = $this->tpl('wrap', [
                            'mf.type' => 'wrap',
                            'mf.items' => $out
                        ]);
                        break;
                }
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

        if (isset($this->params['data'])) {
            $this->data = !empty($this->params['data']) ? json_decode($this->params['data'], true) : [];
        } else {
            switch ($this->params['storage']) {
                case 'files':
                    $this->getDataFromFile();
                    break;

                case 'database':
                    $this->getDataFromBase();
                    break;

                default:
                    $this->getDataFromEvo();
                    break;
            }
        }

        return $this->data;
    }

    /**
     * @param array $data
     * @param int $parent
     * @param int $level
     * @return array
     */
    protected function fillData($data = [], $parent = 0, $level = 0)
    {
        $out = [];
        $level++;
        foreach ($data as $k => $v) {
            if ($parent == $v['parent']) {
                $v['level'] = $level;
                if ($_ = $this->fillData($data, $k, $level)) {
                    $v['items'] = $_;
                }
                $out[] = $v;
            } else {
                unset($data[$k]);
            }
        }

        return $out;
    }

    /**
     * @return array|mixed
     */
    protected function getConfig()
    {
        $this->config = [];

        if (file_exists($this->basePath . 'config/' . $this->params['tvName'] . '.php')) {
            $this->config = require $this->basePath . 'config/' . $this->params['tvName'] . '.php';
        } elseif (file_exists($this->basePath . 'config/' . $this->params['tvId'] . '.php')) {
            $this->config = require $this->basePath . 'config/' . $this->params['tvId'] . '.php';
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
     * @return void
     */
    protected function getDataFromEvo()
    {
        $this->data = [];

        if ($this->params['docid'] == $this->evo->documentIdentifier) {
            if (!empty($this->params['tvId'])) {
                $result = $this->evo->db->query('
                SELECT tv.id, tv.name, tvc.value
                FROM ' . $this->evo->getFullTableName('site_tmplvars') . ' AS tv
                LEFT JOIN ' . $this->evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                WHERE 
                tvc.contentid="' . $this->evo->db->escape($this->params['docid']) . '"
                AND tv.id="' . $this->evo->db->escape($this->params['tvId']) . '"');
                if ($this->evo->db->getRecordCount($result)) {
                    $row = $this->evo->db->getRow($result);
                    $this->data = $row['value'];
                    $this->params['tvName'] = $row['name'];
                }
            } elseif (!empty($this->params['tvName'])) {
                if (isset($this->evo->documentObject[$this->params['tvName']])) {
                    if (is_array($this->evo->documentObject[$this->params['tvName']])) {
                        $this->data = $this->evo->documentObject[$this->params['tvName']][1];
                    } else {
                        $this->data = $this->evo->documentObject[$this->params['tvName']];
                    }
                    $this->params['tvId'] = $this->evo->db->getValue('SELECT id FROM ' . $this->evo->getFullTableName('site_tmplvars') . ' WHERE name="' . $this->params['tvName'] . '"');
                } else {
                    $result = $this->evo->db->query('
                    SELECT tv.id, tv.name, tvc.value
                    FROM ' . $this->evo->getFullTableName('site_tmplvars') . ' AS tv
                    LEFT JOIN ' . $this->evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                    WHERE 
                    tvc.contentid="' . $this->evo->db->escape($this->params['docid']) . '"
                    AND tv.name="' . $this->evo->db->escape($this->params['tvName']) . '"');
                    if ($this->evo->db->getRecordCount($result)) {
                        $row = $this->evo->db->getRow($result);
                        $this->data = $row['value'];
                        $this->params['tvId'] = $row['id'];
                    }
                }
            }

        } else {
            if (!empty($this->params['tvId'])) {
                $result = $this->evo->db->query('
                SELECT tv.id, tv.name, tvc.value
                FROM ' . $this->evo->getFullTableName('site_tmplvars') . ' AS tv
                LEFT JOIN ' . $this->evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                WHERE 
                tvc.contentid="' . $this->evo->db->escape($this->params['docid']) . '"
                AND tv.id="' . $this->evo->db->escape($this->params['tvId']) . '"');
                if ($this->evo->db->getRecordCount($result)) {
                    $row = $this->evo->db->getRow($result);
                    $this->data = $row['value'];
                    $this->params['tvName'] = $row['name'];
                }
            } else {
                $default_field = array(
                    'type',
                    'contentType',
                    'pagetitle',
                    'longtitle',
                    'description',
                    'alias',
                    'link_attributes',
                    'published',
                    'pub_date',
                    'unpub_date',
                    'parent',
                    'isfolder',
                    'introtext',
                    'content',
                    'richtext',
                    'template',
                    'menuindex',
                    'searchable',
                    'cacheable',
                    'createdon',
                    'createdby',
                    'editedon',
                    'editedby',
                    'deleted',
                    'deletedon',
                    'deletedby',
                    'publishedon',
                    'publishedby',
                    'menutitle',
                    'donthit',
                    'privateweb',
                    'privatemgr',
                    'content_dispo',
                    'hidemenu',
                    'alias_visible'
                );
                if (in_array($this->params['tvName'], $default_field)) {
                    $this->data = $this->evo->db->getValue('
                    SELECT sc.' . $this->params['tvName'] . '
                    FROM ' . $this->evo->getFullTableName('site_content') . ' AS sc
                    WHERE 
                    sc.id="' . $this->evo->db->escape($this->params['docid']) . '"');
                    $this->params['tvId'] = $this->params['tvName'];
                } else {
                    $result = $this->evo->db->query('
                    SELECT tv.id, tv.name, tvc.value
                    FROM ' . $this->evo->getFullTableName('site_tmplvars') . ' AS tv
                    LEFT JOIN ' . $this->evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                    WHERE 
                    tvc.contentid="' . $this->evo->db->escape($this->params['docid']) . '"
                    AND tv.name="' . $this->evo->db->escape($this->params['tvName']) . '"');
                    if ($this->evo->db->getRecordCount($result)) {
                        $row = $this->evo->db->getRow($result);
                        $this->data = $row['value'];
                        $this->params['tvId'] = $row['id'];
                    }
                }
            }
        }

        $this->data = !empty($this->data) ? json_decode($this->data, true) : [];
    }

    /**
     * @return void
     */
    protected function getDataFromFile()
    {
        if (file_exists($this->params['file'])) {
            include $this->params['file'];
        }
    }

    /**
     * @return void
     */
    protected function getDataFromBase()
    {
        $this->data = [];

        if (!empty($this->params['docid']) && !empty($this->params['tvId'])) {
            $sql = $this->evo->db->query('
            SELECT id, field_parent, field_id, field_name, field_value
            FROM ' . $this->evo->getFullTableName('multifields') . ' 
            WHERE doc_id=' . $this->params['docid'] . ' AND tv_id=' . $this->params['tvId'] . '
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
    }

    /**
     * @param int $parent
     * @param int $level
     * @param array $config
     * @return array
     */
    protected function renderData($parent = 0, $level = 0, $config = [])
    {
        $out = [
            'mf.items' => []
        ];
        $level++;
        $i = 1;
        $result = [];
        foreach ($this->data as $k => $v) {
            $v['mf.parent'] = $v['parent'];
            $v['mf.name'] = $v['name'];
            $v['mf.level'] = $level;

            unset($v['parent']);
            unset($v['name']);

            $prepare = 'prepare_' . $v['mf.name'];
            $tpl = 'tpl_' . $v['mf.name'];

            if ($parent == $v['mf.parent']) {
                $v['mf.iteration'] = $i++;
                if (isset($v['items'])) {
                    $v['mf.items'] = $v['items'];
                }

                $this->findData($v['mf.name'], $config, $result);

                if (isset($this->config[$v['mf.name']])) {
                    $result = $this->config[$v['mf.name']];
                }

                if (isset($v['value'])) {
                    $v['value'] = stripcslashes($v['value']);
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
                    if (!empty($this->params[$prepare])) {
                        $v = $this->prepare($this->params[$prepare], $v);
                    } elseif (isset($result['prepare'])) {
                        $v = $this->prepare($result['prepare'], $v);
                    }
                }

                $_out = $this->tpl($tpl, $v);

                if (!isset($out[$v['mf.name']])) {
                    $out[$v['mf.name']] = '';
                }

                // Assign by name
                $out[$v['mf.name']] .= $_out;

                // Assign by name and iteration
                $out[$v['mf.name'] . '__' . $v['mf.iteration']] = $_out;

                // Assign All
                $out['mf.items'][] = $_out;
            }
        }

        if (!empty($out['mf.items'])) {
            $out['mf.items'] = $this->tpl('', [
                'mf.type' => 'wrap',
                'mf.items' => is_array($out['mf.items']) ? implode($out['mf.items']) : $out['mf.items'],
                'mf.level' => $level
            ]);
        } else {
            unset($out['mf.items']);
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
        $this->params = $params;

        $pluginParams = [];
        if (!empty($this->evo->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode($this->evo->pluginCache['multifieldsProps'], true);
        }

        $this->params['theme'] = empty($pluginParams['multifields_theme']) ? 'default' : $pluginParams['multifields_theme'];
        $this->params['storage'] = empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'];

        if (empty($this->params['docid'])) {
            $this->params['docid'] = $this->evo->documentIdentifier;
        }

        if (empty($this->params['tvId'])) {
            $this->params['tvId'] = 0;
        }

        if (empty($this->params['tvName'])) {
            $this->params['tvName'] = '';
        }

        if (empty($this->params['prepare'])) {
            $this->params['prepare'] = '';
        }

        $this->params['file'] = $this->basePath . 'data/' . $this->params['docid'] . '__' . $this->params['tvId'] . '.php';

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
