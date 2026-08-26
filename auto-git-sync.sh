#!/bin/bash

while true
do
  echo "Checking Git..."

  # Kalau tidak ada perubahan lokal, tarik update remote
  if git diff --quiet && git diff --cached --quiet; then
    git pull --rebase origin main
  else
    # Simpan semua perubahan lokal
    git add -A

    # Commit hanya kalau memang ada perubahan
    git commit -m "Auto sync $(date '+%Y-%m-%d %H:%M:%S')" || true

    # Ambil update remote sebelum push
    git pull --rebase origin main

    # Push ke GitHub
    git push origin main
  fi

  sleep 60
done