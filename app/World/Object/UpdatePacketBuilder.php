<?php
namespace app\World\Object;

use app\World\Object\ObjectPublic;
use app\World\Object\UpdateBlocksBuilder;

/**
 * 构造数据包
 */
class UpdatePacketBuilder
{
    public $update_object;
    public $update_type;
    public $object_type;
    public $update_flags;
    public $batches;
    public $packets;

    public $movement_flags;
    public $movement_flags2;

    public $update_blocks_builder;

    const TYPES_WITH_FIELDS = [
        ObjectPublic::ObjectUpdateType['VALUES'],
        ObjectPublic::ObjectUpdateType['MOVEMENT'],
        ObjectPublic::ObjectUpdateType['CREATE_OBJECT'],
        ObjectPublic::ObjectUpdateType['CREATE_OBJECT2'],
    ];

    const TYPES_WITH_OBJECT_TYPE = [
        ObjectPublic::ObjectUpdateType['CREATE_OBJECT'],
        ObjectPublic::ObjectUpdateType['CREATE_OBJECT2'],
    ];

    const TYPES_WITH_MOVEMENT = [
        ObjectPublic::ObjectUpdateType['MOVEMENT'],
        ObjectPublic::ObjectUpdateType['CREATE_OBJECT'],
        ObjectPublic::ObjectUpdateType['CREATE_OBJECT2'],
    ];

    public $MAX_UPDATE_PACKETS_AS_ONE = 15;

    public function __construct()
    {
        $this->update_flags          = null;
        $this->update_blocks_builder = new UpdateBlocksBuilder;

        $this->movement_flags  = ObjectPublic::MovementFlags['NONE'];
        $this->movement_flags2 = 0;
    }

    public function set_update_flags($update_flags)
    {
        $this->update_flags = $update_flags;
    }

    public function _has_fields()
    {
        return in_array($this->update_type, self::TYPES_WITH_FIELDS);
    }

    public function add_field($field, $value, $offset = 0)
    {
        if ($this->_has_fields()) {
            $this->update_blocks_builder->add($field, $value, $offset);
        }
    }

    public function create_batch($send_packed_guid = false)
    {
        $guid = pack('Q', $this->update_object['guid']);

        $mask = '';
        if ($send_packed_guid) {
            $guid = $this->update_object['pack_guid'];
        } else {
            $mask = pack('c', 0xff);
        }

        $header = pack('c', $this->update_object['update_type']);

        $header .= $mask . $guid;

        $object_type = '';
        if (in_array($this->update_type, self::TYPES_WITH_OBJECT_TYPE)) {
            $object_type = pack('c', $this->object_type);
        }

        $object_movement = '';
        if (in_array($this->update_type, self::TYPES_WITH_MOVEMENT)) {
            $object_movement = $this->_get_movement_info();
        }

        $builder_data = '';
        if (in_array($this->update_type, self::TYPES_WITH_FIELDS)) {
            $builder_data = $this->update_blocks_builder->to_bytes();
        }

        $packet = $header . $object_type . $object_movement . $builder_data;

        return $packet;
    }

    //获取运动信息
    public function _get_movement_info()
    {
        $data = pack('c', $this->update_flags);

        //是否活的
        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_LIVING']) {

            if ($this->object_type == ObjectPublic::ObjectType['PLAYER']) {
                //玩家
                $this->movement_flags &= ~ObjectPublic::MovementFlags['ONTRANSPORT'];
            } elseif ($this->object_type == ObjectPublic::ObjectType['UNIT']) {
                //生物
                $this->movement_flags &= ~ObjectPublic::MovementFlags['ONTRANSPORT'];
            }

            $data .= pack('IcI', $this->movement_flags, $this->movement_flags2, $this->update_object['time']);
        }

        //是否有位置
        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_HAS_POSITION']) {
            //更新位置信息
            $data .= pack('f4', $this->update_object['x'], $this->update_object['y'], $this->update_object['z'], $this->update_object['orientation']);
        }

        //是否活的
        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_LIVING']) {

            $data .= pack('I', 0);

            //速度设置
            $data .= pack(
                'f8',
                $this->update_object['speed_walk'],
                $this->update_object['speed_run'],
                $this->update_object['speed_run_back'],
                $this->update_object['speed_swim'],
                $this->update_object['speed_swim_back'],
                $this->update_object['speed_flight'],
                $this->update_object['speed_flight_back'],
                $this->update_object['speed_turn']
            );
        }

        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_LOWGUID']) {

            if ($this->object_type == ObjectPublic::ObjectType['CORPSE']) {

                $data .= pack('I', $this->update_object['low_guid']);
            } elseif ($this->object_type == ObjectPublic::ObjectType['UNIT']) {

                $data .= pack('I', 0x0000000B);
            } elseif ($this->object_type == ObjectPublic::ObjectType['PLAYER']) {

                if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_SELF']) {

                    $data .= pack('I', 0x00000015);
                } else {

                    $data .= pack('I', 0x00000008);
                }
            } else {

                $data .= pack('I', 0x00000000);
            }
        }

        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_HIGHGUID']) {

            if ($this->object_type == ObjectPublic::ObjectType['CORPSE']) {

                $data .= pack('I', $this->update_object['high_guid']);
            } else {

                $data .= pack('I', 0x00000000);
            }
        }

        //可攻击目标
        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_HAS_ATTACKING_TARGET']) {
            // if (((Unit*)this)->getVictim())
            //     *data << ((Unit*)this)->getVictim()->GetPackGUID();
            // else
            //     // data->appendPackGUID(0);

            if (false) {

            } else {
                $data .= pack('c', 0)->appendPackGUID(0);
            }
        }

        // 运动的
        if ($this->update_flags & ObjectPublic::UpdateObjectFlags['UPDATEFLAG_TRANSPORT']) {
            $data .= pack('l', msectime());
        }

        return $data;
    }

    public function add_batch($batch)
    {
        $this->batches[] = $batch;
    }

    public function build()
    {
        $has_transport      = intval(false);
        $count              = count($this->batches);
        $head_update_packet = '';

        for ($i = 0; $i < $count; $i++) {
            if ($i <= $this->MAX_UPDATE_PACKETS_AS_ONE) {
                $head_update_packet .= $this->batches[$i];
            }
        }

        $header = pack('Ic', $count, $has_transport);

        $this->packets[] = $header . $head_update_packet;
    }

    public function get_packets()
    {
        return $this->packets;
    }
}
