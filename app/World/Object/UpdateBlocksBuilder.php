<?php
namespace app\World\Object;

use app\World\Object\FieldType;
use app\World\Object\UpdateObjectFields;

/**
 *
 */
class UpdateBlocksBuilder
{
    const FIELD_BIN_MAP = [
        FieldType::INT32      => 'i',
        FieldType::TWO_INT16  => 'I',
        FieldType::FLOAT      => 'f',
        FieldType::INT64      => 'q',
        FieldType::FOUR_BYTES => 'I',
    ];

    public $mask_blocks = [];
    public $update_blocks = [];
    public $UpdateObjectFields;

    public function __construct()
    {
        if (!$this->UpdateObjectFields) {
            $this->UpdateObjectFields = new \ReflectionClass('app\World\Object\UpdateObjectFields');
        }
    }

    public function add($field, $value, $offset = 0)
    {
        if (isset(FieldType::FIELD_TYPE_MAP[$field]) && $field_type = FieldType::FIELD_TYPE_MAP[$field]) {

            $field_struct = self::FIELD_BIN_MAP[$field_type];

            $expload   = explode('.', $field);
            $key       = $expload[0];
            $key_value = $expload[1];

            $field_value = $this->UpdateObjectFields->getConstant($key)[$key_value];

            $tmp = $offset ? $field_value + $offset : $offset;

            $index = $tmp ? $tmp : $field_value;

            $field_index = $this->_get_field_index($index);

            $this->_set_field_mask_bits($field_index, $field_struct);

            $this->_set_field_value($field_index, $field_struct, $value);
        }
    }

    public function _set_field_mask_bits($field_index, $field_struct)
    {
        $num_mask_blocks = ceil(strlen(pack($field_struct, 0)) / 4);

        for ($i = $field_index; $i < $field_index + $num_mask_blocks; $i++) {
            $this->_set_field_mask_bit($i);
        }
    }

    public function _set_field_mask_bit($field_index)
    {
        $mask_block_index = floor($field_index / 32);
        $bit_index        = $field_index % 32;

        while (count($this->mask_blocks) < $mask_block_index + 1) {
            $this->mask_blocks[] = 0;
        }

        $this->mask_blocks[$mask_block_index] |= 1 << $bit_index;
    }

    public function _set_field_value($field_index, $field_struct, $value)
    {
        $update_block                      = pack($field_struct, $value);
        $this->update_blocks[$field_index] = $update_block;
    }

    public function _get_field_index($field)
    {
        if ($field) {
            return $field;
        } else {
            return intval($field);
        }
    }

    public function to_bytes()
    {
    	$num_mask_blocks_bytes = pack('c',count($this->mask_blocks));

    	$mask_blocks = '';
    	foreach ($this->mask_blocks as $k => $v) 
    	{
    		$mask_blocks.= pack('i',$v);
    	}

    	$keys = array_keys($this->update_blocks);
    	sort($keys);
    	
    	$sorted_blocks = '';
    	foreach ($keys as $k => $v) 
    	{
    		$sorted_blocks.= $this->update_blocks[$v];
    	}

    	$builder_data = $num_mask_blocks_bytes . $mask_blocks . $sorted_blocks;

 		return $builder_data;
    }
}
