#!/usr/bin/env python3
"""Build the /dev/null release post for a published GitHub release.

Reads the release from env (set by .github/workflows/devnull.yml) and the
GitHub REST/GraphQL API through `gh api` (GH_TOKEN = the workflow token,
read-only). Prints plain text, capped at 4000 chars. Sections with no data
are left out; nothing is invented.

Env: REPO (owner/name), TAG, RELEASE_BODY, RELEASE_URL, PITCH, PACKAGE,
     SELF_JOBS (comma-separated job names of this workflow, left out of Tests).
"""
import json
import os
import re
import subprocess
import sys

LIMIT = 4000
MAX_NOTES = 10
REPO = os.environ["REPO"]
TAG = os.environ["TAG"]
BODY = os.environ.get("RELEASE_BODY", "") or ""
URL = os.environ.get("RELEASE_URL", "")
PITCH = os.environ.get("PITCH", "").strip()
PACKAGE = os.environ.get("PACKAGE", "").strip()
SELF_JOBS = {j.strip() for j in os.environ.get("SELF_JOBS", "compose,post").split(",") if j.strip()}
OWNER, NAME = REPO.split("/", 1)

NOISE = re.compile(r"(dependabot|renovate|^bump |^update .* requirement from|chore\(deps|^fix styling$|^update changelog|^merge (branch|pull request)|^merge remote|full changelog)", re.I)
HOUSE_APPS = {"epic-werkflow", "lexi-chief-of-staff"}
QR_DUPES = {"pest", "pint", "phpstan", "rector"}  # Lexi mirrors the quality-runners statuses under these names
BOTS_SKIP_REVIEW = {"dependabot[bot]", "github-actions[bot]"}


def api(path, raw=False):
    cmd = ["gh", "api", "-H", "Accept: application/vnd.github+json", path]
    attempts = [cmd[:2] + ["--allow-escape-sequences"] + cmd[2:], cmd] if raw else [cmd]
    out = None
    for c in attempts:
        try:
            out = subprocess.run(c, capture_output=True, text=True, timeout=60, check=True).stdout
            break
        except Exception as exc:  # missing data must never break the post
            print(f"warn: gh api {path} failed: {exc}", file=sys.stderr)
    if out is None:
        return None
    if raw:
        return out
    try:
        return json.loads(out) if out.strip() else None
    except ValueError:
        return None


def graphql(query, **variables):
    cmd = ["gh", "api", "graphql", "-f", f"query={query}"]
    for k, v in variables.items():
        cmd += ["-F", f"{k}={v}"]
    try:
        return json.loads(subprocess.run(cmd, capture_output=True, text=True, timeout=60, check=True).stdout)
    except Exception as exc:
        print(f"warn: graphql failed: {exc}", file=sys.stderr)
        return None


def clean(text, width=220):
    """One plain line: no markdown/HTML, no em dashes, no @-mentions, no hashtags."""
    t = re.sub(r"<[^>]+>", " ", text or "")
    t = re.sub(r"(?m)^\s*#{1,6}\s+", "", t)
    t = re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", t)          # [label](url) -> label
    t = re.sub(r"\s+by @[\w-]+(\[bot\])? in https?://\S+", "", t)
    t = re.sub(r"\s*\(?https?://github\.com/\S+/pull/\d+\)?", "", t)
    t = re.sub(r"\s*\(#\d+\)", "", t)
    t = t.replace("\u2014", "-").replace("\u2013", "-")
    t = re.sub(r"[*_`]{1,3}", "", t)
    t = re.sub(r"@([\w-]+)", r"\1", t)
    t = re.sub(r"(^|[\s(])#(\d+)", r"\1PR \2", t)
    t = re.sub(r"(^|[\s(])#(\w)", r"\1\2", t)
    t = re.sub(r"\s+", " ", t).strip(" -:")
    return t if len(t) <= width else t[: width - 3].rstrip() + "..."


def who(user):
    return (user or {}).get("login", "someone").replace("[bot]", "")


