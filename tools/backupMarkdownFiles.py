import glob
import os
import time
import shutil

BLOGPATH = "../blog"


def backupMarkdownFiles():
    mdFiles = glob.glob(BLOGPATH+"/*/index.md")
    mdFiles = [os.path.abspath(x) for x in mdFiles]
    for filePath in mdFiles:
        filePath2 = filePath + '.' + str(int(time.time())) + ".backup"
        print("BACKING UP:", filePath2)
        shutil.copy(filePath, filePath2)


def deleteMarkdownBackups():
    mdFiles = glob.glob(BLOGPATH+"/*/*.backup")
    mdFiles = [os.path.abspath(x) for x in mdFiles]
    for filePath in mdFiles:
        print("DELETING:", filePath)
        os.remove(filePath)


if __name__ == "__main__":
    #backupMarkdownFiles()
    #deleteMarkdownBackups()