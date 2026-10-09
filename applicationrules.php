<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Rules | Internship Management System</title>
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
        
        .rules-wrapper {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin: 40px auto;
            position: relative;
        }
        
        /* Header */
        .rules-header {
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .rules-header:before {
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
        
        .rules-header h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }
        
        .rules-header p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 20px;
        }
        
        .header-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 25px;
        }
        
        .badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }
        
        /* Navigation */
        .rules-nav {
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
        .rules-content {
            padding: 50px 40px;
        }
        
        .section {
            margin-bottom: 60px;
            scroll-margin-top: 100px;
            padding: 40px;
            background: #f8fafc;
            border-radius: 20px;
            border-left: 5px solid #4f46e5;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .section-icon {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }
        
        .section-title-group {
            flex: 1;
        }
        
        .section-title {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .section-subtitle {
            color: #64748b;
            font-size: 18px;
            font-weight: 500;
        }
        
        .section-description {
            color: #475569;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 25px;
            padding-left: 10px;
            border-left: 3px solid #c7d2fe;
            padding-left: 20px;
        }
        
        /* Rules List */
        .rules-list {
            list-style: none;
            margin: 30px 0;
        }
        
        .rule-item {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 70px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .rule-item:hover {
            border-color: #c7d2fe;
            background: linear-gradient(to right, #f8fafc, white);
        }
        
        .rule-number {
            position: absolute;
            left: 20px;
            top: 25px;
            background: #e0e7ff;
            color: #4f46e5;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        
        .rule-text {
            color: #1e293b;
            font-size: 17px;
            line-height: 1.7;
        }
        
        .rule-details {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
            padding-left: 20px;
            border-left: 2px solid #e2e8f0;
            margin-top: 10px;
        }
        
        /* Example Box */
        .example-box {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            border-left: 4px solid #4f46e5;
            padding: 25px;
            margin: 25px 0;
            border-radius: 12px;
            position: relative;
            padding-left: 60px;
        }
        
        .example-box:before {
            content: '📌';
            position: absolute;
            left: 20px;
            top: 25px;
            font-size: 24px;
        }
        
        .example-title {
            color: #4f46e5;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .example-content {
            color: #4338ca;
            font-size: 16px;
            line-height: 1.7;
        }
        
        /* Important Sections */
        .section.important {
            border-left-color: #ef4444;
            background: linear-gradient(to right, #fef2f2, #f8fafc);
        }
        
        .section.important .section-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        /* Quick Summary */
        .summary-section {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin: 50px 0;
        }
        
        .summary-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .summary-title:before {
            content: '⭐';
            font-size: 32px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .summary-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease;
        }
        
        .summary-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }
        
        .summary-check {
            background: white;
            color: #10b981;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .summary-text {
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Professional Paragraph */
        .professional-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin: 50px 0;
        }
        
        .professional-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .professional-title:before {
            content: '📝';
            font-size: 28px;
        }
        
        .professional-content {
            font-size: 17px;
            line-height: 1.8;
            opacity: 0.95;
        }
        
        /* Dashboard Feature */
        .dashboard-section {
            background: white;
            border: 3px solid #e2e8f0;
            border-radius: 20px;
            padding: 40px;
            margin: 50px 0;
        }
        
        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dashboard-title:before {
            content: '🎯';
            font-size: 32px;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .feature-card {
            background: #f8fafc;
            padding: 30px;
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            border-color: #c7d2fe;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .feature-icon {
            font-size: 32px;
            margin-bottom: 20px;
            display: inline-block;
            background: #e0e7ff;
            color: #4f46e5;
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .feature-name {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .feature-desc {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
        }
        
        /* Footer */
        .rules-footer {
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
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn {
            padding: 16px 35px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
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
        }
        
        .btn-secondary:hover {
            background: #334155;
            transform: translateY(-3px);
        }
        
        .btn-outline {
            background: transparent;
            color: #4f46e5;
            border: 2px solid #4f46e5;
        }
        
        .btn-outline:hover {
            background: #4f46e5;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .rules-header {
                padding: 40px 20px;
            }
            
            .rules-header h1 {
                font-size: 32px;
            }
            
            .rules-nav {
                padding: 0 20px;
            }
            
            .rules-content {
                padding: 30px 20px;
            }
            
            .section {
                padding: 25px;
            }
            
            .section-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .section-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .rule-item {
                padding: 20px 20px 20px 60px;
            }
            
            .nav-container {
                gap: 0;
            }
            
            .nav-link {
                padding: 15px;
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="rules-wrapper">
            <!-- Header -->
            <div class="rules-header">
                <div class="header-icon">📋</div>
                <h1>Application Rules & Regulations</h1>
                <p>CENADI Internship Management System • Version 2.0</p>
                <p>Foundation rules that govern the entire internship application process</p>
                
                <div class="header-badges">
                    <div class="badge">🔐 Secure Application</div>
                    <div class="badge">⚡ Real-time Processing</div>
                    <div class="badge">✅ Automated Validation</div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="rules-nav">
                <div class="nav-container">
                    <a href="#application" class="nav-link active">📝 Application Rules</a>
                    <a href="#eligibility" class="nav-link">🎓 Eligibility Rules</a>
                    <a href="#process" class="nav-link">🔄 Process Rules</a>
                    <a href="#summary" class="nav-link">⭐ Key Rules</a>
                    <a href="#dashboard" class="nav-link">🎯 Dashboard Features</a>
                </div>
            </nav>
            
            <!-- Content -->
            <div class="rules-content">
                <!-- Application Submission Rules -->
                <section id="application" class="section">
                    <div class="section-header">
                        <div class="section-icon">1️⃣</div>
                        <div class="section-title-group">
                            <h2 class="section-title">Application Submission Rules</h2>
                            <p class="section-subtitle">These rules control how students apply for internships</p>
                        </div>
                    </div>
                    
                    <p class="section-description">
                        These foundational rules ensure that all internship applications are submitted correctly, 
                        completely, and within the specified timeframe to maintain fairness and efficiency in the 
                        selection process.
                    </p>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <div class="rule-number">1</div>
                            <div class="rule-text">
                                <strong>Complete Application Requirement:</strong> All interns must fully complete the online application form with accurate information.
                            </div>
                            <div class="rule-details">
                                The system validates all mandatory fields. Incomplete applications cannot be submitted.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">2</div>
                            <div class="rule-text">
                                <strong>Document Upload Requirement:</strong> Required documents ( application letter) must be uploaded before final submission.
                            </div>
                            <div class="rule-details">
                                Supported formats: PDF, DOC, DOCX. Maximum file size: 10MB per document.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">3</div>
                            <div class="rule-text">
                                <strong>Incomplete Application Policy:</strong> Applications missing required information or documents will not be processed.
                            </div>
                            <div class="rule-details">
                                System automatically flags incomplete applications and notifies applicants.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">4</div>
                            <div class="rule-text">
                                <strong>Deadline Adherence:</strong> Applications must be submitted before the official closing deadline.
                            </div>
                            <div class="rule-details">
                                Late submissions are automatically rejected without exception.
                            </div>
                        </li>
                    </ul>
                    
                    <div class="example-box">
                        <div class="example-title">System Implementation Example</div>
                        <p class="example-content">
                            Late submissions are automatically rejected by the system. The application portal displays a 
                            real-time countdown timer showing remaining submission time. One week before deadline, 
                            automated reminder emails are sent to all registered applicants.
                        </p>
                    </div>
                </section>
                
                <!-- Eligibility Rules -->
                <section id="eligibility" class="section">
                    <div class="section-header">
                        <div class="section-icon">2️⃣</div>
                        <div class="section-title-group">
                            <h2 class="section-title">Eligibility Rules</h2>
                            <p class="section-subtitle">Qualifications and criteria for internship applicants</p>
                        </div>
                    </div>
                    
                    <p class="section-description">
                        These rules define who is eligible to apply for internships, ensuring that only qualified 
                        candidates from recognized institutions are considered for the program.
                    </p>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <div class="rule-number">1</div>
                            <div class="rule-text">
                                <strong>Institutional Recognition:</strong> Only students from recognized universities or institutions may apply.
                            </div>
                            <div class="rule-details">
                                The system verifies institution credentials against an approved database.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">2</div>
                            <div class="rule-text">
                                <strong>Academic Level Requirement:</strong> Interns must meet required academic level (e.g., Level 2 or Level 3).
                            </div>
                            <div class="rule-details">
                                Specific departments may have additional academic prerequisites.
                            </div>
                        </li>
                    </ul>
                </section>
                
                <!-- Application Process Rules -->
                <section id="process" class="section important">
                    <div class="section-header">
                        <div class="section-icon">3️⃣</div>
                        <div class="section-title-group">
                            <h2 class="section-title">Application Process Rules</h2>
                            <p class="section-subtitle">Rules governing the complete application workflow</p>
                        </div>
                    </div>
                    
                    <p class="section-description">
                        These comprehensive rules cover the entire application lifecycle from submission to 
                        approval, ensuring consistency, fairness, and transparency throughout the process.
                    </p>
                    
                    <ul class="rules-list">
                        <li class="rule-item">
                            <div class="rule-number">1</div>
                            <div class="rule-text">
                                <strong>Single Application Policy:</strong> Each student is allowed only one active application at a time.
                            </div>
                            <div class="rule-details">
                                Multiple submissions are automatically detected and flagged by the system.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">2</div>
                            <div class="rule-text">
                                <strong>Review Process:</strong> Applications are reviewed by HR personnel or administrators.
                            </div>
                            <div class="rule-details">
                                Supervisors may be involved in the approval process for technical positions.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">3</div>
                            <div class="rule-text">
                                <strong>Status Tracking:</strong> Application statuses include: Pending, Under Review, Approved, Rejected.
                            </div>
                            <div class="rule-details">
                                Applicants can track their status in real-time through the portal.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">4</div>
                            <div class="rule-text">
                                <strong>Approval Requirement:</strong> Interns can only begin after receiving official system approval.
                            </div>
                            <div class="rule-details">
                                Approved applicants are automatically assigned to supervisors based on availability.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">5</div>
                            <div class="rule-text">
                                <strong>Modification Rules:</strong> Applications can only be edited before final submission.
                            </div>
                            <div class="rule-details">
                                After submission, changes require administrative approval via support ticket.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">6</div>
                            <div class="rule-text">
                                <strong>Notification System:</strong> Applicants are notified of status changes via email and SMS.
                            </div>
                            <div class="rule-details">
                                Supervisors receive notifications when interns are assigned to them.
                            </div>
                        </li>
                        
                        <li class="rule-item">
                            <div class="rule-number">7</div>
                            <div class="rule-text">
                                <strong>Rejection Policy:</strong> Applications may be rejected for specific reasons.
                            </div>
                            <div class="rule-details">
                                Reasons include incomplete information, missing documents, expired deadlines, or filled positions.
                            </div>
                        </li>
                    </ul>
                </section>
                
                <!-- Quick Summary -->
                <section id="summary" class="summary-section">
                    <h2 class="summary-title">⭐ Essential Application Rules Summary</h2>
                    <p style="opacity: 0.9; font-size: 17px; margin-bottom: 20px;">
                        These are the most critical rules that every applicant must understand and follow:
                    </p>
                    
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-check">✅</div>
                            <div class="summary-text">Application Deadline Adherence</div>
                        </div>
                        
                        <div class="summary-item">
                            <div class="summary-check">✅</div>
                            <div class="summary-text">Complete Document Submission</div>
                        </div>
                        
                        <div class="summary-item">
                            <div class="summary-check">✅</div>
                            <div class="summary-text">Institutional Eligibility Verification</div>
                        </div>
                        
                        <div class="summary-item">
                            <div class="summary-check">✅</div>
                            <div class="summary-text">Single Application Policy</div>
                        </div>
                        
                        <div class="summary-item">
                            <div class="summary-check">✅</div>
                            <div class="summary-text">Accurate Information Provision</div>
                        </div>
                        
                        <div class="summary-item">
                            <div class="summary-check">✅</div>
                            <div class="summary-text">Approval Requirement Before Start</div>
                        </div>
                    </div>
                </section>
                
                <!-- Professional Paragraph -->
                <section class="professional-section">
                    <h2 class="professional-title">Professional Report Summary</h2>
                    <p class="professional-content">
                        The internship management system enforces a comprehensive set of application rules and regulations 
                        to ensure proper coordination between interns, supervisors, and administrators. These include 
                        strict guidelines on application submission deadlines, eligibility requirements, document 
                        verification, single application policy, real-time status tracking, and mandatory approval 
                        processes. Such regulations promote transparency, accountability, and effective internship 
                        supervision at CENADI, ensuring that only qualified candidates proceed through the selection 
                        pipeline while maintaining the highest standards of academic and professional integrity.
                    </p>
                </section>
                
                <!-- Dashboard Features -->
                <section id="dashboard" class="dashboard-section">
                    <h2 class="dashboard-title">🎯 Dashboard Management Features</h2>
                    <p style="color: #64748b; font-size: 17px; margin-bottom: 30px; line-height: 1.7;">
                        In your Admin Dashboard, you can manage these application rules through the following features:
                    </p>
                    
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon">📍</div>
                            <h3 class="feature-name">Rules & Regulations Page</h3>
                            <p class="feature-desc">
                                Centralized page displaying all application rules with search and filter capabilities.
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">➕</div>
                            <h3 class="feature-name">Add New Rules</h3>
                            <p class="feature-desc">
                                Create new application rules with custom categories, descriptions, and enforcement levels.
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">✏️</div>
                            <h3 class="feature-name">Edit Existing Rules</h3>
                            <p class="feature-desc">
                                Modify existing rules, update requirements, and change enforcement parameters.
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">📢</div>
                            <h3 class="feature-name">Publish Rules for Interns</h3>
                            <p class="feature-desc">
                                Make rules visible to interns through their dashboard with acceptance tracking.
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">📄</div>
                            <h3 class="feature-name">Download Rules PDF</h3>
                            <p class="feature-desc">
                                Generate and download printable PDF versions of all application rules and regulations.
                            </p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">📊</div>
                            <h3 class="feature-name">Compliance Analytics</h3>
                            <p class="feature-desc">
                                Monitor rule compliance rates and generate reports on application quality metrics.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Footer -->
            <div class="rules-footer">
                <div class="footer-content">
                    <div class="header-icon" style="width: 80px; height: 80px; font-size: 40px; margin: 0 auto 25px;">⚖️</div>
                    <h2 class="footer-title">Ready to Implement?</h2>
                    <p class="footer-text">
                        These application rules are ready to be implemented in your CENADI Internship Management System. 
                        They provide a comprehensive framework for managing applications efficiently and fairly.
                    </p>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="implementRules()">
                            <span>🚀</span> Implement These Rules
                        </button>
                        <button class="btn btn-secondary" onclick="downloadPDF()">
                            <span>📥</span> Download as PDF
                        </button>
                        <button class="btn btn-outline" onclick="window.print()">
                            <span>🖨️</span> Print Rules
                        </button>
                    </div>
                    
                    <p style="color: #94a3b8; font-size: 14px; margin-top: 40px;">
                        For implementation assistance: tech@cenadi-internship.com • 
                        Phone: +1 (555) 987-6543 • Support Hours: Mon-Fri 8AM-6PM
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        //