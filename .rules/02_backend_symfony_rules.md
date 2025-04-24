# SPECIFIC RULES: SYMFONY BACKEND (SIGE FJKM)

## Architecture & Conventions
1.  **API Platform Usage:** Prefer API Platform for standard CRUD operations on Doctrine entities if it meets the requirements. Configure serialization groups and validation effectively.
2.  **Manual REST Controllers:** For non-standard actions or where API Platform is insufficient, use dedicated controllers (suffixed `ApiController`) returning `JsonResponse`. Keep controllers thin.
3.  **Service Layer:** Implement business logic (beyond simple CRUD) in dedicated Service classes (e.g., `MemberManager`, `TransactionProcessor`, `ReportGenerator`). Inject services into controllers via Dependency Injection.
4.  **DTOs (Data Transfer Objects):** **MANDATORY** for API request bodies (POST, PUT, PATCH). Use DTOs to decouple the API from Doctrine entities and for validation purposes. Use `symfony/validator` on DTOs. **Do NOT** use Doctrine entities directly as input for write operations. Prefer `#[MapRequestPayload]` for controller argument resolution.
5.  **Validation:** Utilize `symfony/validator`. Apply validation constraints primarily on DTOs for input validation, and also on Doctrine Entities for data integrity guarantees (useful for CLI commands, fixtures, etc.).
6.  **API Responses:** Ensure consistent JSON response structures. If not using API Platform's standards (Hydra/JSON:API), consider a custom format like `{ "success": true/false, "data": [...], "error": { ... } }`. Use Symfony Serializer Component with Serialization Groups (e.g., 'user:read', 'member:list', 'member:detail') for controlling output data.
7.  **Security (`symfony/security`):**
    *   **Authentication:** Use JWT (e.g., `lexik/jwt-authentication-bundle` or API Platform's built-in JWT support). Endpoints: `/login` (likely custom), `/api/me`.
    *   **Authorization:** Use roles mapped from `system_roles` in `security.yaml`. Implement custom **Voters** for fine-grained access control (e.g., a Group Treasurer can only access their group's accounts). Deny access by default (`access_control` rules).
    *   **Password Hashing:** Use Symfony's built-in password hashers configured in `security.yaml`.
8.  **Error Handling:** Implement custom Exception Listeners to catch application exceptions (ValidationFailed, Domain specific, NotFoundHttpException, AccessDeniedHttpException) and return standardized JSON error responses suitable for an API (e.g., respecting RFC 7807 Problem Details or a simpler structure). Log detailed errors server-side only (never expose stack traces in production API responses).
9.  **`FinancialAccount.current_balance` Maintenance:** This **MUST** be handled reliably, preferably via a **Doctrine Event Listener** (listening to `FinancialTransaction` entity changes: `postPersist`, `postUpdate`, `postRemove`). The listener should recalculate or adjust the related `FinancialAccount` balance within a transaction to ensure atomicity and prevent race conditions. Alternatively, use a dedicated service called reliably after transaction persistence.

## Doctrine ORM
10. **Entities:** Create Doctrine entities mapping precisely to the tables defined in `03_database_rules.md`. Adhere strictly to column names, data types (using `Doctrine\DBAL\Types\Types`), and relations. Use PHP 8+ Attributes for mapping.
11. **Relations:** Define Doctrine relationships (`ManyToOne`, `OneToOne`, `OneToMany`, `ManyToMany`) correctly, including `inversedBy`/`mappedBy`, fetch modes (use `EXTRA_LAZY` where appropriate), and cascade options (use `cascade={"persist", "remove"}` with extreme caution). Pay attention to `orphanRemoval`. Initialize collection properties (e.g., `new ArrayCollection()`) in the constructor.
12. **Repositories:** Create custom Repository classes extending `Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository`. Use these for any non-trivial database queries (using DQL or Query Builder). Keep query logic out of Controllers and Services. Method names should be descriptive (e.g., `findActiveMembers()`, `sumIncomeByCategoryForPeriod()`).
13. **Migrations:** **MANDATORY** use of `doctrine/migrations-bundle` for ALL schema changes after the initial setup. Generate migrations using `bin/console doctrine:migrations:diff` and review/adjust them carefully before applying (`bin/console doctrine:migrations:migrate`).

## Quality & Testing
14. **Coding Standard:** Strictly follow PSR-12 (enforce with `php-cs-fixer` or `ecs`).
15. **Strict Typing:** Enable strict types (`declare(strict_types=1);`) in all PHP files. Use PHP 8+ type hints for properties, method arguments, and return types (including union/intersection/readonly properties where appropriate).
16. **Testing:**
    *   **Unit Tests (PHPUnit):** Cover critical business logic in Services, DTOs, and complex Entities. Mock dependencies effectively (e.g., using `Mockery` or PHPUnit's built-in mocks).
    *   **Integration/API Tests (Symfony Functional Tests / API Platform Testing):** Test every API endpoint extensively. Use `Symfony\Bundle\FrameworkBundle\Test\WebTestCase` or API Platform's specific testing tools. Create authenticated clients. Assert status codes (2xx, 4xx, 5xx). Assert JSON response structure and data using `assertJsonContains` or similar. Test validation rules, permissions, and edge cases. Test database interactions where relevant.