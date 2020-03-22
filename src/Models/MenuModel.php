<?php

namespace agungsugiarto\boilerplate\Models;

use agungsugiarto\boilerplate\Entities\MenuEntity;
use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id';

    protected $returnType = MenuEntity::class;

    protected $allowedFields = ['parent_id', 'active', 'title', 'icon', 'route', 'sequence'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title'       => 'required|min_length[10]|max_length[60]',
        'parent_id'   => 'required',
        'active'      => 'required',
        'icon'        => 'required',
        'route'       => 'required',
        'groups_menu' => 'required',
    ];

    protected $validationMessages = [];
    protected $skipValidation = true;

    /**
     * Find menu. By default we need to detect driver,
     * because different function group_concat
     * between MySQLi and Postgre.
     *
     * @param int id
     *
     * @return array
     */
    public function getMenuById($id)
    {
        switch ($this->db->DBDriver) {
            case 'MySQLi':
                // do mysqli
                return $this->getMenuDriverMySQLi($id);
                break;
            case 'Postgre':
                // do postgre
                return $this->getMenuDRiverPostgre($id);
                break;
        }
    }

    /**
     * function getMenu for select2.
     *
     * @return array
     */
    public function getMenu()
    {
        return $this->db->table('menu')
            ->select('id, title as text')
            ->orderBy('sequence', 'asc')
            ->get()
            ->getResultArray();
    }

    /**
     * Function getRole for select2.
     *
     * @return array
     */
    public function getRole()
    {
        return $this->db->table('auth_groups')
            ->select('id, name as text')
            ->get()
            ->getResultArray();
    }

    /**
     * Function getMenuDriverMySQLi.
     *
     * @param int id
     *
     * @return array
     */
    private function getMenuDriverMySQLi($id)
    {
        return $this->db->table('menu')
            ->select('menu.id, menu.parent_id, menu.active, menu.title, menu.icon, menu.icon, menu.route, groups_menu.menu_id, group_concat(groups_menu.group_id) as group_id')
            ->join('groups_menu', 'menu.id = groups_menu.menu_id', 'left')
            ->join('auth_groups', 'groups_menu.group_id = auth_groups.id', 'left')
            ->where('menu.id', $id)
            ->get()
            ->getResultArray();
    }

    /**
     * Function getMenuDRiverPostgre.
     *
     * @param int id
     *
     * @return array
     */
    private function getMenuDRiverPostgre($id)
    {
        return $this->db->table('menu')
            ->select('menu.id, menu.parent_id, menu.active, menu.title, menu.icon, menu.icon, menu.route, groups_menu.menu_id, array_agg(groups_menu.group_id) as group_id')
            ->join('groups_menu', 'menu.id = groups_menu.menu_id', 'left')
            ->join('auth_groups', 'groups_menu.group_id = auth_groups.id', 'left')
            ->where('menu.id', $id)
            ->get()
            ->getResultArray();
    }
}