# ---------------------------------------------------------------- data
def previous_tag():
    rels = api(f"repos/{REPO}/releases?per_page=100") or []
    rels = [r for r in rels if not r.get("draft")]
    rels.sort(key=lambda r: r.get("published_at") or r.get("created_at") or "", reverse=True)
    names = [r["tag_name"] for r in rels]
    if TAG in names:
        i = names.index(TAG)
        return names[i + 1] if i + 1 < len(names) else None
    return names[0] if names else None


def tag_sha():
    ref = api(f"repos/{REPO}/commits/{TAG}")
    return (ref or {}).get("sha")


def range_commits(prev):
    if not prev:
        return []
    cmp = api(f"repos/{REPO}/compare/{prev}...{TAG}")
    return (cmp or {}).get("commits", [])[-100:]


def merged_prs(commits):
    prs, seen = [], set()
    for c in commits[-50:]:
        for pr in api(f"repos/{REPO}/commits/{c['sha']}/pulls") or []:
            if pr.get("merged_at") and pr["number"] not in seen:
                seen.add(pr["number"])
                prs.append(pr)
    prs.sort(key=lambda p: p["merged_at"])
    return prs


# ---------------------------------------------------------------- sections
def patch_notes(commits, prs):
    items = []
    for line in BODY.splitlines():
        m = re.match(r"^\s*[-*]\s+(.*)", line)
        if m and not NOISE.search(m.group(1)) and not re.search(r"full changelog|new contributors?", m.group(1), re.I):
            items.append(clean(m.group(1)))
    prose = ""
    if not items:
        paras = [p for p in re.split(r"\n\s*\n", BODY) if p.strip() and not p.lstrip().startswith(("#", "**Full"))]
        prose = clean(paras[0], 400) if paras else ""
        pr_shas = set()
        for pr in prs:
            if pr.get("user", {}).get("type") == "Bot" or NOISE.search(pr["title"]):
                continue
            items.append(clean(pr["title"]))
        for pr in prs:
            pr_shas.add(pr.get("merge_commit_sha"))
        pr_titles = {clean(p["title"]).lower() for p in prs}
        for c in commits:
            subj = c["commit"]["message"].split("\n")[0]
            if c["sha"] in pr_shas or re.search(r"\(#\d+\)$", subj) or NOISE.search(subj) or len(c.get("parents", [])) > 1:
                continue
            if clean(subj).lower() not in pr_titles:
                items.append(clean(subj))
    items = [i for i in dict.fromkeys(items) if i]
    more = len(items) - MAX_NOTES
    lines = ([prose] if prose else []) + [f"- {i}" for i in items[:MAX_NOTES]]
    if more > 0:
        lines.append(f"- (+{more} more in the release notes)")
    return ("Patch notes:", lines)


def strip_ansi(s):
    return re.sub(r"\x1b\[[0-9;]*[A-Za-z]", "", s)


def test_counts(runs):
    """Pest/PHPUnit totals and coverage from the first test job log that has them."""
    skip = re.compile(r"phpstan|pint|styl|lint|rector|update|changelog|dependabot|lean-package", re.I)
    for r in [r for r in runs if r.get("conclusion") == "success" and not skip.search(r["name"]) and r["name"] not in SELF_JOBS][:4]:
        log = api(f"repos/{REPO}/actions/jobs/{r['id']}/logs", raw=True)
        if not log:
            continue
        log = strip_ansi(re.sub(r"(?m)^\S+Z ", "", log))
        out = []
        m = re.search(r"Tests:\s+([^\n]*?\d+ passed[^\n]*)", log)
        if m:
            out.append(re.sub(r"\s+", " ", m.group(1)).strip())
        else:
            m = re.search(r"OK \((\d+) tests?, (\d+) assertions?\)", log)
            if m:
                out.append(f"{m.group(1)} passed ({m.group(2)} assertions)")
        cov = re.search(r"Total:\s+([\d.]+)\s?%", log) or re.search(r"Lines:\s+([\d.]+)%", log)
        if cov:
            out.append(f"coverage {cov.group(1)}%")
        if out:
            return f"{r['name'].replace('.*', '')}: " + ", ".join(out)
    return None


