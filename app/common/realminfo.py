
class realminfo:

    def __init__(self, name, addr_port, population):
        self.name = name
        self.addr_port = addr_port
        self.population = population

    def realm_info(self):
        #Creates the RealmInfo and sends it to the client. #
        # get all realms from config...
        # 2: RealmInfo_Server 服信息#
        type_b = int.to_bytes(0, 4, byteorder='little')
        flags = 0x00
        
        # name = b'WpcoreServer\0'
        # addr_port = b'127.0.0.1:13250\0'
        # population = b'\x00\x00\x00\x00'

        name = bytes(self.name+'\0',encoding = "utf-8")
        addr_port = bytes(self.addr_port+'\0',encoding = "utf-8")
        population = bytes(self.population,encoding = "utf-8")

        num_chars = 0x00
        time_zone = 0x00
        unknown = 0x00
        RealmInfo_Server = []
        for i in type_b:
            RealmInfo_Server.append(i)
        RealmInfo_Server.append(flags)
        for i in name:
            RealmInfo_Server.append(i)
        for i in addr_port:
            RealmInfo_Server.append(i)
        for i in population:
            RealmInfo_Server.append(i)
        RealmInfo_Server.append(num_chars)
        RealmInfo_Server.append(time_zone)
        RealmInfo_Server.append(unknown)

        #3: RealmFooter_Server 领域页脚服务器#
        unk_ = int.to_bytes(0, 2, byteorder='little')
        RealmFooter_Server = [unk_[0], unk_[1]]

        #1: RealmHeader_Server 领域标题服务器#
        cmd = 0x10
        length = 7 + len(RealmInfo_Server)
        length_b = int.to_bytes(length, 2, byteorder='little')
        unk = int.to_bytes(0, 4, byteorder='little')
        num_realms = 0x01
        RealmHeader_Server = [cmd]
        for i in length_b:
            RealmHeader_Server.append(i)
        for i in unk:
            RealmHeader_Server.append(i)
        RealmHeader_Server.append(num_realms)

        return RealmHeader_Server+RealmInfo_Server+RealmFooter_Server
