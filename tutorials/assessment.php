<?php
/**
 * Tutorial Assessment Component
 * Hotel PMS Training System - Interactive Tutorials
 */

session_start();
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // For testing
}

// Set page title
$page_title = 'Tutorial Assessment';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel PMS Training System</title>
    <link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .assessment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .question-card {
            transition: all 0.3s ease;
        }
        
        .question-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .option-button {
            transition: all 0.2s ease;
        }
        
        .option-button:hover {
            background-color: #f3f4f6;
            border-color: #3b82f6;
        }
        
        .option-button.selected {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .progress-bar {
            transition: width 0.3s ease;
        }
        
        .feedback-correct {
            background-color: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .feedback-incorrect {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .assessment-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: none;
        }
        
        .assessment-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <a href="index.php" class="text-blue-600 hover:text-blue-800 mr-4">
                            <i class="fas fa-arrow-left"></i> Back to Tutorials
                        </a>
                        <h1 class="text-xl font-semibold text-gray-900">Tutorial Assessment</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-600">
                            <span id="current-question">1</span> of <span id="total-questions">5</span>
                        </div>
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="progress-bar bg-blue-600 h-2 rounded-full" style="width: 20%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Container -->
        <div class="assessment-container py-8 px-4">
            <!-- Loading State -->
            <div id="loading-state" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">Loading assessment...</p>
            </div>

            <!-- Assessment Questions -->
            <div id="assessment-questions" class="hidden">
                <!-- Questions will be loaded dynamically -->
            </div>

            <!-- Assessment Complete -->
            <div id="assessment-complete" class="hidden text-center py-12">
                <div class="bg-white rounded-lg shadow p-8">
                    <i class="fas fa-trophy text-6xl text-yellow-500 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Assessment Complete!</h2>
                    <p class="text-gray-600 mb-6">Great job completing the tutorial assessment.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-blue-600" id="final-score">0</div>
                            <div class="text-sm text-blue-800">Final Score</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-green-600" id="correct-answers">0</div>
                            <div class="text-sm text-green-800">Correct Answers</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-purple-600" id="time-taken">0</div>
                            <div class="text-sm text-purple-800">Time Taken (min)</div>
                        </div>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <button id="retake-assessment" class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Retake Assessment
                        </button>
                        <button id="continue-tutorial" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Continue Tutorial
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Overlay -->
        <div id="assessment-overlay" class="assessment-overlay"></div>
        
        <!-- Assessment Modal -->
        <div id="assessment-modal" class="assessment-modal hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900"></h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="modal-content" class="text-gray-600">
                    <!-- Modal content will be loaded dynamically -->
                </div>
                <div class="flex justify-end mt-6">
                    <button id="modal-ok" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Global assessment state
        let currentAssessment = null;
        let currentQuestionIndex = 0;
        let userAnswers = {};
        let startTime = null;
        let assessmentQuestions = [];
        
        // Initialize assessment
        const urlParams = new URLSearchParams(window.location.search);
        const moduleId = urlParams.get('module_id') || 1;
        const stepId = urlParams.get('step_id') || 1;
        
        loadAssessment(moduleId, stepId);
        
        // Event handlers
        $('#retake-assessment').click(function() {
            resetAssessment();
        });
        
        $('#continue-tutorial').click(function() {
            window.location.href = 'index.php';
        });
        
        $('#close-modal').click(function() {
            closeModal();
        });
        
        $('#modal-ok').click(function() {
            closeModal();
        });
        
        function loadAssessment(moduleId, stepId) {
            startTime = new Date();
            
            $.ajax({
                url: '../api/tutorials/get-steps.php',
                method: 'GET',
                data: { module_id: moduleId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Find assessments for the current step
                        const currentStep = response.steps.find(step => step.id == stepId);
                        if (currentStep && currentStep.assessments) {
                            assessmentQuestions = currentStep.assessments;
                            displayAssessment();
                        } else {
                            showNoAssessmentMessage();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading assessment:', error);
                    showErrorMessage('Failed to load assessment. Please try again.');
                }
            });
        }
        
        function displayAssessment() {
            $('#loading-state').hide();
            $('#assessment-questions').removeClass('hidden');
            
            if (assessmentQuestions.length === 0) {
                showNoAssessmentMessage();
                return;
            }
            
            $('#total-questions').text(assessmentQuestions.length);
            showQuestion(0);
        }
        
        function showQuestion(index) {
            if (index >= assessmentQuestions.length) {
                completeAssessment();
                return;
            }
            
            currentQuestionIndex = index;
            const question = assessmentQuestions[index];
            
            $('#current-question').text(index + 1);
            $('#progress-bar').css('width', ((index + 1) / assessmentQuestions.length * 100) + '%');
            
            const questionHtml = createQuestionHtml(question, index);
            $('#assessment-questions').html(questionHtml);
            
            // Attach event handlers
            attachQuestionHandlers(question, index);
        }
        
        function createQuestionHtml(question, index) {
            let optionsHtml = '';
            
            if (question.question_type === 'multiple_choice' && question.options) {
                const options = JSON.parse(question.options);
                options.forEach((option, optionIndex) => {
                    optionsHtml += `
                        <button class="option-button w-full text-left p-4 border border-gray-300 rounded-lg mb-2 hover:bg-gray-50" 
                                data-option="${optionIndex}" data-answer="${option}">
                            <div class="flex items-center">
                                <div class="w-6 h-6 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full hidden"></div>
                                </div>
                                <span>${option}</span>
                            </div>
                        </button>
                    `;
                });
            } else if (question.question_type === 'true_false') {
                optionsHtml = `
                    <button class="option-button w-full text-left p-4 border border-gray-300 rounded-lg mb-2 hover:bg-gray-50" 
                            data-answer="true">
                        <div class="flex items-center">
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                <div class="w-2 h-2 bg-blue-600 rounded-full hidden"></div>
                            </div>
                            <span>True</span>
                        </div>
                    </button>
                    <button class="option-button w-full text-left p-4 border border-gray-300 rounded-lg mb-2 hover:bg-gray-50" 
                            data-answer="false">
                        <div class="flex items-center">
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                <div class="w-2 h-2 bg-blue-600 rounded-full hidden"></div>
                            </div>
                            <span>False</span>
                        </div>
                    </button>
                `;
            } else if (question.question_type === 'fill_blank') {
                optionsHtml = `
                    <div class="space-y-4">
                        <input type="text" id="fill-blank-answer" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Enter your answer here...">
                    </div>
                `;
            }
            
            return `
                <div class="question-card bg-white rounded-lg shadow p-6 mb-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Question ${index + 1}</h3>
                        <p class="text-gray-700 text-base">${question.question}</p>
                    </div>
                    
                    <div class="mb-6">
                        ${optionsHtml}
                    </div>
                    
                    <div id="question-feedback-${index}" class="hidden mb-6">
                        <!-- Feedback will be shown here -->
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button id="prev-question" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" 
                                ${index === 0 ? 'disabled' : ''}>
                            <i class="fas fa-chevron-left mr-2"></i>Previous
                        </button>
                        <button id="next-question" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            ${index === assessmentQuestions.length - 1 ? 'Submit Assessment' : 'Next'}
                            <i class="fas fa-chevron-right ml-2"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        function attachQuestionHandlers(question, index) {
            // Option selection handlers
            $('.option-button').click(function() {
                const answer = $(this).data('answer');
                userAnswers[index] = answer;
                
                // Update visual selection
                $('.option-button').removeClass('selected');
                $(this).addClass('selected');
                
                // Update radio button visual
                $(this).find('.w-2.h-2').removeClass('hidden');
                $('.option-button').not(this).find('.w-2.h-2').addClass('hidden');
            });
            
            // Navigation handlers
            $('#prev-question').click(function() {
                if (index > 0) {
                    showQuestion(index - 1);
                }
            });
            
            $('#next-question').click(function() {
                if (index < assessmentQuestions.length - 1) {
                    showQuestion(index + 1);
                } else {
                    submitAssessment();
                }
            });
        }
        
        function submitAssessment() {
            const endTime = new Date();
            const timeTaken = Math.round((endTime - startTime) / 1000 / 60); // minutes
            
            let correctAnswers = 0;
            let totalScore = 0;
            
            // Calculate results
            assessmentQuestions.forEach((question, index) => {
                const userAnswer = userAnswers[index];
                const correctAnswer = question.correct_answer;
                const points = question.points || 1;
                
                let isCorrect = false;
                
                if (question.question_type === 'multiple_choice' || question.question_type === 'true_false') {
                    isCorrect = userAnswer === correctAnswer;
                } else if (question.question_type === 'fill_blank') {
                    // For fill-in-the-blank, check if answer contains key words
                    const userWords = userAnswer ? userAnswer.toLowerCase().split(' ') : [];
                    const correctWords = correctAnswer.toLowerCase().split(' ');
                    const matchingWords = userWords.filter(word => correctWords.includes(word));
                    isCorrect = matchingWords.length >= (correctWords.length * 0.7);
                }
                
                if (isCorrect) {
                    correctAnswers++;
                    totalScore += points;
                }
            });
            
            const finalScore = Math.round((totalScore / assessmentQuestions.reduce((sum, q) => sum + (q.points || 1), 0)) * 100);
            
            // Display results
            $('#final-score').text(finalScore);
            $('#correct-answers').text(correctAnswers);
            $('#time-taken').text(timeTaken);
            
            $('#assessment-questions').hide();
            $('#assessment-complete').removeClass('hidden');
            
            // Submit results to server
            submitAssessmentResults(finalScore, correctAnswers, timeTaken);
        }
        
        function submitAssessmentResults(score, correctAnswers, timeTaken) {
            $.ajax({
                url: '../api/tutorials/submit-assessment.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    assessment_id: assessmentQuestions[0].id, // Use first assessment ID
                    answer: JSON.stringify(userAnswers),
                    time_spent: timeTaken * 60 // Convert to seconds
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('Assessment results submitted successfully');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error submitting assessment results:', error);
                }
            });
        }
        
        function resetAssessment() {
            currentQuestionIndex = 0;
            userAnswers = {};
            startTime = new Date();
            $('#assessment-complete').addClass('hidden');
            $('#assessment-questions').removeClass('hidden');
            showQuestion(0);
        }
        
        function completeAssessment() {
            $('#assessment-questions').hide();
            $('#assessment-complete').removeClass('hidden');
        }
        
        function showNoAssessmentMessage() {
            $('#loading-state').hide();
            $('#assessment-questions').html(`
                <div class="text-center py-12">
                    <i class="fas fa-question-circle text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Assessment Available</h3>
                    <p class="text-gray-600 mb-6">This tutorial step doesn't have an assessment.</p>
                    <a href="index.php" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        Return to Tutorials
                    </a>
                </div>
            `);
        }
        
        function showErrorMessage(message) {
            $('#loading-state').hide();
            $('#assessment-questions').html(`
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Error</h3>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <a href="index.php" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                        Return to Tutorials
                    </a>
                </div>
            `);
        }
        
        function showModal(title, content) {
            $('#modal-title').text(title);
            $('#modal-content').html(content);
            $('#assessment-overlay').show();
            $('#assessment-modal').removeClass('hidden');
        }
        
        function closeModal() {
            $('#assessment-overlay').hide();
            $('#assessment-modal').addClass('hidden');
        }
    });
    </script>
</body>
</html>
