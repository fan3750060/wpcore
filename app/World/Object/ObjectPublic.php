<?php
namespace app\World\Object;

/**
 * 公用状态
 */
class ObjectPublic
{
    const UpdateObjectFlags = [
        'UPDATEFLAG_NONE'                 => 0x0000, #没有更新标志
        'UPDATEFLAG_SELF'                 => 0x0001, #自我更新标志
        'UPDATEFLAG_TRANSPORT'            => 0x0002, #更新运动标志
        'UPDATEFLAG_HAS_ATTACKING_TARGET' => 0x0004, #更新具有攻击目标标记
        'UPDATEFLAG_LOWGUID'              => 0x0008, #低引导
        'UPDATEFLAG_HIGHGUID'             => 0x0010, #高度向导
        'UPDATEFLAG_LIVING'               => 0x0020, #活
        'UPDATEFLAG_HAS_POSITION'         => 0x0040, #位置
    ];

    const ObjectUpdateType = [
        'VALUES'               => 0,
        'MOVEMENT'             => 1, # 移动
        'CREATE_OBJECT'        => 2, # 用于没有位置的实体：物品，袋子等
        'CREATE_OBJECT2'       => 3, # 适用于在空间中具有位置的实体：游戏对象，尸体，生物，玩家等
        'OUT_OF_RANGE_OBJECTS' => 4, # 超出范围的对象
        'NEAR_OBJECTS'         => 5, # 附近的对象
    ];

    const ObjectUpdateFlags = [
        'UPDATEFLAG_NONE'                 => 0x0000, #没有更新标志
        'UPDATEFLAG_SELF'                 => 0x0001, #自我更新标志
        'UPDATEFLAG_TRANSPORT'            => 0x0002, #更新运动标志
        'UPDATEFLAG_HAS_ATTACKING_TARGET' => 0x0004, #更新具有攻击目标标记
        'UPDATEFLAG_LOWGUID'              => 0x0008, #低引导
        'UPDATEFLAG_HIGHGUID'             => 0x0010, #高度向导
        'UPDATEFLAG_LIVING'               => 0x0020, #活
        'UPDATEFLAG_HAS_POSITION'         => 0x0040, #位置
    ];

    const ObjectType = [
        'OBJECT'         => 0, # 0x01 (object)
        'ITEM'           => 1, # 0x03 (object, item)
        'CONTAINER'      => 2, # 0x07 (object, item, container)
        'UNIT'           => 3, # 0x09 (object, unit)
        'PLAYER'         => 4, # 0x19 (object, unit, player)
        'GAME_OBJECT'    => 5, # 0x21 (object, game_object)
        'DYNAMIC_OBJECT' => 6, # 0x41 (object, dynamic_object)
        'CORPSE'         => 7, # 0x81 (object, corpse)
    ];

    const MovementFlags = [
        'NONE'             => 0x00000000,
        'FORWARD'          => 0x00000001,
        'BACKWARD'         => 0x00000002,
        'STRAFE_LEFT'      => 0x00000004,
        'STRAFE_RIGHT'     => 0x00000008,
        'TURN_LEFT'        => 0x00000010,
        'TURN_RIGHT'       => 0x00000020,
        'PITCH_UP'         => 0x00000040,
        'PITCH_DOWN'       => 0x00000080,
        'WALK_MODE'        => 0x00000100, # Walking
        'ONTRANSPORT'      => 0x00000200, # Used for flying on some creatures
        'LEVITATING'       => 0x00000400,
        'ROOT'             => 0x00000800,
        'FALLING'          => 0x00001000,
        'FALLINGFAR'       => 0x00004000,
        'SWIMMING'         => 0x00200000, # 也与飞行标志一起出现
        'ASCENDING'        => 0x00400000, # 也可以游泳
        'CAN_FLY'          => 0x00800000,
        'FLYING'           => 0x01000000,
        'FLYING2'          => 0x02000000, # 实际飞行模式
        'SPLINE_ELEVATION' => 0x04000000, # 用于飞行路线
        'SPLINE_ENABLED'   => 0x08000000, # 用于飞行路线
        'WATERWALKING'     => 0x10000000, # 防止装置落水
        'SAFE_FALL'        => 0x20000000, # 主动盗贼安全坠落法术（被动）
        'HOVER'            => 0x40000000,
    ];

    const HighGuid = [
        'HIGHGUID_ITEM'          => 0x4000, # blizz 4000
        'HIGHGUID_CONTAINER'     => 0x4000, # blizz 4000
        'HIGHGUID_PLAYER'        => 0x0000, # blizz 0000
        'HIGHGUID_GAMEOBJECT'    => 0xF110, # blizz F110
        'HIGHGUID_TRANSPORT'     => 0xF120, # blizz F120 (for GAMEOBJECT_TYPE_TRANSPORT)
        'HIGHGUID_UNIT'          => 0xF130, # blizz F130
        'HIGHGUID_PET'           => 0xF140, # blizz F140
        'HIGHGUID_DYNAMICOBJECT' => 0xF100, # blizz F100
        'HIGHGUID_CORPSE'        => 0xF101, # blizz F100
        'HIGHGUID_MO_TRANSPORT'  => 0x1FC0, # blizz 1FC0 (for GAMEOBJECT_TYPE_MO_TRANSPORT)
        'HIGHGUID_GROUP'         => 0x1F50, # blizz 1F5x
    ];

    const UnitPower = [
        'MANA'      => 0,
        'RAGE'      => 1,
        'FOCUS'     => 2, # tonus ?
        'ENERGY'    => 3,
        'HAPPINESS' => 4,
    ];

    const CharacterEquipSlot = [
        'HEAD'      => 0, #656-657+
        'NECK'      => 1, #658-659
        'SHOULDERS' => 2, #660-661
        'BODY'      => 3, #662-663+
        'CHEST'     => 4, #664-665+
        'WAIST'     => 5, #666-667
        'LEGS'      => 6, #668-669
        'FEET'      => 7, #670-671
        'WRISTS'    => 8, #672-673
        'HANDS'     => 9, #674-675
        'FINGER1'   => 10, #676-677
        'FINGER2'   => 11, #678-679
        'TRINKET1'  => 12, #680-681+
        'TRINKET2'  => 13, #682-683+
        'BACK'      => 14, #684-685
        'MAINHAND'  => 15, #686-687
        'OFFHAND'   => 16, #688-689
        'RANGED'    => 17, #690-691
        'TABARD'    => 18, #692-693
        'BAG1'      => 19, #694-695
        'BAG2'      => 20, #696-697+
        'BAG3'      => 21, #698-699+
        'BAG4'      => 22, #700-701
    ];

}
