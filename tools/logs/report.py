import os
import gzip
import glob


def getLatestLogLines() -> list:
    latestLogFile = sorted(glob.glob("*.gz"))[-1]
    with gzip.open(latestLogFile, 'rt') as f:
        lines = f.readlines()
    return lines


def getRequestedFiles(lines: list, code: int):

    requests = {}
    referrals = {}
    for line in lines:
        parts = line.split('"')
        if len(parts) < 7:
            print("BAD LINE:", line)
            continue
        thisCode = int(parts[2].strip().split(" ")[0])
        thisFile = parts[1].split(" ")[1]
        thisReferral = parts[3]

        if (thisCode != code):
            continue

        if ("/plus/data/" in thisFile):
            continue

        if (thisFile in requests):
            requests[thisFile] = requests[thisFile] + 1
            referrals[thisFile].append(thisReferral)
        else:
            requests[thisFile] = 1
            referrals[thisFile] = []

    countByRequest = dict(
        sorted(requests.items(), key=lambda item: item[1], reverse=True))

    return countByRequest, referrals


def reportMissing404(logLines: str, minReqs=2):
    reqs, refs = getRequestedFiles(logLines, 404)
    html = "<h1>404 Not Found Report</h1>"
    html += "<table border=1>"
    html += "<tr><th>404s</th><th>URL</th><th>referrers</th></tr>"

    for key in reqs:
        if reqs[key] < minReqs:
            break
        count = reqs[key]
        requestPath = key
        url = "https://swharden.com" + requestPath
        realRefs = [x for x in refs[key] if x != "-"]
        refHtml = "".join([f"<li>{x}</li>" for x in realRefs])

        html += f"<tr>"
        html += f"<td>{count}</td>"
        html += f"<td><a href='{url}'>{requestPath}</a></td>"
        html += f"<td><ul>{refHtml}</td></ul>"
        html += f"</tr>"

    html += "</table>"

    filePath = os.path.abspath("404.html")
    with open(filePath, 'w') as f:
        f.write(html)
    print("Saved:", filePath)
    os.system("explorer.exe " + filePath)


def reportOk200(logLines: str, minReqs=2):
    reqs, refs = getRequestedFiles(logLines, 200)
    html = "<h1>200 OK Report</h1>"
    html += "<table border=1>"
    html += "<tr><th>200</th><th>URL</th><th>External Refs</th></tr>"

    for key in reqs:
        if "/qrss/" in key:
            continue
        if reqs[key] < minReqs:
            break
        count = reqs[key]
        requestPath = key
        url = "https://swharden.com" + requestPath
        realRefs = [x for x in refs[key] if len(x.strip()) > 5]
        realRefs = [x for x in realRefs if not "swharden.com" in x]
        realRefs = [x for x in realRefs if not "google.com/" in x]
        realRefs = [x for x in realRefs if not "bing.com/" in x]
        realRefs = [x for x in realRefs if not "duckduckgo.com/" in x]
        realRefs = [x for x in realRefs if not "youtube.com/" in x]
        realRefs = [x for x in realRefs if not "googleusercontent.com/" in x]
        realRefs = [x for x in realRefs if not "github.com/" in x]
        if not len(realRefs):
            continue

        refHtml = "".join([f"<li>{x}</li>" for x in sorted(realRefs)])
        html += f"<tr>"
        html += f"<td>{count}</td>"
        html += f"<td><a href='{url}'>{requestPath}</a></td>"
        html += f"<td><ul>{refHtml}</td></ul>"
        html += f"</tr>"

    html += "</table>"

    filePath = os.path.abspath("200.html")
    with open(filePath, 'w') as f:
        f.write(html)
    print("Saved:", filePath)
    os.system("explorer.exe " + filePath)


if __name__ == "__main__":
    logLines = getLatestLogLines()
    reportMissing404(logLines)
    reportOk200(logLines)
