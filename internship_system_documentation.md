# Internship Management System - Technical Documentation

## 1. Use Case Diagram

### 1.1 System Overview
The Internship Management System involves three main actors:
- **Admin** - System administrator who manages the entire platform
- **Supervisor** - Staff members who oversee and evaluate interns
- **Intern** - Students applying for and completing internships

### 1.2 Use Case Diagram Description

```
                    ┌─────────────────────────────────────────────────────────┐
                    │          INTERNSHIP MANAGEMENT SYSTEM                 │
                    └─────────────────────────────────────────────────────────┘
                                       │
          ┌────────────────────────────┼────────────────────────────┐
          │                            │                            │
          ▼                            ▼                            ▼
   ┌─────────────┐             ┌─────────────┐            ┌─────────────┐
   │    ADMIN    │             │  SUPERVISOR │            │    INTERN   │
   └─────────────┘             └─────────────┘            └─────────────┘
          │                            │                            │
          │                            │                            │
          └────────────────────────────┴────────────────────────────┘
                                       │
                    ┌──────────────────────────────────────────┐
                    │           AUTHENTICATION                  │
                    │  ┌────────────┐  ┌────────────┐         │
                    │  │   Login    │  │  Register  │         │
                    │  └────────────┘  └────────────┘         │
                    │  ┌────────────┐                          │
                    │  │   Logout   │                          │
                    │  └────────────┘                          │
                    └──────────────────────────────────────────┘
                                       │
          ┌────────────────────────────┼────────────────────────────┐
          │                            │                            │
          ▼                            ▼                            ▼
   ┌─────────────────────────────────┬─────────────────────────────────────┐
   │      ADMIN USE CASES             │       SUPERVISOR USE CASES         │
   ├─────────────────────────────────┼─────────────────────────────────────┤
   │  • Manage Interns               │  • View Assigned Interns           │
   │  • Manage Supervisors           │  • View Submitted Reports          │
   │  • View Reports & Logs         │  • Evaluate Intern Performance     │
   │  • Send/Receive Messages       │  • Schedule Meetings               │
   │  • System Settings             │  • Chat with Interns               │
   │  • View Dashboard              │  • View Dashboard                  │
   │  • Assign Supervisors          │  • Provide Feedback                │
   └─────────────────────────────────┴─────────────────────────────────────┘
                                       │
                                       ▼
                        ┌───────────────────────────────────┐
                        │        INTERN USE CASES           │
                        ├───────────────────────────────────┤
                        │  • Apply for Internship           │
                        │  • Submit Reports                 │
                        │  • View My Reports                │
                        │  • Create/Edit Profile            │
                        │  • Chat with Supervisor          │
                        │  • View Dashboard                 │
                        │  • View Evaluation Results        │
                        └───────────────────────────────────┘
```

### 1.3 Detailed Use Case Descriptions

#### 1.3.1 Authentication Use Cases

**Use Case: Login**
- Actor: Admin, Supervisor, Intern
- Description: Users authenticate to access the system
- Preconditions: User must have valid credentials
- Flow:
  1. User enters username and password
  2. System validates credentials
  3. System redirects to appropriate dashboard based on role

**Use Case: Register**
- Actor: Intern (potential)
- Description: New intern creates an account
- Preconditions: None
- Flow:
  1. User fills registration form (username, email, password)
  2. System creates user account with 'intern' role
  3. System creates application entry with 'pending' status

**Use Case: Logout**
- Actor: Admin, Supervisor, Intern
- Description: User ends session
- Preconditions: User must be logged in

#### 1.3.2 Admin Use Cases

**Use Case: Manage Interns**
- Actor: Admin
- Description: View, add, edit, delete intern records
- Preconditions: Admin must be logged in
- Flow:
  1. Admin views list of all interns
  2. Admin can assign supervisor to intern
  3. Admin can delete intern

**Use Case: Manage Supervisors**
- Actor: Admin
- Description: View and manage supervisor accounts
- Preconditions: Admin must be logged in

