flutter clean
Remove-Item -Recurse -Force .dart_tool, build, android/build, windows/build, web/build -ErrorAction SilentlyContinue
flutter pub get
