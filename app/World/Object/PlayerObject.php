<?php
namespace app\World\Object;

use app\World\Object\ObjectPublic;

/**
 * 玩家对象数据
 */
class PlayerObject extends ObjectManager
{
    const CharacterClass = [
        'NONE'    => 0x0,
        'WARRIOR' => 0x1,
        'PALADIN' => 0x2,
        'HUNTER'  => 0x3,
        'ROGUE'   => 0x4,
        'PRIEST'  => 0x5,
        'SHAMAN'  => 0x7,
        'MAGE'    => 0x8,
        'WARLOCK' => 0x9,
        'DRUID'   => 0xb,
    ];

    public $SPAWN_FIELDS = [

        # 对象字段
        'ObjectField.GUID',
        'ObjectField.TYPE',
        'ObjectField.SCALE_X',

        # 生物字段
        'UnitField.HEALTH',
        'UnitField.MAXHEALTH',
        'UnitField.LEVEL',
        'UnitField.FACTIONTEMPLATE',
        'UnitField.BYTES_0',
        'UnitField.FLAGS',
        'UnitField.BOUNDINGRADIUS',
        'UnitField.COMBATREACH',
        'UnitField.DISPLAYID',
        'UnitField.NATIVEDISPLAYID',
        'UnitField.STAT0',
        'UnitField.STAT1',
        'UnitField.STAT2',
        'UnitField.STAT3',
        'UnitField.STAT4',
        'UnitField.RESISTANCE_NORMAL',
        'UnitField.BASE_HEALTH',

        # 玩家字段
        'PlayerField.FLAGS',
        'PlayerField.BYTES_1',
        'PlayerField.BYTES_2',
        // 'PlayerField.BYTES_3',

        'PlayerField.VISIBLE_ITEM_1_0',
        'PlayerField.VISIBLE_ITEM_2_0',
        'PlayerField.VISIBLE_ITEM_3_0',
        'PlayerField.VISIBLE_ITEM_4_0',
        'PlayerField.VISIBLE_ITEM_5_0',
        'PlayerField.VISIBLE_ITEM_6_0',
        'PlayerField.VISIBLE_ITEM_7_0',
        'PlayerField.VISIBLE_ITEM_8_0',
        'PlayerField.VISIBLE_ITEM_9_0',
        'PlayerField.VISIBLE_ITEM_10_0',
        'PlayerField.VISIBLE_ITEM_11_0',
        'PlayerField.VISIBLE_ITEM_12_0',
        'PlayerField.VISIBLE_ITEM_13_0',
        'PlayerField.VISIBLE_ITEM_14_0',
        'PlayerField.VISIBLE_ITEM_15_0',
        'PlayerField.VISIBLE_ITEM_16_0',
        'PlayerField.VISIBLE_ITEM_17_0',

        'PlayerField.INV_SLOT_HEAD',
        'PlayerField.INV_SLOT_NECK',
        'PlayerField.INV_SLOT_SHOULDERS',
        'PlayerField.INV_SLOT_BODY',
        'PlayerField.INV_SLOT_CHEST',
        'PlayerField.INV_SLOT_WAIST',
        'PlayerField.INV_SLOT_LEGS',
        'PlayerField.INV_SLOT_FEET',
        'PlayerField.INV_SLOT_WRISTS',
        'PlayerField.INV_SLOT_HANDS',
        'PlayerField.INV_SLOT_FINGER1',
        'PlayerField.INV_SLOT_FINGER2',
        'PlayerField.INV_SLOT_TRINKET1',
        'PlayerField.INV_SLOT_TRINKET2',
        'PlayerField.INV_SLOT_BACK',
        'PlayerField.INV_SLOT_MAINHAND',
        'PlayerField.INV_SLOT_OFFHAND',
        'PlayerField.INV_SLOT_RANGED',
        'PlayerField.INV_SLOT_TABARD',

        'PlayerField.XP',
        'PlayerField.NEXT_LEVEL_XP',
        'PlayerField.CHARACTER_POINTS1',
        'PlayerField.CHARACTER_POINTS2',
        'PlayerField.SHIELD_BLOCK',
        'PlayerField.EXPLORED_ZONES_1',
        'PlayerField.MOD_DAMAGE_NORMAL_DONE_PCT',
        'PlayerField.BYTES',
        'PlayerField.WATCHED_FACTION_INDEX',
        'PlayerField.MAX_LEVEL',
        'PlayerField.COINAGE',
    ];

