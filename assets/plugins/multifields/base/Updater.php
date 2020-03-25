<?php

namespace Multifields\Base;

class Updater
{
    const OLD_VERSION = '1.3.0';

    /**
     * Run updates
     */
    public function run()
    {
        if (version_compare($this->getVersion(), '2.0', '<')) {
            $this->setVersion((new \Multifields\Base\Updates\Updater200())->run());
        }

        if (version_compare($this->getVersion(), Core::VERSION, '<')) {
            $this->setVersion(Core::VERSION);
        }
    }

    /**
     * @return string
     */
    protected function getVersion()
    {
        $version = self::OLD_VERSION;

        if (is_file(dirname(__DIR__) . '/version.multifields.php')) {
            $version = file_get_contents(dirname(__DIR__) . '/version.multifields.php');
        }

        return $version;
    }

    /**
     * @param string $version
     * @return string
     */
    protected function setVersion($version = null)
    {
        if (empty($version)) {
            $version = self::OLD_VERSION;
        }

        file_put_contents(dirname(__DIR__) . '/version.multifields.php', $version);

        return $version;
    }
}
