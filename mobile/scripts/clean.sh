#!/usr/bin/env bash
set -euo pipefail
flutter clean
rm -rf .dart_tool build android/build windows/build web/build
flutter pub get
