# PROJECT CONTEXT: FJKM SIGE - Backend API (Symfony)

## 1. General Objective
This project is the **Backend API** for the FJKM Church Integrated Management System (SIGE). Its primary role is to provide a secure, robust, and well-documented RESTful API based on the project's functional requirements and database schema. This API will be consumed by a separate Frontend application (Nuxt.js/Vue.js).

## 2. Application Domain
The SIGE manages administrative, financial, and asset information for an FJKM (Church of Jesus Christ in Madagascar) Fitandremana (Parish). Key concepts include:
*   `Members` (Mpino, Katekomena) with FJKM milestones (Baptism, Confirmation).
*   `Households` (Tokantrano).
*   `Groups` (Sampana like SA, SLK, Dorkasy) with leadership (Bureau) and memberships.
*   `FinancialAccounts` (Church vs. Group accounts).
*   `FinancialTransactions` (Income: Rakitra, Adidy; Expenses) categorized according to FJKM practices.
*   `Assets` (Fitaovana: materials, equipment, real estate) owned by Church or Groups.
*   `SystemUsers` & `SystemRoles` for application access control aligned with FJKM responsibilities.

## 3. Technology Stack (Backend Focus)
*   **Framework:** Symfony (Target Version: 7.x or latest stable).
*   **API Style:** RESTful API. Usage of **API Platform** is preferred for standard CRUD operations; manual controllers for custom actions.
*   **Language:** PHP 8.2+. Strict typing is enforced.
*   **ORM:** Doctrine. Mapping MUST precisely follow `03_database_rules.md`.
*   **Database:** PostgreSQL (preferred) or MySQL/MariaDB.
*   **Authentication:** JWT (LexikJWTAuthenticationBundle or API Platform JWT).
*   **Environment:** Docker for local development. Deployment on Linux server (Nginx/Caddy + PHP-FPM + DB).

## 4. Architecture (Backend Focus)
*   **API-Centric:** This project solely provides API endpoints. No HTML rendering.
*   **Service Layer:** Business logic resides in dedicated services. Controllers are thin.
*   **DTOs:** Mandatory for API inputs (write operations) via `#[MapRequestPayload]` or similar. Validation occurs on DTOs.
*   **Doctrine:** Entities map to the DB schema. Repositories handle custom queries. Listeners handle cross-cutting concerns (e.g., balance updates).
*   **Security:** Handled by Symfony Security component (Firewalls, Roles, Voters).
*   **Error Handling:** Custom Exception Listeners for standardized JSON error responses.

## 5. Languages & Communication
*   **Source Code & Comments:** Strictly **ENGLISH**.
*   **API Responses:** Data is typically primary data; strings within data are usually Malagasy or user-input. Error messages can be English or use i18n keys for the frontend to translate.
*   **Git Commits:** **ENGLISH**, following Conventional Commits.
*   **Frontend Interaction:** This backend serves a separate Nuxt.js/Vue.js frontend application. API design should consider frontend needs for data and structure.

## 6. Reference Documents
The primary references for development are the files within this `.rules` directory, especially:
*   `02_backend_symfony_rules.md`: Symfony specific coding standards and practices.
*   `03_database_rules.md`: The authoritative database schema definition.
*   `04_coding_style_general.md`: General coding style rules.