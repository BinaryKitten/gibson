<?php

namespace Arazu\Mapper;

use ZfcBase\Mapper\AbstractDbMapper as ZfcBaseDbMapper;
use Zend\Stdlib\Exception\InvalidArgumentException;
use Zend\Stdlib\Hydrator\HydratorInterface;

class AbstractDbMapper extends ZfcBaseDbMapper
{
    /**
     * Insert a record into the database
     *
     * Overrides the insert() method to set the id on the resulting saved entity
     * @param array|object $entity
     * @param null $tableName
     * @param HydratorInterface $hydrator
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        $result = parent::insert($entity, $tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    /**
     * Updated an existing a record in the database
     *
     * Overrides the update() method filter the updated based on the ID of our entity
     * @param array|object $entity
     * @param null $where
     * @param null $tableName
     * @param HydratorInterface $hydrator
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }

        return parent::update($entity, $where, $tableName, $hydrator);
    }

    /**
     * Persist the model to the database.
     *
     * Issues an update if an ID is present, else insert into the db
     * @param $entity
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function save($entity) {
        if ($entity->getId()) {
            return $this->update($entity);
        }
        return $this->insert($entity);
    }

    /**
     * Delete Item based on id or Entity
     *
     * @param int| $idOrEntity
     */
    public function deleteItemByIdOrEntity($idOrEntity)
    {
        $id = null;
        if (is_object($idOrEntity) && method_exists($idOrEntity, 'getId')) {
            $id = $idOrEntity->getId();
        } elseif (is_numeric($idOrEntity)) {
            $id = $idOrEntity;
        }

        if ($id === null) {
            throw new InvalidArgumentException('Argument passed to ' .__FUNCTION__.' should be a numeric id or instance of entity');
        }

        $this->delete(array('id' => $id));

    }

    /**
     * Find a single result by the specified identifier (primary key)
     *
     * @param $id
     * @return object
     */
    public function findById($id)
    {
        return $this->findOneById($id);
    }

    /**
     * Find one by Id
     *
     * @param int $id
     * @return object
     */
    public function findOneById($id)
    {
        return $this->findOneBy('id', $id);
    }


    /**
     * @param array $id
     * @return mixed
     */
    public function findAllById(array $id)
    {
        $select = $this->getSelect();
        $select->where->in('id', $id);

        $entity = $this->select($select);
        return $entity;
    }

    /**
     * @param $field
     * @param $value
     * @return mixed
     */
    public function findOneBy($field, $value)
    {
        $select = $this->getSelect()
            ->where(array($field => $value))
            ->limit(1);
        $results = $this->select($select);
        return $results->current();
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function findAll()
    {
        return $this->select($this->getSelect());
    }

}