def tests(sha):
    data = api(f"repos/{REPO}/commits/{sha}/check-runs?per_page=100&filter=latest") or {}
    latest = {}
    for r in data.get("check_runs", []):
        if (r.get("app") or {}).get("slug") != "github-actions" or r["name"] in SELF_JOBS:
            continue
        if r["name"] not in latest or (r.get("started_at") or "") > (latest[r["name"]].get("started_at") or ""):
            latest[r["name"]] = r
    runs = list(latest.values())
    if not runs:
        return ("Tests:", [])
    done = [r for r in runs if r.get("status") == "completed"]
    ok = [r for r in done if r.get("conclusion") in ("success", "skipped", "neutral")]
    groups = {}
    for r in runs:
        key = re.sub(r"\d+(\.\d+)*(\.\*)?", "x", r["name"])
        groups.setdefault(key, []).append(r["name"])
    names = ", ".join(v[0].replace(".*", "") if len(v) == 1 else f"{k} ({len(v)} jobs)" for k, v in groups.items())
    lines = [f"- CI on {sha[:7]}: {len(ok)}/{len(runs)} checks passed ({clean(names, 300)})"]
    bad = [r for r in done if r not in ok]
    if bad:
        lines.append("- not passing: " + clean(", ".join(f"{r['name']} {r['conclusion']}" for r in bad)))
    running = len(runs) - len(done)
    if running:
        lines.append(f"- still running when posted: {running}")
    counts = test_counts(runs)
    if counts:
        lines.insert(1, "- " + clean(counts))
    return ("Tests:", lines)


AC_QUERY = """query($o:String!,$n:String!,$pr:Int!){repository(owner:$o,name:$n){pullRequest(number:$pr){
closingIssuesReferences(first:10){nodes{number title body}}}}}"""


def acceptance(prs):
    lines, seen = [], set()
    for pr in prs:
        d = graphql(AC_QUERY, o=OWNER, n=NAME, pr=pr["number"])
        nodes = (((d or {}).get("data") or {}).get("repository") or {}).get("pullRequest") or {}
        for issue in (nodes.get("closingIssuesReferences") or {}).get("nodes") or []:
            if issue["number"] in seen:
                continue
            seen.add(issue["number"])
            sec = re.search(r"(?ims)^#{1,6}\s*acceptance criteria\s*$(.*?)(?=^#{1,6}\s|\Z)", issue.get("body") or "")
            if not sec:
                sec = re.search(r"(?ims)^\**acceptance criteria:?\**\s*$(.*?)(?=^\s*$\s*^[^-*\s]|\Z)", issue.get("body") or "")
            boxes = re.findall(r"(?m)^\s*[-*]\s+\[([ xX])\]\s+(.*)$", sec.group(1)) if sec else []
            if not boxes:
                continue
            lines.append(f"Issue {issue['number']} ({clean(issue['title'], 90)}), PR {pr['number']}:")
            lines += [f"[{'x' if s.strip() else ' '}] {clean(t, 160)}" for s, t in boxes]
    return ("Acceptance criteria:", lines)


def review_line(body):
    text = re.sub(r"(?s)<details>.*?</details>", " ", body or "")
    text = re.sub(r"(?s)```.*?```", " ", text)
    text = re.sub(r"(?s)<!--.*?-->", " ", text)
    parts = [l.strip() for l in text.splitlines() if l.strip() and not l.strip().startswith(("|", ">"))]
    parts = [p for p in parts if clean(p, 400)]
    if not parts:
        return None
    first = clean(parts[0], 140)
    rest = next((clean(p, 160) for p in parts[1:] if re.match(r"^([-*]|\*\*|\d+\.)", p)), "")
    return f"{first} {rest}".strip() if rest and rest != first else first


