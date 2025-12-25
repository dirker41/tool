import os


def uncompress(path, name):
    cmd="unzip "+ path+name+".zip -d ./unzip/"
    os.system(cmd)


def compress(path, name):
    cmd="zip -r "+name+".zip "+ path+name
    os.system(cmd)

def main():
    PATH="./"
    NAME="OVL"
    compress(PATH, NAME)
    uncompress(PATH, NAME)

main()
