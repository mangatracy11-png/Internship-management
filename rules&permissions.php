<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions | Internship Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.8;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .terms-wrapper {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin: 40px auto;
            position: relative;
        }
        
        /* Header */
        .terms-header {
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .terms-header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        
        .header-icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            margin: 0 auto 25px;
        }
        
        .terms-header h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }
        
        .terms-header p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Navigation */
        .terms-nav {
            background: #f8fafc;
            padding: 0 40px;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            overflow-x: auto;
            gap: 2px;
        }
        
        .nav-link {
            padding: 20px 25px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link:hover {
            color: #4f46e5;
            background: #f1f5f9;
        }
        
        .nav-link.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
            background: linear-gradient(to bottom, rgba(79, 70, 229, 0.05), transparent);
        }
        
        /* Content */
        .terms-content {
            padding: 50px 40px;
        }
        
        .section {
            margin-bottom: 60px;
            scroll-margin-top: 100px;
            padding: 30px;
            background: #f8fafc;
            border-radius: 16px;
            border-left: 5px solid #4f46e5;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .section-number {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            flex: 1;
        }
        
        .section-subtitle {
            color: #64748b;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-subtitle:before {
            content: '';
            width: 10px;
            height: 10px;
            background: #4f46e5;
            border-radius: 50%;
        }
        
        .rules-list {
            list-style: none;
            margin: 20px 0;
        }
        
        .rule-item {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 60px;
        }
        
        .rule-item:hover {
            border-color: #c7d2fe;
            background: #f8fafc;
        }
        
        .rule-item:before {
            content: '✓';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: #10b981;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .rule-item strong {
            color: #1e293b;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .rule-item p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.7;
        }
        
        .example-box {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            border-left: 4px solid #4f46e5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            position: relative;
            padding-left: 50px;
        }
        
        .example-box:before {
            content: '📌';
            position: absolute;
            left: 15px;
            top: 15px;
            font-size: 20px;
        }
        
        .example-title {
            color: #4f46e5;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        /* Important Sections */
        .section.important {
            border-left-color: #ef4444;
            background: linear-gradient(to right, #fef2f2, #f8fafc);
        }
        
        .section.important .section-number {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .section.important .rule-item:before {
            background: #ef4444;
        }
        
        /* Footer */
        .terms-footer {
            background: #1e293b;
            color: white;
            padding: 50px 40px;
            text-align: center;
        }
        
        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .footer-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #f8fafc;
        }
        
        .footer-text {
            color: #cbd5e1;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .acceptance-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 12px;
            margin: 30px auto;
            max-width: 500px;
            backdrop-filter: blur(10px);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .accept-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #4f46e5;
        }
        
        .accept-label {
            font-size: 16px;
            color: #f8fafc;
            font-weight: 500;
        }
        
        .btn {
            padding: 16px 40px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.5);
        }
        
        .btn-secondary {
            background: #475569;
            color: white;
            margin-left: 15px;
        }
        
        .btn-secondary:hover {
            background: #334155;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .terms-header {
                padding: 40px 20px;
            }
            
            .terms-header h1 {
                font-size: 32px;
            }
            
            .terms-nav {
                padding: 0 20px;
            }
            
            .terms-content {
                padding: 30px 20px;
            }
            
            .section {
                padding: 20px;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .rule-item {
                padding-left: 50px;
            }
            
            .nav-container {
                gap: 0;
            }
            
            .nav-link {
                padding: 15px 20px;
                font-size: 14px;
            }
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .terms-wrapper {
                box-shadow: none;
                margin: 0;
            }
            
            .terms-nav,
            .terms-footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="terms-wrapper">
            <!-- Header -->
            <div class="terms-header">
                <div class="header-icon">📜</div>
                <h1>Terms & Conditions</h1>
                <p>Internship Management System - Version 2.0 • Last Updated: January 2024</p>
            </div>
            
            <!-- Navigation -->
            <nav class="terms-nav">
                <div class="nav-container">
                    <a href="#application" class="nav-link active">📝 Application</a>
                    <a href="#eligibility" class="nav-link">🎓 Eligibility</a>
                    <a href="#duration" class="nav-link">📅 Duration</a>
                    <a href="#attendance" class="nav-link">⏰ Attendance</a>
                    <a href="#conduct" class="nav-link">🤝 Conduct</a>
                    <a href="#supervisor" class="nav-link">👨‍🏫 Supervisor</a>
                    <a href="#reports" class="nav-link">📄 Reports</a>
                    <a href="#privacy" class="nav-link">🔒 Privacy</a>
                    <a href="#system" class="nav-link">💻 System</a>
                    <a href="#approval" class="nav-link">✅ Approval</a>
                </div>
            </nav>
            
            <!-- Content -->
            <div class="terms-content">
                <!-- 1. Application Submission Rules -->
                <section id="application" class="section">
                    <div class="section-header">
                        <div class="section-number">1</div>
                        <h2 class="section-title">Application Submission Rules</h2>
                    </div>
                    
                    <div class="section-subtitle">These rules control how students apply for internships</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Complete Application Requirement</strong>
                            <p>All prospective interns must fully complete the online application form with accurate and truthful information. Incomplete submissions will be automatically rejected by the system.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Document Submission</strong>
                            <p>Required documents including  application letter must be uploaded in PDF format before final submission. Maximum file size is 10MB per document.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Submission Deadline</strong>
                            <p>Applications must be submitted before the specified deadline. </p>
                        </li>
                        <li class="rule-item">
                            <strong>Application Review Process</strong>
                            <p>All applications undergo a two-stage review process: automated system validation followed by manual review by the HR department. Applicants will be notified of their status within 7-10 business days.</p>
                        </li>
</ul>
                </section>
                
                <!-- 2. Eligibility Rules -->
                <section id="eligibility" class="section">
                    <div class="section-header">
                        <div class="section-number">2</div>
                        <h2 class="section-title">Eligibility Requirements</h2>
                    </div>
                    
                    <div class="section-subtitle">Qualifications and criteria for internship applicants</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Institutional Recognition</strong>
                            <p>Only students from government-recognized educational institutions may apply. Applicants must provide valid institution identification during the application process.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Academic Level Requirement</strong>
                            <p>Interns must meet the minimum academic level requirement (typically Level 2 or Level 3). Specific departments may have additional academic prerequisites.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Age and Citizenship</strong>
                            <p>Applicants must be at least 16 years old and possess valid citizenship or student visa documentation for the internship duration.</p>
                        </li>
                    </ul>
                </section>
                
                <!-- 3. Internship Duration Rules -->
                <section id="duration" class="section">
                    <div class="section-header">
                        <div class="section-number">3</div>
                        <h2 class="section-title">Internship Duration Policy</h2>
                    </div>
                    
                    <div class="section-subtitle">Defines the internship period and scheduling requirements</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Minimum Duration Requirement</strong>
                            <p>All internships must last a minimum of 1–3 months (4-12 weeks). Shorter periods do not qualify for certification.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Schedule Adherence</strong>
                            <p>Interns must respect the official start and end dates specified in their offer letter. Early departure or late arrival requires written approval.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Extension Policy</strong>
                            <p>Extensions require formal written approval from both the direct supervisor and HR department. Requests must be submitted at least two weeks before the original end date.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Flexible Scheduling</strong>
                            <p>While standard internships follow a 7 AM - 4 PM schedule, flexible arrangements may be approved based on departmental needs and academic requirements.</p>
                        </li>
                    </ul>
                </section>
                
                <!-- 4. Attendance and Punctuality Rules -->
                <section id="attendance" class="section important">
                    <div class="section-header">
                        <div class="section-number">4</div>
                        <h2 class="section-title">Attendance & Punctuality</h2>
                    </div>
                    
                    <div class="section-subtitle">Critical rules for monitoring intern presence and timeliness</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Punctuality Requirement</strong>
                            <p>Interns must report to work at the designated start time. A grace period of 15 minutes is allowed for exceptional circumstances.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Absence Notification</strong>
                            <p>Any absence requires prior notification to the supervisor via the designated system. Medical absences require doctor's certification.</p>
</li>
                    </ul>
                    
                    <div class="example-box">
                        <div class="example-title">Enforcement Example</div>
                        <p>More than 3 unexcused absences may result in internship termination. The system automatically generates warnings after the second absence and escalates to HR after the third.</p>
                    </div>
                </section>
                
                <!-- 5. Code of Conduct Rules -->
                <section id="conduct" class="section">
                    <div class="section-header">
                        <div class="section-number">5</div>
                        <h2 class="section-title">Professional Code of Conduct</h2>
                    </div>
                    
                    <div class="section-subtitle">Behavioral expectations and professional standards</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Respectful Behavior</strong>
                            <p>Interns must maintain respectful behavior toward all staff, colleagues, and clients at all times. Harassment or discrimination of any kind is strictly prohibited.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Professional Ethics</strong>
                            <p>Interns must adhere to workplace ethics including confidentiality, honesty, and professional integrity in all interactions.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Dress Code</strong>
                            <p>Business casual attire is required unless otherwise specified by the department. The company reserves the right to define appropriate workplace attire.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Disciplinary Action</strong>
                            <p>Misconduct may lead to progressive disciplinary action including verbal warning, written warning, suspension, and ultimately internship termination.</p>
                        </li>
                    </ul>
                </section>
                
                <!-- 6. Supervisor Responsibilities -->
                <!---<section id="supervisor" class="section">
                    <div class="section-header">
                        <div class="section-number">6</div>
                        <h2 class="section-title">Supervisor Responsibilities</h2>
                    </div>
                    
                    <div class="section-subtitle">Duties and obligations of internship supervisors</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Progress Monitoring</strong>
                            <p>Supervisors must conduct weekly progress reviews with assigned interns and document discussions in the system.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Guidance Provision</strong>
                            <p>Supervisors must provide regular guidance, mentorship, and constructive feedback to support intern development.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Evaluation Submission</strong>
                            <p>Performance evaluations must be submitted through the system by the 25th of each month for monthly interns, and bi-weekly for shorter internships.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Resource Allocation</strong>
                            <p>Supervisors must ensure interns have access to necessary tools, resources, and training to complete assigned tasks effectively.</p>
                        </li>
                    </ul>
                </section>
                
                7. Report Submission Rules 
                <section id="reports" class="section important">
                    <div class="section-header">
                        <div class="section-number">7</div>
                        <h2 class="section-title">Report Submission Requirements</h2>
                    </div>
                    
                    <div class="section-subtitle">Rules governing documentation and reporting</div>
                    
                    <ul class="rules-list">
                        <class="rule-item">
                            <strong>Weekly/Monthly Reports</strong>
                            <p>Interns must submit progress reports every Friday by 5:00 PM. Monthly reports are due on the last working day of each month.</p>
                        
                        <li class="rule-item">
                            <strong>Final Report Requirement</strong>
                            <p>A comprehensive final internship report must be submitted at least three working days before the internship completion date.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Report Format Standards</strong>
                            <p>All reports must follow the standard template provided in the system and include specific sections: achievements, challenges, lessons learned, and future recommendations.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Certification Impact</strong>
                            <p>Failure to submit required reports may result in delayed or withheld internship certification. Extensions for report submission are rarely granted.</p>
                        </li>
                    </ul>
                </section>
-->
                
                <!-- 8. Data Privacy and Confidentiality -->
                <section id="privacy" class="section">
                    <div class="section-header">
                        <div class="section-number">8</div>
                        <h2 class="section-title">Data Privacy & Confidentiality</h2>
                    </div>
                    
                    <div class="section-subtitle">Information protection and data security policies</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Confidential Information</strong>
                            <p>Interns must not share, disclose, or discuss confidential company information with unauthorized persons during or after the internship period.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Data Protection</strong>
                            <p>User data is protected under GDPR compliance standards and is only accessible to authorized personnel with proper clearance levels.</p>
                        </li>
                        <li class="rule-item">
                            <strong>System Access</strong>
                            <p>Unauthorized access to systems, files, or data is strictly prohibited and may result in immediate termination and legal action.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Non-Disclosure Agreement</strong>
                            <p>All interns must sign a Non-Disclosure Agreement (NDA) before accessing any company systems or confidential information.</p>
                        </li>
                    </ul>
                </section>
                
                <!-- 9. System Usage Rules -->
                <section id="system" class="section">
                    <div class="section-header">
                        <div class="section-number">9</div>
                        <h2 class="section-title">System Usage Policy</h2>
                    </div>
                    
                    <div class="section-subtitle">Rules for using the internship management platform</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Credential Security</strong>
                            <p>Users must not share login credentials with anyone. Each account is personal and must be secured with strong passwords changed every 90 days.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Account Ownership</strong>
                            <p>Accounts are non-transferable. Sharing accounts or allowing others to use your credentials is grounds for immediate suspension.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Activity Monitoring</strong>
                            <p>All system activity is logged and monitored. Suspicious activities are automatically flagged and investigated by the security team.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Acceptable Use</strong>
                            <p>The system may only be used for legitimate internship-related activities. Commercial use, spamming, or illegal activities are prohibited.</p>
                        </li>
                    </ul>
                </section>
                <!-- 10. Approval and Evaluation Rules -->
                <section id="approval" class="section">
                    <div class="section-header">
                        <div class="section-number">10</div>
                        <h2 class="section-title">Approval & Evaluation Process</h2>
                    </div>
                    
                    <div class="section-subtitle">Rules governing decision-making and performance assessment</div>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <strong>Application Review</strong>
                            <p>All applications must be reviewed and approved by the HR department before acceptance. No internship may commence without formal HR approval.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Performance Evaluation</strong>
                            <p>Intern performance is evaluated based on three key metrics: attendance (30%), task completion (40%), and report quality (30%).</p>
                        </li>
                        <li class="rule-item">
                            <strong>Commencement Requirement</strong>
                            <p>Only approved interns who have completed all onboarding requirements may begin their internship on the scheduled start date.</p>
                        </li>
                        <li class="rule-item">
                            <strong>Feedback Mechanism</strong>
                            <p>Evaluation feedback is provided through the system portal. Interns have 5 business days to acknowledge or dispute evaluation results.</p>
                        </li>
                    </ul>
                </section>
            </div>
            
            <!-- Footer -->
            <div class="terms-footer">
                <div class="footer-content">
                    <div class="header-icon" style="width: 80px; height: 80px; font-size: 40px;">⚖️</div>
                    <h2 class="footer-title">Acceptance Required</h2>
                    <p class="footer-text">
                        By using the Internship Management System, you acknowledge that you have read, understood, 
                        and agree to be bound by these Terms & Conditions. These terms constitute a legally binding 
                        agreement between you and the organization.
                    </p>
                    
                    <div class="acceptance-box">
                        <div class="checkbox-group">
                            <input type="checkbox" id="acceptTerms" class="accept-checkbox">
                            <label for="acceptTerms" class="accept-label">
                                I have read and agree to all Terms & Conditions above
                            </label>
                        </div>
                        
                        <div>
                            <button class="btn btn-primary" onclick="acceptTerms()" >
                                <a href="interndashboard.php"><span>✅</span> Accept & Continue</a>
                            </button>
                        </div>
                    </div>
                    
                    <p style="color: #94a3b8; font-size: 14px; margin-top: 30px;">
                        For questions or clarifications, contact: legal@internship-system.com • 
                        Phone: +1 (555) 123-4567 • Office Hours: Mon-Fri 9AM-5PM
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Smooth scrolling for navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                // Update active nav link
                document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                // Scroll to section
                window.scrollTo({
                    top: targetSection.offsetTop - 100,
                    behavior: 'smooth'
                });
            });
        });
        
        // Highlight active section based on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.section');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let currentSection = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 150;
                const sectionHeight = section.clientHeight;
                if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                    currentSection = '#' + section.id;
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentSection) {
                    link.classList.add('active');
                }
            });
        });
        
        // Terms acceptance
        function acceptTerms() {
            const checkbox = document.getElementById('acceptTerms');
            if (!checkbox.checked) {
                alert('Please check the box to indicate you have read and agree to the Terms & Conditions.');
                return;
            }
            
            // Save acceptance to localStorage
            localStorage.setItem('termsAccepted', 'true');
            localStorage.setItem('termsAcceptedDate', new Date().toISOString());
            
            // Show success message
            alert('Thank you for accepting the Terms & Conditions. You may now proceed.');
            
            // Redirect to dashboard or previous page
            const returnUrl = new URLSearchParams(window.location.search).get('return') || 'interndashboard.php';
            window.location.href = returnUrl;
        }
        
        // Check if already accepted
        window.addEventListener('load', function() {
            if (localStorage.getItem('termsAccepted') === 'true') {
                document.getElementById('acceptTerms').checked = true;
            }
            
            // Add section numbers dynamically (optional)
            document.querySelectorAll('.section').forEach((section, index) => {
                const number = section.querySelector('.section-number');
                if (number) {
                    number.textContent = index + 1;
                }
            });
        });
        
        // Print functionality
        function printTerms() {
            window.print();
        }
    </script>
</body>
</html>