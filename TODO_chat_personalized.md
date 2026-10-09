# Personalized Chat TODO

**Status:** In Progress (3/6) - Steps 1-2: Intern/supervisor support + send fix

## Steps:
- [ ] 1. Update chat.php: Add intern role - show assigned supervisor from assignments table
- [ ] 2. Update chat.php: Add admin role - global broadcast form (receiver_id=0 for system-wide)
- [ ] 3. Fix message query: Handle receiver_id=0 (broadcasts visible to all), role-based filtering
- [ ] 4. UI: Role-specific sidebar/header/input (Supervisor: intern list, Intern: supervisor, Admin: broadcast)
- [ ] 5. Test: Login as each role, verify personalized view + send/receive
- [ ] 6. Update dashboards links if needed

**Notes:** 
- Assume 1 intern-supervisor assignment (find via assignments table).
- Broadcasts: sender_id=admin, receiver_id=0, visible when ?system or always.

