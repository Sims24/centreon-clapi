<?php
/**
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 */
require_once "Centreon/Db/Manager/Manager.php";

abstract class CentreonObject
{
    const MISSINGPARAMETER = "Missing parameters";
    const MISSINGNAMEPARAMETER = "Missing name parameter";
    const OBJECTALREADYEXISTS = "Object already exists";
    const OBJECT_NOT_FOUND = "Object not found";
    const NB_UPDATE_PARAMS = 3;
    protected $db;
    protected $version;
    protected $object;
    protected $params;
    protected $nbOfCompulsoryParams;
    protected $delim;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = Centreon_Db_Manager::factory('centreon');
        $res = $this->db->query("SELECT `value` FROM informations WHERE `key` = 'version'");
        $row = $res->fetch();
        $this->version = $row['value'];
        $this->params = array();
        $this->delim = ";";
    }

    /**
     * Checks if object exists
     *
     * @param string $name
     * @return bool
     */
    protected function objectExists($name)
    {
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($name));
        if (count($ids)) {
            return true;
        }
        return false;
    }

    /**
     * Get Object Id
     *
     * @param string $name
     * @return int
     */
    protected function getObjectId($name)
    {
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($name));
        if (count($ids)) {
            return $ids[0];
        }
        return 0;
    }

    /**
     * Checks if parameters are correct
     *
     * @throws Exception
     */
    protected function checkParameters()
    {
        if (!isset($this->params[$this->object->getUniqueLabelField()])) {
            throw new Exception(self::MISSINGNAMEPARAMETER);
        }
        if ($this->objectExists($this->params[$this->object->getUniqueLabelField()]) === true) {
            throw new Exception(self::OBJECTALREADYEXISTS);
        }
    }

    /**
     * Add Action
     *
     * @return int
     */
    public function add()
    {
        return $this->object->insert($this->params);
    }

    /**
     * Del Action
     *
     * @param string $objectName
     * @return void
     * @throws Exception
     */
    public function del($objectName)
    {
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($objectName));
        if (count($ids)) {
            $this->object->delete($ids[0]);
        } else {
            throw new Exception(self::OBJECT_NOT_FOUND);
        }
    }

    /**
     * Set Param
     *
     * @param int $objectId
     * @param array $params
     */
    public function setparam($objectId, $params = array())
    {
        $this->object->update($objectId, $params);
    }

    /**
     * Shows list
     *
     * @param array $params
     * @return void
     */
    public function show($params = array(), $filters = array())
    {
        echo str_replace("_", " ", implode($this->delim, $params)) . "\n";
        $elements = $this->object->getList($params, -1, 0, null, null, $filters);
        foreach ($elements as $tab) {
            echo implode($this->delim, $tab) . "\n";
        }
    }
}