    //加载玩家对象
    public function LoadPlayerObject($characters = null)
    {
        $guid        = $characters['guid']; #角色ID
        $x           = $characters['position_x'];
        $y           = $characters['position_y'];
        $z           = $characters['position_z'];
        $orientation = $characters['orientation'];
        $char_class  = $characters['class']; #种族
        $time        = time(); #时间戳

        $speed_walk        = 2.5;
        $speed_run         = 7.0;
        $speed_run_back    = 4.5;
        $speed_swim        = 4.722222;
        $speed_swim_back   = 2.5;
        $speed_flight      = 7.0;
        $speed_flight_back = 4.5;
        $speed_turn        = 3.141594;

        $update_flags = $this->GetUpdateFlags();
        $pack_guid    = $this->GetPackGuid($guid);

        $power_type = $this->SetPlayerPower($char_class);

        $param = [
            'guid'              => $guid,
            'pack_guid'         => $pack_guid,
            'update_type'       => ObjectPublic::ObjectUpdateType['CREATE_OBJECT2'],
            'object_type'       => ObjectPublic::ObjectType['PLAYER'],
            'update_flags'      => $update_flags,
            'time'              => $time,
            'x'                 => $x,
            'y'                 => $y,
            'z'                 => $z,
            'orientation'       => $orientation,
            'speed_walk'        => $speed_walk,
            'speed_run'         => $speed_run,
            'speed_run_back'    => $speed_run_back,
            'speed_swim'        => $speed_swim,
            'speed_swim_back'   => $speed_swim_back,
            'speed_flight'      => $speed_flight,
            'speed_flight_back' => $speed_flight_back,
            'speed_turn'        => $speed_turn,
            'skills'            => [
                ['entry' => 756, 'min' => 1, 'max' => 1],
                ['entry' => 137, 'min' => 300, 'max' => 300],
            ],
            'type_mask'         => 25,
            'entry'             => null,
            'scale_x'           => 1.0,

        ];

        $bytes_0 = ($characters['race'] | $characters['class'] << 8 | $characters['gender'] << 16 | $power_type << 24);

        $bytes_1 = ($characters['skin'] | $characters['face'] << 8 | $characters['hairStyle'] << 16 | $characters['hairColor'] << 24);

        $bytes_2 = ($characters['facialStyle'] | 0x00 << 8 | 0x00 << 16 | 0x02 << 24);

        $bytes_3 = $characters['gender'];

        $this->set_object_update_type(ObjectPublic::ObjectUpdateType['CREATE_OBJECT2']);
        $this->set($param)->prepare()->set_update_flags($update_flags);

        //加载生物属性
        // $this->set_object_field('UnitField.HEALTH', $characters['health']); //血值
        $this->set_object_field('UnitField.HEALTH', 10); //血值
        $this->set_object_field('UnitField.MAXHEALTH', 120); //最大血值
        $this->set_object_field('UnitField.LEVEL', $characters['level']);
        $this->set_object_field('UnitField.FACTIONTEMPLATE', ObjectPublic::CHARACTER_DISPLAY_ID[$characters['race']]['faction_template']);
        $this->set_object_field('UnitField.BYTES_0', $bytes_0);
        $this->set_object_field('UnitField.FLAGS', 0);
        $this->set_object_field('UnitField.BOUNDINGRADIUS', config('BOUNDINGRADIUS'));
        $this->set_object_field('UnitField.COMBATREACH', config('COMBATREACH'));
        $this->set_object_field('UnitField.DISPLAYID', ObjectPublic::CHARACTER_DISPLAY_ID[$characters['race']][$characters['gender']]);
        $this->set_object_field('UnitField.NATIVEDISPLAYID', ObjectPublic::CHARACTER_DISPLAY_ID[$characters['race']][$characters['gender']]);
        $this->set_object_field('UnitField.STAT0', 4);
        $this->set_object_field('UnitField.STAT1', 13);
        $this->set_object_field('UnitField.STAT2', 12);
        $this->set_object_field('UnitField.STAT3', 13);
        $this->set_object_field('UnitField.STAT4', 10);
        $this->set_object_field('UnitField.RESISTANCE_NORMAL', 0);
        $this->set_object_field('UnitField.BASE_HEALTH', 12);

        $this->set_object_field('UnitField.POWER1', $characters['power1']); //魔法
        $this->set_object_field('UnitField.POWER2', $characters['power2']); //怒气
        $this->set_object_field('UnitField.POWER3', $characters['power3']);
        $this->set_object_field('UnitField.POWER4', $characters['power4']); //能量
        $this->set_object_field('UnitField.POWER5', $characters['power5']);

        $this->set_object_field('UnitField.MAXPOWER1', 119); //最大魔法值
        $this->set_object_field('UnitField.MAXPOWER2', 0); //最大怒气
        $this->set_object_field('UnitField.MAXPOWER3', 0);
        $this->set_object_field('UnitField.MAXPOWER4', 0); //最大能量
        $this->set_object_field('UnitField.MAXPOWER5', 0);

        //加载玩家属性
        $this->set_object_field('PlayerField.FLAGS', $characters['playerFlags']);
        $this->set_object_field('PlayerField.BYTES_1', $bytes_1);
        $this->set_object_field('PlayerField.BYTES_2', $bytes_2);

        $this->set_object_field('PlayerField.XP', $characters['xp']);
        $this->set_object_field('PlayerField.NEXT_LEVEL_XP', 0);
        $this->set_object_field('PlayerField.CHARACTER_POINTS1', 0);
        $this->set_object_field('PlayerField.CHARACTER_POINTS2', 0);
        $this->set_object_field('PlayerField.SHIELD_BLOCK', 0);
        $this->set_object_field('PlayerField.EXPLORED_ZONES_1', 0);
        $this->set_object_field('PlayerField.BYTES', 0);
        $this->set_object_field('PlayerField.WATCHED_FACTION_INDEX', -1);
        $this->set_object_field('PlayerField.MAX_LEVEL', 70);
        $this->set_object_field('PlayerField.COINAGE', $characters['money']);

        //加载玩家装备
        foreach (ObjectPublic::CharacterEquipSlot as $k => $v) {
            $item = !empty($characters['character_inventory'][$v]) ? $characters['character_inventory'][$v] : 0;

            if ($item) {
                $visible_item_index = 'PlayerField.VISIBLE_ITEM_' . ($v + 1) . '_0';
                $this->set_object_field($visible_item_index, $item['item_template']);
                $this->set_object_field('PlayerField.INV_SLOT_' . $k, $item['displayid']);
                $this->set_object_field('PlayerField.MOD_DAMAGE_NORMAL_DONE_PCT', 1);
            }
        }

        //技能
        foreach ($param['skills'] as $k => $v) {
            $offset = $k * 3;
            $this->add_field('PlayerField.SKILL_INFO_1_ID', $v['entry'], $offset);
            $this->add_field('PlayerField.SKILL_INFO_1_LEVEL', $v['min'], $offset + 1);
            $this->add_field('PlayerField.SKILL_INFO_1_STAT_LEVEL', $v['max'], $offset + 2);
        }

        $batch = $this->create_batch($this->SPAWN_FIELDS);

        $response = $this->add_batch($batch)->build_update_packet()->get_update_packets();

        $response = implode('', $response);

        return $response;
    }

