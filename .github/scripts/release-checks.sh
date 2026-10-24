#!/usr/bin/env bash
# Release-time checks run before the /dev/null post.
#   version-aligner: git tag, latest CHANGELOG.md entry and (if the package has one)
#                    the application version must agree. Skipped when there is no CHANGELOG.md.
#   composer-peel status: read-only release-readiness report. `composer-peel peel` is never run.
# Needs version-aligner, composer-peel and jq on PATH. RELEASE_TAG (optional) is the tag being released.
set -uo pipefail
fail=0
norm() { local v="${1#[vV]}"; printf '%s' "$v"; }
bad() { echo "::error title=$1::$2"; fail=1; }

echo "== version-aligner check"
if [ ! -f CHANGELOG.md ]; then
  echo "::notice title=version-aligner::skipped, this repo has no CHANGELOG.md"
else
  json=$(version-aligner check --format=json 2>&1) || true
  echo "$json"
  if ! jq -e . >/dev/null 2>&1 <<<"$json"; then
    bad version-aligner "could not read version-aligner output"
  else
    tag=$(jq -r '.versions.gitTag // empty' <<<"$json")
    log=$(jq -r '.versions.changelog // empty' <<<"$json")
    app=$(jq -r '.versions.application // empty' <<<"$json")
    [ -n "$tag" ] || bad version-aligner "no git tag found"
    if [ -n "${RELEASE_TAG:-}" ] && [ "$(norm "$tag")" != "$(norm "$RELEASE_TAG")" ]; then
      bad version-aligner "latest git tag $tag is not the release tag $RELEASE_TAG"
    fi
    if [ -z "$log" ]; then
      bad version-aligner "CHANGELOG.md has no versioned entry (expected $tag)"
    elif [ "$(norm "$log")" != "$(norm "$tag")" ]; then
      bad version-aligner "CHANGELOG.md latest entry is $log, git tag is $tag"
    fi
    if [ -n "$app" ] && [ "$(norm "$app")" != "$(norm "$tag")" ]; then
      bad version-aligner "application version is $app, git tag is $tag"
    fi
    [ -z "$app" ] && echo "no application version source (normal for a library), compared tag and CHANGELOG.md only"
  fi
fi

echo "== composer-peel status (read-only)"
out=$(composer-peel status --no-ansi 2>&1); rc=$?
echo "$out"
if ! grep -q 'Release state' <<<"$out"; then
  bad composer-peel "status did not report a release state (exit $rc)"
else
  # A library has no application version source; that alone is not a release blocker.
  issues=$(sed -n '/Issues:/,$p' <<<"$out" | grep -E '^[[:space:]]*- ' | grep -v 'No supported application version source was found' || true)
  if [ -n "$issues" ]; then
    while IFS= read -r i; do bad composer-peel "${i#*- }"; done <<<"$issues"
  elif [ "$rc" -ne 0 ]; then
    echo "not ready only because there is no application version source (normal for a library)"
  fi
fi

exit $fail