**Use Case: View Reports & Logs**
- Actor: Admin
- Description: View system logs and all submitted reports
- Preconditions: Admin must be logged in

**Use Case: Send/Receive Messages**
- Actor: Admin
- Description: Communicate with supervisors and interns
- Preconditions: Admin must be logged in

**Use Case: System Settings**
- Actor: Admin
- Description: Configure system parameters
- Preconditions: Admin must be logged in

#### 1.3.3 Supervisor Use Cases

**Use Case: View Assigned Interns**
- Actor: Supervisor
- Description: View list of interns assigned to supervisor
- Preconditions: Supervisor must be logged in

**Use Case: View Submitted Reports**
- Actor: Supervisor
- Description: Review reports submitted by interns
- Preconditions: Supervisor must be logged in

**Use Case: Evaluate Intern Performance**
- Actor: Supervisor
- Description: Rate and provide feedback on intern performance
- Preconditions: Supervisor must be logged in, must have assigned intern
- Flow:
  1. Supervisor selects intern to evaluate
  2. Supervisor rates on multiple criteria (1-5 stars)
  3. Supervisor provides strengths, areas for improvement, comments
  4. System stores evaluation

**Use Case: Schedule Meetings**
- Actor: Supervisor
- Description: Schedule meetings with interns
- Preconditions: Supervisor must be logged in

**Use Case: Chat with Interns**
- Actor: Supervisor
- Description: Real-time messaging with assigned interns
- Preconditions: Supervisor must be logged in

**Use Case: Provide Feedback**
- Actor: Supervisor
- Description: Provide feedback on submitted reports
- Preconditions: Supervisor must be logged in

#### 1.3.4 Intern Use Cases

**Use Case: Apply for Internship**
- Actor: Intern
- Description: Submit internship application
- Preconditions: Intern must be logged in
- Flow:
  1. Intern fills application form
  2. Intern provides personal info, school, dates
  3. Intern selects department and internship type
  4. Intern uploads supporting documents
  5. System creates application with 'pending' status

**Use Case: Submit Reports**
- Actor: Intern
- Description: Submit work reports to supervisor
- Preconditions: Intern must be logged in
- Flow:
  1. Intern enters report title and description
  2. Intern uploads file (optional)
  3. System stores report and notifies supervisor

**Use Case: View My Reports**
- Actor: Intern
- Description: View history of submitted reports and feedback
- Preconditions: Intern must be logged in

**Use Case: Create/Edit Profile**
- Actor: Intern
- Description: Manage personal profile information
- Preconditions: Intern must be logged in

**Use Case: Chat with Supervisor**
- Actor: Intern
- Description: Real-time messaging with assigned supervisor
- Preconditions: Intern must be logged in, must have assigned supervisor

**Use Case: View Evaluation Results**
- Actor: Intern
- Description: View performance evaluation results
- Preconditions: Intern must be logged in

---

## 2. Class Diagram

### 2.1 Overview
The class diagram shows the main entities (classes) in the system and their relationships.

