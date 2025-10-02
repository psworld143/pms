# Database Schema

This is the database schema implementation for the spec detailed in @.agent-os/specs/2025-10-02-interactive-tutorials/spec.md

## Database Changes

### New Tables

#### tutorial_modules
```sql
CREATE TABLE tutorial_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    module_type ENUM('pos', 'inventory', 'booking') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    estimated_duration INT NOT NULL, -- in minutes
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### tutorial_steps
```sql
CREATE TABLE tutorial_steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutorial_module_id INT NOT NULL,
    step_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instruction TEXT NOT NULL,
    target_element VARCHAR(200), -- CSS selector for highlighting
    action_type ENUM('click', 'input', 'select', 'navigate', 'simulate') NOT NULL,
    expected_result TEXT,
    is_interactive BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE
);
```

#### tutorial_progress
```sql
CREATE TABLE tutorial_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tutorial_module_id INT NOT NULL,
    current_step INT DEFAULT 1,
    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
    time_spent INT DEFAULT 0, -- in seconds
    score DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('not_started', 'in_progress', 'completed', 'paused') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_tutorial (user_id, tutorial_module_id)
);
```

#### tutorial_assessments
```sql
CREATE TABLE tutorial_assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutorial_step_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'fill_blank', 'simulation') NOT NULL,
    options JSON, -- for multiple choice questions
    correct_answer TEXT NOT NULL,
    explanation TEXT,
    points INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutorial_step_id) REFERENCES tutorial_steps(id) ON DELETE CASCADE
);
```

#### tutorial_analytics
```sql
CREATE TABLE tutorial_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tutorial_module_id INT NOT NULL,
    action_type ENUM('start', 'step_complete', 'assessment_complete', 'pause', 'resume', 'complete') NOT NULL,
    step_id INT NULL,
    time_spent INT DEFAULT 0, -- in seconds
    score DECIMAL(5,2) NULL,
    metadata JSON, -- additional tracking data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutorial_module_id) REFERENCES tutorial_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES tutorial_steps(id) ON DELETE SET NULL
);
```

## Rationale

- **tutorial_modules**: Central repository for all tutorial content with difficulty levels and module types
- **tutorial_steps**: Granular step-by-step instructions with interactive elements and targeting
- **tutorial_progress**: Individual student progress tracking with comprehensive metrics
- **tutorial_assessments**: Integrated quiz and evaluation system within tutorials
- **tutorial_analytics**: Detailed tracking for instructor insights and system optimization

## Performance Considerations

- Indexes on frequently queried columns (user_id, tutorial_module_id, status)
- JSON columns for flexible metadata storage
- Foreign key constraints for data integrity
- Timestamp tracking for progress analysis
