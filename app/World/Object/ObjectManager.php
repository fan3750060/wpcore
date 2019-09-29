<?php
namespace app\World\Object;

use app\World\Object\ObjectPublic;
use app\World\Object\UpdatePacketBuilder;

/**
 * 对象管理
 */
class ObjectManager
{

    public $UpdatePacketBuilder;
    public $fields;
    public $object_update_type;

    public function __construct()
    {
        $this->UpdatePacketBuilder = new UpdatePacketBuilder;

        $this->object_update_type = ObjectPublic::ObjectUpdateType['CREATE_OBJECT'];
    }

    //获取对象字段
    public function get_object_field($field)
    {
        if (array_key_exists($field, $this->fields)) {
            return $this->fields[$field];
        } else {
            return 0;
        }
    }

    //设置更新类型
    public function set_object_update_type($object_update_type)
    {
        $this->object_update_type = $object_update_type;
    }

    //创建批次
    public function create_batch($fields = [])
    {
        if (!$fields) {
            return false;
        }

        foreach ($fields as $k => $field) {
            $this->UpdatePacketBuilder->add_field($field, $this->get_object_field($field));
        }

        return $this->UpdatePacketBuilder->create_batch(true);
    }

    //添加字段
    public function add_field($field, $value, $offset)
    {
        $this->UpdatePacketBuilder->add_field($field, $value, $offset);
    }

    //设置更新标记
    public function set_update_flags($update_flags)
    {
        $this->UpdatePacketBuilder->set_update_flags($update_flags);
    }

    //设置对象字段
    public function set_object_field($field, $value)
    {
        $this->fields[$field] = $value;
    }

    //设置数据库字段数据
    public function set($world_object)
    {
        $this->world_object = $world_object;
        return $this;
    }

    //添加对象字段
    public function add_object_fields()
    {
        $this->set_object_field('ObjectField.GUID', $this->world_object['guid']);
        $this->set_object_field('ObjectField.TYPE', $this->world_object['type_mask']);
        $this->set_object_field('ObjectField.ENTRY', $this->world_object['entry']);
        $this->set_object_field('ObjectField.SCALE_X', $this->world_object['scale_x']);
    }

    //添加批次
    public function add_batch($batch)
    {
        $this->UpdatePacketBuilder->add_batch($batch);
        return $this;
    }

    //构造数据包
    public function build_update_packet()
    {
        $this->UpdatePacketBuilder->build();
        return $this;
    }

    //获取数据包
    public function get_update_packets()
    {
        return $this->UpdatePacketBuilder->get_packets();
    }

    //准备
    public function prepare()
    {
        //初始化
        $this->add_object_fields();

        $this->UpdatePacketBuilder->update_object = $this->world_object;
        $this->UpdatePacketBuilder->update_type   = $this->object_update_type;
        $this->UpdatePacketBuilder->object_type   = $this->world_object['object_type'];

        return $this;
    }
}