### 2.2 Class Diagram Description

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    CLASS DIAGRAM                                            │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                                    ┌─────────────────┐
                                    │    User         │
                                    ├─────────────────┤
                                    │ - id: int       │
                                    │ - name: string  │
                                    │ - email: string │
                                    │ - password: string│
                                    │ - role: string  │
                                    │ - supervisor_id: int│
                                    ├─────────────────┤
                                    │ + login()       │
                                    │ + register()    │
                                    │ + logout()      │
                                    │ + updateProfile()│
                                    └────────┬────────┘
                                             │
          ┌──────────────────────────────────┼──────────────────────────────────┐
          │                                  │                                  │
          │                     ┌────────────┴────────────┐                     │
          │                     │                         │                     │
          ▼                     ▼                         ▼                     ▼
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│     Admin       │  │    Supervisor   │  │     Intern      │  │   Department    │
├─────────────────┤  ├─────────────────┤  ├─────────────────┤  ├─────────────────┤
│ - user_id: int  │  │ - user_id: int │  │ - user_id: int │  │ - id: int       │
│ - privileges    │  │ - department    │  │ - school       │  │ - name: string  │
├─────────────────┤  ├─────────────────┤  ├─────────────────┤  ├─────────────────┤
│ + manageInterns │  │ + viewInterns() │  │ + apply()      │  │ + addDept()     │
│ + manageSuper() │  │ + reviewReport()│  │ + submitReport()│  │ + updateDept()  │
│ + viewReports() │  │ + evaluate()    │  │ + viewReports()│  │ + deleteDept()  │
│ + sendMessage() │  │ + scheduleMeet()│ │ + chat()       │  └─────────────────┘
│ + settings()    │  │ + chat()        │  │ + viewProfile()│
└─────────────────┘  └─────────────────┘  └─────────────────┘
                                             │
                                             │ creates
                                             ▼
                          ┌─────────────────────────────────┐
                          │     Application                  │
                          ├─────────────────────────────────┤
                          │ - id: int                        │
                          │ - intern_id: int                │
                          │ - position: string              │
                          │ - department: string           │
                          │ - status: string                │
                          │ - applied_date: date           │
                          ├─────────────────────────────────┤
                          │ + submit()                       │
                          │ + updateStatus()                │
                          │ + getStatus()                   │
                          └─────────────────────────────────┘
                                             │
                                             │ has
                                             ▼
                          ┌─────────────────────────────────┐
                          │      InternProfile               │
                          ├─────────────────────────────────┤
                          │ - id: int                        │
                          │ - user_id: int                  │
                          │ - full_name: string             │
                          │ - school: string                │
                          │ - department: string           │
                          │ - supervisor_id: int           │
                          │ - status: string               │
                          │ - date_of_birth: date          │
                          │ - start_date: date             │
                          │ - end_date: date               │
                          │ - internship_type: string       │
                          │ - file_path: string            │
                          ├─────────────────────────────────┤
                          │ + createProfile()               │
                          │ + updateProfile()              │
                          │ + assignSupervisor()           │
                          └─────────────────────────────────┘
                                             │
                                             │ submits
                                             ▼
                          ┌─────────────────────────────────┐
                          │         Report                  │
                          ├─────────────────────────────────┤
                          │ - id: int                        │
                          │ - intern_id: int                │
                          │ - supervisor_id: int           │
                          │ - title: string                 │
                          │ - description: string           │
                          │ - file_path: string             │
                          │ - file_name: string             │
                          │ - file_type: string             │
                          │ - file_size: int               │
                          │ - status: string               │
                          │ - submitted_at: datetime       │
                          │ - feedback: string             │
                          ├─────────────────────────────────┤
                          │ + submit()                      │
                          │ + updateStatus()               │
                          │ + addFeedback()                │
                          │ + getReports()                  │
                          └─────────────────────────────────┘
                                             │
                                             │ receives
                                             ▼
                          ┌─────────────────────────────────┐
                          │  InternPerformance              │
                          ├─────────────────────────────────┤
                          │ - id: int                        │
                          │ - intern_id: int                │
                          │ - supervisor_id: int           │
                          │ - evaluation_date: date         │
                          │ - technical_skills: int        │
                          │ - communication: int          │
                          │ - teamwork: int                │
                          │ - initiative: int              │
                          │ - punctuality: int             │
                          │ - problem_solving: int         │
                          │ - learning_ability: int         │
                          │ - professionalism: int         │
                          │ - overall_performance: int     │
                          │ - strengths: text              │
                          │ - areas_for_improvement: text  │
                          │ - supervisor_comments: text   │
                          │ - goals_set: text              │
                          │ - goals_achieved: text         │
                          ├─────────────────────────────────┤
                          │ + evaluate()                    │
                          │ + getHistory()                 │
                          │ + calculateOverall()           │
                          └─────────────────────────────────┘
                                             │
                                             │ sends
                                             ▼
                          ┌─────────────────────────────────┐
                          │        Message                  │
                          ├─────────────────────────────────┤
                          │ - id: int                        │
                          │ - sender_id: int                │
                          │ - receiver_id: int             │
                          │ - message: text                │
                          │ - created_at: datetime         │
                          ├─────────────────────────────────┤
                          │ + send()                        │
                          │ + receive()                    │
                          │ + getConversation()            │
                          └─────────────────────────────────┘
