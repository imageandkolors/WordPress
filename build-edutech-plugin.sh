#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$ROOT_DIR/project/school-management-pro"
RELEASE_DIR="$ROOT_DIR/release"
PACKAGE_NAME="edutech-v1.0.0"
STAGE_DIR="$RELEASE_DIR/$PACKAGE_NAME"
ZIP_PATH="$RELEASE_DIR/$PACKAGE_NAME.zip"

command -v zip >/dev/null 2>&1 || { echo "zip is required" >&2; exit 1; }

rm -rf "$STAGE_DIR" "$ZIP_PATH"
mkdir -p "$STAGE_DIR"

# Copy the complete plugin, including bundled runtime dependencies.
cp -a "$PLUGIN_DIR/." "$STAGE_DIR/"

# Remove development-only and editor artifacts from the installable package.
find "$STAGE_DIR" -type f \( -name '*.log' -o -name '*.tmp' -o -name '*~' \) -delete
find "$STAGE_DIR" -type d \( -name '.git' -o -name '.github' \) -prune -exec rm -rf {} +

# Validate every first-party PHP file before packaging when PHP is available.
# The build remains usable for source packaging in environments without PHP;
# CI and release runners should install PHP and treat the validation as required.
if command -v php >/dev/null 2>&1; then
  while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null
  done < <(find "$STAGE_DIR" -type f -name '*.php' -not -path '*/includes/vendor/*' -print0)
else
  echo "WARNING: php not found; PHP syntax validation was skipped."
fi

# Require the plugin entry point and public version marker.
test -f "$STAGE_DIR/school-management.php"
grep -q "Plugin Name: Edutech v1.0" "$STAGE_DIR/school-management.php"
grep -q "EDUTECH_VERSION" "$STAGE_DIR/school-management.php"

cat > "$STAGE_DIR/EDUTECH-MANIFEST.json" <<EOF
{
  "product": "Edutech v1.0",
  "package": "$PACKAGE_NAME",
  "plugin_version": "1.0.0",
  "database_version": "1.0.0",
  "module_registry_version": "1.0.0",
  "legacy_compatibility_version": "10.7.1",
  "entry_point": "school-management.php",
  "legacy_identifiers_preserved": true,
  "build_timestamp_utc": "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
}
EOF

(
  cd "$RELEASE_DIR"
  zip -qr "$ZIP_PATH" "$PACKAGE_NAME"
)
sha256sum "$ZIP_PATH" > "$ZIP_PATH.sha256"
rm -rf "$STAGE_DIR"

echo "Built: $ZIP_PATH"
echo "Checksum: $ZIP_PATH.sha256"
