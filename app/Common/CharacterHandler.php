<?php
namespace app\Common;

use core\query\DB;

/**
 *
 */
class CharacterHandler
{

    public static function create($param = null)
    {
        $data = [
            'account'     => $param['account'],
            'name'        => $param['name'],
            'race'        => $param['race'],
            'class'       => $param['class'],
            'gender'      => $param['gender'],
            'level'       => isset($param['level']) ? $param['level'] : 1,
            'money'       => env('MONEY', 0),
            'skin'        => $param['skin'],
            'face'        => $param['face'],
            'hairStyle'   => $param['hairStyle'],
            'hairColor'   => $param['hairColor'],
            'facialStyle' => $param['facialStyle'],
        ];

        //获取坐标
        $where = [
            'race'  => $param['race'],
            'class' => $param['class'],
        ];
        $playercreateinfo    = DB::table('playercreateinfo', 'world')->where($where)->find();
        $data['map']         = $playercreateinfo['map'];
        $data['zone']        = $playercreateinfo['zone'];
        $data['position_x']  = $playercreateinfo['position_x'];
        $data['position_y']  = $playercreateinfo['position_y'];
        $data['position_z']  = $playercreateinfo['position_z'];
        $data['orientation'] = $playercreateinfo['orientation'];

        DB::Transaction('characters'); //开启事务
        $DBTransaction = true;

        if ($guid = DB::table('characters', 'characters')->insert($data)) {
            //人物的出生地
            $character_homebind = [
                'guid'       => $guid,
                'map'        => $playercreateinfo['map'],
                'zone'       => $playercreateinfo['zone'],
                'position_x' => $playercreateinfo['position_x'],
                'position_y' => $playercreateinfo['position_y'],
                'position_z' => $playercreateinfo['position_z'],
            ];

            $result = DB::table('character_homebind', 'characters')->insert($character_homebind);

            if ($result != 0 && !$result) {
                $DBTransaction = false;
            }

            //出生时快捷键的技能图标
            if ($playercreateinfo_action = DB::table('playercreateinfo_action', 'world')->where($where)->select()) {
                $new_action = [];
                foreach ($playercreateinfo_action as $k => $v) {
                    $action = [
                        'guid'   => $guid,
                        'button' => $v['button'],
                        'action' => $v['action'],
                        'type'   => $v['type'],
                    ];

                    $new_action[] = $action;
                }

                if (DB::table('character_action', 'characters')->insert($new_action) == false) {
                    $DBTransaction = false;
                }
            }

            //魔法技能
            if ($playercreateinfo_spell = DB::table('playercreateinfo_spell', 'world')->where($where)->select()) {
                $new_spell = [];
                foreach ($playercreateinfo_spell as $k => $v) {
                    $spell = [
                        'guid'  => $guid,
                        'spell' => $v['Spell'],
                    ];

                    $new_spell[] = $spell;
                }

                if (DB::table('character_spell', 'characters')->insert($new_spell) == false) {
                    $DBTransaction = false;
                }
            }

            //初始物品
            $whereitem = [
                'playercreateinfo_item.race'  => $param['race'],
                'playercreateinfo_item.class' => $param['class'],
            ];

            $field = ['playercreateinfo_item.*', 'item_template.InventoryType'];
            $join  = [
                ['item_template', 'item_template.entry = playercreateinfo_item.itemid', 'left'],
            ];

            $playercreateinfo_item = DB::table('playercreateinfo_item', 'world')
                ->field($field)
                ->join($join)
                ->where($whereitem)
                ->select();

            if ($playercreateinfo_item) {
                $instance = [
                    'owner_guid' => $guid,
                    'data'       => '',
                ];

                $new_temp = [];
                foreach ($playercreateinfo_item as $k => $v) {
                    if (($item = DB::table('item_instance', 'characters')->insert($instance)) == false) {
                        $DBTransaction = false;
                    }

                    $temp = [
                        'guid'          => $guid,
                        'bag'           => 0,
                        'slot'          => $v['InventoryType'],
                        'item'          => $item,
                        'item_template' => $v['itemid'],
                    ];

                    $new_temp[] = $temp;
                }

                if (DB::table('character_inventory', 'characters')->insert($new_temp) == false) {
                    $DBTransaction = false;
                }
            }
        }

        if ($DBTransaction) {
            DB::Commit('characters');
        } else {
            DB::Rollback('characters');
        }

        return $DBTransaction;
    }

    public static function rolenum($param = null)
    {
        $where = [
            'account' => $param['account'],
            'isdel'   => 1,
        ];

        return DB::table('characters', 'characters')->where($where)->count();
    }

    public static function delete($guid = 0)
    {
        $where = [
            'guid' => $guid,
        ];

        $data = ['isdel' => 2];

        return DB::table('characters', 'characters')->where($where)->update($data);
    }

    public static function CharEnum($param = [])
    {
        $PET_SAVE_AS_CURRENT = 0;
        $sql                 = "SELECT characters.guid, characters.name, characters.race, characters.class, characters.gender,characters.skin,characters.face,characters.hairStyle,characters.hairColor,characters.facialStyle, characters.playerBytes, characters.playerBytes2, characters.level,characters.zone, characters.map, characters.position_x, characters.position_y, characters.position_z, guild_member.guildid, characters.playerFlags,characters.at_login, character_pet.entry, character_pet.modelid, character_pet.level as pet_level, characters.equipmentCache FROM characters LEFT JOIN character_pet ON characters.guid=character_pet.owner AND character_pet.slot= $PET_SAVE_AS_CURRENT  LEFT JOIN guild_member ON characters.guid = guild_member.guid WHERE characters.account = {$param['account']} and characters.isdel = 1 ORDER BY characters.guid";

        return DB::table('characters', 'characters')->query($sql);
    }

    public static function CharEnumItem($guids)
    {
        $where = 'guid in (' . implode(',', $guids) . ')';

        $character_inventory     = DB::table('character_inventory', 'characters')->where($where)->select();
        $new_character_inventory = [];
        if ($character_inventory) {
            foreach ($character_inventory as $k => $v) {
                $new_character_inventory[$v['guid']][$v['slot']] = $v;
            }

            //获取物品属性
            $item_entry = array_column($character_inventory, 'item_template');
            $item_entry = array_unique($item_entry);

            $where             = 'entry in (' . implode(',', $item_entry) . ')';
            $item_template     = DB::table('item_template', 'world')->field(['entry', 'displayid', 'InventoryType'])->where($where)->select();
            $new_item_template = [];
            if ($item_template) {
                foreach ($item_template as $k => $v) {
                    $new_item_template[$v['entry']] = $v;
                }
            }

            foreach ($new_character_inventory as $k => $v) {
                foreach ($v as $k1 => $v1) {
                    $new_character_inventory[$k][$k1]['displayid']     = $new_item_template[$v1['item_template']]['displayid'];
                    $new_character_inventory[$k][$k1]['InventoryType'] = $new_item_template[$v1['item_template']]['InventoryType'];
                }
            }
        }

        return $new_character_inventory;
    }
}
