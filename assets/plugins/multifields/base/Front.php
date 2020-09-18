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
    protected static $elements;
    protected static $params;
    protected static $config;
    protected static $data;
    private static $instance;

    public function __construct($params = [])
    {
        $evo = evolutionCMS();

        $pluginParams = [];
        if (!empty($evo->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode($evo->pluginCache['multifieldsProps'], true);
        }

        self::setParams(array_merge([
            'basePath' => str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)) . '/',
            'storage' => empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'],
            'docid' => $evo->documentIdentifier,
            'tvId' => 0,
            'tvName' => '',
            'api' => null
        ], $params));
    }

    /**
     * @param array $params
     * @return static
     */
    public static function getInstance($params = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($params);
        } else {
            self::setParams($params);
        }

        return self::$instance;
    }

    /**
     * @param array $params
     * @return false|string
     */
    public function render($params = [])
    {
        if (!isset($params['tvId'])) {
            $params['tvId'] = 0;
        }

        if (!isset($params['tvName'])) {
            $params['tvName'] = '';
        }

        self::setParams($params);
        self::setConfig(null);
        self::setData(null);

        $out = '';

        if (!empty(self::getData() && (!empty(self::getParams('tvId')) || !empty(self::getParams('tvName') || !empty(self::getParams('data')))))) {
            switch (self::getParams('api')) {
                case '1':
                    $out = self::getData();
                    break;

                case 'json':
                    $out = json_encode(self::getData(), JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                    break;

                default:
                    $out = $this->renderData(self::getData(), 0, self::getConfig('templates'));
                    $out = $out['mf.items'];
                    break;
            }
        }

        return $out;
    }

    /**
     * @param null $key
     * @param string $default
     * @return array|mixed
     */
    public static function getParams($key = null, $default = '')
    {
        if ($key == null) {
            return self::$params;
        } elseif ($key != '' && isset(self::$params[$key])) {
            return self::$params[$key];
        } elseif ($default != '') {
            return $default;
        }

        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    protected static function setParams($data = [])
    {
        if (empty(self::$params)) {
            self::$params = [];
        }

        return self::$params = array_merge(self::$params, $data);
    }

    /**
     * @param array $data
     * @return array
     */
    public static function setData($data = [])
    {
        return self::$data = $data;
    }

    /**
     * @return array
     */
    protected static function getData()
    {

        if (empty(self::$data)) {
            if (!is_null(self::getParams('data'))) {
                self::$data = json_decode(self::getParams('data', '{}'), true);
            } else {
                switch (self::getParams('storage')) {
                    case 'files':
                        self::getDataFromFile();
                        break;

                    default:
                        self::getDataFromEvo();
                        break;
                }
            }
        }

        return self::$data;
    }

    /**
     * @return void
     */
    protected static function getDataFromFile()
    {
        if (file_exists(self::getParams('basePath') . 'data/' . self::getParams('docid') . '__' . self::getParams('tvId') . '.json')) {
            self::$data = json_encode(file_get_contents(self::getParams('basePath') . 'data/' . self::getParams('docid') . '__' . self::getParams('tvId') . '.json'), true);
        }
    }

    /**
     * @return array|mixed
     */
    protected static function getDataFromEvo()
    {
        $evo = evolutionCMS();
        self::$data = [];

        if (self::getParams('docid') == $evo->documentIdentifier) {
            if (!empty(self::getParams('tvId'))) {
                foreach ($evo->documentObject as $k => $v) {
                    if (!empty($v['id']) && $v['id'] == self::getParams('tvId')) {
                        self::$data = json_decode($v[1], true);
                        break;
                    }
                }
            } elseif (!empty(self::getParams('tvName'))) {
                if (isset($evo->documentObject[self::getParams('tvName')][1])) {
                    self::setParams([
                        'tvId' => $evo->documentObject[self::getParams('tvName')]['id']
                    ]);
                    self::$data = json_decode($evo->documentObject[self::getParams('tvName')][1], true);
                }
            }
        } else {
            if (!empty(self::getParams('tvId'))) {
                $result = $evo->db->query('
                SELECT tv.id, tv.name, tvc.value
                FROM ' . $evo->getFullTableName('site_tmplvars') . ' AS tv
                LEFT JOIN ' . $evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                WHERE 
                tvc.contentid="' . $evo->db->escape(self::getParams('docid')) . '"
                AND tv.id="' . $evo->db->escape(self::getParams('tvId')) . '"');

                if ($evo->db->getRecordCount($result)) {
                    $row = $evo->db->getRow($result);
                    self::setParams([
                        'tvName' => $row['name']
                    ]);
                    self::$data = json_decode($row['value'], true);
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
                if (in_array(self::getParams('tvName'), $default_field)) {
                    self::setParams([
                        'tvId' => self::getParams('tvName')
                    ]);
                    self::$data = json_decode($evo->db->getValue('
                    SELECT sc.' . self::getParams('tvName') . '
                    FROM ' . $evo->getFullTableName('site_content') . ' AS sc
                    WHERE 
                    sc.id="' . $evo->db->escape(self::getParams('docid')) . '"'), true);
                } else {
                    $result = $evo->db->query('
                    SELECT tv.id, tv.name, tvc.value
                    FROM ' . $evo->getFullTableName('site_tmplvars') . ' AS tv
                    LEFT JOIN ' . $evo->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid = tv.id
                    WHERE 
                    tvc.contentid="' . $evo->db->escape(self::getParams('docid')) . '"
                    AND tv.name="' . $evo->db->escape(self::getParams('tvName')) . '"');
                    if ($evo->db->getRecordCount($result)) {
                        $row = $evo->db->getRow($result);
                        self::setParams([
                            'tvId' => self::getParams('id')
                        ]);
                        self::$data = json_decode($row['value'], true);
                    }
                }
            }
        }

        return self::$data;
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
        $level++;
        $i = 1;

        foreach ($data as $k => &$v) {
            if (!isset($v['name'])) {
                $v['name'] = $k;
            }
            $v['mf.name'] = $v['name'];
            $v['mf.type'] = $v['type'];
            $v['mf.level'] = $level;
            $v['mf.iteration'] = $i++;

            if (!isset($v['value'])) {
                $v['value'] = '';
            }

            $find = $this->findData($v['name'], $config);

            if ($this->element($v['type'])) {
                $v = $this->element($v['type'])
                    ->afterFindData($v, $find);
            }

            // template
            if (isset($find['tpl'])) {
                $tpl = $find['tpl'];
            } elseif (isset($v['tpl'])) {
                $tpl = $v['tpl'];
            } else {
                $tpl = null;
            }
            $v['tpl'] = self::getParams('tpl_' . $v['name'], $tpl);

            if ($v['tpl'] === null) {
                $first = reset($data);
                if (isset($first['tpl'])) {
                    $v['tpl'] = $first['tpl'];
                }
            }

            // prepare
            if (!empty($find['prepare'])) {
                $prepare = $find['prepare'];
            } elseif (!empty($v['prepare'])) {
                $prepare = $v['prepare'];
            } else {
                $prepare = null;
            }
            $prepare = self::getParams('prepare_' . $v['name'], $prepare);

            $this->prepare($prepare, $v);

            if (isset($v['items'])) {
                $v = array_merge($v, $this->renderData($v['items'], $level, $find));
                $this->prepare($prepare, $v);
            }
            $out[$k] = $this->tpl($v['tpl'], $v);
        }

        if (!empty($out)) {
            $out['mf.items'] = is_array($out) ? implode($out) : $out;
            $out['mf.level'] = $level;
        }

        return $out;
    }

    /**
     * @param $key
     * @param $config
     * @return array|mixed
     */
    protected function findData($key, $config)
    {
        $result = [];

        if (isset($config[$key])) {
            $result = $config[$key];
        } elseif (isset(self::getConfig('templates')[$key])) {
            $result = self::getConfig('templates')[$key];
        } elseif (is_array($config)) {
            foreach ($config as $k => $v) {
                if ($k == 'items') {
                    $result = self::findData($key, $v);
                }
            }
        }

        return $result;
    }

    /**
     * @param null $className
     * @return object|null
     */
    protected static function element($className = null)
    {
        $element = null;

        if (!isset($className)) {
            $className = get_called_class();
        } elseif (strpos($className, '\\') === false) {
            $className = 'Multifields\\Elements\\' . ucfirst($className) . '\\Front' . ucfirst($className);
        }

        if (isset(self::$elements[$className])) {
            $element = self::$elements[$className];
        } elseif (class_exists($className)) {
            $element = self::$elements[$className] = new $className();
            if (!empty(self::$elements[$className]->disabled)) {
                unset(self::$elements[$className]);
                $element = null;
            }
        }

        return $element;
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
     * @param array $data
     * @return array
     */
    public static function setConfig($data = [])
    {
        return self::$config = $data;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    protected static function getConfig($key = null)
    {
        if (empty(self::$config)) {
            if (file_exists(self::getParams('basePath') . 'config/' . self::getParams('tvName') . '.php')) {
                self::$config = require_once self::getParams('basePath') . 'config/' . self::getParams('tvName') . '.php';
            } elseif (file_exists(self::getParams('basePath') . 'config/' . self::getParams('tvId') . '.php')) {
                self::$config = require_once self::getParams('basePath') . 'config/' . self::getParams('tvId') . '.php';
            }
        }

        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        return self::$config;
    }

    /**
     * @param $documentObject
     * @return array
     */
    public function addDocumentObject($documentObject = [])
    {
        $evo = evolutionCMS();

        if (!empty($documentObject['id'])) {
            if (version_compare($evo->getConfig('settings_version'), '2.0', '<')) {
                $rs = $evo->db->select("tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value", $evo->getFullTableName('site_tmplvars') . " tv
                INNER JOIN " . $evo->getFullTableName('site_tmplvar_templates') . " tvtpl ON tvtpl.tmplvarid = tv.id
                LEFT JOIN " . $evo->getFullTableName('site_tmplvar_contentvalues') . " tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '{$documentObject['id']}'", "tvtpl.templateid = '{$documentObject['template']}'");

                while ($row = $evo->db->getRow($rs)) {
                    if (isset($documentObject[$row['name']])) {
                        $documentObject[$row['name']]['id'] = $row['id'];
                    }
                }
            } else {
                $rs = \DB::table('site_tmplvars as tv')
                    ->select('tv.*', 'tvc.value', 'tv.default_text')
                    ->join('site_tmplvar_templates as tvtpl', 'tvtpl.tmplvarid', '=', 'tv.id')
                    ->leftJoin('site_tmplvar_contentvalues as tvc', function ($join) use ($documentObject) {
                        $join->on('tvc.tmplvarid', '=', 'tv.id');
                        $join->on('tvc.contentid', '=', \DB::raw((int)$documentObject['id']));
                    })
                    ->where('tvtpl.templateid', (int)$documentObject['template'])
                    ->get();

                foreach ($rs as $row) {
                    if (isset($documentObject[$row->name])) {
                        $documentObject[$row->name]['id'] = $row->id;
                    }
                }
            }
        }

        return $documentObject;
    }

    /**
     * @param $name
     * @param string $default
     * @return string
     */
    public function param($name, $default = '')
    {
        return self::getParams($name, $default);
    }
}
