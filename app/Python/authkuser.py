import os
import pickle
import binascii

class authkuser:

	def __init__(self,I,data):
		self.I = I
		self.packet = data

	def _parse(self):
		with open('runtime/SRP6CAHE_'+self.I+"_0.pkl",'rb') as file:
			self.srp  = pickle.loads(file.read())
			self.A = bytes(self.packet[1:33])
			self.M1 = bytes(self.packet[33:53])
			if(os.path.exists('runtime/SRP6CAHE_'+self.I+"_0.pkl")):
				os.remove('runtime/SRP6CAHE_'+self.I+"_0.pkl")
				return self._work()

	def _work(self):
		self.srp.A = self.A
		data = self.get(self.M1)

		#保存sessionkey
		output_hal = open('runtime/SESSIONKEY_'+self.I+"_0.pkl", 'wb')
		strobj = pickle.dumps(self.srp.sessionkey_save)
		output_hal.write(strobj)
		output_hal.close()
		
		return data

	def get(self, M1):
		if self.srp.getM(M1):
			data = [0x01, 0x00]
			for m in self.srp.M:
				data.append(m)
			data.append(0x00)
			data.append(0x00)
			data.append(0x80)
			data.append(0x00)
			data.append(0x00)
			data.append(0x00)
			data.append(0x00)
			data.append(0x00)
			data.append(0x00)
			data.append(0x00)
		else:
			data = [0x00, 0x00, 0x04]
		return data