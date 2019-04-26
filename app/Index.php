<?php
namespace app;

use core\query\DB;

/**
 *
 */
class Index
{
    public $size = 100;
    public $page = 1;

    /**
     * [update_table_info 修复特定表数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-05-08
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function update_table_info()
    {
        //获取所有表
        $all_table = [
           ' quest_visual_effect'
        ];

        foreach ($all_table as $k => $v) {
            $this->page = 1;
            $this->godatatable($v);
        }
    }

    /**
     * [godatatable 获取当前数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-16
     * ------------------------------------------------------------------------------
     * @param   [type]          $table_name [description]
     * @return  [type]                      [description]
     */
    public function godatatable($table_name)
    {
        $page   = ($this->page - 1) * $this->size;
        $result = DB::table($table_name,'database_2')->limit([$page, $this->size])->select();

        if ($result) {
            foreach ($result as $k => $v) {

                $where = $v;
                if(isset($where['VerifiedBuild']))
                {
                    unset($where['VerifiedBuild']);
                }

                echolog($table_name);

                //验证当前数据是否存在
                // $info = DB::table($table_name)->where($where)->debug()->find();
                // echolog($info);
                $info = DB::table($table_name)->where($where)->find();
                if (!$info) {
                    $where = [];
                    if(isset($v['ID']))
                    {
                        $where['ID'] = $v['ID'];
                    }else{
                        $where['QuestID'] = $v['QuestID'];
                    }

                    if(DB::table($table_name)->where($where)->find())
                    {
                        $sql = DB::table($table_name)->where($where)->debug()->update($v);
                    }else{
                        $sql = DB::table($table_name)->debug()->insert($v);
                    }
                    
                    echolog('Find New Sql : '.$sql);
                    file_put_contents('./new_insert.sql', $sql . ';' . PHP_EOL, FILE_APPEND);
                } else {
                    // $sql = DB::table($table_name)->debug()->insert($v);
                    // echolog($sql);
                    // file_put_contents('./old_insert.sql', $sql . ';' . PHP_EOL, FILE_APPEND);
                }
            }

            $this->page++;

            $this->godatatable($table_name);
        }
    }