```

### 2.3 Class Descriptions

#### User Class
- **Purpose**: Base class for all users
- **Attributes**: id, name, email, password, role, supervisor_id
- **Methods**: login(), register(), logout(), updateProfile()

#### Admin Class (extends User)
- **Purpose**: Represents system administrator
- **Additional Attributes**: privileges
- **Methods**: manageInterns(), manageSupervisors(), viewReports(), sendMessage(), settings()

#### Supervisor Class (extends User)
- **Purpose**: Represents internship supervisor
- **Additional Attributes**: department
- **Methods**: viewInterns(), reviewReport(), evaluate(), scheduleMeeting(), chat()

#### Intern Class (extends User)
- **Purpose**: Represents internship applicant/participant
- **Additional Attributes**: school
- **Methods**: apply(), submitReport(), viewReports(), chat(), viewProfile()

#### Application Class
- **Purpose**: Represents internship application
- **Attributes**: id, intern_id, position, department, status, applied_date
- **Methods**: submit(), updateStatus(), getStatus()

#### InternProfile Class
- **Purpose**: Detailed intern profile information
- **Attributes**: id, user_id, full_name, school, department, supervisor_id, status, dates, etc.
- **Methods**: createProfile(), updateProfile(), assignSupervisor()

#### Report Class
- **Purpose**: Represents work reports submitted by interns
- **Attributes**: id, intern_id, supervisor_id, title, description, file info, status, feedback
- **Methods**: submit(), updateStatus(), addFeedback(), getReports()

#### InternPerformance Class
- **Purpose**: Stores performance evaluations
- **Attributes**: Multiple rating criteria (1-5), comments, goals
- **Methods**: evaluate(), getHistory(), calculateOverall()

#### Message Class
- **Purpose**: For chat/messaging between users
- **Attributes**: id, sender_id, receiver_id, message, created_at
- **Methods**: send(), receive(), getConversation()

#### Department Class
- **Purpose**: Represents organizational departments
- **Attributes**: id, name
- **Methods**: addDept(), updateDept(), deleteDept()

---

## 3. Sequence Diagrams

### 3.1 Login Sequence Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                           LOGIN SEQUENCE DIAGRAM                                         │
└─────────────────────────────────────────────────────────────────────────────────────────┘

   Actor              System                                    Database
    │                  │                                          │
    │  1. Enter username/password                                  │
    │─────────────────>│                                          │
    │                  │                                          │
    │                  │  2. Prepare SQL query                    │
    │                  │───>│                                     │
    │                  │    │                                     │
    │                  │    │  3. SELECT id, name, role, password │
    │                  │    │     FROM users WHERE name = ?        │
    │                  │    │──────────────────────────────────>│  │
    │                  │    │                                     │
    │                  │    │  4. Return user record              │
    │                  │    │<────────────────────────────────────│  │
    │                  │    │                                     │
    │                  │  5. Validate password                   │
    │                  │───>│                                     │
    │                  │    │                                     │
    │                  │  6. Password valid?                      │
    │                  │    │                                     │
    │                  │    └───────┐                             │
    │                  │            │                             │
    │                  │            ▼                             │
    │                  │     ┌─────────────┐                      │
    │                  │     │   YES       │                      │
    │                  │     └──────┬──────┘                      │
    │                  │            │                             │
    │                  │            ▼                             │
    │                  │  7. Set session variables               │
    │                  │     (user_id, username, role)            │
    │                  │───>│                                     │
    │                  │    │                                     │
    │                  │    │         ┌─────────────┐            │
    │                  │    │         │    NO       │            │
    │                  │    │         └──────┬──────┘            │
    │                  │    │                │                   │
    │                  │    │                ▼                   │
    │                  │    │     8. Return error                │
    │                  │    │<───│                                 │
    │                  │    │     │                               │
    │                  │<───│     │                               │
    │                  │    │     │                               │
    │  9. Redirect to  │     │     │                               │
    │     appropriate  │     │     │                               │
    │     dashboard    │     │     │                               │
    │<────────────────│     │     │                               │
    │                  │     │     │                               │
```

