# Matching Settings

The administrator page is `/admin/matching-settings`. It displays **Income Categories first, followed by Fields of Study**, on one page. The old `/admin/income-categories` URL still opens the combined page. Existing income actions remain available.

## Fields of Study

Use **Add Field** to create a shared choice. **Edit** can rename a field or change Available for new selections. Names are required, limited to 255 characters and unique, including case-insensitive duplicates. Field validation uses a separate error bag; invalid submissions reopen the field editor with the entered values.

- Active fields appear in new student academic and scholarship-rule selections.
- Inactive fields are hidden from new assignments. Existing students retain their recorded field, remain ready for matching and may save it unchanged. Existing scholarship restrictions can also retain their inactive fields.
- Filters and the non-persistent admin sample preview can still inspect known inactive fields. Deactivation does not silently remove scholarship restrictions or invalidate existing students.
- Renaming updates academic records, legacy profile fields, legacy single-field rules and current multi-field JSON restrictions in one transaction. Recommendation caches are invalidated afterward. Historical recommendation logs keep their original snapshot until the usual evaluation workflow creates/updates a result.
- There is no permanent field-delete action. Use inactive status to preserve references.

The student form, admin scholarship/rules forms, sample preview, field filter, validation and academic readiness read the same registry. Configured field names are escaped when displayed.

## Database and seeding

Migration `2026_11_07_000000_create_study_fields_table` adds `study_fields` with:

| Column | Meaning |
|---|---|
| id | Primary key for managing a field. |
| name | Current unique label used by existing student/rule string references. |
| seed_name | Nullable unique original default label, retained internally when a default field is renamed. |
| is_active | Whether this field is available for new assignments. |
| created_at / updated_at | Creation/update timestamps. |

Migration initializes the existing 21 default choices and registers any additional used labels from academic/profile/rule data without rewriting those records. Existing field columns stay strings; this release does not introduce field-ID foreign keys.

`config/scholarship.php` now supplies initial defaults only. StudyFieldSeeder adds missing defaults without resetting administrator renames or active status. Bundled demo rule seeding resolves original labels through seed_name so it respects default-field renames. Custom field settings are database data, not changes automatically shared by Git.

On another existing device, pull the source changes and run `php artisan migrate`, then build assets. Do not reset its database or run account seeders merely to enable this page. Rollback refuses to discard custom, renamed or inactive registry entries until they are preserved.

SQLite transfer includes the field registry and preserves IDs, labels, original seed names and active status. Both source and target require matching completed migrations. The target's application tables must be empty; its unused migration-created field defaults may be replaced within the verified import transaction. A destination with edited/custom field settings is refused. Keep archival backups intact and migrate a working source database before taking a new transfer snapshot.

## Verification

The implementation is covered by admin-only access, duplicate/malformed input, linked rename/matching, active/inactive assignment, reseeding and MySQL transfer tests. Browser verification uses synthetic users in a separate database. See improve.md for current results and the screenshot.
