<?php
/**
 * Multifields
 *
 * Custom fields for documents
 *
 * @author 64j
 */

namespace Multifields\Base;

use DLTemplate;

class Front
{
    private static $instance;
    protected $basePath;
    protected $params;
    protected $data;
    protected $config;

    public function __construct()
    {
        $evo = evolutionCMS();
        $this->basePath = str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)) . '/';

        $pluginParams = [];
        if (!empty($evo->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode($evo->pluginCache['multifieldsProps'], true);
        }
        $this->params['theme'] = empty($pluginParams['multifields_theme']) ? 'default' : $pluginParams['multifields_theme'];
        $this->params['storage'] = empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'];
        $this->params['docid'] = $evo->documentIdentifier;
        $this->params['tvId'] = 0;
        $this->params['tvName'] = '';
        $this->params['api'] = null;
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
     * @param array $params
     * @return false|string
     */
    public function render($params = [])
    {
        $this->params = array_merge($this->params, $params);
        $this->params['file'] = $this->basePath . 'data/' . $this->params['docid'] . '__' . $this->params['tvId'] . '.json';
        $out = '';

        if (!empty($this->getData() && (!empty($this->params['tvId']) || !empty($this->params['tvName'])))) {
            switch ($this->params['api']) {
                case '1':
                    $out = $this->data;
                    break;

                case 'json':
                    $out = json_encode($this->data, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                    break;

                default:
                    $out = $this->renderData($this->data, 0, $this->getConfig('templates'));
                    $out = $out['mf.items'];
                    break;
            }
        }

        return $out;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $this->data = [];

        switch ($this->params['storage']) {
            case 'files':
                $this->getDataFromFile();
                break;

            default:
                $this->getDataFromEvo();
                break;
        }

        return $this->data;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    protected function getConfig($key = null)
    {
        $this->config = [];

        if (file_exists($this->basePath . 'config/' . $this->params['tvName'] . '.php')) {
            $this->config = require_once $this->basePath . 'config/' . $this->params['tvName'] . '.php';
        } elseif (file_exists($this->basePath . 'config/' . $this->params['tvId'] . '.php')) {
            $this->config = require_once $this->basePath . 'config/' . $this->params['tvId'] . '.php';
        }

        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @param array $data
     * @param int $level
     * @param array $config
     * @return array
     */
    protected function renderData($data = [], $level = 0, $config = [])
    {
        $out = [];
        $find = [];
        $level++;
        $i = 1;

        foreach ($data as $k => $v) {
            $v['mf.name'] = $v['name'];
            $v['mf.level'] = $level;
            $v['mf.iteration'] = $i++;

            if (!isset($v['value'])) {
                $v['value'] = '';
            }

            $this->findData($v['name'], $config, $find);

            // template
            if (isset($this->params['tpl_' . $v['name']])) {
                $tpl = $this->params['tpl_' . $v['name']];
            } elseif (isset($find['tpl'])) {
                $tpl = $find['tpl'];
            } else {
                $tpl = null;
            }

            // prepare
            if (!empty($this->params['prepare_' . $v['name']])) {
                $prepare = $this->params['prepare_' . $v['name']];
            } elseif (!empty($find['prepare'])) {
                $prepare = $find['prepare'];
            } else {
                $prepare = null;
            }

            $this->prepare($prepare, $v);

            if (isset($v['items'])) {
                $v = array_merge($v, $this->renderData($v['items'], $level, $config));
                $this->prepare($prepare, $v);
            }

            $out[] = $this->tpl($tpl, $v);
        }

        if (!empty($out)) {
            $out['mf.items'] = is_array($out) ? implode($out) : $out;
            $out['mf.level'] = $level;
        }

        return $out;
    }

    /**
     * @param $key
     * @param $arr
     * @param $find
     */
    protected function findData($key, $arr, &$find)
    {
        if (isset($arr[$key])) {
            $find = $arr[$key];

            return;
        }
        foreach ($arr as $k => $v) {
            if ($k == 'items') {
                $this->findData($key, $v, $find);
            }
        }
    }

    /**
     * @return array|mixed
     */
    protected function getDataFromEvo()
    {
        $evo = evolutionCMS();
        $this->data = [];

        if ($this->params['docid'] == $evo->documentIdentifier) {
            if (!empty($this->params['tvId'])) {
                foreach ($evo->documentObject as $k => $v) {
                    if (!empty($v['id']) && $v['id'] == $this->params['tvId']) {
                        $this->data = json_decode($v[1], true);
                        break;
                    }
                }
            } elseif (!empty($this->params['tvName'])) {
                if (isset($evo->documentObject[$this->params['tvName']][1])) {
                    $this->params['tvId'] = $evo->documentObject[$this->params['tvName']]['id'];
                    $this->data = json_decode($evo->documentObject[$this->params['tvName']][1], true);
                }
            }
        } else {
            if (!empty($this->params['tvId'])) {
                $result = $evo->db->query('
                SELECT tv.id, tv.name, tvc.value
                FROM ' . $evo->getFullTableName('site_tmplvars') . ' AS tv
                LEFT JOIN ' . $evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                WHERE 
                tvc.contentid="' . $evo->db->escape($this->params['docid']) . '"
                AND tv.id="' . $evo->db->escape($this->params['tvId']) . '"');

                if ($evo->db->getRecordCount($result)) {
                    $row = $evo->db->getRow($result);
                    $this->params['tvName'] = $row['name'];
                    $this->data = json_decode($row['value'], true);
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
                    $this->params['tvId'] = $this->params['tvName'];
                    $this->data = json_decode($evo->db->getValue('
                    SELECT sc.' . $this->params['tvName'] . '
                    FROM ' . $evo->getFullTableName('site_content') . ' AS sc
                    WHERE 
                    sc.id="' . $evo->db->escape($this->params['docid']) . '"'), true);
                } else {
                    $result = $evo->db->query('
                    SELECT tv.id, tv.name, tvc.value
                    FROM ' . $evo->getFullTableName('site_tmplvars') . ' AS tv
                    LEFT JOIN ' . $evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                    WHERE 
                    tvc.contentid="' . $evo->db->escape($this->params['docid']) . '"
                    AND tv.name="' . $evo->db->escape($this->params['tvName']) . '"');
                    if ($evo->db->getRecordCount($result)) {
                        $row = $evo->db->getRow($result);
                        $this->params['tvId'] = $row['id'];
                        $this->data = json_decode($row['value'], true);
                    }
                }
            }
        }

        return $this->data;
    }

    /**
     * @return void
     */
    protected function getDataFromFile()
    {
        if (file_exists($this->basePath . 'data/' . $this->params['docid'] . '__' . $this->params['tvId'] . '.json')) {
            $this->data = json_encode(file_get_contents($this->basePath . 'data/' . $this->params['docid'] . '__' . $this->params['tvId'] . '.json'), true);
        }
    }

    /**
     * @param $documentObject
     * @return array
     */
    public function addDocumentObject($documentObject = [])
    {
        $evo = evolutionCMS();

        if (!empty($documentObject['id'])) {
            $rs = $evo->db->select("tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value", $evo->getFullTableName('site_tmplvars') . " tv
                INNER JOIN " . $evo->getFullTableName('site_tmplvar_templates') . " tvtpl ON tvtpl.tmplvarid = tv.id
                LEFT JOIN " . $evo->getFullTableName('site_tmplvar_contentvalues') . " tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '{$documentObject['id']}'", "tvtpl.templateid = '{$documentObject['template']}'");

            while ($row = $evo->db->getRow($rs)) {
                if (isset($documentObject[$row['name']])) {
                    $documentObject[$row['name']]['id'] = $row['id'];
                }
            }
        }

        return $documentObject;
    }

    /**
     * @param null $tpl
     * @param array $plh
     * @return string
     */
    protected function tpl($tpl = null, $plh = [])
    {
        if (empty($tpl)) {
            if (isset($plh['mf.items'])) {
                $tpl = '@CODE:[+mf.items+]';
            } else {
                $tpl = '@CODE:[+value+]';
            }
        }

        return class_exists('DLTemplate') ? DLTemplate::getInstance(evolutionCMS())
            ->parseChunk($tpl, $plh, false, true) : evolutionCMS()->parseText(evolutionCMS()->getTpl($tpl), $plh);
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
    protected function prepare($name = 'prepare', &$data = [])
    {
        if (!empty($name)) {
            $params = [
                'data' => $data,
                'modx' => evolutionCMS(),
                '_MF' => $this
            ];

            if ((is_object($name)) || is_callable($name)) {
                $data = call_user_func_array($name, $params);
            } else {
                $data = evolutionCMS()->runSnippet($name, $params);
            }
        }
    }
}