### 3.2 Internship Application Sequence Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                    INTERNSHIP APPLICATION SEQUENCE DIAGRAM                                │
└─────────────────────────────────────────────────────────────────────────────────────────┘

   Intern              System                                    Database
    │                  │                                          │
    │  1. Fill application form                                   │
    │  (name, school, email, contact,                            │
    │   dates, department, type, file)                            │
    │─────────────────>│                                          │
    │                  │                                          │
    │                  │  2. Validate input data                  │
    │                  │───>│                                     │
    │                  │    │                                     │
    │                  │  3. Input valid?                        │
    │                  │    │                                     │
    │                  │    └───────┐                             │
    │                  │            │                             │
    │                  │      ┌─────┴─────┐                       │
    │                  │      │           │                       │
    │                  │    YES          NO                       │
    │                  │      │           │                       │
    │                  │      │           ▼                       │
    │                  │      │     4. Return error              │
    │                  │      │<───│                             │
    │                  │      │     │                             │
    │                  │<─────│     │                             │
    │                  │      │     │                             │
    │  5. Show error  │      │     │                             │
    │<────────────────│      │     │                             │
    │                  │      │     │                             │
    │                  │      ▼     │                             │
    │                  │  6. Handle file upload                   │
    │                  │───>│     │                             │
    │                  │    │     │                             │
    │                  │    │     │                             │
    │                  │  7. Move file to uploads/               │
    │                  │    │─────│                             │
    │                  │    │     │                             │
    │                  │    │     │                             │
    │                  │  8. File uploaded?                     │
    │                  │    │     │                             │
    │                  │    └───────┐                             │
    │                  │            │                             │
    │                  │      ┌─────┴─────┐                       │
    │                  │      │           │                       │
    │                  │    YES          NO                       │
    │                  │      │           │                       │
    │                  │      │           ▼                       │
    │                  │      │     9. Continue without file      │
    │                  │      │───>│     │                       │
    │                  │      │     │     │                       │
    │                  │      │     │     │                       │
    │                  │<─────│     │     │                       │
    │                  │      │     │     │                       │
    │                  │      ▼     │     │                       │
    │                  │ 10. Insert into applications table      │
    │                  │    │─────────────────────────────────>│  │
    │                  │    │                                     │
    │                  │    │ 11. Application created             │
    │                  │    │<────────────────────────────────────│  │
    │                  │    │                                     │
    │                  │    │                                     │
    │                  │ 12. Insert into intern_profiles table   │
    │                  │    │─────────────────────────────────>│  │
    │                  │    │                                     │
    │                  │    │ 13. Profile created                │
    │                  │    │<────────────────────────────────────│  │
    │                  │    │                                     │
    │                  │<───│                                     │
    │                  │    │                                     │
    │  14. Redirect to confirmation                              │
    │<────────────────│     │                                   │
    │                  │     │                                   │
