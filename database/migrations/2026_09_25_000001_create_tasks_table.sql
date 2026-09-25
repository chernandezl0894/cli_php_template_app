CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    status TEXT NOT NULL DEFAULT 'inbox',
    due_date DATETIME NULL,
    reminder_at DATETIME NULL,
    reminder_sent INTEGER NOT NULL DEFAULT 0,
    is_recurring INTEGER NOT NULL DEFAULT 0,
    recurrence_pattern TEXT NOT NULL DEFAULT 'daily',-- 'daily', 'weekly', 'monthly'
    next_ocurrence_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_tasks_reminder ON tasks(reminder_at, reminder_sent);
