# Smart Library Management System

A comprehensive web-based library management system built with PHP, MySQL, and Tailwind CSS. This system provides role-based access control, automated penalty calculations, and semester-based clearance management.

## 🚀 Features

### Core Features
- **Role-Based Access Control**: Different interfaces for Students, Teachers, Staff, and Librarians
- **Book Inventory Management**: Complete CRUD operations for library books
- **Borrowing & Returning System**: Automated transaction management
- **Penalty & Fine Management**: Automated calculation and processing
- **Semester-Based Operations**: Academic year management
- **User Registration & Management**: Complete user lifecycle management
- **Clearance System**: Semester-end clearance processing

### User Roles
- **Student**: Can borrow up to 3 books per semester, must return books for clearance
- **Teacher**: Can borrow unlimited books, must return all books at semester end
- **Staff**: Facilitates borrowing, returning, and clearance processes
- **Librarian**: Manages book inventory and system administration

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS + Flowbite
- **Server**: Apache (XAMPP)
- **Architecture**: MVC Pattern

## 📁 Project Structure

```
smart-library/
├── config/                 # Configuration files
│   ├── database.php        # Database connection
│   ├── penalty_manager.php # Penalty calculation logic
│   ├── user_manager.php    # User management logic
│   └── semester_manager.php # Semester management
├── database/               # Database schemas
│   └── schema_v3.sql      # Final schema with semesters
├── staff/                  # Staff interface
│   ├── dashboard.php      # Main staff dashboard
│   ├── penalty_management.php
│   └── clearance_management.php
├── student/               # Student interface
│   ├── dashboard.php      # Student dashboard
│   └── fines.php         # Student fines page
├── teacher/               # Teacher interface
│   ├── dashboard.php      # Teacher dashboard
│   └── fines.php         # Teacher fines page
├── librarian/             # Librarian interface
│   └── dashboard.php      # Librarian dashboard
├── admin/                 # Admin interface
│   └── user_management.php
├── index.php             # Main entry point
├── login.php             # Login page
├── register.php          # User registration
└── logout.php            # Logout handler
```

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser
- Internet connection (for CDN resources)

### Installation Steps

1. **Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install and start Apache and MySQL services

2. **Setup Project**
   - Copy project to `C:\xampp\htdocs\smart-library\`
   - Ensure proper file permissions

3. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `smart_library`
   - Import schema: `database/schema_v3.sql`

4. **System Ready**
   - The system comes with sample data in the schema

5. **Access System**
   - Open: `http://localhost/smart-library/`
   - Login with demo credentials

## 🔐 Demo Credentials

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| **Librarian** | librarian | password | Full system access |
| **Staff** | staff | password | Borrowing, returns, penalties |
| **Student** | student1 | password | Limited borrowing (3 books) |
| **Teacher** | teacher1 | password | Unlimited borrowing |

## 🎯 Key Features

### Book Management
- Add, edit, and archive books
- ISBN management with duplicate handling
- Price tracking for penalty calculations
- Status management (available, borrowed, archived)

### Borrowing System
- Real-time user and book search
- Automated borrowing limits enforcement
- Transaction tracking and history
- Return processing with status updates

### Penalty System
- Automatic penalty calculations
- Role-based penalty rates (Students: ₱10/day, Teachers: ₱5/day)
- Payment processing and tracking
- Clearance status management

### User Management
- User registration and validation
- Role-based access control
- User activation/deactivation
- Profile management

### Semester Management
- Academic year tracking
- Semester-based borrowing limits
- Clearance processing
- Academic calendar management

## 🧪 Testing

### Test Scenarios
1. **Student Borrowing**: Test 3-book limit enforcement
2. **Teacher Borrowing**: Test unlimited borrowing
3. **Penalty Calculation**: Test overdue book penalties
4. **Clearance Process**: Test semester-end clearance
5. **User Management**: Test user registration and management

### Demo Data
- Pre-created users for all roles
- Sample books with various statuses
- Overdue transactions for penalty testing
- Active semesters for testing

## 📊 System Benefits

### For Students
- Easy book borrowing process
- Clear penalty information
- Online reservation system
- Transparent borrowing limits

