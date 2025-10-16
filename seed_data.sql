-- Seed Data for Life Atlas Organizer
-- Demo User and Sample Data for Testing

-- Create demo user (password: demo123)
INSERT INTO users (name, email, password, is_admin, created_at) 
VALUES (
    'Demo User',
    'demo@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- demo123
    FALSE,
    CURRENT_TIMESTAMP
) ON CONFLICT (email) DO NOTHING;

-- Get demo user ID
DO $$
DECLARE
    demo_user_id INTEGER;
BEGIN
    SELECT id INTO demo_user_id FROM users WHERE email = 'demo@example.com';
    
    IF demo_user_id IS NOT NULL THEN
        -- Sample Assets
        INSERT INTO assets (user_id, name, category, value, acquisition_date, description) VALUES
        (demo_user_id, 'Laptop MacBook Pro', 'Electronics', 2500.00, '2024-01-15', 'Work laptop'),
        (demo_user_id, 'Car - Toyota Camry', 'Vehicle', 25000.00, '2023-06-10', 'Primary vehicle'),
        (demo_user_id, 'Smart TV 65"', 'Electronics', 1200.00, '2024-03-20', 'Living room TV'),
        (demo_user_id, 'iPhone 15 Pro', 'Electronics', 1199.00, '2024-09-15', 'Personal phone');
        
        -- Sample Bills
        INSERT INTO bills (user_id, name, amount, due_date, payment_status, recurring, frequency) VALUES
        (demo_user_id, 'Electric Bill', 150.00, DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '15 days', 'pending', TRUE, 'monthly'),
        (demo_user_id, 'Internet Service', 80.00, DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 days', 'paid', TRUE, 'monthly'),
        (demo_user_id, 'Water Bill', 50.00, DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '20 days', 'pending', TRUE, 'monthly'),
        (demo_user_id, 'Car Insurance', 120.00, DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '5 days', 'pending', TRUE, 'monthly');
        
        -- Sample Birthdays
        INSERT INTO birthdays (user_id, name, birth_date, relationship, notes) VALUES
        (demo_user_id, 'John Smith', '1990-05-15', 'Friend', 'College buddy'),
        (demo_user_id, 'Sarah Johnson', '1985-08-22', 'Family', 'Sister'),
        (demo_user_id, 'Mike Davis', '1992-12-03', 'Friend', 'Gym partner');
        
        -- Sample Finance Transactions
        INSERT INTO finance (user_id, type, category, amount, date, description) VALUES
        (demo_user_id, 'income', 'Salary', 5000.00, CURRENT_DATE - INTERVAL '1 day', 'Monthly salary'),
        (demo_user_id, 'expense', 'Groceries', 250.00, CURRENT_DATE - INTERVAL '2 days', 'Weekly shopping'),
        (demo_user_id, 'expense', 'Transportation', 60.00, CURRENT_DATE - INTERVAL '3 days', 'Gas'),
        (demo_user_id, 'expense', 'Entertainment', 50.00, CURRENT_DATE - INTERVAL '4 days', 'Movie tickets'),
        (demo_user_id, 'income', 'Freelance', 800.00, CURRENT_DATE - INTERVAL '5 days', 'Side project'),
        (demo_user_id, 'expense', 'Dining', 120.00, CURRENT_DATE - INTERVAL '1 week', 'Restaurant dinner'),
        (demo_user_id, 'expense', 'Utilities', 200.00, CURRENT_DATE - INTERVAL '10 days', 'Monthly utilities');
        
        -- Sample Goals
        INSERT INTO goals (user_id, title, description, category, target_date, progress, status) VALUES
        (demo_user_id, 'Learn Spanish', 'Complete B2 level Spanish course', 'Education', CURRENT_DATE + INTERVAL '6 months', 40, 'active'),
        (demo_user_id, 'Save $10,000', 'Emergency fund savings goal', 'Finance', CURRENT_DATE + INTERVAL '1 year', 65, 'active'),
        (demo_user_id, 'Run Marathon', 'Complete first full marathon', 'Health', CURRENT_DATE + INTERVAL '8 months', 25, 'active');
        
        -- Sample Habits
        INSERT INTO habits (user_id, name, description, frequency, streak, best_streak) VALUES
        (demo_user_id, 'Morning Exercise', '30 minutes workout', 'daily', 12, 30),
        (demo_user_id, 'Reading', 'Read for 30 minutes', 'daily', 8, 21),
        (demo_user_id, 'Meditation', '10 minutes mindfulness', 'daily', 5, 14);
        
        -- Sample Habit Logs
        INSERT INTO habit_logs (habit_id, user_id, completed_date, notes) 
        SELECT h.id, demo_user_id, CURRENT_DATE - INTERVAL '1 day', 'Completed morning routine'
        FROM habits h WHERE h.user_id = demo_user_id AND h.name = 'Morning Exercise';
        
        -- Sample Health Records
        INSERT INTO health (user_id, date, weight, exercise_minutes, water_intake, sleep_hours, notes) VALUES
        (demo_user_id, CURRENT_DATE - INTERVAL '1 day', 75.5, 45, 2.5, 7.5, 'Good workout session'),
        (demo_user_id, CURRENT_DATE - INTERVAL '2 days', 75.8, 30, 2.0, 7.0, 'Light exercise'),
        (demo_user_id, CURRENT_DATE - INTERVAL '3 days', 76.0, 60, 3.0, 8.0, 'Long run');
        
        -- Sample Hobbies
        INSERT INTO hobbies (user_id, name, category, time_spent_hours, progress_notes) VALUES
        (demo_user_id, 'Photography', 'Creative', 120, 'Learning portrait photography'),
        (demo_user_id, 'Cooking', 'Culinary', 80, 'Mastering Italian cuisine');
        
        -- Sample Investments
        INSERT INTO investments (user_id, name, type, amount_invested, current_value, purchase_date, notes) VALUES
        (demo_user_id, 'S&P 500 Index Fund', 'ETF', 10000.00, 12500.00, '2023-01-15', 'Long-term investment'),
        (demo_user_id, 'Tech Stocks Portfolio', 'Stocks', 5000.00, 6200.00, '2023-06-01', 'Growth stocks'),
        (demo_user_id, 'Real Estate Fund', 'REIT', 8000.00, 8800.00, '2023-09-10', 'Dividend income');
        
        -- Sample Journal Entries
        INSERT INTO journal (user_id, date, title, content, mood) VALUES
        (demo_user_id, CURRENT_DATE - INTERVAL '1 day', 'Productive Day', 'Had a very productive day at work. Completed two major projects and got positive feedback from the team.', 'happy'),
        (demo_user_id, CURRENT_DATE - INTERVAL '3 days', 'Weekend Plans', 'Planning a hiking trip this weekend. Excited to explore the new trail.', 'excited'),
        (demo_user_id, CURRENT_DATE - INTERVAL '5 days', 'Reflection', 'Spent time reflecting on my goals and progress. Need to focus more on health habits.', 'thoughtful');
        
        -- Sample Learning
        INSERT INTO learning (user_id, title, type, platform, progress, status, start_date, notes) VALUES
        (demo_user_id, 'Advanced JavaScript', 'Course', 'Udemy', 75, 'in_progress', CURRENT_DATE - INTERVAL '2 months', 'Great course on JS patterns'),
        (demo_user_id, 'Atomic Habits', 'Book', 'Amazon', 100, 'completed', CURRENT_DATE - INTERVAL '3 months', 'Excellent book on habit formation'),
        (demo_user_id, 'Python for Data Science', 'Course', 'Coursera', 45, 'in_progress', CURRENT_DATE - INTERVAL '1 month', 'Learning pandas and numpy');
        
        -- Sample Media
        INSERT INTO media (user_id, title, type, status, rating, review, completion_date) VALUES
        (demo_user_id, 'The Shawshank Redemption', 'Movie', 'watched', 5, 'Masterpiece of cinema', CURRENT_DATE - INTERVAL '1 week'),
        (demo_user_id, 'Breaking Bad', 'TV Series', 'watching', NULL, NULL, NULL),
        (demo_user_id, 'Inception', 'Movie', 'to_watch', NULL, NULL, NULL);
        
        -- Sample Subscriptions
        INSERT INTO subscriptions (user_id, name, cost, billing_cycle, renewal_date, status, category) VALUES
        (demo_user_id, 'Netflix', 15.99, 'monthly', CURRENT_DATE + INTERVAL '15 days', 'active', 'Entertainment'),
        (demo_user_id, 'Spotify Premium', 9.99, 'monthly', CURRENT_DATE + INTERVAL '5 days', 'active', 'Music'),
        (demo_user_id, 'Adobe Creative Cloud', 52.99, 'monthly', CURRENT_DATE + INTERVAL '20 days', 'active', 'Software');
        
        -- Sample Tasks
        INSERT INTO tasks (user_id, title, description, category, priority, due_date, status) VALUES
        (demo_user_id, 'Finish quarterly report', 'Complete Q4 financial report', 'Work', 'high', CURRENT_DATE + INTERVAL '3 days', 'in_progress'),
        (demo_user_id, 'Buy groceries', 'Weekly grocery shopping', 'Personal', 'medium', CURRENT_DATE + INTERVAL '1 day', 'pending'),
        (demo_user_id, 'Schedule dentist appointment', 'Routine checkup', 'Health', 'low', CURRENT_DATE + INTERVAL '1 week', 'pending'),
        (demo_user_id, 'Review investment portfolio', 'Monthly portfolio review', 'Finance', 'high', CURRENT_DATE + INTERVAL '2 days', 'pending');
        
        -- Sample Crypto Portfolio
        INSERT INTO crypto_portfolio (user_id, symbol, name, amount, purchase_price, purchase_date, notes) VALUES
        (demo_user_id, 'BTC', 'Bitcoin', 0.5, 45000.00, '2024-01-15', 'Long-term hold'),
        (demo_user_id, 'ETH', 'Ethereum', 5.0, 3000.00, '2024-02-20', 'DeFi investment'),
        (demo_user_id, 'ADA', 'Cardano', 1000.0, 0.50, '2024-03-10', 'Staking rewards');
        
        -- Sample Crypto Alerts
        INSERT INTO crypto_alerts (user_id, symbol, alert_type, price_target, is_active) VALUES
        (demo_user_id, 'BTC', 'above', 50000.00, TRUE),
        (demo_user_id, 'ETH', 'below', 2500.00, TRUE);
        
        -- Create sample notifications
        INSERT INTO notifications (user_id, type, title, message, is_read) VALUES
        (demo_user_id, 'info', 'Welcome to Life Atlas!', 'Your personal management system is ready to use. Start by exploring the modules.', FALSE);
        
        RAISE NOTICE 'Seed data inserted successfully for demo user!';
        RAISE NOTICE 'Demo user created: email=demo@example.com, password=demo123';
    END IF;
END $$;
