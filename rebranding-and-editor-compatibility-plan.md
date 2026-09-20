# Edutech v1.0 — Rebranding and WordPress Editor Compatibility Plan

## Executive recommendation

A complete rebrand is feasible, but it must be carried out as a **controlled product fork and compatibility program**, not as a search-and-replace exercise.

The new public product brand is **Edutech v1.0**. The version suffix identifies this first rebranded product release; it remains separate from the internal plugin version and database migration version.

The plugin currently contains the original vendor name, website URLs, product name, `school-management` text domain, `WLSM_` PHP prefixes, `wlsm_` database and JavaScript identifiers, admin page slugs, translation files, documentation branding, CSS classes, AJAX actions, REST namespaces, and asset handles. Some of these are visible branding and can be replaced. Others are internal compatibility identifiers that should remain stable or be migrated carefully.

The recommended policy is:

> **Change the visible brand immediately, preserve internal identifiers during the transition, and introduce new namespaces with compatibility aliases rather than renaming the database and API in one release.**

This is the safest way to protect existing school data, saved shortcodes, translations, integrations, custom CSS, third-party extensions, and upgrade paths.

## Legal and ownership check before coding

The supplied archive includes GPL licensing material, but a full rebrand still requires an ownership and distribution review. Before publishing the rebranded product, confirm that the original license and any third-party licenses permit the intended redistribution, modification, and commercial packaging. Preserve required copyright and license notices for code that remains under those licenses.

Also verify that the new name, logo, icons, domain, and product descriptions do not conflict with existing trademarks. Remove the original vendor’s public branding only where the licensing and commercial rights permit it. Do not remove third-party license notices from bundled libraries.

The rebrand should have a written manifest of:

- Original product name and vendor.
- New product name and vendor.
- Code and assets being modified.
- Licenses retained.
- Third-party notices retained.
- New trademarks and domains.
- Migration and compatibility policy.

## Branding layers to change

### Public brand layer

The following should use the new brand:

- Plugin display name.
- Description.
- Public website and documentation URLs.
- Dashboard headings and logos.
- Frontend portal title.
- Login, email, SMS, receipt, invoice, certificate, and result branding.
- Admin notices shown to the designer.
- Elementor widget labels.
- Gutenberg block labels.
- Shortcode documentation.
- Help links and support URLs.
- Default email sender name and notification footer.
- Frontend CSS variables and design tokens.
- Favicon and application icons.
- Documentation screenshots and language.

### Internal compatibility layer

The following should **not** be renamed in the first release unless a migration layer is already available:

- Existing database table prefixes such as `wlsm_`.
- Existing stored option keys.
- Existing user-meta keys.
- Existing shortcode names.
- Existing AJAX action names.
- Existing REST namespace and routes used by mobile clients.
- Existing class names such as `WLSM_*`.
- Existing function names.
- Existing CSS selectors that customer themes may override.
- Existing translation text domain for stored translations.
- Existing scheduled-hook names.
- Existing plugin settings keys.

These identifiers are not normally visible to end users, but changing them can break installations.

### Compatibility aliases

Introduce the new namespace gradually:

```php
// New public namespace introduced by the Edutech compatibility layer.
class EDUTECH_Portal_Service {}

// Compatibility bridge during migration.
class_alias( 'EDUTECH_Portal_Service', 'WLSM_Portal_Service' );
```

For hooks and routes, register both old and new names where safe. For shortcodes, keep the old shortcode and add the new shortcode as an alias. For options and user metadata, read the new key first and fall back to the old key, then migrate lazily or through a controlled upgrade routine.

Do not use `class_alias()` blindly for classes with static initialization or side effects. Prefer explicit adapters where behavior differs.

## Proposed product identity

The rebranded product should use **Edutech v1.0** as its public platform brand and have a clear separation between:

- **Platform brand:** the education management product.
- **Module names:** Admissions, Attendance, Finance, Examinations, CBT, Curriculum, Transport, Library, and so on.
- **Country packs:** Nigeria Education Pack, Ghana Education Pack, or other supported configurations.
- **Integrations:** payment, SMS, email, video, and external examination providers.

Use a single configuration object for branding rather than hard-coded names throughout templates:

