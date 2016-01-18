<?php

/**
 * Abstract Raw Query
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Api\Domain\Repository\Query;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract Raw Query
 *
 * @NOTE Where possible, you should try to write a DQL query. However, there are certain limitations, and occasionally
 * you will need to write a "raw" sql query instead
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractRawQuery implements QueryInterface, FactoryInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Namespace prefix
     *
     * @var string
     */
    protected $entityNamespacePrefix = '\Dvsa\Olcs\Api\Entity\\';

    /**
     * Map aliases to entities
     *
     * @var array
     */
    protected $templateMap = [];

    /**
     * To be extended
     *
     * @var string
     */
    protected $queryTemplate = '';

    /**
     * Params
     *
     * @var array
     */
    protected $params = [];

    /**
     * Inject the DB connection object
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();

        $this->em = $sm->get('doctrine.entitymanager.orm_default');
        $this->connection = $this->em->getConnection();

        return $this;
    }

    /**
     * Execute the query
     *
     * @param array $params
     * @return mixed
     */
    public function execute(array $params = [])
    {
        $params = array_merge($this->getParams(), $params);
        $query = $this->buildQueryFromTemplate($this->getQueryTemplate());

        try {
            $statement = $this->connection->prepare($query);

            foreach ($params as $param => $value) {
                $statement->bindValue($param, $value);
            }

            return $statement->execute();
        } catch (\Exception $ex) {
            throw new RuntimeException('An unexpected error occurred while running query: ' . get_class($this));
        }
    }

    /**
     * Get the query template.
     *
     * @return string
     */
    protected function getQueryTemplate()
    {
        return $this->queryTemplate;
    }

    /**
     * Get the default query params
     *
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    /**
     * Grab the table name of the entity
     *
     * @param string $entity
     * @return string
     */
    private function getTableName($entity)
    {
        return $this->em->getClassMetadata($entity)->getTableName();
    }

    /**
     * Grab the column name for the field
     *
     * @param string $entity
     * @param string $field
     * @return string
     */
    private function getColumnName($entity, $field)
    {
        $meta = $this->em->getClassMetadata($entity);

        if ($meta->isAssociationWithSingleJoinColumn($field)) {
            return $this->em->getClassMetadata($entity)->getSingleAssociationJoinColumnName($field);
        }

        return $this->em->getClassMetadata($entity)->getColumnName($field);
    }

    /**
     * Build a query from the template. Replace entity and field aliases with actual table and column names
     *
     * e.g.
     * Given template map:
     *      ['f' => \Entity\Foo::class, 'b' => \Entity\Bar::class]
     * and query template:
     *      UPDATE {f} INNER JOIN {b} ON {b.id} = {f.b} WHERE {b.fieldName} = 1
     * the output would be:
     *      UPDATE tbl_foo f INNER JOIN tbl_bar b ON b.id = f.b_id WHERE b.column_name = 1
     *
     * @param $template
     */
    private function buildQueryFromTemplate($template)
    {
       return preg_replace_callback(
           '/\{(?P<alias>[a-zA-Z]+)(?:\.(?P<field>[a-zA-Z]+))?\}/',
           [$this, 'replaceTableOrField'],
           $template
        );
    }

    /**
     * Replace a table or field name
     *
     * @param array $matches
     * @return string
     */
    private function replaceTableOrField(array $matches = [])
    {
        $entity = $this->templateMap[$matches['alias']];

        if (empty($matches['field'])) {
            return $this->getTableName($entity) . ' ' . $matches['alias'];
        }

        return $matches['alias'] . '.' . $this->getColumnName($entity, $matches['field']);
    }
}
