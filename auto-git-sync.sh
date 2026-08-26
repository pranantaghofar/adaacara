#!/bin/bash

BRANCH="main"
INTERVAL=50

while true
do
  echo "Checking Git..."

  # Ambil info terbaru dari GitHub
  git fetch origin "$BRANCH"

  # Kalau working tree bersih, tarik perubahan remote
  if git diff --quiet && git diff --cached --quiet; then
    LOCAL=$(git rev-parse HEAD)
    REMOTE=$(git rev-parse origin/$BRANCH)

    if [ "$LOCAL" != "$REMOTE" ]; then
      echo "Remote update found, pulling..."
      git pull --rebase origin "$BRANCH"
    else
      echo "Already up to date."
    fi

  # Kalau ada perubahan lokal
  else
    echo "Local changes found..."

    git add -A

    git commit -m "Auto sync $(date '+%Y-%m-%d %H:%M:%S')" || true

    echo "Pulling remote before push..."
    git pull --rebase origin "$BRANCH"

    echo "Pushing..."
    git push origin "$BRANCH"
  fi

  echo "sleep $INTERVAL"
  sleep "$INTERVAL"
done