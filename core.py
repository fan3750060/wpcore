import os
import sys
import base64
import pickle

from app.Python.checkuser import checkuser
from app.Python.authkuser import authkuser
from app.Python.realminfo import realminfo

if __name__ == '__main__':
    # os.system('cls')
    try:
        if sys.argv[1] == "0":
            I = sys.argv[2]
            P = sys.argv[3]
            print(checkuser(I,P)._parse())

        elif sys.argv[1] == "1":
            I = sys.argv[2]
            data = base64.b64decode(sys.argv[3].encode("utf-8"))
            print(authkuser(I, data)._parse())

        elif sys.argv[1] == "2":
            name = sys.argv[2]
            addr_port = sys.argv[3]
            population = sys.argv[4]
            print(realminfo(name, addr_port, population).realm_info())

        elif sys.argv[1] == "3":
            I = sys.argv[2]
            with open('runtime/SESSIONKEY_'+I+"_0.pkl",'rb') as file:
                sessionkey  = pickle.loads(file.read())
                if(os.path.exists('runtime/SESSIONKEY_'+I+"_0.pkl")):
                    os.remove('runtime/SESSIONKEY_'+I+"_0.pkl")
                    print(sessionkey)
            
            

    except KeyboardInterrupt:
        sys.exit()