```php
$brand = WLSM_Brand::get();

// Example values returned by the brand service.
$brand['name']; // Edutech v1.0
$brand['short_name'];
$brand['logo_url'];
$brand['primary_color'];
$brand['support_url'];
$brand['email_from_name'];
```

The existing internal `WLSM_` namespace can remain in the first compatibility release. A later major version can introduce a new namespace if the product has a complete migration mechanism.

## WordPress upgrade compatibility

The plugin should support normal WordPress core upgrades by following WordPress extension conventions and testing against a compatibility matrix.

### Required compatibility rules

- Do not modify WordPress core files or database tables outside the plugin’s own tables and approved WordPress APIs.
- Use hooks and filters rather than overriding core templates or functions.
- Register activation, deactivation, uninstall, and upgrade routines safely.
- Use a versioned migration runner rather than performing all schema changes only on activation.
- Use `$wpdb->prepare()` for request-derived query values.
- Use capability checks and nonces for every mutation.
- Use `wp_enqueue_script()` and `wp_enqueue_style()` with versioned asset handles.
- Avoid loading plugin assets on unrelated pages.
- Use `wp_add_inline_script()` or `wp_localize_script()` for configuration rather than injecting unsafe inline JavaScript.
- Use `wp_safe_redirect()` for redirects.
- Use WordPress internationalization functions consistently.
- Avoid deprecated WordPress APIs and test deprecation logs.
- Do not assume a specific theme, page builder, permalink structure, or PHP minor version.
- Avoid fatal errors when optional integrations are inactive or unavailable.
- Check that REST routes provide explicit permission callbacks.
- Keep all scheduled hooks idempotent.
- Make uninstall data deletion explicit and opt-in.

### Support matrix

Before release, define and test a matrix similar to:

| Component | Minimum | Tested current | Tested latest |
|---|---:|---:|---:|
| WordPress | Supported minimum | Yes | Yes |
| PHP | Supported minimum | Yes | Yes |
| MySQL/MariaDB | Supported minimum | Yes | Yes |
| Gutenberg plugin | Supported minimum if used | Yes | Yes |
| Elementor | Supported minimum if used | Yes | Yes |
| Default theme | One supported baseline | Yes | Yes |
| Multisite | Explicitly supported or excluded | Yes if supported | Yes if supported |

The exact version numbers should be chosen after reviewing the actual production hosting requirements. The important point is to publish a support policy and test it continuously.

### Upgrade testing

Each release should be tested from:

- Fresh installation.
- Previous supported plugin release.
- At least two older migration states.
- A large school with many students and invoices.
- Multiple schools with separate settings.
- Active scheduled jobs.
- Existing shortcodes and saved pages.
- Existing translations.
- Existing REST/mobile clients.
- Active payment and notification integrations.

The test should verify that database records, user accounts, custom roles, settings, scheduled events, uploaded files, and generated documents remain usable.

## Elementor compatibility

The current plugin appears to rely mainly on shortcodes and does not expose a dedicated Elementor widget library. Elementor can render many shortcode-based frontend pages, but the plugin should add deliberate Elementor support rather than assuming compatibility.

### Recommended Elementor integration

Create an optional Elementor integration module containing:

- Login widget.
- Student dashboard widget.
- Parent dashboard widget.
- Teacher dashboard widget.
- School-admin portal widget.
- Student registration widget.
- Inquiry form widget.
- Exam timetable widget.
- Exam result widget.
- Fee invoice widget.
- Noticeboard widget.
- CBT launch widget.
- CBT practice widget.
- School selector or campus selector widget.

Each widget should:

- Check whether Elementor is active before loading.
- Register only after Elementor’s widgets-loaded hook.
- Use Elementor controls for title, alignment, visibility, school context, and display options.
- Render through the same frontend services and permissions as the normal portal.
- Avoid duplicating business logic inside widget classes.
- Support editor preview without exposing private student data.
- Display a safe placeholder in the editor when no authenticated user exists.
- Avoid fatal errors if Elementor is deactivated.
- Declare asset dependencies properly.
- Work with Elementor’s responsive controls and theme styles.

### Elementor-specific safety rules

Do not use Elementor editor preview as proof of authorization. Preview mode is not a student session. Do not render private invoices, grades, attendance, or child data to an unauthenticated editor preview.

