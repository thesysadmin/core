<?php
declare(strict_types=1);

namespace LotGD\Core\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\AnsiQuoteStrategy;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

use LotGD\Core\Configuration;
use LotGD\Core\ComposerManager;
use LotGD\Core\LibraryConfigurationManager;
use LotGD\Core\Exceptions\InvalidConfigurationException;

/**
 * Description of ModelTestCase
 */
abstract class ModelTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /** @var \PDO */
    static private $pdo = null;
    /** @var EntityManager */
    static private $em = null;
    /** @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection */
    private $connection = null;

    /**
     * Returns a connection to test models
     * @return \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    final public function getConnection(): \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
    {
        if ($this->connection === null) {
            $configFilePath = getenv('LOTGD_TESTS_CONFIG_PATH');
            if ($configFilePath === false || strlen($configFilePath) == 0 || is_file($configFilePath) === false) {
                throw new InvalidConfigurationException("Invalid or missing configuration file: '{$configFilePath}'.");
            }
            $config = new Configuration($configFilePath);

            if (self::$pdo === null) {
                self::$pdo = new \PDO($config->getDatabaseDSN(), $config->getDatabaseUser(), $config->getDatabasePassword());

                // Read db annotations from model files
                $composerManager = new ComposerManager(getcwd());
                $libraryConfigurationManager = new LibraryConfigurationManager($composerManager, getcwd());
                $directories = $libraryConfigurationManager->getEntityDirectories();
                $directories[] = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'src', 'Models']);
                $directories[] = implode(DIRECTORY_SEPARATOR, [__DIR__, 'Ressources', 'TestModels']);

                // Read db annotations from model files
                $configuration = Setup::createAnnotationMetadataConfiguration($directories, true);
                $configuration->setQuoteStrategy(new AnsiQuoteStrategy());

                self::$em = EntityManager::create(["pdo" => self::$pdo], $configuration);

                // Create Schema
                $metaData = self::$em->getMetadataFactory()->getAllMetadata();
                $schemaTool = new SchemaTool(self::$em);
                $schemaTool->updateSchema($metaData);
            }

            $this->connection = $this->createDefaultDBConnection(self::$pdo, $config->getDatabaseName());
        }

        return $this->connection;
    }

    /**
     * Returns the current entity manager
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return self::$em;
    }

    protected function tearDown() {
        parent::tearDown();

        // Clear out the cache so tests don't get confused.
        $this->getEntityManager()->clear();
    }
}
