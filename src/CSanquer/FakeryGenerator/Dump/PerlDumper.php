<?php

namespace CSanquer\FakeryGenerator\Dump;

use CSanquer\FakeryGenerator\Model\Config;

/**
 * PerlDumper
 *
 * @author Charles Sanquer <charles.sanquer.gmail.com>
 */
class PerlDumper implements DumperInterface
{
    public function dumpRow(array $row = array())
    {

    }

    public function finalize()
    {

    }

    public function initialize(Config $config, $directory)
    {

    }

    public function getExtension()
    {
        return 'pl';
    }
}