    //更新表注释
    public function update_table_comment()
    {
        $result = $this->table_comment();

        foreach ($result as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $sql  = 'show tables like "' . $k1 . '" ';
                $info = DB::table($k1, '', $k)->query($sql);
                if ($info) {
                    $sql  = 'alter table ' . $k1 . ' comment "' . $v1 . '"';
                    echolog($sql);
                    $info = DB::table($k1, '', $k)->query($sql);
                }
                // $info = DB::table($k1)->dbname($k)->query($sql);
            }
        }
    }

    /**
     * [table_comment 表注释]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-17
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function table_comment()
    {
        $characters = [
            'arena_team'                  => '竞技场队伍信息',
            'arena_team_member'           => '竞技场队伍成员',
            'arena_team_stats'            => '竞技点数统计',
            'auctionhouse'                => '拍卖行(参数--观看时所对应)',
            'bugreport'                   => '给GM发信所对应的数据',
            'character'                   => '角色资料对应playercreateinfo',
            'character_action'            => '角色学会的技能所对应的快捷键',
            'character_aura'              => '角色的BUFF效果',
            'character_gifts'             => '角色的物品描述信息',
            'character_homebind'          => '角色炉石回城所绑定的地点（或出生地点）',
            'character_instance'          => '角色玩家副本信息',
            'character_inventory'         => '角色身上和背包内的物品',
            'character_kill'              => '角色的荣誉信息(杀或被杀，有无荣誉等)(TYPE=1 获得 2=损失)',
            'character_pet'               => '角色的宠物信息',
            'character_queststatus'       => '角色的任务信息',
            'character_queststatus_daily' => '每日任务(记录一些与时间有关的任务等)',
            'character_reputation'        => '角色的所在阵营的声望、荣誉等',
            'character_social'            => '角色好友列表',
            'character_spell'             => '人物所学的魔法和熟练度信息(仅会魔法的人才会出现ID)',
            'character_spell_cooldown'    => '角色法术冷却时间',
            'character_ticket'            => '角色传送点信息',
            'character_tutorial'          => '角色补习，讲解信息(例如游戏里的帮助内容和上线的一些提示信息)',
            'corpse'                      => '角色死亡后尸体相关的信息(这里有对应记录时角色即为死亡状态，包括尸体对应的墓地信息等，官服此表每星期清一次)',
            'corpse_grid'                 => '同上。',
            'group'                       => '组队，团队信息',
            'group_member'                => '队伍成员(登陆132错误多为不同阵营间组队造成，清除玩家的队伍就可以了)',
            'guild'                       => '工会信息(包括竞技场队伍信息，现在的系统在加入工会的状态下不能申请竞技”砀瘢就是因使用了此一个表)',
            'guild_member'                => '工会成员',
            'guild_rank'                  => '工会的阶级划分记录信息',
            'instance'                    => '副本记录（临时表）记录玩家开启的副本并对重置时间进行计时',
            'item_instance'               => '储存玩家道具character_inventory的详细信息，DATA 48 space（新开区可以清空）',
            'item_page'                   => '游戏中一些信件等的具体内容',
            'item_text'                   => '道具说明(item_template相关字段)',
            'mail'                        => '邮件系统',
            'mail_item'                   => '邮物品的信息',
            'petition'                    => '已被召唤的BB，对应角色GUID',
            'petition_sign'               => 'BB归属哪个角色标志',
            'et_aura'                     => 'BB的光环效果',
            'pet_spell'                   => 'BB法术定义（所学得的法术）',
            'pet_spell_cooldown'          => 'BB法术冷却时间信息',
        ];

        $world = [
            'access_requirement'                => '副本进入的条件',
            'achievement_criteria_data'         => '获得某成就所需要达成的条件',
            'achievement_dbc'                   => '储存Achievement.dbc文件丢失的数据',
            'achievement_reward'                => '记录获得某成就时能够得到的奖励',
            'areatrigger_involvedrelation'      => '区域触发器和探索类任务的链接',
            'areatrigger_scripts'               => '区域触发器和脚本的链接',
            'areatrigger_tavern'                => '区域触发器引起进入休息状态',
            'autobroadcast'                     => '世界公告表',
            'conditions'                        => '多个系统（掉落、对话等）的条件定义',
            'creature_ai_scripts'               => '生物的EAI，已被SAI代替，不建议使用',
            'creature_ai_texts'                 => '生物的EAI中使用的文字，不建议使用',
            'creature_ai_summons'               => '生物的EAI召唤生物的信息，不建议用',
            'creature_classlevelstats'          => '生物的基础HP、MP、护甲等',
            'creature_formations'               => '生物小团体（一群在一起跟随移动、攻击）',
            'creature_text'                     => 'SAI使用的生物说话文字',
            'creature_transport'                => '交通工具上的NPC数据',
            'custom_texts'                      => '未使用',
            'db_script_string'                  => 'EAI和路径动作所需的对话/表情文字内容',
            'disables'                          => '关闭的东西',
            'event_scripts'                     => '物品或者技能触发的事件',
            'game_event_arena_seasons'          => '竞技场赛季',
            'Game_event_battleground_holiday'   => '节日战场事件',
            'game_event_condition'              => '世界事件条件',
            'game_event_gameobject_quest'       => '事件中物品的任务',
            'game_event_npcflag'                => '事件中生物的标志',
            'game_event_npc_vendor'             => '事件中生物的卖品',
            'game_event_pool'                   => '事件中的联合体',
            'game_event_prerequisite'           => '事件出现需完成的前提事件',
            'game_event_quest_condition'        => '世界事件中的任务',
            'game_event_seasonal_questrelation' => '周期性事件',
            'game_graveyard_zone'               => '墓地信息',
            'gameobject_involvedrelation'       => '与触发任务相关的游戏物件. 内核需要这些标记来对任务相关的游戏物件进行预处理以正确显示相关内容',
            'gameobject_loot_template'          => '打开物体时的掉落',
            'gameobject_questrelation'          => '与任务相关的游戏物件. 当接受相关任务时此类游戏物件将变为可用状态',
            'gossip_menu'                       => '生物与点击对话的链接',
            'gossip_menu_option'                => '生物对话菜单选项',
            'instance_encounters'               => '副本进度记录, 一般仅记录BOSS级生物, 当其死亡时会修改副本进度',
            'item_loot_template'                => '物品打开的掉落',
            'item_set_names'                    => '套装部件的名称',
            'lfg_dungeon_encounters'            => '随机地下城功能相关的副本信息',
            'lfg_entrances'                     => '随机地下城队伍组成后队员传送到的位置',
            'linked_respawn'                    => '副本BOSS决定的刷新小怪',
            'locales_achievement_reward'        => '成就奖励',
            'locales_creature_text'             => '生物喊话本地化',
            'locales_gossip_menu_option'        => '生物对话菜单本地化',
            'locales_item_set_names'            => '套装部件名称本地化',
            'locales_points_of_interest'        => '小地图位置点文字本地化',
            'mail_level_reward'                 => '达到某等级收到的邮件',
            'mail_loot_template'                => '邮件掉落',
            'milling_loot_template'             => '药剂合成掉落',
            'npc_spellclick_spells'             => '生物施法',
            'outdoorpvp_template'               => '野外战场',
            'player_classlevelstats'            => '玩家职业和等级对应生命和魔力',
            'player_factionchange_achievement'  => '部落和联盟转换时成就转换',
            'player_factionchange_items'        => '转换阵营时物品转换',
            'player_factionchange_reputations'  => '转换阵营时声望转换',
            'player_factionchange_spells`'      => '转换阵营时技能转换',
            'player_xp_for_level'               => '玩家升级所需经验',
            'playercreateinfo_spell_custom'     => '出生法术（配置文件中设置出生即会全部技能时起作用）',
            'points_of_interest'                => '小地图上显示的位置点（如卫兵指路）',
            'pool_creature'                     => '生物所在的联合体（在联合体中随机选择一个刷新出来）',
            'pool_gameobject'                   => '物件所在联合体（只能是箱子，采集物，钓鱼，随机刷）',
            'pool_pool'                         => '锚点池信息. 比如在一个生物群有X%几率刷新在X位置, Y%几率刷新在Y位置, 此类效果都通过这一系统实现',
            'pool_quest'                        => '任务所在联合体？（随机出现某个任务？）',
            'pool_template'                     => '联合体模板（前面几个中使用）',
            'quest_poi'                         => '任务目标点链接',
            'quest_poi_points'                  => '任务目标点在地图上的位置',
            'reference_loot_template'           => '所有其他掉落的参考',
            'reputation_reward_rate'            => '声望获取的倍率（乘数）',
            'reputation_spillover_rate'         => '声望损失的倍率？？？',
            'script_waypoint'                   => '脚本用的路径点',
            'script_texts'                      => '脚本用的对话文本',
            'skill_extra_item_template'         => '专业制造时获得额外物品的几率',
            'skill_fishing_base_level'          => '区域钓鱼所需的钓鱼专业技能熟练度',
            'smart_scripts'                     => 'SAI系统',
            'spell_area'                        => '区域地图附加到玩家身上的技能',
            'spell_bonus_data'                  => '技能伤害倍率调整？',
            'spell_dbc'                         => '客户端DBC中没有的技能',
            'spell_enchant_proc'                => '附魔效果的触发信息(PPM/PPH等).',
            'spell_group'                       => '法术分组. 同一组的法术不会同时出现, 而是会互相覆盖',
            'spell_group_stack_rules'           => '同组法术的叠加规则',
            'spell_linked_spell'                => '施法魔法触发目标释放魔法',
            'spell_loot_template'               => '技能掉落物品',
            'spell_pet_auras'                   => '宠物技能光环',
            'spell_proc'                        => '开发中，未使用',
            'spell_proc_event'                  => '法术触发规则. 这一数据复写了spell.dbc中的触发规则',
            'spell_ranks'                       => '法术对应后续等级法术',
            'spell_required'                    => '法术和技能学习的先决条件',
            'spell_script_names'                => '技能对应脚本名称',
            'spell_target_position'             => '技能传送目标到的位置',
            'spelldifficulty_dbc'               => '不同地下城难度生物的法术',
            'transports'                        => '交通工具（船、飞艇）',
            'trinity_string'                    => 'TC端使用的提示文字',
            'vehicle_accessory'                 => '交通工具类生物上的生物（比如角鹰骑士、侏儒飞机）',
            'vehicle_template_accessory'        => '交通工具类生物上的生物',
            'warden_checks'                     => '基于数据包检查的反作弊系统',
            'waypoint_data'                     => '生物移动用路径点',
            'waypoint_scripts'                  => '路径点上使用的脚本',
            'areatrigger_teleport'              => '可见区域触发_传送(传送门)具体坐标等(副本都在里边与tavern所对应)',
            'battleground_template'             => '战场竞技场基本配置',
            'battlemaster_entry'                => '战场管理NPC分管的战场（NPC的entry对应战场的ID）',
            'button_scripts'                    => '鼠标右键按钮触发的脚本配置（如开箱子或开门等而触发剧情控制）',
            'command'                           => 'GM命令',
            'creature'                          => '地图刷怪配置',
            'creature_addon'                    => '生物刷怪补充(即根据GUID的不同一个生物通过此表可定义为不同属性的)',
            'creature_equip_template'           => '生物身上的装备模板库（creature_template调用这里的内容，如用什么武器和盾等）',
            'creature_involvedrelation'         => 'NPC或怪物(特殊类)参与的任务关系，所涉及的关系',
            'creature_loot_template'            => '生物的掉率',
            'creature_model_info'               => '生物模型信息库（creature_template调用这里的内容）',
            'creature_movement'                 => '怪物或NPC移动的关系，活动范围',
            'creature_onkill_reputation'        => '生物被杀声誉配置',
            'creature_questrelation'            => '怪物触发的任务关系',
            'creature_respawn'                  => '生物再生(临时表)针对creature里的生物时间被杀死后进行记录。',
            'creature_template'                 => '怪物或NPC的具体信息如HP，SP等',
            'creature_template_addon'           => '怪物或NPC的具体信息补充(即相同的生物可以设置不同的属性)',
            'db_version'                        => '数据库版本说明',
            'disenchant_loot_template'          => '附魔合成出产物品表',
            'exploration_basexp'                => '等级和基本经验配置',
            'fishing_loot_template'             => '钓鱼出的爆率配置',
            'gameobject'                        => '世界刷对象配置物品信息(地上的箱子，草，矿)仅刷新点，时间，位置',
            'gameobject_grid'                   => '可以拿取的有效距离(不明)',
            'gameobject_respawn'                => '对象再生(临时表)(如gameobject刷出的对象箱子等在被打开后这里就开始计时)',
            'gameobject_template'               => '对象具体信息配置，如草，矿等',
            'game_event'                        => '游戏事件，定时触发',
            'game_event_creature'               => '游戏事件由生物触发',
            'game_event_creature_quest'         => '由生物来处理的游戏事件内的任务和问题',
            'game_event_gameobject'             => '游戏事件由对象触发',
            'game_event_model_equip'            => '游戏事件中发重变动的模型装备配置',
            'game_graveyard'                    => '人物死亡后所回到的墓地(复活需要对应game_corpse表)',
            'game_graveyard_zone'               => '地图区域连接墓地配置',
            'game_tele'                         => '游戏不同区域的广播视频信息',
            'game_spell'                        => '魔法(参数有可以创造什么，比如FS做水)',
            'game_talent'                       => '游戏的天赋系统',
            'game_weather'                      => '天气系统（可以为不同的区域配置不同的天气变化）',
            'instance_template'                 => '副本配置（等级、团队限止和副本AI脚本控制等）',
            'item_enchantment_template'         => '附魔产品配置',
            'item_template'                     => '道具及任务物品的详细信息',
            'item_trainer'                      => '传送宝石技能师',
            'item_vendor'                       => '传送宝石商店',
            'locales_creature'                  => '生物名七国语言支持',
            'locales_gameobject'                => '世界对象七国语言支持',
            'locales_item'                      => '物品名称七国语言支持',
            'locales_npc_text'                  => 'NPC对话七国语言支持',
            'locales_page_text'                 => '其它对话七国语言支持',
            'locales_quest'                     => '任务对话七国语言支持',
            'npc_gossip'                        => 'NPC对话索引，没事说的话，如有些副本人物一进入就可以看到BOSS遇到你说的话',
            'npc_gossip_textid'                 => '话的内容对应NPC_gossip',
            'npc_option'                        => '共48条记录，NPC的类型',
            'npc_text'                          => '跟NPC说话的内容，不同于任务内容，此内容大多是通过AI实现调用',
            'npc_trainer'                       => '训练师所对应的内容(学习技能要求的等级，金钱前置技能等)',
            'npc_vendor'                        => '卖东西的NPC所对应的商品配置',
            'npc_SpiritHealer'                  => '灵魂医者表',
            'page_text'                         => '对话提示内容不同于任务',
            'petcreateinfo_spell'               => 'BB法术初始定义',
            'pet_levelstats'                    => 'BB等级初始定义',
            'pet_name_generation'               => 'BB名称代换定义',
            'pickpocketing_loot_template'       => '盗贼偷窃爆率（可以得到的物品配置）',
            'playercreateinfo'                  => '人物出生的信息，根据种族配置出生时所在的地图坐标等',
            'playercreateinfo_action'           => '出生时快捷键的技能图标',
            'playercreateinfo_item'             => '出生时身上道具',
            'playercreateinfo_reputation'       => '出生时各个派别的声望',
            'playercreateinfo_skill'            => '出生时所会的技能',
            'playercreateinfo_spell'            => '出生时所会的法术(同上，数据表内容很像，但有出入，法术和技能是两个不同的概念)',
            'player_levelstats'                 => '初始人物等级配置定义',
            'prospecting_loot_template'         => '采矿爆率',
            'quest_end_scripts'                 => '任务结束脚本(例如完成风剑任务召唤桑德兰王子就是调用这里)',
            'quest_start_scripts'               => '任务开始脚本(按任务时触发的剧情配置)',
            'quest_template'                    => '任务的详细信息(可接范围等)对应QUESTID可能跟object_involvedrelation有关系',
            'reserved_name'                     => '保留名称为不可被其它用户所注册',
            'skill_discovery_template'          => '技能规定必要条件要求模板(处理某些技能，规定发现的机会大小)',
            'skinning_loot_template'            => '剥皮爆率',
            'spell_affect'                      => '法术触发技能或效果处理',
            'spell_chain'                       => '法术的链接处理(设置某一法术的前置技能和顺序等)',
            'spell_learn_skill'                 => '可以学习的技能',
            'spell_learn_spell'                 => '可以学习的法术',
            'spell_scripts'                     => '法术脚本',
            'spell_script_target'               => '法术脚本目标(技能图腾，type=0对应世界对象ID或type=1对应生物ID，或type=2为必须死亡的生物（被人或NPC杀死的）)',
            'spell_teleport'                    => '法术传送（和areatrigger_teleport传送门基本一样）',
            'spell_threat'                      => '法术的威胁(仇恨)',
            'uptime'                            => '系统运行时间记录',
            'taxi_node'                         => 'WOW的飞机系统(具体不明)',
            'taxi_path'                         => '飞到哪的价格什么的吧(不明)',
            'taxi_pathnode'                     => '飞机的完整表(对应taxi_NODE~taxi_path)',
        ];

        $auth = [
            'account'         => '账号具体ID等信息',
            'zone_coordinate' => '地区id，地区名字缩写，例:Orgrimmar=>Orgi',
            'account_banned'  => '被禁止的账号',
            'db_version'      => '登录控制数据库的版本和所支持客户端的标识',
            'ip_banned'       => '被禁止的IP',
            'localization'    => '语言环境设置（七国语言目前只能用前3个，如果全用需要修改原程序相关内容）',
            'realmcharacters' => '登录器角色所登录的服务器主数据库信息（即为一个登录数据库对应多个主控制器时分配标识）',
            'realmlist'       => '登录控制器列表（设置对外开放的登录IP和端口号等）',
        ];

        return ['auth' => $auth, 'world' => $world, 'characters' => $characters];
    }
}
