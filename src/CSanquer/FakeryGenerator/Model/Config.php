<?php

namespace CSanquer\FakeryGenerator\Model;

use \CSanquer\ColibriCsv\Dialect;
use \CSanquer\FakeryGenerator\Config\FakerConfig;
use \CSanquer\FakeryGenerator\Dump\DumpManager;
use \Doctrine\Common\Inflector\Inflector;
use \Faker\Factory;
use \Faker\Generator;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Config
 *
 * @author Charles Sanquer <charles.sanquer@spyrit.net>
 */
class Config extends ColumnContainer
{
    /**
     *
     * @var string
     */
    protected $locale;

    /**
     *
     * @var int
     */
    protected $seed;

    /**
     *
     * @var \DateTime
     */
    protected $maxTimestamp;

    /**
     *
     * @var string
     */
    protected $className;

    /**
     *
     * @var array
     */
    protected $formats = [];

    /**
     *
     * @var int
     */
    protected $fakeNumber = 0;

    /**
     *
     * @var array of Variable
     */
    protected $variables = [];

    /**
     *
     * @var Dialect
     */
    protected $csvDialect;

    /**
     *
     * @var FakerConfig
     */
    protected $fakerConfig;
    
    /**
     * 
     * @param FakerConfig $fakerConfig
     */
    public function __construct(FakerConfig $fakerConfig = null)
    {
        parent::__construct([]);
        $this->setLocale(FakerConfig::DEFAULT_LOCALE);
        $this->generateSeed();
        $this->setMaxTimestamp('now');
        $this->csvDialect = Dialect::createExcelDialect();
        
        if ($fakerConfig) {
            $this->setFakerConfig($fakerConfig);
        }
    }
    
    /**
     * 
     * @return FakerConfig
     */
    public function getFakerConfig()
    {
        return $this->fakerConfig;
    }

    /**
     * 
     * @param FakerConfig $fakerConfig
     * @return Config
     */
    public function setFakerConfig(FakerConfig $fakerConfig)
    {
        $this->fakerConfig = $fakerConfig;
        $this->updateVariableFakerConfig();
        
        return $this;
    }

    /**
     * update all variable Max timestamp with the current config max timestamp
     */
    public function updateVariableFakerConfig()
    {
        foreach ($this->variables as $variable) {
            $variable->setFakerConfig($this->fakerConfig);
        }
    }
    
    /**
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     *
     * @param  string $locale
     * @return Config
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     *
     * @return Config
     */
    public function generateSeed()
    {
        return $this->setSeed(mt_rand(0, 50000));
    }

