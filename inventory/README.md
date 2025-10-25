# Inventory Management System
## Hotel PMS Training System for Students

A comprehensive inventory management system designed for hospitality management training, allowing students to learn inventory control, stock management, and supply chain operations in a hotel environment.

## 🚀 Features

### Core Functionality
- **Inventory Item Management**: Add, edit, and track inventory items with categories, pricing, and stock levels
- **Transaction Tracking**: Record stock in/out transactions with detailed logging and user attribution
- **Request Management**: Create and manage inventory requests with approval workflows
- **Training Scenarios**: Interactive training modules for inventory management skills
- **Reports & Analytics**: Comprehensive reporting with charts and statistics
- **Low Stock Alerts**: Automated alerts for items below minimum stock levels

### Training Features
- **Interactive Scenarios**: Real-world inventory management scenarios
- **Progress Tracking**: Monitor student progress and completion rates
- **Skill Assessment**: Score-based evaluation system
- **Difficulty Levels**: Beginner, intermediate, and advanced scenarios

## 📋 System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## 🛠️ Installation

### 1. Database Setup
Run the installation script to create all necessary database tables:
```
http://localhost/seait/pms/inventory/install-inventory.php
```

### 2. Access the System
Navigate to the inventory dashboard:
```
http://localhost/seait/pms/inventory/index.php
```

## 📊 Database Schema

### Core Tables
- `inventory_categories` - Item categories and classifications
- `inventory_items` - Individual inventory items with stock levels
- `inventory_transactions` - All stock movements and transactions
- `inventory_requests` - Request management and approval workflow
- `inventory_request_items` - Items within each request
- `inventory_suppliers` - Supplier information and contacts

### Training Tables
- `inventory_training_scenarios` - Training scenarios and instructions
- `inventory_training_progress` - Student progress and completion tracking

## 🎯 Training Scenarios

### Available Scenarios
1. **Stock Replenishment** (Beginner)
   - Learn to identify low stock items
   - Create reorder requests
   - Update inventory records

2. **Inventory Audit** (Intermediate)
   - Conduct physical inventory counts
   - Reconcile discrepancies
   - Generate audit reports

3. **Supplier Management** (Advanced)
   - Handle supplier issues
   - Find alternative suppliers
   - Process returns

4. **Cost Control Analysis** (Advanced)
   - Analyze spending patterns
   - Identify cost-saving opportunities
   - Present recommendations

## 🎨 User Interface

### Design Principles
- **Green Theme**: Consistent with user preferences [[memory:8286215]]
- **Tailwind CSS**: Modern, responsive design via CDN [[memory:7320359]]
- **Clean UI**: Simple interface without gradients [[memory:7686375]]
- **Mobile Responsive**: Works on all device sizes

### Navigation Structure
- **Dashboard**: Overview and quick actions
- **Items**: Inventory item management
- **Transactions**: Stock movement tracking
- **Requests**: Request management and approval
- **Training**: Interactive learning scenarios
- **Reports**: Analytics and reporting

## 🔧 Configuration

### Database Configuration
The system uses the main PMS database configuration located at:
```
pms/includes/database.php
```

### Inventory-Specific Settings
Additional configuration is available in:
```
pms/inventory/config/database.php
```

## 📈 Key Metrics Tracked

### Inventory Metrics
- Total items count
- Total inventory value
- Low stock items count
- Stock turnover rates

### Transaction Metrics
- Stock in/out volumes
- Transaction frequency
- User activity tracking
- Value movements

### Training Metrics
- Scenario completion rates
- Average scores
- Time to completion
- Progress tracking

## 🎓 Educational Value

### Learning Objectives
- **Inventory Control**: Understanding stock levels and reorder points
- **Supply Chain Management**: Managing suppliers and procurement
- **Cost Management**: Analyzing inventory costs and optimization
- **Process Improvement**: Identifying inefficiencies and improvements
- **Technology Integration**: Using modern inventory management systems

### Skill Development
- Critical thinking and problem-solving
- Data analysis and interpretation
- Process optimization
- Communication and reporting
- Technology proficiency

## 🔒 Security Features

- **User Authentication**: Integrated with PMS user system
- **Role-Based Access**: Different access levels for different user types
- **Data Validation**: Input sanitization and validation
- **Transaction Logging**: Complete audit trail of all changes

## 📱 Mobile Support

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes

## 🚀 Future Enhancements

### Planned Features
- Barcode scanning integration
- Mobile app development
- Advanced analytics dashboard
- Integration with external suppliers
- Automated reorder suggestions
- Multi-location inventory support

## 📞 Support

For technical support or questions about the inventory system:
- Check the installation script output for any errors
- Review the database schema for table creation issues
- Ensure proper user authentication is working
- Verify database connection settings

## 🎯 Best Practices

### For Students
- Complete training scenarios in order of difficulty
- Practice with different inventory scenarios
- Review reports to understand inventory patterns
- Use the system regularly to build familiarity

### For Instructors
- Monitor student progress through training scenarios
- Use reports to assess learning outcomes
- Create custom scenarios for specific learning objectives
- Encourage hands-on practice with real inventory data

---

**Note**: This system is designed for educational purposes and training in hospitality management. It provides a realistic simulation of inventory management processes used in the hotel industry.
