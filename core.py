import os
import sys
import base64

from app.common.checkuser import checkuser
from app.common.authkuser import authkuser
from app.common.realminfo import realminfo

if __name__ == '__main__':
    # os.system('cls')
    try:
        if sys.argv[1] == "0":
            data = sys.argv[2]
            print(checkuser(data)._parse())

        elif sys.argv[1] == "1":
            I = sys.argv[2]
            data = base64.b64decode(sys.argv[3].encode("utf-8"))
            print(authkuser(I, data)._parse())

        elif sys.argv[1] == "2":
            name = sys.argv[2]
            addr_port = sys.argv[3]
            population = sys.argv[4]
            print(realminfo(name, addr_port, population).realm_info())

    except KeyboardInterrupt:
        sys.exit()
