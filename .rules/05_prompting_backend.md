# AI PROMPTING GUIDE: SYMFONY BACKEND (SIGE FJKM)

## General Prompting Strategy
1.  **Be Specific:** Clearly state WHAT you need, including the Symfony components involved (Entity, Repository, Controller, Service, DTO, Voter, Listener, Migration, etc.).
2.  **Provide Context:** Mention "Symfony 7", "API Platform", "Doctrine", "SIGE FJKM Backend API" to orient the AI. Reference the specific Module (Members, Financial...).
3.  **Reference Rules:** Explicitly mention relevant rules files (e.g., "Following `02_backend_symfony_rules.md` and `03_database_rules.md`..."). Refer to PSR-12 and strict typing rules.
4.  **Include Existing Code:** Provide snippets of relevant existing code (Entities, DTOs, interfaces, service signatures) if the AI needs to interact with or modify them. Ensure the AI has access to the current file context if possible (essential for IDE integrations).
5.  **Iterate:** Ask for specific refinements (e.g., "Add validation constraint for this field in the DTO", "Refactor this controller logic into a dedicated Service", "Ensure this uses Dependency Injection correctly", "Generate PHPDoc for this class", "Optimize this DQL query", "Write tests for this method").

## Backend-Specific Examples

**Generating Entities/Repositories:**
*   "Generate the Doctrine Entity class `Asset` mapping the `assets` table defined in `03_database_rules.md`. Include all fields, use Doctrine `Types`, define relations to `AssetCategory` (ManyToOne) and `Group` (ManyToOne, nullable). Add required PHPDoc and ensure it extends a potential BaseEntity with audit fields."
*   "Create the `FinancialTransactionRepository`. Add a method `findTransactionsByAccount(int $accountId, DateTimeInterface $startDate, DateTimeInterface $endDate)` using the QueryBuilder to retrieve transactions for a specific account within a date range."

**Creating API Endpoints (Manual Controller):**
*   "Create a Symfony `ApiController` method `listFinancialAccounts` (GET `/api/financial-accounts`). Use the `FinancialAccountRepository` to fetch accounts. Apply security checks using `IsGranted()` or a Voter (`FinancialAccountVoter::VIEW`) to ensure only authorized users see relevant accounts (Admin/Church Treasurer see all, Group Treasurer sees their group's account). Serialize the result using group 'account:list' and return a `JsonResponse`."
*   "Generate the API endpoint `/api/groups/{id}/members` (POST) in `GroupApiController`. It should accept a DTO `AddMemberToGroupInput` (containing `member_id`). Use Voters or Services to check permissions. Create a new `MemberGroupMembership` record linking the group (`id` from path) and the member. Return 201 Created or appropriate error response."

**Implementing Services:**
*   "Implement the `MemberTransferService` with a method `transferOut(int $memberId, MemberTransferOutDto $dto, SystemUser $actingUser): Member`. This method should: 1. Fetch the Member. 2. Check permissions. 3. Update the member's `fjkm_status` to 'TRANSFERRED_OUT', set `transfer_out_date` and `transfer_out_destination`. 4. Set `is_active` to false. 5. Set `updated_by_user_id`. 6. Persist the changes via EntityManager. 7. Return the updated Member entity. Follow SOLID."

**Writing Tests:**
*   "Write a PHPUnit Unit Test for the `MemberTransferService::transferOut` method. Mock `EntityManagerInterface`, `MemberRepository`, and potentially permission checks. Verify the Member entity's properties are updated correctly and `persist`/`flush` are called."
*   "Write a Symfony Functional Test for the `POST /api/groups/{id}/members` endpoint. Create necessary fixtures (Group, Member, authenticated User with appropriate role). Test success case (201 Created), validation errors (400/422 for invalid `member_id`), authorization errors (403 if user cannot add members to this group), and not found errors (404 if group/member doesn't exist)."

**Security / Voters:**
*   "Refine the `FinancialAccountVoter` (created previously). Implement the `voteOnAttribute` method for the 'VIEW' attribute. It should return true if the user has 'ROLE_ADMIN' or 'ROLE_CHURCH_TREASURER'. If the user has a role like 'ROLE_GROUP_TREASURER_X' (parse X from roles), it should return true only if the subject `FinancialAccount`'s `group_id` matches X. Otherwise, return false."

**Doctrine Listener / Balance Update:**
*   "Implement the `TransactionBalanceListener` service (implementing relevant Doctrine interfaces or using Attributes). In the `postPersist` method for a `FinancialTransaction`: get the `amount` and `transaction_type`; fetch the related `FinancialAccount`; update its `current_balance` (add for INCOME, subtract for EXPENSE); persist the `FinancialAccount`. Handle `postUpdate` (considering changes in amount or type) and `postRemove` similarly (reversing the operation)."
*   "Generate a Doctrine Migration using `doctrine:migrations:diff` after adding a new `notes` field to the `groups` entity."