### For Teachers
- Unlimited book access
- Flexible borrowing terms
- Clear fine management
- Academic year tracking

### For Staff
- Efficient transaction processing
- Automated penalty calculations
- Clear user management
- Comprehensive reporting

### For Librarians
- Complete inventory control
- User management capabilities
- System administration
- Data analytics

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- Session management and validation
- Input sanitization and validation
- SQL injection protection with prepared statements
- Role-based access control
- CSRF protection considerations

## 🚀 Future Enhancements

- Mobile app development
- Advanced reporting and analytics
- Email notifications
- Barcode scanning integration
- Multi-language support
- Advanced search functionality
- Automated email reminders
- Integration with student information systems

## 📝 Development Status

- ✅ **Phase 1**: MVP with basic CRUD operations
- ✅ **Phase 2**: Penalty calculation and clearance system
- ✅ **Phase 3**: User registration and management
- ✅ **Phase 4**: Search functionality and UX improvements
- ✅ **Phase 5**: Demo data and testing scripts

## 🛠️ Technical Implementation

### Database Design
- Normalized relational database
- Foreign key constraints for data integrity
- Indexed columns for performance
- Semester-based transaction tracking

### Code Architecture
- MVC pattern implementation
- Separation of concerns
- Reusable components
- Error handling and logging

### User Experience
- Responsive design with Tailwind CSS
- Real-time search functionality
- Intuitive navigation
- Error feedback and validation

## 📞 Support

### Documentation
- **User Manual**: `USER_MANUAL.md` - Comprehensive user guide
- **Installation Guide**: `INSTALLATION_GUIDE.md` - Step-by-step setup
- **Demo Presentation Guide**: `DEMO_PRESENTATION_GUIDE.md` - Presentation script

### Common Issues
- Database connection errors
- File permission issues
- Browser compatibility
- XAMPP service problems

## 🎯 Project Deliverables

### ✅ Completed Deliverables
1. **Functional Web Application**: Complete front-end and back-end implementation
2. **Source Code**: Well-documented PHP code with comments
3. **Database Schema**: Normalized database design with relationships
4. **User Manual**: Comprehensive user guide
5. **Installation Guide**: Step-by-step setup instructions
6. **Demo Presentation Guide**: Complete presentation script
7. **Testing Scripts**: Automated demo data creation

### 📋 Final Deliverables Checklist
- [x] Functional web application (front-end & back-end)
- [x] Source code with documentation
- [x] User manual or guide
- [x] Installation and setup guide
- [x] Demo presentation guide
- [x] Testing and validation scripts
- [x] Database schema and migrations
- [x] Demo data and scenarios

## 🏆 Project Achievements

### Technical Achievements
- Modern web application with responsive design
- Secure authentication and authorization system
- Automated penalty calculation system
- Real-time search and filtering
- Comprehensive user management
- Academic year and semester tracking

### Business Value
- Streamlined library operations
- Reduced manual work and errors
- Improved user experience
- Better data management and reporting
- Scalable and maintainable system

## 🎬 Demo Presentation

### Quick Demo Flow
1. **Login System** - Show all user roles
2. **Book Management** - Add, edit, view books
3. **Borrowing Process** - Complete workflow
4. **Search Functionality** - Real-time search
5. **Penalty System** - Calculations and payments
6. **User Management** - Registration and management
7. **Clearance System** - Semester-end processing

### Demo Script
See `DEMO_PRESENTATION_GUIDE.md` for complete presentation script and talking points.

## 🚀 Getting Started

1. **Read the Installation Guide**: `INSTALLATION_GUIDE.md`
2. **Set up the system**: Follow the installation steps
3. **Create demo data**: Run the demo scripts
4. **Test the system**: Use the demo credentials
5. **Review the User Manual**: `USER_MANUAL.md`

## 📞 Support & Documentation

- **User Manual**: Complete user guide with screenshots
- **Installation Guide**: Step-by-step setup instructions
- **Demo Presentation Guide**: Complete presentation script
- **Source Code**: Well-documented PHP code
- **Database Schema**: Normalized design with relationships

---

**Smart Library Management System v1.0**  
*Developed with PHP, MySQL, and Tailwind CSS*

*For technical support or questions, refer to the documentation files in the project root.*
