<?php

namespace FHJ\Repositories;

use FHJ\Entities\User;
use FHJ\Entities\Project;

/**
 * ProjectDbRepository
 * @package FHJ\Repositories
 */
class ProjectDbRepository extends BaseRepository implements ProjectDbRepositoryInterface {
	
	private $table = 'projects';
	
	public function createProject(User $user, $title, $description, $facebookGroupId) {
	    $this->getLogger()->addInfo('creating new project from user id', array('user_id' => $user->getId()));
	    $this->checkInt($user->getId(), 'user#id');
	    $this->checkNotEmpty($title, 'title');
	    $this->checkNotEmpty($facebookGroupId, 'facebookGroupId');
        
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            $connection->insert($this->table, array(
                'user_id' => intval($user->getId()),
                'is_enabled' => false,
                'facebook_group_id' => $connection->quote($facebookGroupId, \PDO::PARAM_STR),
                'title' => $connection->quote($title, \PDO::PARAM_STR),
                'description' => $connection->quote($description, \PDO::PARAM_STR),
            ), array(
                \PDO::PARAM_INT,
                'boolean',
                \PDO::PARAM_STR,
                \PDO::PARAM_STR,
                \PDO::PARAM_STR
            ));

            $insertId = $connection->lastInsertId();
            $project = new Project(intval($insertId), $user->getId(), $facebookGroupId);
        
            $connection->commit();
        }  catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        
        return $project;
	}
    
    public function updateProject(Project $project) {
        $this->getLogger()->addInfo('updating project', array('id' => $project->getId()));

        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $connection->update($this->table, array(
                'user_id' => intval($project->getUserId()),
                'is_enabled' => $project->isEnabled(),
                'facebook_group_id' => $connection->quote($project->getFacebookGroupId(), \PDO::PARAM_STR),
                'secret_key' => $connection->quote($project->getSecretKey(), \PDO::PARAM_STR),
                'svnplot_db_path' => $connection->quote($project->getSvnplotDbPath(), \PDO::PARAM_STR),
                'title' => $connection->quote($project->getTitle(), \PDO::PARAM_STR),
                'description' => $connection->quote($project->getDescription(), \PDO::PARAM_STR),
            ), array(
                'id' => intval($project->getId())
            ), array(
                \PDO::PARAM_INT,
                'boolean',
                \PDO::PARAM_STR,
                \PDO::PARAM_STR,
                \PDO::PARAM_STR,
                \PDO::PARAM_STR,
                \PDO::PARAM_STR,
                \PDO::PARAM_INT
            ));
        
            $connection->commit();
        }  catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
    
    public function deleteProject(Project $project) {
        $this->getLogger()->addInfo('deleting project', array('id' => $project->getId()));

        $this->deleteEntity($this->table, $project->getId());
    }
    
    public function findAllProjects() {
        $this->getLogger()->addInfo('looking up all projects');

        $sql = sprintf('SELECT * FROM %s', $this->table);
        $statement = $this->getConnection()->executeQuery($sql);
        
        return $this->fetchManyEntitiesBySql($statement);
    }
    
    public function findProjectsByUser(User $user) {
        $this->getLogger()->addInfo('looking up projects by user id', array('user_id' => $user->getId()));

        $sql = sprintf('SELECT * FROM %s WHERE user_id = ?', $this->table);
        $statement = $this->getConnection()->executeQuery($sql, array(intval($user->getId())),
            array(\PDO::PARAM_INT));
        
        return $this->fetchManyEntitiesBySql($statement);
    }
    
    public function findProjectById($id) {
        $this->getLogger()->addInfo('looking up project by id', array('id' => $id));
        $this->checkInt($id, 'id');

        $result = $this->fetchEntityById($this->table, $id);
        if ($result === null) {
            return null;
        }
        
        return $this->fillProjectEntity($result);
    }
	
	/**
     * Fills a new Project entity by using a result set. 
     *
     * @param array $resultSet The result set array
     * 
     * @return Project
     */
    private function fillProjectEntity(array $resultSet) {

        return new Project(intval($resultSet['id']), intval($resultSet['user_id']),
            $resultSet['facebook_group_id'],
            // The values in the database are integers, the User class only accepts booleans
	        $resultSet['is_enabled'] ? true : false,
	        $resultSet['secret_key'], $resultSet['svnplot_db_path'], $resultSet['title'],
	        $resultSet['description']
        );
    }
	
} 