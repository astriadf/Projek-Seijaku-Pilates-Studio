CREATE DATABASE IF NOT EXISTS seijaku_pilates_new;
USE seijaku_pilates_new;

-- ========== 1. USERS ==========
CREATE TABLE users (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    username        VARCHAR(50) UNIQUE NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    full_name       VARCHAR(100) NOT NULL,
    phone           VARCHAR(20),
    avatar          VARCHAR(255) DEFAULT 'default-avatar.png',
    role            ENUM('member','admin') DEFAULT 'member',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 2. INSTRUCTORS (8 sample) ==========
CREATE TABLE instructors (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    name                VARCHAR(100) NOT NULL,
    bio                 TEXT,
    specialty           VARCHAR(100),
    experience_years    INT,
    avatar              VARCHAR(255) DEFAULT 'default-instructor.png',
    instagram_url       VARCHAR(255),
    is_active           BOOLEAN DEFAULT TRUE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active    (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 3. CLASS TYPES ==========
CREATE TABLE class_types (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(100) NOT NULL,
    description     TEXT,
    difficulty      ENUM('beginner','intermediate','advanced') NOT NULL,
    duration_minutes INT DEFAULT 60,
    price           DECIMAL(10,2) NOT NULL,
    image           VARCHAR(255),
    is_active       BOOLEAN DEFAULT TRUE,
    INDEX idx_diff  (difficulty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 4. CLASS SCHEDULES ==========
CREATE TABLE class_schedules (
    id                      INT PRIMARY KEY AUTO_INCREMENT,
    class_type_id           INT NOT NULL,
    instructor_id           INT NOT NULL,
    class_date              DATE NOT NULL,
    class_time              TIME NOT NULL,
    max_participants        INT DEFAULT 10,
    current_participants    INT DEFAULT 0,
    status                  ENUM('available','full','cancelled') DEFAULT 'available',
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_type_id) REFERENCES class_types(id),
    FOREIGN KEY (instructor_id) REFERENCES instructors(id),
    INDEX idx_date          (class_date),
    INDEX idx_status        (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 5. BOOKINGS ==========
CREATE TABLE bookings (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    user_id             INT NOT NULL,
    class_schedule_id   INT NOT NULL,
    booking_date        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status              ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    notes               TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (class_schedule_id) REFERENCES class_schedules(id),
    INDEX idx_user      (user_id),
    INDEX idx_status2   (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 6. VIDEOS (12 sample) - ERROR FIXED ==========
CREATE TABLE videos (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    youtube_url     VARCHAR(255) NOT NULL,
    thumbnail_url   VARCHAR(255),
    category        VARCHAR(50),                    -- <- field ini yang hilang
    difficulty      ENUM('beginner','intermediate','advanced'),
    instructor_id   INT,
    is_free         BOOLEAN DEFAULT TRUE,
    views           INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id),
    INDEX idx_free  (is_free)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 7. TESTIMONIALS (10 sample) ==========
CREATE TABLE testimonials (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    rating          INT CHECK (rating >= 1 AND rating <= 5),
    comment         TEXT NOT NULL,
    is_approved     BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_appr  (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== 8. ACHIEVEMENTS (Ghibli badges) ==========
CREATE TABLE achievements (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    type            ENUM('first_class','ten_classes','twentyfive_classes','forty_classes') NOT NULL,
    name            VARCHAR(100) NOT NULL,
    description     TEXT,
    badge_icon      VARCHAR(50),        -- emoji Ghibli 🌱🔥🌊
    earned_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_user_achievement (user_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========== SAMPLE DATA ==========

-- Instructors (8 orang)
INSERT INTO instructors (name, bio, specialty, experience_years, avatar) VALUES
('Sarah Johnson', 'Classical Pilates specialist with 8 years experience', 'Classical Pilates', 8, 'sarah.jpg'),
('Michael Chen', 'Core strength & flexibility expert', 'Mat Pilates', 6, 'michael.jpg'),
('Emma Rodriguez', 'Stott certified, focus on rehabilitation', 'Stott Pilates', 7, 'emma.jpg'),
('Luna Park', 'Korean instructor, K-Pop Pilates creator', 'Dance Pilates', 5, 'luna.jpg'),
('Hiroshi Tanaka', 'Japanese method, Zen Pilates master', 'Zen Pilates', 10, 'hiroshi.jpg'),
('Sofia Martinez', 'Prenatal & postnatal specialist', 'Prenatal Pilates', 6, 'sofia.jpg'),
('David Kim', 'Athletic performance enhancement', 'Sports Pilates', 7, 'david.jpg'),
('Isabella Rossi', 'Italian contemporary style', 'Contemporary Pilates', 9, 'isabella.jpg');

-- Class types
INSERT INTO class_types (name, description, difficulty, duration_minutes, price) VALUES
('Classic Pilates', 'Traditional method for all levels', 'beginner', 60, 150000),
('Mat Pilates', 'Floor exercises using body weight', 'beginner', 45, 120000),
('Reformer Pilates', 'Advanced machine-based workout', 'intermediate', 75, 200000),
('Clinical Pilates', 'Therapeutic approach for recovery', 'intermediate', 60, 180000),
('Contemporary Pilates', 'Modern dynamic movements', 'advanced', 90, 220000);

-- Videos (12 video) - SUDAH INCLUDE category
INSERT INTO videos (title, description, youtube_url, thumbnail_url, category, difficulty, instructor_id, views) VALUES
('Beginner Mat - 20min', 'Perfect intro to Pilates', 'https://youtube.com/watch?v=abc123', 'thumb1.jpg', 'Mat Workout', 'beginner', 1, 1520),
('Core Strength Routine', 'Build strong core muscles', 'https://youtube.com/watch?v=def456', 'thumb2.jpg', 'Core Training', 'intermediate', 2, 2300),
('Flexibility Stretch', 'Gentle stretching routine', 'https://youtube.com/watch?v=ghi789', 'thumb3.jpg', 'Stretching', 'beginner', 3, 890),
('Advanced Reformer Flow', 'High intensity reformer', 'https://youtube.com/watch?v=jkl012', 'thumb4.jpg', 'Reformer', 'advanced', 4, 3100),
('Zen Morning Routine', 'Start day with calm movements', 'https://youtube.com/watch?v=mno345', 'thumb5.jpg', 'Morning Flow', 'beginner', 5, 1800),
('Prenatal Safe Workout', 'Safe for pregnant women', 'https://youtube.com/watch?v=pqr678', 'thumb6.jpg', 'Prenatal', 'beginner', 6, 950),
('Athletic Power Core', 'For athletes & runners', 'https://youtube.com/watch?v=stu901', 'thumb7.jpg', 'Athletic', 'advanced', 7, 2700),
('Contemporary Flow', 'Modern dynamic movements', 'https://youtube.com/watch?v=vwx234', 'thumb8.jpg', 'Contemporary', 'intermediate', 8, 1600),
('15-min Quick Fix', 'Short but effective', 'https://youtube.com/watch?v=yza567', 'thumb9.jpg', 'Quick Workout', 'beginner', 1, 4200),
('Dance Pilates Party', 'Fun dance-inspired Pilates', 'https://youtube.com/watch?v=bcd890', 'thumb10.jpg', 'Dance', 'intermediate', 4, 3800),
('Senior Friendly Moves', 'Gentle for older adults', 'https://youtube.com/watch?v=efg123', 'thumb11.jpg', 'Senior', 'beginner', 3, 1200),
('Core & Glutes Focus', 'Targeted lower body workout', 'https://youtube.com/watch?v=hij456', 'thumb12.jpg', 'Lower Body', 'intermediate', 2, 2100);

-- Testimonials (10 review)
INSERT INTO testimonials (user_id, rating, comment, is_approved) VALUES
(1, 5, 'Seijaku changed my life! The instructors are amazing and the Ghibli atmosphere is so calming.', 1),
(2, 5, 'Love the Zen Pilates with Hiroshi-sensei. Feel like I am in Spirited Away studio!', 1),
(3, 4, 'Great for beginners. Luna''s K-Pop Pilates is so much fun!', 1),
(4, 5, 'Prenatal classes with Sofia saved my pregnancy. Highly recommend!', 1),
(5, 5, 'David''s athletic program improved my marathon time significantly.', 1),
(6, 4, 'Good variety of classes. The online videos help when I cant come to studio.', 1),
(7, 5, 'Isabella''s contemporary style is unique in Purbalingga. Worth every rupiah!', 1),
(8, 5, 'Sarah''s classical method is perfect for my back pain recovery.', 1),
(9, 4, 'Michael''s core classes are challenging but effective. Lost 5kg in 2 months!', 1),
(10, 5, 'The whole family loves Seijaku! Even my husband enjoys the sessions now.', 1);

-- Admin account
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@seijaku.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');