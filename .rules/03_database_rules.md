# SPECIFIC RULES: DATABASE (SIGE FJKM)

## 1. Schema Definition - The Single Source of Truth

The following SQL statements define the **required** database schema for the SIGE FJKM project. All backend code (especially Doctrine Entities) **MUST** map precisely to this structure. Naming convention: English, `lowercase`, `snake_case`. Target SGBD: PostgreSQL preferred, MySQL/MariaDB compatible.

```sql
-- ==========================================================================
-- Schema BDD SIGE Fitandremana FJKM - Version 4.0 / 2025-04-23
-- Convention: English, lowercase, snake_case
-- SGBD Target: PostgreSQL / MySQL (compatible syntax)
-- ==========================================================================

-- --------------------------------------------------------------------------
-- Table: system_users
-- Description: System application users
-- --------------------------------------------------------------------------
CREATE TABLE system_users (
    id SERIAL PRIMARY KEY,
    login_name VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE,
    is_active BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL
);

COMMENT ON TABLE system_users IS 'Utilisateurs pouvant se connecter au système SIGE.';
COMMENT ON COLUMN system_users.login_name IS 'Nom de connexion unique.';
COMMENT ON COLUMN system_users.password_hash IS 'Hash sécurisé du mot de passe.';
COMMENT ON COLUMN system_users.is_active IS 'Indique si le compte est actif (true) ou désactivé (false).';
COMMENT ON COLUMN system_users.created_by_user_id IS 'Utilisateur ayant créé cet enregistrement.';
COMMENT ON COLUMN system_users.updated_by_user_id IS 'Utilisateur ayant modifié cet enregistrement en dernier.';

-- --------------------------------------------------------------------------
-- Table: system_roles
-- Description: User roles within the application
-- --------------------------------------------------------------------------
CREATE TABLE system_roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

COMMENT ON TABLE system_roles IS 'Définit les rôles système (Admin, Trésorier, etc.).';
COMMENT ON COLUMN system_roles.role_name IS 'Nom technique unique du rôle (ex: ROLE_CHURCH_TREASURER, ROLE_ADMIN). Must start with ROLE_ for Symfony Security.';

-- --------------------------------------------------------------------------
-- Table: user_roles
-- Description: Junction table for users and roles (Many-to-Many)
-- --------------------------------------------------------------------------
CREATE TABLE user_roles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES system_users(id) ON DELETE CASCADE,
    role_id INTEGER NOT NULL REFERENCES system_roles(id) ON DELETE CASCADE,
    assigned_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    assigned_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    UNIQUE (user_id, role_id)
);

COMMENT ON TABLE user_roles IS 'Associe les utilisateurs à leurs rôles.';

-- --------------------------------------------------------------------------
-- Table: households
-- Description: Households or family units
-- --------------------------------------------------------------------------
CREATE TABLE households (
    id SERIAL PRIMARY KEY,
    head_of_household_member_id INTEGER UNIQUE, -- FK to members.id (Added via ALTER TABLE after 'members' creation)
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL
);

COMMENT ON TABLE households IS 'Représente les unités familiales / Tokantrano.';
COMMENT ON COLUMN households.head_of_household_member_id IS 'Membre désigné comme chef de ce foyer (Facultatif).';

-- --------------------------------------------------------------------------
-- Table: members
-- Description: Members and all registered individuals
-- --------------------------------------------------------------------------
CREATE TABLE members (
    id SERIAL PRIMARY KEY,
    -- Identity
    last_name VARCHAR(100) NOT NULL,
    first_names VARCHAR(150),
    gender VARCHAR(10) CHECK (gender IN ('MALE', 'FEMALE', 'UNKNOWN')),
    birth_date DATE,
    birth_place VARCHAR(150),
    address TEXT,
    fokontany VARCHAR(100),
    phone_fixed VARCHAR(20),
    phone_mobile VARCHAR(20),
    email VARCHAR(100),
    photo_path VARCHAR(255),
    -- Civil Status
    marital_status VARCHAR(20) CHECK (marital_status IN ('SINGLE', 'MARRIED', 'WIDOWED', 'DIVORCED', 'UNKNOWN')),
    spouse_member_id INTEGER REFERENCES members(id) ON DELETE SET NULL,
    national_id_number VARCHAR(20),
    -- FJKM Status & Milestones
    fjkm_status VARCHAR(20) NOT NULL CHECK (fjkm_status IN ('CATECHUMEN', 'RECEIVED_MEMBER', 'CHILD_MEMBER', 'TRANSFERRED_OUT', 'DECEASED', 'LEFT', 'UNKNOWN')) DEFAULT 'UNKNOWN',
    catechumen_start_date DATE,
    baptism_date DATE,
    baptism_place VARCHAR(150),
    baptism_pastor_name VARCHAR(150),
    confirmation_date DATE,
    confirmation_place VARCHAR(150),
    confirmation_pastor_name VARCHAR(150),
    fjkm_marriage_date DATE,
    fjkm_marriage_place VARCHAR(150),
    fjkm_marriage_pastor_name VARCHAR(150),
    -- Transfers
    transfer_out_date DATE,
    transfer_out_destination VARCHAR(200),
    transfer_in_date DATE,
    transfer_in_origin VARCHAR(200),
    -- Household Link
    household_id INTEGER REFERENCES households(id) ON DELETE SET NULL,
    -- Notes
    notes TEXT,
    -- Record Status
    is_active BOOLEAN DEFAULT true,
    -- Audit
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    -- Indexes
    INDEX idx_member_last_name (last_name),
    INDEX idx_member_fjkm_status (fjkm_status),
    INDEX idx_member_household_id (household_id)
);

COMMENT ON TABLE members IS 'Table centrale pour les informations des membres (Mpino, Katekomena...).';
COMMENT ON COLUMN members.fjkm_status IS 'Statut FJKM actuel de la personne.';
COMMENT ON COLUMN members.confirmation_date IS 'Date Fandraisana ny Fanasan''ny Tompo.';
COMMENT ON COLUMN members.household_id IS 'Lien vers le Tokantrano.';

-- Add FK Constraint to households after members table exists
-- NB: Execute this manually or in a later migration script
-- ALTER TABLE households ADD CONSTRAINT fk_household_head FOREIGN KEY (head_of_household_member_id) REFERENCES members(id) ON DELETE SET NULL;

-- --------------------------------------------------------------------------
-- Table: groups
-- Description: Church groups (Sampana, Choir...)
-- --------------------------------------------------------------------------
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    group_name VARCHAR(150) NOT NULL UNIQUE,
    group_acronym VARCHAR(20) UNIQUE,
    description TEXT,
    establishment_date DATE,
    group_status VARCHAR(20) DEFAULT 'ACTIVE' CHECK (group_status IN ('ACTIVE', 'INACTIVE', 'DISSOLVED')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL
);

COMMENT ON TABLE groups IS 'Définit les différents groupes (Sampana, Antoko Mpihira...).';

-- --------------------------------------------------------------------------
-- Table: member_group_memberships
-- Description: Members belonging to groups (Many-to-Many)
-- --------------------------------------------------------------------------
CREATE TABLE member_group_memberships (
    id SERIAL PRIMARY KEY,
    member_id INTEGER NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    join_date DATE,
    leave_date DATE,
    role_in_group TEXT,
    is_active_membership BOOLEAN DEFAULT true,
    UNIQUE (member_id, group_id, is_active_membership),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL
);

COMMENT ON TABLE member_group_memberships IS 'Table de liaison Membres-Groupes.';
COMMENT ON COLUMN member_group_memberships.is_active_membership IS 'Indique si l''appartenance au groupe est active.';

-- --------------------------------------------------------------------------
-- Table: member_roles_mandates
-- Description: Member roles and mandates (Deacon, Board...)
-- --------------------------------------------------------------------------
CREATE TABLE member_roles_mandates (
    id SERIAL PRIMARY KEY,
    member_id INTEGER NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    role_title VARCHAR(150) NOT NULL,
    group_id INTEGER REFERENCES groups(id) ON DELETE SET NULL,
    start_date DATE NOT NULL,
    planned_end_date DATE,
    actual_end_date DATE,
    is_current BOOLEAN DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    INDEX idx_mandate_member_id (member_id),
    INDEX idx_mandate_group_id (group_id)
);

COMMENT ON TABLE member_roles_mandates IS 'Attribution de rôles/mandats FJKM (Diakona, Biraona, Bureau Sampana) aux membres.';
COMMENT ON COLUMN member_roles_mandates.role_title IS 'Titre du rôle (ex: Diakona, Filoha SLK).';
COMMENT ON COLUMN member_roles_mandates.group_id IS 'Lien vers le groupe si rôle de Sampana (NULL si Fiangonana).';

-- --------------------------------------------------------------------------
-- Table: financial_categories
-- Description: Financial categories (Income/Expense)
-- --------------------------------------------------------------------------
CREATE TABLE financial_categories (
    id SERIAL PRIMARY KEY,
    category_name VARCHAR(150) NOT NULL UNIQUE,
    category_type VARCHAR(10) NOT NULL CHECK (category_type IN ('INCOME', 'EXPENSE')),
    description TEXT,
    is_fjkm_standard BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL
);

COMMENT ON TABLE financial_categories IS 'Catégories pour classer les transactions financières (Rakitra, Adidy, Asa...).';
COMMENT ON COLUMN financial_categories.category_type IS 'Type: INCOME (Recette) ou EXPENSE (Dépense).';
COMMENT ON COLUMN financial_categories.is_fjkm_standard IS 'Indique si conforme aux catégories FJKM standard.';

-- --------------------------------------------------------------------------
-- Table: financial_accounts
-- Description: Financial accounts (Cash boxes, Bank accounts)
-- --------------------------------------------------------------------------
CREATE TABLE financial_accounts (
    id SERIAL PRIMARY KEY,
    account_name VARCHAR(100) NOT NULL UNIQUE,
    account_type VARCHAR(10) NOT NULL CHECK (account_type IN ('CASH', 'BANK')),
    group_id INTEGER REFERENCES groups(id) ON DELETE SET NULL,
    initial_balance DECIMAL(15, 2) DEFAULT 0.00,
    current_balance DECIMAL(15, 2) DEFAULT 0.00, -- Maintained by application logic/triggers
    bank_account_reference VARCHAR(50),
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    INDEX idx_faccount_group_id (group_id)
);

COMMENT ON TABLE financial_accounts IS 'Comptes où l''argent est détenu (Caisses/Banques) pour la Fiangonana ou un Sampana.';
COMMENT ON COLUMN financial_accounts.account_type IS 'Type: CASH (Caisse) ou BANK (Banque).';
COMMENT ON COLUMN financial_accounts.group_id IS 'Lien vers le groupe si compte de Sampana (NULL si compte central Fiangonana).';
COMMENT ON COLUMN financial_accounts.current_balance IS 'Solde actuel, DOIT être mis à jour par la logique applicative ou des triggers.';

-- --------------------------------------------------------------------------
-- Table: financial_transactions
-- Description: All financial transactions (In/Out)
-- --------------------------------------------------------------------------
CREATE TABLE financial_transactions (
    id SERIAL PRIMARY KEY,
    transaction_date DATE NOT NULL,
    transaction_type VARCHAR(10) NOT NULL CHECK (transaction_type IN ('INCOME', 'EXPENSE')),
    amount DECIMAL(15, 2) NOT NULL,
    account_id INTEGER NOT NULL REFERENCES financial_accounts(id) ON DELETE RESTRICT,
    category_id INTEGER REFERENCES financial_categories(id) ON DELETE SET NULL,
    description TEXT NOT NULL,
    source_payer VARCHAR(150),
    destination_payee VARCHAR(150),
    voucher_reference VARCHAR(100),
    scan_document_path VARCHAR(255),
    -- Contribution Tracking Fields
    contribution_type VARCHAR(100),
    contribution_member_id INTEGER REFERENCES members(id) ON DELETE SET NULL,
    contribution_household_id INTEGER REFERENCES households(id) ON DELETE SET NULL,
    -- Audit
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    -- Indexes
    INDEX idx_ftrans_date (transaction_date),
    INDEX idx_ftrans_account_id (account_id),
    INDEX idx_ftrans_category_id (category_id),
    INDEX idx_ftrans_contribution_member (contribution_member_id),
    INDEX idx_ftrans_contribution_household (contribution_household_id)
);

COMMENT ON TABLE financial_transactions IS 'Journal de toutes les transactions monétaires.';
COMMENT ON COLUMN financial_transactions.account_id IS 'Compte/Caisse affecté par la transaction.';
COMMENT ON COLUMN financial_transactions.category_id IS 'Catégorie FJKM (Rakitra Alahady...).';
COMMENT ON COLUMN financial_transactions.voucher_reference IS 'Référence de la pièce justificative.';
COMMENT ON COLUMN financial_transactions.contribution_type IS 'Type de collecte (si Adidy spécifique).';

-- --------------------------------------------------------------------------
-- Table: asset_categories
-- Description: Asset categories
-- --------------------------------------------------------------------------
CREATE TABLE asset_categories (
    id SERIAL PRIMARY KEY,
    category_name VARCHAR(150) NOT NULL UNIQUE,
    description TEXT
);

COMMENT ON TABLE asset_categories IS 'Catégories de biens matériels (Fitaovana).';

-- --------------------------------------------------------------------------
-- Table: assets
-- Description: Inventory of assets
-- --------------------------------------------------------------------------
CREATE TABLE assets (
    id SERIAL PRIMARY KEY,
    inventory_code VARCHAR(50) UNIQUE,
    asset_name VARCHAR(200) NOT NULL,
    description TEXT,
    serial_number VARCHAR(100),
    category_id INTEGER REFERENCES asset_categories(id) ON DELETE SET NULL,
    -- Ownership
    owner_type VARCHAR(10) NOT NULL CHECK (owner_type IN ('CHURCH', 'GROUP')),
    owner_group_id INTEGER REFERENCES groups(id) ON DELETE SET NULL,
    -- Acquisition
    acquisition_date DATE,
    acquisition_cost DECIMAL(15, 2),
    supplier VARCHAR(150),
    -- Condition/Location
    condition VARCHAR(20) DEFAULT 'GOOD' CHECK (condition IN ('NEW', 'GOOD', 'FAIR', 'NEEDS_REPAIR', 'DISPOSED')),
    location VARCHAR(150),
    current_custodian TEXT,
    -- Status
    is_in_inventory BOOLEAN DEFAULT true,
    disposal_date DATE,
    disposal_reason TEXT,
    -- Audit
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    updated_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    -- Indexes
    INDEX idx_asset_category_id (category_id),
    INDEX idx_asset_owner_type (owner_type, owner_group_id)
);

COMMENT ON TABLE assets IS 'Inventaire des biens (Fitaovana) de l''Eglise ou des Groupes.';
COMMENT ON COLUMN assets.owner_type IS 'Propriétaire/Gestionnaire: CHURCH (Fiangonana) ou GROUP (Sampana).';
COMMENT ON COLUMN assets.owner_group_id IS 'Lien vers le groupe propriétaire/gestionnaire si owner_type=GROUP.';
COMMENT ON COLUMN assets.is_in_inventory IS 'Indique si le bien est actif dans l''inventaire.';

-- --------------------------------------------------------------------------
-- Table: asset_maintenance_log
-- Description: Asset maintenance history (Optional)
-- --------------------------------------------------------------------------
CREATE TABLE asset_maintenance_log (
    id SERIAL PRIMARY KEY,
    asset_id INTEGER NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    maintenance_date DATE NOT NULL,
    intervention_type TEXT NOT NULL,
    service_provider VARCHAR(150),
    maintenance_cost DECIMAL(15, 2),
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL
);

COMMENT ON TABLE asset_maintenance_log IS 'Historique des maintenances effectuées sur les biens (Fitaovana).';

-- --------------------------------------------------------------------------
-- Table: audit_log
-- Description: Detailed audit trail (Optional/Advanced)
-- --------------------------------------------------------------------------
CREATE TABLE audit_log (
    id BIGSERIAL PRIMARY KEY,
    timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    user_id INTEGER REFERENCES system_users(id) ON DELETE SET NULL,
    action_type VARCHAR(10) NOT NULL, -- 'INSERT', 'UPDATE', 'DELETE'
    table_name VARCHAR(64) NOT NULL,
    record_primary_key VARCHAR(255) NOT NULL,
    field_name VARCHAR(64),
    old_value TEXT,
    new_value TEXT
);

COMMENT ON TABLE audit_log IS 'Journal détaillé de toutes les modifications de données (requiert implémentation de triggers).';

-- ==========================================================================
-- End of Schema Definition
-- ==========================================================================
```

## 2. General Database Rules (Backend Focus)

### ORM Mapping
- Every table defined above MUST have a corresponding Doctrine Entity. Mapping via PHP Attributes is preferred. Precisely match columns, types (Doctrine\DBAL\Types\Types), and relations.

### Relations
- Define Doctrine relations (ManyToOne, OneToOne, etc.) correctly with inversedBy/mappedBy, appropriate fetch modes, and carefully considered cascade options (RESTRICT or SET NULL safer than CASCADE usually). Initialize collection properties.

### Migrations
- Use doctrine/migrations-bundle exclusively for ALL schema changes after initial setup. Generate with diff, review, edit if necessary, and apply with migrate.

### Integrity
- Use Foreign Keys, NOT NULL, and UNIQUE constraints as defined.

### Audit Fields
- Manage created_at, updated_at, created_by_user_id, updated_by_user_id correctly. Timestamps (TIMESTAMP WITH TIME ZONE preferred) ideally via Doctrine extensions/listeners (like Gedmo Timestampable). User IDs must be set programmatically based on the current authenticated user.