    /**
     *
     * @return int
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     *
     * @param  int    $seed
     * @return Config
     */
    public function setSeed($seed)
    {
        $this->seed = (int) $seed;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getMaxTimestamp()
    {
        return $this->maxTimestamp;
    }

    /**
     *
     * @param  \DateTime|int|string $maxTimestamp $maxTimestamp default = 'now'
     * @return Config
     */
    public function setMaxTimestamp($maxTimestamp = 'now')
    {
        $this->maxTimestamp = $maxTimestamp instanceof \DateTime ? $maxTimestamp : new \DateTime($maxTimestamp);
        $this->updateVariableMaxTimestamp();

        return $this;
    }

    /**
     * update all variable Max timestamp with the current config max timestamp
     */
    public function updateVariableMaxTimestamp()
    {
        foreach ($this->variables as $variable) {
            $variable->setMaxTimestamp($this->maxTimestamp);
        }
    }

    /**
     *
     * @param  bool   $withoutSlashes
     * @return string
     */
    public function getClassName($withoutSlashes = false)
    {
        return $withoutSlashes ? str_ireplace('\\', '_', $this->className) : $this->className;
    }

    /**
     *
     * @return string
     */
    public function getClassNameLastPart($pluralized = false)
    {
        $res = preg_match('/([a-zA-Z0-9]+)[^a-zA-Z0-9]*$/', $this->className, $matches);
        $lastPart = $res ? $matches[1] : $this->getClassName(true);
        
        return $pluralized ? Inflector::pluralize($lastPart) : $lastPart;
    }

    /**
     *
     * @param  string $className
     * @return Config
     */
    public function setClassName($className)
    {
        $this->className = preg_replace('/[^a-zA-Z0-9_\\\\]/', '', $className);

        return $this;
    }

    /**
     *
     * @param  string $format
     * @return Config
     */
    public function addFormat($format)
    {
        if (in_array($format, array_keys(DumpManager::getAvailableFormats())) && !in_array($format, $this->formats)) {
            $this->formats[] = $format;
        }

        return $this;
    }

    /**
     *
     * @param string
     * @return boolean
     */
    public function removeFormat($format)
    {
        $key = array_search($format, $this->formats, true);

        if ($key !== false) {
            unset($this->formats[$key]);

            return true;
        }

        return false;
    }

    /**
     *
     * @param  string $format
     * @return bool
     */
    public function hasFormat($format)
    {
        return in_array($format, $this->formats);
    }

    /**
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     *
     * @param  array  $formats
     * @return Config
     */
    public function setFormats(array $formats)
    {
        $this->formats = $formats;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getFakeNumber()
    {
        return $this->fakeNumber;
    }

    /**
     *
     * @param  int    $fakeNumber
     * @return Config
     */
    public function setFakeNumber($fakeNumber)
    {
        $this->fakeNumber = (int) $fakeNumber;

        return $this;
    }

    /**
     *
     * @return Dialect
     */
    public function getCsvDialect()
    {
        return $this->csvDialect;
    }

    /**
     *
     * @param  Dialect $csvDialect
     * @return Config
     */
    public function setCsvDialect(Dialect $csvDialect)
    {
        $this->csvDialect = $csvDialect;

        return $this;
    }

    /**
     *
     * @return array of Variable
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     *
     * @return int
     */
    public function countVariables()
    {
        return count($this->variables);
    }

    /**
     *
     * @param  string   $name
     * @return Variable
     */
    public function getVariable($name)
    {
        return isset($this->variables[$name]) ? $this->variables[$name] : null ;
    }

    /**
     *
     * @param  array  $variables array of Variable
     * @return Config
     */
    public function setVariables($variables)
    {
        foreach ($variables as $variable) {
            $this->addVariable($variable);
        }

        return $this;
    }

    /**
     * @param  Variable $variable
     * @return Config
     */
    public function addVariable(Variable $variable)
    {
        $name = $variable->getName();
        if (empty($name)) {
            throw new \InvalidArgumentException('The variable must have a name.');
        }

        $this->variables[$name] = $variable;
        $this->variables[$name]->setMaxTimestamp($this->maxTimestamp);
        if ($this->fakerConfig) {
            $this->variables[$name]->setFakerConfig($this->fakerConfig);
        }
                
        return $this;
    }

    /**
     *
     * @param  Column  $variable
     * @return boolean
     */
    public function removeVariable(Variable $variable)
    {
        $key = array_search($variable, $this->variables, true);

        if ($key !== false) {
            unset($this->variables[$key]);

            return true;
        }

        return false;
    }

    /**
     * if no column configs, generate a column config for each variable config
     */
    public function createDefaultColumns()
    {
        if (empty($this->columns) && is_array($this->variables) && !empty($this->variables)) {
            foreach ($this->variables as $variable) {
                $this->addColumn(new Column($variable->getName(), $variable->getVarName()));
            }
        }
    }

    /**
     * create Faker Generator instance from Config
     * 
     * @return Generator
     */
    public function createFaker()
    {
        $faker = Factory::create($this->getLocale());
        if ($this->getSeed() !== null) {
            $faker->seed($this->getSeed());
        }
        
        return $faker;
    }
    
    /**
     *
     * @param Generator $faker
     * @param array     $values (by reference)
     */
    public function generateVariableValues(Generator $faker, array &$values)
    {
        foreach ($this->variables as $variable) {
            $variable->generateValue($faker, $values, $this->variables, false, false, true);
        }
    }

    /**
     *
     * @param array $values
     */
    public function generateColumnValues(array $values)
    {
        $data = [];
        foreach ($this->columns as $column) {
            $data[$column->getName()] = $column->replaceVariable($values);
        }

        return $data;
    }
    
    public function validateLocale(ExecutionContextInterface $context)
    {
        if ($this->fakerConfig && !in_array($this->getLocale(), $this->fakerConfig->getLocales())) {
            $context->addViolationAt(
                'locale',
                'This locale \'{{ locale }}\' is not available in Faker.',
                ['{{ locale }}' => $this->getLocale()],
                null
            );
        }
    }
}
