-- Update Customer Service Training with Real Answers
-- This SQL will replace generic "Option A, B, C, D" with real, meaningful answers

-- First, let's see what we have
SELECT sq.id, sq.question, sq.correct_answer, qo.option_value, qo.option_text 
FROM scenario_questions sq 
JOIN question_options qo ON sq.id = qo.question_id 
WHERE sq.scenario_id IN (SELECT id FROM customer_service_scenarios)
ORDER BY sq.scenario_id, sq.question_order, qo.option_order;

-- Update CS001: Angry Guest Complaint - Question 1
UPDATE question_options 
SET option_text = 'Listen actively and acknowledge their concern' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 1) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Immediately offer compensation' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 1) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Call the manager right away' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 1) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Ask them to calm down' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 1) AND option_value = 'D';

-- Update CS001: Angry Guest Complaint - Question 2
UPDATE question_options 
SET option_text = 'Promise immediate compensation' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 2) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Investigate first, then offer appropriate solution' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 2) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Refuse any compensation' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 2) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Let the manager decide' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 2) AND option_value = 'D';

-- Update CS001: Angry Guest Complaint - Question 3
UPDATE question_options 
SET option_text = 'Send an email the next day' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 3) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Call them in their room' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 3) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Check with them personally before they leave' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 3) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Send a written apology' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 1 AND question_order = 3) AND option_value = 'D';

-- Update CS002: Special Dietary Request - Question 1
UPDATE question_options 
SET option_text = 'Take detailed notes and coordinate with kitchen staff' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 1) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Give them a list of safe restaurants' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 1) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Ask them to bring their own food' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 1) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Refer them to room service only' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 1) AND option_value = 'D';

-- Update CS002: Special Dietary Request - Question 2
UPDATE question_options 
SET option_text = 'Just the main allergies' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 2) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Complete list of allergies, severity, and cross-contamination concerns' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 2) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Only what they tell you' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 2) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Basic dietary preferences' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 2) AND option_value = 'D';

-- Update CS002: Special Dietary Request - Question 3
UPDATE question_options 
SET option_text = 'Send a quick email' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 3) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Write it on a sticky note' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 3) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Tell them verbally only' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 3) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Provide written documentation and discuss in person' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 3) AND option_value = 'D';

-- Update CS002: Special Dietary Request - Question 4
UPDATE question_options 
SET option_text = 'Check once and assume it is handled' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 4) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Follow up daily to ensure compliance' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 4) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Only check if the guest complains' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 4) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Monitor the first meal and then check periodically' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 2 AND question_order = 4) AND option_value = 'D';

-- Update CS003: Medical Emergency - Question 1
UPDATE question_options 
SET option_text = 'Call emergency services immediately' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 1) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Move the person to a private area' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 1) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Ask other guests to help' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 1) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Call the hotel manager first' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 1) AND option_value = 'D';

-- Update CS003: Medical Emergency - Question 2
UPDATE question_options 
SET option_text = 'Let them stay and watch' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 2) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Ask them to step back and give space' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 2) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Ask them to leave the lobby' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 2) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Ignore them and focus on the emergency' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 2) AND option_value = 'D';

-- Update CS003: Medical Emergency - Question 3
UPDATE question_options 
SET option_text = 'Try to move the person' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 3) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Give them water or food' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 3) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Stay with them and monitor their condition' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 3) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Search for their identification' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 3) AND option_value = 'D';

-- Update CS003: Medical Emergency - Question 4
UPDATE question_options 
SET option_text = 'Wait for emergency services to handle everything' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 4) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Document the incident and notify management' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 4) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Ask other guests to leave immediately' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 4) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Call the guest\'s family members' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 3 AND question_order = 4) AND option_value = 'D';

-- Update CS004: Noise Complaint - Question 1
UPDATE question_options 
SET option_text = 'Knock politely and explain the complaint' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 1) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Barging in and demand they stop' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 1) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Call security immediately' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 1) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Ignore the complaint' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 1) AND option_value = 'D';

-- Update CS004: Noise Complaint - Question 2
UPDATE question_options 
SET option_text = 'Accept their refusal' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 2) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Explain hotel policies and involve management' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 2) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Threaten to evict them' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 2) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Move the complaining guest instead' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 2) AND option_value = 'D';

-- Update CS004: Noise Complaint - Question 3
UPDATE question_options 
SET option_text = 'Assume the problem is solved' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 3) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Wait for them to complain again' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 3) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Send a written apology' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 3) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Check with them to ensure the issue is resolved' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 4 AND question_order = 3) AND option_value = 'D';

-- Update CS005: Lost Luggage - Question 1
UPDATE question_options 
SET option_text = 'Provide emergency toiletries and clothing assistance' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 1) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Wait for the airline to resolve it' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 1) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Ask them to buy new clothes' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 1) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Refer them to a local store' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 1) AND option_value = 'D';

-- Update CS005: Lost Luggage - Question 2
UPDATE question_options 
SET option_text = 'Give them the phone number only' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 2) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Provide contact info and help them make the call' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 2) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Call the airline for them' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 2) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Tell them to use their phone' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 2) AND option_value = 'D';

-- Update CS005: Lost Luggage - Question 3
UPDATE question_options 
SET option_text = 'Nothing, it\'s the airline\'s problem' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 3) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Only if they ask' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 3) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Regular check-ins and updates on their luggage status' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 3) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Compensation for their inconvenience' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 3) AND option_value = 'D';

-- Update CS005: Lost Luggage - Question 4
UPDATE question_options 
SET option_text = 'Wait for the airline to contact them' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 4) AND option_value = 'A';

UPDATE question_options 
SET option_text = 'Provide a list of local stores and services' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 4) AND option_value = 'B';

UPDATE question_options 
SET option_text = 'Offer to arrange emergency shopping assistance' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 4) AND option_value = 'C';

UPDATE question_options 
SET option_text = 'Ask them to handle it themselves' 
WHERE question_id = (SELECT id FROM scenario_questions WHERE scenario_id = 5 AND question_order = 4) AND option_value = 'D';

-- Verify the updates
SELECT 
    css.title as scenario_title,
    sq.question_order,
    sq.question,
    qo.option_value,
    qo.option_text,
    sq.correct_answer
FROM customer_service_scenarios css
JOIN scenario_questions sq ON css.id = sq.scenario_id
JOIN question_options qo ON sq.id = qo.question_id
ORDER BY css.id, sq.question_order, qo.option_order;
