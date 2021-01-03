import gzip
import glob


def getLatestLogLines() -> list:
    latestLogFile = sorted(glob.glob("*.gz"))[-1]
    with gzip.open(latestLogFile, 'rt') as f:
        lines = f.readlines()
    return lines


def getRequestedFiles(lines: list, code: int = 404):

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


def makeReport(reqs, refs, minReqs=2):
    html = ""
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
        print(count, requestPath, "from", len(realRefs))

        html += f"<tr>"
        html += f"<td>{count}</td>"
        html += f"<td><a href='{url}'>{requestPath}</a></td>"
        html += f"<td><ul>{refHtml}</td></ul>"
        html += f"</tr>"

    html += "</table>"

    with open("report.html", 'w') as f:
        f.write(html)


if __name__ == "__main__":
    logLines = getLatestLogLines()
    reqs, refs = getRequestedFiles(logLines)
    makeReport(reqs, refs)
