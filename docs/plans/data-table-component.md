# Plan: Extract Reusable DataTable Component

**Status: DONE**

## Files

| Action | File |
|--------|------|
| Create | `resources/js/views/Common/DataTable.vue` |
| Modify | `resources/js/views/Users/Users.vue` |
| Create | `docs/plans/data-table-component.md` |

## Steps

1. Create `DataTable.vue` — slot-based wrapper with loading skeleton, empty state, table, and pagination
2. Refactor `Users.vue` to use `<DataTable>` instead of inline table markup
