from app.Python.srp6 import SRP6
import pickle

class checkuser:
	I = None
	srp = None
	cmd = 0x00
	error = 0x00
	unk2 = 0
	g_len = 1
	N_len = 32
	unk3 = [0x2A, 0xD5, 0x48, 0xCC, 0x9B, 0x9D, 0xA1, 0x99,0xCC, 0x04, 0x7A, 0x60, 0x91, 0x15, 0x6C, 0x51]
	unk4 = 0

	def __init__(self,I):
		self.I = I
		self.srp = None

	def _parse(self):
		self.srp = SRP6(self.I, self.I)

		# 保存类对象
		output_hal = open('runtime/SRP6CAHE_'+self.I+"_0.pkl", 'wb')
		strobj = pickle.dumps(self.srp)
		output_hal.write(strobj)
		output_hal.close()

		# data = bytearray(self.get())
		data = self.get()
		return data

	def get(self):
		data = []
		data.append(self.cmd)
		data.append(self.error)
		data.append(self.unk2)
		for b in self.srp.B:
			data.append(b)
		data.append(self.g_len)
		data.append(self.srp.g)
		data.append(self.N_len)
		for b in self.srp.N.to_bytes(32, byteorder='little').rstrip(b'\x00'):
			data.append(b)
		for b in self.srp.s:
			data.append(b)
		for b in self.unk3:
			data.append(b)
		data.append(self.unk4)
		return data