Use a public placeholder such as “Student dashboard appears to logged-in students” in editor mode. Render real data only in the authenticated frontend portal.

Avoid depending on Elementor’s internal classes unless there is no stable public hook. Keep the integration in a separate adapter file so Elementor updates do not affect the core plugin.

## Gutenberg compatibility

The plugin should support Gutenberg through a combination of dynamic blocks and normal WordPress shortcodes.

### Recommended blocks

Register blocks for:

- Login form.
- Student dashboard.
- Parent dashboard.
- Teacher dashboard.
- School administrator dashboard.
- Student registration.
- Inquiry form.
- Exam timetable.
- Exam result lookup.
- Noticeboard.
- Fee invoices.
- Payment history.
- CBT practice launcher.
- Official CBT launch screen.
- School branding header.

Use dynamic server-rendered blocks for authenticated or data-sensitive content. Do not store private data in post content or block attributes.

### Block requirements

Each block should define:

- A unique new block name.
- `block.json` metadata.
- Editor label and icon.
- Server render callback.
- Editor placeholder and preview behavior.
- `supports` configuration for alignment and spacing where appropriate.
- Script and style dependencies.
- Translation support.
- Graceful fallback when the plugin module is inactive.
- Capability and authentication checks at render time.

The block editor should never embed a student’s real grades or parent’s private finance data into post content. Store only configuration such as display options and module keys.

### Gutenberg editor UX

The editor should provide a clear configuration panel, but sensitive records should appear only on the frontend. In the editor, show a representative placeholder or an administrator-only preview with explicit mock data. Never rely on hidden HTML or CSS to protect private data.

## Theme and page-builder independence

The portal should work with:

- Classic themes.
- Block themes.
- Elementor themes.
- Child themes.
- Full Site Editing templates.
- Mobile layouts.
- Right-to-left languages.

The plugin should provide a minimal template wrapper, but allow the active theme to control the outer header and footer when configured. Avoid forcing a full page template that removes the theme’s normal styles unless the user explicitly enables a standalone portal layout.

Provide three display modes:

1. **Embedded mode:** portal renders inside the active theme content area.
2. **Full-width mode:** portal uses the theme but expands the content container.
3. **Standalone app mode:** portal renders its own branded shell for schools that want a controlled application interface.

## Branding database and settings migration

Add a brand settings object at the network or school level:

- Product name.
- Short name.
- Logo.
- Login logo.
- Favicon.
- Primary and secondary colors.
- Typography choices.
- Email sender name.
- Support URL.
- Documentation URL.
- Footer text.
- Certificate and report branding.
- Mobile app or portal label.

Existing school settings should be mapped into the new brand object without deleting the legacy keys. When a new value is empty, the system should fall back to the old value or safe default.

## Translation and text-domain strategy

The plugin has a substantial translation directory. A text-domain rename is a compatibility-sensitive operation.

Recommended sequence:

1. Keep `school-management` as the source text domain in the first rebrand release.
2. Replace visible source strings with the new brand where appropriate.
3. Generate new translation templates for the new product.
4. Provide a migration or fallback loader for existing translations.
5. Introduce a new text domain only in a major version with a documented translation migration path.
6. Ensure all new Elementor widgets and Gutenberg blocks use the chosen text domain.

Do not change the text domain in only some files. Mixed domains cause missing translations and inconsistent UI language.

## Asset and CSS migration

The current assets use handles, classes, and file names tied to the legacy product. Do not rename all handles and selectors at once.

Use a dual-period strategy:

- Keep existing asset handles and selectors as compatibility aliases.
- Add new CSS variables and new component classes for the rebranded design system.
- Load legacy CSS only for legacy pages during transition.
- Scope frontend application styles under a stable root such as `.edutech-portal`.
- Avoid global rules that override Elementor or Gutenberg styles.
- Avoid changing Bootstrap or theme-wide selectors from the plugin.
- Version all assets so browser caches do not retain old branding.

## Package and module architecture

The rebrand should align with the modular product plan:

- Core platform.
- Frontend portal.
- Student and parent portal.
- Teacher and staff portal.
- School administration portal.
- Examination engine.
- CBT engine.
- Curriculum and grading engine.
- Finance and payments.
- Country packs.
- Elementor integration.
- Gutenberg integration.
- External integrations.