    //更新标志
    public function GetUpdateFlags()
    {
        $update_flags = (
            ObjectPublic::UpdateObjectFlags['UPDATEFLAG_LIVING'] |
            ObjectPublic::UpdateObjectFlags['UPDATEFLAG_HAS_POSITION'] |
            ObjectPublic::UpdateObjectFlags['UPDATEFLAG_HIGHGUID'] |
            ObjectPublic::UpdateObjectFlags['UPDATEFLAG_SELF']
        );

        return $update_flags;
    }

    //设置玩家能力属性
    public function SetPlayerPower($char_class = null)
    {
        $mana_classes = [
            PlayerObject::CharacterClass['HUNTER'],
            PlayerObject::CharacterClass['WARLOCK'],
            PlayerObject::CharacterClass['SHAMAN'],
            PlayerObject::CharacterClass['MAGE'],
            PlayerObject::CharacterClass['PRIEST'],
            PlayerObject::CharacterClass['DRUID'],
            PlayerObject::CharacterClass['PALADIN'],
        ];

        $rage_classes = [
            PlayerObject::CharacterClass['WARRIOR'],
        ];

        $energy_classes = [
            PlayerObject::CharacterClass['ROGUE'],
        ];

        if (in_array($char_class, $mana_classes)) {

            array_push($this->SPAWN_FIELDS, 'UnitField.POWER1', 'UnitField.MAXPOWER1');

            return ObjectPublic::UnitPower['MANA'];

        } elseif (in_array($char_class, $rage_classes)) {

            array_push($this->SPAWN_FIELDS, 'UnitField.POWER2', 'UnitField.MAXPOWER2');

            return ObjectPublic::UnitPower['RAGE'];
        } elseif (in_array($char_class, $energy_classes)) {

            array_push($this->SPAWN_FIELDS, 'UnitField.POWER4', 'UnitField.MAXPOWER4');
            return ObjectPublic::UnitPower['ENERGY'];
        } else {
            return ObjectPublic::UnitPower['MANA'];
        }
    }

    public function GetPackGuid($guid = null)
    {
        $pack_guid = array_merge(packInt(0, 64), [0]);
        $size      = 1;
        $index     = 0;

        while ($guid) {
            if (($guid & 0xff) > 0) {
                $pack_guid[0] |= (1 << $index);
                $pack_guid[$size] = $guid & 0xff;
                $size += 1;
            }

            $index += 1;
            $guid >>= 8;
        }

        $pack_guid = ToStr(array_slice($pack_guid, 0, $size));

        return $pack_guid;
    }
}
