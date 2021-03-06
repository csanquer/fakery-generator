<?php

namespace CSanquer\FakeryGenerator\Test\Config;

use CSanquer\FakeryGenerator\Config\ConfigSerializer;
use CSanquer\FakeryGenerator\Model\Column;
use CSanquer\FakeryGenerator\Model\Config;
use CSanquer\FakeryGenerator\Model\Variable;
use Symfony\Component\Filesystem\Filesystem;

/**
 * FakerConfigTest
 * 
 * @author Charles Sanquer <charles.sanquer@gmail.com>
 */
class ConfigSerializerTest extends \PHPUnit_Framework_TestCase
{
    protected static $fixtures;
    protected static $cacheDir;
    
    public static function setUpBeforeClass()
    {
        self::$fixtures = __DIR__.'/fixtures/';
        self::$cacheDir = __DIR__.'/tmp';
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $fs = new Filesystem();
        if ($fs->exists(self::$cacheDir)) {
            $fs->remove(self::$cacheDir);
        }
    }
    
    /**
     * @covers CSanquer\FakeryGenerator\Config\ConfigSerializer::__construct
     * @covers CSanquer\FakeryGenerator\Config\ConfigSerializer::dump
     * @dataProvider providerDump
     */
    public function testDump(Config $config, $format, $expected)
    {
        $configSerializer = new ConfigSerializer(
            self::$cacheDir.'/serializer', 
            __DIR__.'/../../../../../src/CSanquer/FakeryGenerator/Resources/Config', 
            true
        );
        
        $filename = $configSerializer->dump($config, self::$cacheDir, $format);
        $this->assertRegExp('#'.preg_quote(self::$cacheDir.'/'.$config->getClassName(true)).'_fakery_generator_config_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.'.$format.'#', $filename);
        $this->assertFileExists($filename);
        $this->assertFileEquals(self::$fixtures.'/ConfigSerializer/valid/'.$expected, $filename);
    }

    public function providerDump()
    {
        $config1 = new Config();
        $config1->setMaxTimestamp('2014-01-01T12:30:45+0100');
        $config1->setClassName('Entity\\User');
        $config1->setFakeNumber(500);
        $config1->setFormats(['php', 'json', 'xml']);
        $config1->setSeed(51);
        $config1->setLocale('fr_FR');
        $config1->setVariables([
            new Variable('firstname', 'firstName', [], false, false),
            new Variable('lastname', 'lastName', [], false, false),
            new Variable('birthday', 'dateTimeThisCentury', ['d/m/Y'], false, 0.5),
            new Variable('email', 'safeEmail', [], true, false),
        ]);
        
        $config1->setColumns([
            new Column('person', null, null, [
                new Column('name', null, null, [
                    new Column('firstname', '%firstname%', 'capitalize'),
                    new Column('lastname', '%lastname%', 'capitalize'),
                ]),
                new Column('birthday', '%birthday%'),
            ]),
            new Column('email', '%email%'),
        ]);
        
        return [
            #data set #0 JSON
            [
                $config1, 
                'json', 
                'Entity_User_fakery_generator_config.json',
            ],
            #data set #1 XML
            [
                $config1, 
                'xml', 
                'Entity_User_fakery_generator_config.xml',
            ],
        ];
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The config file must be an XML or a JSON file.
     */
    public function testLoadYml()
    {
         $configSerializer = new ConfigSerializer(
            self::$cacheDir.'/serializer', 
            __DIR__.'/../../../../../src/CSanquer/FakeryGenerator/Resources/Config', 
            true
        );
        
        $config = $configSerializer->load(self::$fixtures.'/ConfigSerializer/not_valid/test.yml');
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The config file must exist.
     */
    public function testLoadNotExists()
    {
         $configSerializer = new ConfigSerializer(
            self::$cacheDir.'/serializer', 
            __DIR__.'/../../../../../src/CSanquer/FakeryGenerator/Resources/Config', 
            true
        );
        
        $config = $configSerializer->load('foo.json');
    }
    
    /**
     * @covers CSanquer\FakeryGenerator\Config\ConfigSerializer::__construct
     * @covers CSanquer\FakeryGenerator\Config\ConfigSerializer::load
     * @dataProvider providerLoad
     */
    public function testLoad($filename, $expected)
    {
         $configSerializer = new ConfigSerializer(
            self::$cacheDir.'/serializer', 
            __DIR__.'/../../../../../src/CSanquer/FakeryGenerator/Resources/Config', 
            true
        );
        
        $config = $configSerializer->load(self::$fixtures.'/ConfigSerializer/valid/'.$filename);
        $this->assertInstanceOf('\\CSanquer\\FakeryGenerator\\Model\\Config', $config);
        $this->assertEquals($expected, $config);
        $this->assertEquals($expected->getCsvDialect(), $config->getCsvDialect());
        $this->assertEquals($expected->getColumns(), $config->getColumns());
        $this->assertEquals($expected->getVariables(), $config->getVariables());
    }
    
    public function providerLoad()
    {
        $config1 = new Config();
        $config1->setMaxTimestamp('2014-01-01T12:30:45+0100');
        $config1->setClassName('Entity\\User');
        $config1->setFakeNumber(500);
        $config1->setFormats(['php', 'json', 'xml']);
        $config1->setSeed(51);
        $config1->setLocale('fr_FR');
        $config1->setVariables([
            new Variable('firstname', 'firstName', [], false, false),
            new Variable('lastname', 'lastName', [], false, false),
            new Variable('birthday', 'dateTimeThisCentury', ['d/m/Y'], false, 0.5),
            new Variable('email', 'safeEmail', [], true, false),
        ]);

        $config1->setColumns([
            new Column('person', null, null, [
                new Column('name', null, null, [
                    new Column('firstname', '%firstname%', 'capitalize'),
                    new Column('lastname', '%lastname%', 'capitalize'),
                ]),
                new Column('birthday', '%birthday%'),
            ]),
            new Column('email', '%email%'),
        ]);
        
        return [
            #data set #0 JSON
            [
                'Entity_User_fakery_generator_config.json',
                $config1, 
            ],
            #data set #1 XML
            [
                'Entity_User_fakery_generator_config.xml',
                $config1, 
            ],
        ];
    }
}