Elementor and Gutenberg support should be optional integration modules that depend on the core frontend portal. The plugin should continue to work if either editor plugin is inactive.

## Release and rollback process

Every rebrand release should include:

- Database backup recommendation and automated preflight check.
- Migration version and rollback notes.
- List of renamed visible assets.
- Compatibility aliases.
- Translation changes.
- Elementor and Gutenberg test results.
- WordPress/PHP support matrix.
- Known limitations.
- Upgrade instructions.
- Emergency disable flag for new modules.

Use feature flags for the new frontend portal, new brand shell, Elementor widgets, Gutenberg blocks, and major module migrations. A designer should be able to disable a new module without disabling core student or school records.

## Recommended release sequence

### Release 1 — compatibility foundation

- Introduce the new brand settings object.
- Add the new logo, colors, and frontend title.
- Preserve all internal `WLSM_`, `wlsm_`, shortcode, AJAX, REST, option, and translation identifiers.
- Add basic WordPress upgrade tests.
- Add a new scoped frontend portal CSS layer.

### Release 2 — frontend portal and access controls

- Introduce the frontend login and role dashboards.
- Restrict operational users from WordPress admin.
- Add stable portal routes and centralized permission checks.
- Preserve legacy pages as controlled fallback during migration.

### Release 3 — Gutenberg integration

- Add dynamic blocks for the login, portal, reports, and CBT launchers.
- Add editor-safe placeholders.
- Test with block themes, classic themes, and Full Site Editing.

### Release 4 — Elementor integration

- Add dedicated widgets.
- Test Elementor editor preview, responsive controls, caching, and theme compatibility.
- Keep widgets in an optional adapter module.

### Release 5 — internal namespace migration if justified

Only after sufficient adoption should the project consider renaming internal classes or database identifiers. If this is done, ship compatibility wrappers, database migration tooling, deprecated aliases, and a long support period.

## Acceptance criteria

The rebranded plugin is ready for production when:

1. The new public brand is shown consistently across frontend screens, emails, reports, certificates, documentation, and editor integrations.
2. Required license and third-party notices remain intact.
3. Existing installations upgrade without losing users, schools, settings, files, results, invoices, or permissions.
4. Existing shortcodes, REST clients, AJAX adapters, and scheduled jobs continue to work during migration.
5. The plugin works with the supported WordPress and PHP versions.
6. The plugin does not fatal-error when Elementor or Gutenberg is absent.
7. Gutenberg blocks render safely in editor and frontend contexts.
8. Elementor widgets render safely in editor preview and authenticated frontend contexts.
9. Private student, parent, teacher, and finance data is never embedded into editor content or unauthenticated previews.
10. Plugin assets do not globally break themes, Elementor layouts, or Gutenberg editor styles.
11. Translations remain functional and new strings are included in the translation workflow.
12. New and legacy branding can be rolled back through a controlled feature flag.
13. Database migrations are idempotent, logged, and tested on upgraded installations.
14. A clean release archive can be built from the locked dependency set.
15. Automated regression tests cover authentication, roles, school scope, REST, frontend rendering, blocks, widgets, and critical forms.

## Final recommendation

Complete rebranding is a good strategic move, especially because the requested product is expanding into a frontend-first, multi-school, multi-country education platform. However, the rebrand should be performed at the **product and presentation layer first**. Preserve internal identifiers until a mature compatibility layer exists.

WordPress, Elementor, and Gutenberg support should be treated as explicit compatibility targets. The plugin should integrate with them through documented hooks, dynamic blocks, optional Elementor widgets, scoped assets, and safe editor placeholders. It should never assume that a page builder can safely render private education data in the editor.

The safest product strategy is:

> **New brand outside, stable compatibility layer inside, modular integrations around the core.**

## References

[1]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/school-management.php "Current plugin header and bootstrap"
[2]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/public.php "Current public hooks and frontend asset loading"
[3]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/inc/WLSM_Shortcode.php "Current shortcode-based frontend integration"
[4]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/languages/school-management.pot "Current translation template"
[5]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/WLSM_Menu.php "Current WordPress admin menus and assets"
[6]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/composer.lock "Bundled dependency lock file"
