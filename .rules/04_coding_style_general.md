# GENERAL RULES: CODING STYLE & PRACTICES (SIGE FJKM)

## 1. Languages
*   **Source Code:** Comments, variable/function/class names (excluding the DB schema) MUST be in **ENGLISH**.
*   **User Interface (UI):** Visible text strings MUST be handled via i18n, with **Malagasy (mg)** as the default/primary language and French (fr) optional. This rule primarily impacts the Frontend project.
*   **Git Commit Messages:** Write commit messages in **ENGLISH**. Strictly follow the **Conventional Commits** format (e.g., `feat: ...`, `fix: ...`, `refactor: ...`, `docs: ...`, `test: ...`, `chore: ...`). Include scope if applicable (e.g., `feat(API): ...`, `fix(Members): ...`).

## 2. Comments & Documentation
*   Write clear, concise comments in **ENGLISH** to explain *why* code is written a certain way (complex logic, design choices), not *what* the code does (which should be self-evident).
*   Use PHPDoc (`/** ... */`) for PHP classes, methods, properties.
*   Use JSDoc/TSDoc for JavaScript/TypeScript functions, classes, types.
*   Maintain an up-to-date `README.md` at the project root explaining the project purpose, setup instructions (local development), and common commands relevant to the specific project (Backend or Frontend).

## 3. Version Control (Git)
*   Use a standard Git branching workflow (e.g., a simplified Gitflow: `main`, `develop`, feature branches `feature/xxx`, fix branches `fix/xxx`). Adapt if using a different flow like trunk-based development with feature flags.
*   **NEVER** commit directly to `main` or `develop`.
*   Use Pull Requests (PRs) / Merge Requests (MRs) for merging code into `develop` (and subsequently to `main`). Code reviews are strongly encouraged for PRs/MRs.
*   Make small, atomic commits. Each commit should represent a single logical change. Ensure commit messages are clear and follow the Conventional Commits standard.

## 4. General Principles
*   **DRY (Don't Repeat Yourself):** Avoid code duplication. Use reusable functions, services, components, composables, traits, etc., *within the scope of the project (backend or frontend)*. Some necessary duplication might exist between backend DTOs and frontend Types, manage this carefully.
*   **KISS (Keep It Simple, Stupid):** Favor simple, straightforward, and readable solutions over overly complex ones.
*   **SOLID (Backend Focus):** Apply SOLID principles (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion) particularly in the Symfony backend services and classes to enhance testability, maintainability, and flexibility. Frontend can also benefit from SRP and DI concepts.