```

### 3.3 Report Submission Sequence Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                         REPORT SUBMISSION SEQUENCE DIAGRAM                              │
└─────────────────────────────────────────────────────────────────────────────────────────┘

   Intern              System              Supervisor           Database
    │                  │                      │                    │
    │  1. Submit report form                                     │
    │  (title, description, file)                                │
    │─────────────────>│                                         │
    │                  │                                         │
    │                  │  2. Validate input                      │
    │                  │───>│                                    │
    │                  │    │                                    │
    │                  │  3. Input valid?                        │
    │                  │    │                                    │
    │                  │    └───────┐                            │
    │                  │            │                            │
    │                  │      ┌─────┴─────┐                      │
    │                  │      │           │                      │
    │                  │    YES          NO                      │
    │                  │      │           │                      │
    │                  │      │           ▼                      │
    │                  │      │     4. Return error             │
    │                  │      │<───│                            │
    │                  │      │    │                            │
    │                  │<─────│    │                            │
    │                  │      │    │                            │
    │  5. Show error  │      │    │                            │
    │<────────────────│      │    │                            │
    │                  │      │    │                            │
    │                  │      ▼    │                            │
    │                  │  6. Handle file upload (if any)         │
    │                  │───>│    │                            │
    │                  │    │    │                            │
    │                  │    │    │                            │
    │                  │  7. Insert into reports table          │
    │                  │    │────────────────────────────────>│  │
    │                  │    │                                  │
    │                  │    │  8. Report created               │
    │                  │    │<─────────────────────────────────│  │
    │                  │    │                                  │
    │                  │    │                                  │
    │                  │  9. Get supervisor_id for intern      │
    │                  │    │────────────────────────────────>│  │
    │                  │    │                                  │
    │                  │    │ 10. Return supervisor_id         │
    │                  │    │<─────────────────────────────────│  │
    │                  │    │                                  │
    │                  │    │                                  │
    │                  │ 11. Insert into report_notifications  │
    │                  │    │────────────────────────────────>│  │
    │                  │    │                                  │
    │                  │    │ 12. Notification created         │
    │                  │    │<─────────────────────────────────│  │
    │                  │    │                                  │
    │                  │<───│                                  │
    │                  │    │                                  │
    │  13. Show success message                                  │
    │<────────────────│                                          │
    │                  │                                         │
    │                  │         14. Supervisor sees notification│
    │                  │─────────────────>│                       │
    │                  │                  │                       │
    │                  │         15. View report details         │
    │                  │<─────────────────│                       │
    │                  │                  │                       │
    │                  │         16. Provide feedback           │
    │                  │─────────────────>│                       │
    │                  │                  │                       │
    │                  │         17. Update report with feedback │
    │                  │                  │───────>│              │
    │                  │                  │        │              │
    │                  │                  │        18. Feedback saved│
    │                  │                  │<───────│              │
    │                  │                  │        │              │
```

### 3.4 Intern Evaluation Sequence Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                         INTERN EVALUATION SEQUENCE DIAGRAM                              │
└─────────────────────────────────────────────────────────────────────────────────────────┘

 Supervisor           System              Intern              Database
    │                  │                    │                  │
    │  1. Select intern to evaluate                              │
    │─────────────────>│                    │                  │
    │                  │                    │                  │
    │                  │  2. Verify intern is assigned           │
    │                  │    to this supervisor                  │
    │                  │───>│              │                  │
    │                  │    │              │                  │
    │                  │  3. Check assignment                  │
    │                  │    │──────────────────>│             │
    │                  │    │              │                  │
    │                  │    │  4. Return assignment            │
    │                  │    │<──────────────────│              │
    │                  │    │              │                  │
    │                  │  5. Intern assigned?                  │
    │                  │    │              │                  │
    │                  │    └───────┐     │                  │
    │                  │            │     │                  │
    │                  │      ┌─────┴─────┐                │
    │                  │      │           │                  │
    │                  │    YES          NO                  │
    │                  │      │           │                  │
    │                  │      │           ▼                  │
    │                  │      │     6. Access denied         │
    │                  │      │<───│     │                  │
    │                  │      │    │     │                  │
    │                  │<─────│    │     │                  │
    │                  │      │    │     │                  │
    │  7. Show error │      │    │     │                  │
    │<────────────────│      │    │     │                  │
    │                  │      │    │     │                  │
    │                  │      ▼    │     │                  │
    │                  │  8. Display evaluation form           │
    │                  │───>│     │     │                  │
    │                  │    │     │     │                  │
    │  9. View form  │      │     │     │                  │
    │<────────────────│      │     │     │                  │
    │                  │     │     │     │                  │
    │                  │     │     │     │                  │
    │ 10. Rate intern │     │     │     │                  │
    │    (8 criteria)│     │     │     │                  │
    │    + feedback   │     │     │     │                  │
    │────────────────>│     │     │     │                  │
    │                  │     │     │     │                  │
    │                  │ 11. Validate ratings                   │
    │                  │───>│     │     │                  │
    │                  │    │     │     │                  │
    │                  │ 12. All ratings valid?                │
    │                  │    │     │     │                  │
    │                  │    └───────┐     │                  │
    │                  │            │     │                  │
    │                  │      ┌─────┴─────┐                  │
    │                  │      │           │                  │
    │                  │    YES          NO                  │
    │                  │      │           │                  │
    │                  │      │           ▼                  │
    │                  │      │     13. Return error         │
    │                  │      │<───│     │                  │
    │                  │      │    │     │                  │
    │                  │<─────│    │     │                  │
    │                  │      │    │     │                  │
    │  14. Show error │      │    │     │                  │
    │<────────────────│      │    │     │                  │
    │                  │      │    │     │                  │
    │                  │      ▼    │     │                  │
    │                  │ 15. Calculate overall performance     │
    │                  │    (average of 8 criteria)            │
    │                  │───>│     │     │                  │
    │                  │    │     │     │                  │
    │                  │    │     │     │                  │
    │                  │ 16. Insert into intern_performance    │
    │                  │    │───────────────────────────────>│ │
    │                  │    │                               │ │
    │                  │    │ 17. Evaluation saved          │ │
    │                  │    │<──────────────────────────────│ │
    │                  │    │                               │ │
    │                  │<───│                               │ │
    │                  │    │                               │ │
    │  18. Show success│    │                               │ │
    │<────────────────│    │                               │ │
    │                  │    │                               │ │
    │                  │    │    19. Intern views evaluation│ │
    │                  │    │────────────────>│             │
    │                  │    │                    │           │
    │                  │    │ 20. Display evaluation        │
    │                  │    │<──────────────────│           │
    │                  │    │                    │           │
