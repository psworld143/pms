-- Training Scenarios Seed
-- Check-in Process and Overbooking Situation with real options and answers

START TRANSACTION;

-- Scenario 1: Check-in Process
INSERT INTO training_scenarios (title, description, instructions, category, difficulty)
VALUES ('Check-in Process',
        'Handle a guest check-in with special requests',
        'Follow SOPs while accommodating requests professionally.',
        'front_desk', 'beginner');
SET @scenario_id := LAST_INSERT_ID();

-- Q1
INSERT INTO scenario_questions (scenario_id, question_order, question, correct_answer)
VALUES (@scenario_id, 1, 'What is the first step in the check-in process?', 'B');
SET @q1 := LAST_INSERT_ID();

INSERT INTO question_options (question_id, option_order, option_value, option_text) VALUES
(@q1, 1, 'A', 'Offer room upgrade options'),
(@q1, 2, 'B', 'Greet the guest and verify their reservation/ID'),
(@q1, 3, 'C', 'Issue the room key immediately'),
(@q1, 4, 'D', 'Collect payment receipt first');

-- Q2
INSERT INTO scenario_questions (scenario_id, question_order, question, correct_answer)
VALUES (@scenario_id, 2, 'Why must you verify a guest''s ID at check-in?', 'C');
SET @q2 := LAST_INSERT_ID();

INSERT INTO question_options (question_id, option_order, option_value, option_text) VALUES
(@q2, 1, 'A', 'To determine upgrade eligibility only'),
(@q2, 2, 'B', 'To get their loyalty number'),
(@q2, 3, 'C', 'To prevent fraud and match the reservation to the right person'),
(@q2, 4, 'D', 'It is optional if they paid online');

COMMIT;

START TRANSACTION;

-- Scenario 2: Overbooking Situation
INSERT INTO training_scenarios (title, description, instructions, category, difficulty)
VALUES ('Overbooking Situation',
        'Manage a front-desk overbooking case professionally.',
        'Acknowledge the issue, apologize, offer solutions and document actions.',
        'front_desk', 'intermediate');
SET @scenario_id := LAST_INSERT_ID();

-- Q1
INSERT INTO scenario_questions (scenario_id, question_order, question, correct_answer)
VALUES (@scenario_id, 1, 'What is the appropriate first response in an overbooking situation?', 'A');
SET @q1 := LAST_INSERT_ID();

INSERT INTO question_options (question_id, option_order, option_value, option_text) VALUES
(@q1, 1, 'A', 'Apologize, acknowledge the inconvenience, and begin checking alternative solutions'),
(@q1, 2, 'B', 'Explain that the system made a mistake and ask them to return later'),
(@q1, 3, 'C', 'Offer a complimentary drink but take no action'),
(@q1, 4, 'D', 'Ask the guest to call reservations');

-- Q2
INSERT INTO scenario_questions (scenario_id, question_order, question, correct_answer)
VALUES (@scenario_id, 2, 'Which is the best immediate solution if no room is available?', 'B');
SET @q2 := LAST_INSERT_ID();

INSERT INTO question_options (question_id, option_order, option_value, option_text) VALUES
(@q2, 1, 'A', 'Ask the guest to wait until a room frees up'),
(@q2, 2, 'B', 'Walk the guest to a partnered nearby hotel at equal or higher category'),
(@q2, 3, 'C', 'Offer a late check-in the next day'),
(@q2, 4, 'D', 'Cancel the reservation and refund only');

-- Q3
INSERT INTO scenario_questions (scenario_id, question_order, question, correct_answer)
VALUES (@scenario_id, 3, 'What is a fair compensation to offer when walking a guest?', 'D');
SET @q3 := LAST_INSERT_ID();

INSERT INTO question_options (question_id, option_order, option_value, option_text) VALUES
(@q3, 1, 'A', 'A verbal apology only'),
(@q3, 2, 'B', 'A complimentary drink voucher'),
(@q3, 3, 'C', 'Free parking'),
(@q3, 4, 'D', 'Transportation to partner hotel + rate match/upgrade + future stay discount');

-- Q4
INSERT INTO scenario_questions (scenario_id, question_order, question, correct_answer)
VALUES (@scenario_id, 4, 'What should be documented after resolving an overbooking?', 'C');
SET @q4 := LAST_INSERT_ID();

INSERT INTO question_options (question_id, option_order, option_value, option_text) VALUES
(@q4, 1, 'A', 'Only the guest name'),
(@q4, 2, 'B', 'Only the compensation offered'),
(@q4, 3, 'C', 'Guest details, action taken, partner hotel info, costs, and staff initials'),
(@q4, 4, 'D', 'Nothing if the guest accepted the solution');

COMMIT;


