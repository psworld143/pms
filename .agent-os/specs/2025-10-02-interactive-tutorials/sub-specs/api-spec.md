# API Specification

This is the API specification for the spec detailed in @.agent-os/specs/2025-10-02-interactive-tutorials/spec.md

## Endpoints

### GET /api/tutorials/modules

**Purpose:** Retrieve available tutorial modules with progress information
**Parameters:** 
- `difficulty` (optional): Filter by difficulty level
- `module_type` (optional): Filter by module type (pos, inventory, booking)
- `user_id` (required): Current user ID

**Response:**
```json
{
  "success": true,
  "modules": [
    {
      "id": 1,
      "name": "POS System Basics",
      "description": "Learn fundamental POS operations",
      "module_type": "pos",
      "difficulty_level": "beginner",
      "estimated_duration": 30,
      "progress": {
        "completion_percentage": 45.5,
        "time_spent": 900,
        "score": 85.0,
        "status": "in_progress"
      }
    }
  ]
}
```

**Errors:** 401 (Unauthorized), 500 (Server Error)

### GET /api/tutorials/modules/{id}/steps

**Purpose:** Retrieve tutorial steps for a specific module
**Parameters:**
- `id` (path): Tutorial module ID
- `user_id` (required): Current user ID

**Response:**
```json
{
  "success": true,
  "steps": [
    {
      "id": 1,
      "step_number": 1,
      "title": "Welcome to POS System",
      "description": "Introduction to point of sale operations",
      "instruction": "Click on the 'New Order' button to start",
      "target_element": "#new-order-btn",
      "action_type": "click",
      "is_interactive": true
    }
  ]
}
```

**Errors:** 404 (Module not found), 401 (Unauthorized)

### POST /api/tutorials/progress/update

**Purpose:** Update student progress for a tutorial step
**Parameters:**
- `user_id` (required): Current user ID
- `tutorial_module_id` (required): Tutorial module ID
- `step_id` (required): Current step ID
- `action_type` (required): Type of action (step_complete, pause, etc.)
- `time_spent` (optional): Time spent on current step
- `score` (optional): Score for current step

**Response:**
```json
{
  "success": true,
  "progress": {
    "completion_percentage": 50.0,
    "time_spent": 1200,
    "score": 90.0,
    "status": "in_progress"
  }
}
```

**Errors:** 400 (Invalid data), 401 (Unauthorized), 500 (Server Error)

### GET /api/tutorials/analytics/instructor

**Purpose:** Retrieve tutorial analytics for instructors
**Parameters:**
- `instructor_id` (required): Instructor user ID
- `module_type` (optional): Filter by module type
- `difficulty` (optional): Filter by difficulty level
- `date_range` (optional): Filter by date range

**Response:**
```json
{
  "success": true,
  "analytics": {
    "overview": {
      "total_students": 45,
      "average_completion": 78.5,
      "average_time_spent": 2400
    },
    "module_stats": [
      {
        "module_id": 1,
        "module_name": "POS System Basics",
        "completion_rate": 85.2,
        "average_score": 88.5,
        "average_time": 1800
      }
    ],
    "student_progress": [
      {
        "student_id": 123,
        "student_name": "John Doe",
        "completion_percentage": 90.0,
        "time_spent": 2100,
        "score": 95.0
      }
    ]
  }
}
```

**Errors:** 401 (Unauthorized), 403 (Forbidden), 500 (Server Error)

### POST /api/tutorials/assessments/submit

**Purpose:** Submit tutorial assessment answers
**Parameters:**
- `user_id` (required): Current user ID
- `assessment_id` (required): Assessment ID
- `answer` (required): Student's answer
- `time_spent` (optional): Time spent on assessment

**Response:**
```json
{
  "success": true,
  "result": {
    "is_correct": true,
    "score": 1,
    "explanation": "Correct! This is the proper way to process a payment.",
    "next_step": 2
  }
}
```

**Errors:** 400 (Invalid answer), 401 (Unauthorized), 404 (Assessment not found)

## Controllers

### TutorialController
- **getModules()**: Retrieve tutorial modules with progress
- **getSteps()**: Get tutorial steps for a module
- **updateProgress()**: Update student progress
- **getAnalytics()**: Retrieve instructor analytics
- **submitAssessment()**: Process assessment submissions

### ProgressController
- **trackAction()**: Log tutorial actions for analytics
- **calculateProgress()**: Compute completion percentages
- **generateReport()**: Create progress reports for instructors

## Purpose

- **Tutorial Management**: Complete CRUD operations for tutorial content
- **Progress Tracking**: Real-time progress updates and persistence
- **Analytics Integration**: Comprehensive reporting for educational assessment
- **Assessment System**: Interactive quiz and evaluation functionality
