<?php
namespace app\World;

use app\World\Clientstate;

class MessageCache
{
    private static $dataCacheTable;

    /**
     * 创建缓存table
     */
    public function createDataCacheTable()
    {
        self::$dataCacheTable = new \swoole_table(2048);

        // 表格包含fd和content两个字段
        self::$dataCacheTable->column('fd', \swoole_table::TYPE_INT, 8); // 1,2,4,8

        self::$dataCacheTable->column('content', \swoole_table::TYPE_STRING, 5000);

        self::$dataCacheTable->create();
    }

    /**
     * 清除指定连接的缓存
     *
     * @param swoole_server $serv
     * @param int $fd
     */
    public function clearCacheData($serv, $fd)
    {
        self::$dataCacheTable->del($fd);
    }

    /**
     * 组装完整的数据包并进行切割返回
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param string $data
     * @return string
     */
    public function getSplitDataList($fd, $data)
    {
        // 获取指定连接的缓存消息
        $dataCacheArr = self::$dataCacheTable->get($fd);

        $content = "";
        if (!empty($dataCacheArr)) {
            // 取出已有的数据
            $content = $dataCacheArr["content"];
        }

        // 将原有数据和最新接收的数据拼接一起
        if (!empty($content)) {
            $data = $content . $data;
        }

        $endFlag = Clientstate::MESSAGE_END_FLAG;
        $result  = null;

        // 如果不包含结束符，则尚未接收到完整的包，先缓存起来
        if (!strstr($data, $endFlag)) {
            self::$dataCacheTable->set($fd, array(
                "fd"      => $fd,
                "content" => $data,
            ));
        } else {
            // 切割数据成完整包
            $result = explode($endFlag, $data);
            $count  = count($result);
            if (!empty($result[$count - 1])) {
                // 如果末尾不是结束符，则剔除完整包还有残余数据 继续留在缓存中
                self::$dataCacheTable->set($fd, array(
                    "fd"      => $fd,
                    "content" => $result[$count - 1],
                ));
                // 移除最后残余的不完整的数据
                array_splice($result, $count - 1, 1);
            } else {
                // 数据被切割为完整的包，没有残余未完整的数据包，删除当前记录
                self::$dataCacheTable->del($fd);
                // 移除最后一个空数据
                array_splice($result, $count - 1, 1);
            }
        }

        return $result;
    }
}