```

### 3.5 Chat/Messaging Sequence Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                            CHAT SEQUENCE DIAGRAM                                       │
└─────────────────────────────────────────────────────────────────────────────────────────┘

   User A           System              Database              User B
 (Intern/           │                    │                 (Supervisor/
  Supervisor)       │                    │                  Intern)
    │                │                    │                    │
    │ 1. Open chat page                 │                    │
    │──────────────>│                    │                    │
    │                │                    │                    │
    │                │ 2. Determine chat partner              │
    │                │   (Intern→Supervisor, Supervisor→    │
    │                │    select Intern)                     │
    │                │───>│              │                    │
    │                │    │              │                    │
    │                │    │  3. Get chat partner info        │
    │                │    │─────────────────>│              │
    │                │    │              │                    │
    │                │    │  4. Return partner info          │
    │                │    │<─────────────────│              │
    │                │    │              │                    │
    │                │<───│              │                    │
    │                │    │              │                    │
    │ 5. Display chat interface         │                    │
    │<──────────────│    │              │                    │
    │                │    │              │                    │
    │                │    │              │                    │
    │ 6. Load conversation              │                    │
    │──────────────>│    │              │                    │
    │                │    │              │                    │
    │                │ 7. Query messages                     │
    │                │    │────────────────────────────────>│ │
    │                │    │                                  │ │
    │                │    │ 8. Return messages               │ │
    │                │    │<────────────────────────────────│ │
    │                │    │                                  │ │
    │                │<───│              │                    │
    │                │    │              │                    │
    │ 9. Display messages               │                    │
    │<──────────────│    │              │                    │
    │                │    │              │                    │
    │                │    │              │                    │
    │ 10. Type and send message         │                    │
    │──────────────>│    │              │                    │
    │                │    │              │                    │
    │                │ 11. Insert message                   │
    │                │    │────────────────────────────────>│ │
    │                │    │                                  │ │
    │                │    │ 12. Message saved               │ │
    │                │    │<────────────────────────────────│ │
    │                │    │                                  │ │
    │                │<───│              │                    │
    │                │    │              │                    │
    │ 13. Confirm sent │              │                    │
    │<──────────────│    │              │                    │
    │                │
