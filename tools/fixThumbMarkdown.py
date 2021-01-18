"""
This script replaces markdown images with thumbnails with the thumbnail and a link to the original.
Thumbnails of images were created with another script.

OLD:
![](demo.jpg)

NEW:
[![](demo_thumb.jpg)](demo.jpg)

"""
import os
import glob
import time
import shutil

BLOGPATH = "../blog"

if __name__ == "__main__":
    mdFiles = glob.glob(BLOGPATH+"/*/index.md")
    mdFiles = [os.path.abspath(x) for x in mdFiles]
    mods = []
    for filePath in mdFiles:
        print()
        print(filePath)

        with open(filePath, 'rb') as f:
            lines = f.readlines()

        for i, originalLine in enumerate(lines):
            line = originalLine.decode('utf-8')
            if not line.startswith("![]("):
                continue
            folder = os.path.abspath(os.path.dirname(filePath))
            originalName = line.split("(")[1].split(")")[0]
            if "_thumb.jpg" in originalName:
                continue
            originalPath = os.path.join(folder, originalName)
            nameWithoutExt = ".".join(originalName.split(".")[:-1])
            thumbName = nameWithoutExt + "_thumb.jpg"
            thumbPath = os.path.join(folder, thumbName)
            if (os.path.exists(thumbPath)):
                newLine = f"[![]({thumbName})]({originalName})\r\n"
                print(newLine.rstrip())
                lines[i] = newLine.encode('utf-8')

            with open(filePath, 'wb') as f:
                f.writelines(lines)
