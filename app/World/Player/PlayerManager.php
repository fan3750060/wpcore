<?php
namespace app\World\Player;

use app\World\Character\CharacterHandler;
use core\query\DB;

/**
 *  玩家管理
 */
class PlayerManager
{
    //获取玩家
    public static function FindPlayer($guid = null)
    {
        if (!$guid) {
            return false;
        }

        if (is_array($guid)) {
            $where = 'guid in (' . implode(',', $guid) . ')';
        } else {
            $where = [
                'guid' => $guid,
            ];
        }

        $characters = DB::table('characters', 'characters')->where($where)->select();

        if ($characters) {

            //人物等级经验值
            $result           = DB::table('player_xp_for_level', 'world')->select();
            $PlayerXpForLevel = [];
            foreach ($result as $k => $v) {
                $PlayerXpForLevel[$v['lvl']] = $v['xp_for_next_level'];
            }

            foreach ($characters as $k => $v) {
                //人物装备
                $character_inventory = CharacterHandler::CharEnumItem([$v['guid']]);

                if (!empty($character_inventory[$v['guid']])) {
                    $characters[$k]['character_inventory'] = $character_inventory[$v['guid']];
                }

                //人物面板属性
                $characters[$k]['character_stats'] = DB::table('character_stats', 'characters')->where(['guid' => $v['guid']])->find();

                //当前升级所需经验
                $characters[$k]['next_level_xp'] = $PlayerXpForLevel[$v['level']];

                //人物基础血值
                $where = [
                	'class' => $v['class'],
                	'level' => $v['level']
                ];
                $characters[$k]['player_classlevelstats'] = DB::table('player_classlevelstats', 'world')->where($where)->find();
            }
        }

        return $characters;
    }
}