def reviews_and_changes(prs):
    found, changed = [], []
    for pr in prs:
        revs = [r for r in (api(f"repos/{REPO}/pulls/{pr['number']}/reviews?per_page=100") or [])
                if (r.get("user") or {}).get("login") not in BOTS_SKIP_REVIEW and r.get("submitted_at")]
        for r in revs:
            line = review_line(r.get("body"))
            if line and not re.match(r"actionable comments posted: 0\b", line, re.I):
                state = r["state"].lower().replace("_", " ")
                found.append(f"- {who(r['user'])} ({state}, PR {pr['number']}): {line}")
        if revs:
            first = min(r["submitted_at"] for r in revs)
            commits = api(f"repos/{REPO}/pulls/{pr['number']}/commits?per_page=100") or []
            after = [clean(c["commit"]["message"].split("\n")[0], 140) for c in commits
                     if (c["commit"].get("committer") or {}).get("date", "") > first]
            changed += [f"- PR {pr['number']}: {s}" for s in after if s]
    return ("Review findings:", found), ("Changed in review:", changed)


def local_pipeline(sha, prs):
    """House pipeline: quality-runners commit statuses, plus Lexi (risk) and Werkflow check runs."""
    lines = []
    targets = [(f"release {sha[:7]}", sha)] + [(f"PR {p['number']}", p["head"]["sha"]) for p in prs]
    for label, s in targets:
        st = api(f"repos/{REPO}/commits/{s}/status") or {}
        qr = {x["context"]: x for x in st.get("statuses", []) if x["context"].startswith("quality-runners/")}
        cr = api(f"repos/{REPO}/commits/{s}/check-runs?per_page=100&filter=latest") or {}
        wf = [r for r in cr.get("check_runs", []) if (r.get("app") or {}).get("slug") in HOUSE_APPS
              and not r["name"].startswith("quality-runners/") and r["name"] not in QR_DUPES]
        if not qr and not wf:
            continue
        bits = []
        gate = qr.pop("quality-runners/gate", None)
        if gate:
            bits.append(f"gate {gate['state']}" + (f" ({clean(gate.get('description'), 120)})" if gate.get("description") else ""))
        if qr:
            bits.append(", ".join(f"{k.split('/', 1)[1]} {v['state']}" for k, v in sorted(qr.items())))
        for r in wf:
            title = (r.get("output") or {}).get("title") or ""
            bits.append(f"{clean(r['name'], 40)} {r.get('conclusion') or r.get('status')}" + (f" ({clean(title, 80)})" if title else ""))
        lines.append(f"- {label}: " + "; ".join(bits))
    return ("Local pipeline:", lines)


# ---------------------------------------------------------------- render
def render(sections, head, tail):
    blocks = [head] + ["\n".join([t] + ls) for t, ls in sections if ls] + [tail]
    return "\n\n".join(b for b in blocks if b)


def main():
    sha = tag_sha()
    prev = previous_tag()
    commits = range_commits(prev)
    prs = merged_prs(commits)
    rev, chg = reviews_and_changes(prs)
    # display order
    sections = [patch_notes(commits, prs), tests(sha) if sha else ("Tests:", []), acceptance(prs), rev, chg,
                local_pipeline(sha, prs) if sha else ("Local pipeline:", [])]
    head = f"{NAME} {TAG}: {clean(PITCH, 400)}" if PITCH else f"{NAME} {TAG}"
    tail = "\n".join(x for x in [f"composer require {PACKAGE}" if PACKAGE else "", URL] if x)
    # trim lowest priority first: changed in review, local pipeline, reviews, AC, tests, patch notes
    order = [4, 5, 3, 2, 1, 0]
    for idx in order:
        while len(render(sections, head, tail)) > LIMIT and sections[idx][1]:
            title, ls = sections[idx]
            ls = [l for l in ls if not l.startswith("- (+")]
            dropped = getattr(main, "dropped", {}).get(idx, 0) + 1
            main.dropped = {**getattr(main, "dropped", {}), idx: dropped}
            ls = ls[:-1]
            sections[idx] = (title, ls + ([f"- (+{dropped} more)"] if ls else []))
    print(render(sections, head, tail)[:LIMIT])


if __name__ == "__main__":
    main()
