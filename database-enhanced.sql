-- Enhanced database with additional events and exhibitions (videos only for virtual tours)
-- Create database
CREATE DATABASE IF NOT EXISTS museum_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE museum_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exhibitions table
CREATE TABLE exhibitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    location VARCHAR(255),
    image VARCHAR(255),
    category VARCHAR(100),
    status ENUM('current', 'upcoming', 'past') DEFAULT 'upcoming',
    featured BOOLEAN DEFAULT FALSE,
    curator VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    start_time TIME,
    end_time TIME,
    location VARCHAR(255),
    image VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    capacity INT DEFAULT 0,
    event_type VARCHAR(100),
    featured BOOLEAN DEFAULT FALSE,
    instructor VARCHAR(255),
    requirements TEXT,
    related_exhibition_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (related_exhibition_id) REFERENCES exhibitions(id) ON DELETE SET NULL
);

-- Collections table
CREATE TABLE collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    artist VARCHAR(255),
    year VARCHAR(50),
    medium VARCHAR(255),
    image VARCHAR(255),
    category VARCHAR(100),
    dimensions VARCHAR(255),
    acquisition_date DATE,
    provenance TEXT,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Virtual Tours table (only table with video_url)
CREATE TABLE virtual_tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    image VARCHAR(255),
    video_url VARCHAR(255),
    highlights TEXT,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced Memberships table with additional fields
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    membership_type ENUM('individual', 'family', 'student', 'senior', 'patron') NOT NULL,
    member_id VARCHAR(50) UNIQUE NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'active',
    price_paid DECIMAL(10,2) DEFAULT 0.00,
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer', 'paypal', 'cash', 'check') DEFAULT 'credit_card',
    billing_address TEXT,
    phone_number VARCHAR(20),
    newsletter_opt_in BOOLEAN DEFAULT TRUE,
    card_mailed BOOLEAN DEFAULT FALSE,
    card_mailed_date DATE NULL,
    renewal_reminder_sent BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_member_id (member_id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_expiry (end_date, status)
);

-- Newsletter subscriptions
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event bookings
CREATE TABLE event_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    tickets INT DEFAULT 1,
    total_price DECIMAL(10,2),
    booking_status ENUM('confirmed', 'cancelled', 'pending') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Search logs table for analytics
CREATE TABLE search_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    search_term VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User activity logs
CREATE TABLE user_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) VALUES 
('Admin', 'User', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert enhanced exhibitions with appropriate images
INSERT INTO exhibitions (title, description, start_date, end_date, location, category, status, featured, curator, price, image) VALUES 
('Modern Masterpieces: A Century of Innovation', 'Explore the evolution of modern art through a curated selection of masterpieces spanning the 20th century. This exhibition showcases the revolutionary techniques and bold visions that defined an era of artistic innovation, featuring works by Picasso, Kandinsky, Pollock, and other pioneering artists.', '2024-03-01', '2024-08-15', 'Main Gallery - East Wing', 'Modern Art', 'current', TRUE, 'Dr. Sarah Mitchell', 15.00, 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&h=600&fit=crop'),

('Renaissance Reimagined: Classical Meets Contemporary', 'A fresh look at Renaissance art and its influence on contemporary artistic expression. This groundbreaking exhibition juxtaposes masterworks from the 15th and 16th centuries with modern interpretations, revealing the timeless relevance of Renaissance ideals.', '2024-02-15', '2024-07-30', 'Gallery 2 - Renaissance Wing', 'Renaissance', 'current', TRUE, 'Prof. Marco Benedetti', 18.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop'),

('Digital Frontiers: Art in the Age of Technology', 'Exploring the intersection of art and technology in the digital age. This immersive exhibition features interactive installations, virtual reality experiences, and digital artworks that challenge traditional boundaries between artist and audience.', '2024-04-01', '2024-09-05', 'Digital Gallery - Innovation Center', 'Digital Art', 'current', FALSE, 'Dr. Alex Chen', 20.00, 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop'),

('Impressionist Gardens: Light and Color in Nature', 'Step into the world of Impressionist masters and their revolutionary approach to capturing light and atmosphere. This exhibition features rare works by Monet, Renoir, and Degas, focusing on their outdoor painting techniques and garden scenes.', '2024-09-15', '2025-01-15', 'Impressionist Hall - West Wing', 'Impressionism', 'upcoming', TRUE, 'Dr. Claire Dubois', 16.00, 'https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=800&h=600&fit=crop'),

('Photography Through Time: From Camera Obscura to Digital', 'A comprehensive journey through the evolution of photography from its earliest days to modern digital techniques. Featuring works by Ansel Adams, Henri Cartier-Bresson, Annie Leibovitz, and contemporary digital artists.', '2024-10-01', '2025-02-28', 'Photography Wing - Level 3', 'Photography', 'upcoming', FALSE, 'James Rodriguez', 12.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop'),

('Ancient Civilizations: Treasures of the Past', 'Discover artifacts and artworks from ancient civilizations around the world. From Egyptian sarcophagi to Greek pottery, Roman sculptures to Mesopotamian tablets, this exhibition spans 5,000 years of human creativity.', '2023-10-01', '2024-02-28', 'History Hall - Ground Floor', 'Ancient Art', 'past', FALSE, 'Dr. Elena Vasquez', 14.00, 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=800&h=600&fit=crop'),

('Abstract Expressions: The Power of Non-Representational Art', 'Dive deep into the world of abstract art with works by Jackson Pollock, Mark Rothko, Willem de Kooning, and contemporary abstract artists. This exhibition explores how abstract art communicates emotion and meaning without depicting recognizable subjects.', '2024-11-01', '2025-03-15', 'Contemporary Gallery - Level 2', 'Abstract Art', 'upcoming', TRUE, 'Dr. Michael Thompson', 17.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop'),

('Sculpture in Space: Three-Dimensional Masterworks', 'Experience the power of sculpture from classical marble works to contemporary installations. This exhibition features pieces by Michelangelo, Rodin, Moore, and modern sculptors who push the boundaries of form and material.', '2024-12-01', '2025-04-30', 'Sculpture Garden & Gallery', 'Sculpture', 'upcoming', FALSE, 'Isabella Romano', 15.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop');

-- Insert enhanced events with detailed information and appropriate images
INSERT INTO events (title, description, event_date, start_time, end_time, location, event_type, price, capacity, featured, instructor, requirements, image, related_exhibition_id) VALUES 

('Guided Tour: Modern Masterpieces Deep Dive', 'Join our expert curator Dr. Sarah Mitchell for an in-depth guided tour of our Modern Masterpieces exhibition. Learn about the revolutionary techniques, historical context, and hidden stories behind each masterwork. This 90-minute tour includes exclusive access to conservation areas and behind-the-scenes insights.', '2024-06-15', '14:00:00', '15:30:00', 'Main Gallery - East Wing', 'guided_tour', 15.00, 25, TRUE, 'Dr. Sarah Mitchell', 'Comfortable walking shoes recommended', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop', 1),

('Artist Talk: Contemporary Expressions in Digital Art', 'Meet with renowned digital artist Alex Chen and contemporary painters Maria Santos and David Kim as they discuss their creative processes, the intersection of traditional and digital media, and the future of artistic expression in the digital age.', '2024-06-18', '18:30:00', '20:00:00', 'Auditorium - Level 1', 'artist_talk', 20.00, 150, TRUE, 'Alex Chen, Maria Santos, David Kim', 'None', 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=600&h=400&fit=crop', 3),

('Family Workshop: Create Your Own Masterpiece', 'A hands-on art workshop designed for families with children ages 5-12. Learn basic painting techniques, color theory, and composition while creating your own artwork inspired by the masters. All materials provided, and each family takes home their creations.', '2024-06-22', '10:00:00', '13:00:00', 'Education Center - Workshop Room A', 'family_workshop', 35.00, 20, FALSE, 'Emma Rodriguez', 'Suitable for ages 5-12 with adult supervision', 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=600&h=400&fit=crop', NULL),

('Summer Concert Series: Jazz in the Garden', 'Enjoy an evening of smooth jazz in our beautiful sculpture garden. The Marcus Williams Quartet will perform classic jazz standards and contemporary pieces while you relax among world-class sculptures under the stars.', '2024-07-05', '19:00:00', '21:00:00', 'Sculpture Garden', 'concert', 25.00, 200, TRUE, 'Marcus Williams Quartet', 'Bring blankets or chairs for lawn seating', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&h=400&fit=crop', NULL),

('Photography Workshop: Mastering Light and Shadow', 'Learn the fundamentals of photography with professional photographer James Rodriguez. This comprehensive workshop covers camera settings, composition, lighting techniques, and post-processing basics. Bring your own camera (DSLR or mirrorless preferred).', '2024-07-12', '09:00:00', '16:00:00', 'Photography Studio - Level 3', 'photography_workshop', 85.00, 15, FALSE, 'James Rodriguez', 'Bring your own camera, basic photography knowledge helpful', 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=600&h=400&fit=crop', 5),

('Lecture Series: The Renaissance Revolution', 'Professor Marco Benedetti presents a fascinating lecture on how Renaissance artists revolutionized art and culture. Explore the social, political, and technological factors that enabled this artistic flowering and its lasting impact on Western civilization.', '2024-07-18', '19:00:00', '20:30:00', 'Lecture Hall - Level 2', 'lecture', 12.00, 100, FALSE, 'Prof. Marco Benedetti', 'None', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=400&fit=crop', 2),

('Wine & Art Evening: Impressionist Inspirations', 'An elegant evening combining fine wine tasting with art appreciation. Sommelier Catherine Laurent will guide you through wines that inspired Impressionist painters, while art historian Dr. Claire Dubois shares stories of the artists\' lives and techniques.', '2024-08-02', '18:00:00', '21:00:00', 'Members Lounge - Level 2', 'wine_tasting', 65.00, 40, TRUE, 'Catherine Laurent & Dr. Claire Dubois', 'Ages 21+, advance registration required', 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=600&h=400&fit=crop', 4),

('Children\'s Art Camp: Young Artists Program', 'A week-long art camp for children ages 8-14. Each day focuses on different artistic techniques and mediums including drawing, painting, sculpture, and digital art. Professional art instructors guide students through projects inspired by museum collections.', '2024-08-05', '09:00:00', '15:00:00', 'Education Center - All Rooms', 'art_camp', 150.00, 30, FALSE, 'Art Education Team', 'Ages 8-14, lunch provided, week-long commitment', 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&h=400&fit=crop', NULL),

('Sculpture Workshop: Clay to Creation', 'Learn the ancient art of sculpture with master sculptor Isabella Romano. This hands-on workshop covers basic clay techniques, form and proportion, and finishing methods. Create your own small sculpture to take home.', '2024-08-10', '10:00:00', '16:00:00', 'Sculpture Studio - Basement Level', 'sculpture_workshop', 75.00, 12, FALSE, 'Isabella Romano', 'Wear clothes that can get dirty, all materials provided', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop', 8),

('Evening Gala: Celebrating Modern Art', 'Our annual summer gala celebrating modern art and supporting museum education programs. Enjoy cocktails, gourmet dinner, live music, and exclusive access to exhibitions. Silent auction features artwork donations from local and international artists.', '2024-08-15', '18:00:00', '23:00:00', 'Main Gallery & Atrium', 'gala', 150.00, 300, TRUE, 'Various Artists & Performers', 'Formal attire, advance ticket purchase required', 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600&h=400&fit=crop', 1),

('Digital Art Workshop: NFTs and Blockchain', 'Explore the world of digital art and NFTs with tech artist Alex Chen. Learn about blockchain technology, digital art creation tools, and the future of art ownership in the digital age. Hands-on session includes creating your first digital artwork.', '2024-08-22', '14:00:00', '17:00:00', 'Digital Gallery - Computer Lab', 'digital_workshop', 45.00, 20, FALSE, 'Alex Chen', 'Basic computer skills required, laptops provided', 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=600&h=400&fit=crop', 3),

('Mindfulness & Art: Meditation in the Galleries', 'Combine art appreciation with mindfulness practice in this unique experience. Certified meditation instructor Sarah Kim guides participants through mindful observation exercises in front of carefully selected artworks, promoting relaxation and deeper artistic connection.', '2024-08-25', '10:00:00', '11:30:00', 'Quiet Gallery - Level 3', 'meditation', 20.00, 15, FALSE, 'Sarah Kim', 'Comfortable clothing, meditation experience not required', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&h=400&fit=crop', NULL),

('Behind the Scenes: Conservation Lab Tour', 'Get exclusive access to our conservation lab and see how our expert conservators preserve and restore artworks. Learn about the science behind art conservation, see works in progress, and discover the detective work involved in art restoration.', '2024-09-01', '13:00:00', '14:30:00', 'Conservation Lab - Restricted Area', 'special_tour', 25.00, 10, TRUE, 'Conservation Team', 'Limited capacity, advance booking essential', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop', NULL),

('Film Screening: Art & Artists Documentary Series', 'Monthly documentary screening featuring acclaimed films about artists and art movements. This month: "The Mystery of Chi Wara" - exploring African art and its influence on modern Western artists. Discussion follows with film curator and art historians.', '2024-09-08', '19:00:00', '21:30:00', 'Museum Theater - Basement Level', 'film_screening', 10.00, 80, FALSE, 'Film Curator Team', 'None', 'https://images.unsplash.com/photo-1489599735734-79b4169c2a78?w=600&h=400&fit=crop', NULL);

-- Insert enhanced collections with appropriate images
INSERT INTO collections (title, description, artist, year, category, dimensions, acquisition_date, provenance, featured, image) VALUES 

('The Starry Night (Study)', 'A preparatory study for van Gogh\'s famous masterpiece, showing the artist\'s process of developing the swirling night sky composition. This rare work demonstrates his innovative brushwork and color theory that would influence generations of artists.', 'Vincent van Gogh', '1889', 'Post-Impressionism', '73.7 cm × 92.1 cm', '1975-03-15', 'Private collection, Paris; acquired through donation', TRUE, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=500&fit=crop'),

('David (Bronze Cast)', 'A bronze cast of Michelangelo\'s Renaissance masterpiece, created from the original molds. This sculpture represents the pinnacle of Renaissance art and humanist ideals, depicting the Biblical hero David before his battle with Goliath.', 'Michelangelo Buonarroti', '1501-1504', 'Renaissance Sculpture', '517 cm height', '1962-08-20', 'Authorized cast from Fonderia Artistica Ferdinando Marinelli', TRUE, 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=600&fit=crop'),

('Guernica (Tapestry)', 'A faithful tapestry reproduction of Picasso\'s powerful anti-war painting, woven by master craftsmen in France. This monumental work depicts the horrors of war and remains one of the most powerful political statements in art history.', 'Pablo Picasso', '1937', 'Modern Art', '349.3 cm × 776.6 cm', '1980-11-12', 'Commissioned reproduction, estate approved', FALSE, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=400&fit=crop'),

('The Thinker (Original Cast)', 'An original bronze cast of Rodin\'s iconic sculpture, created during the artist\'s lifetime. Originally part of "The Gates of Hell," this figure has become a universal symbol of intellectual activity and philosophical contemplation.', 'Auguste Rodin', '1904', 'Modern Sculpture', '186 cm height', '1958-05-30', 'Direct acquisition from Musée Rodin', TRUE, 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=600&fit=crop'),

('Campbell\'s Soup Cans (Complete Set)', 'The complete set of 32 canvases representing each variety of Campbell\'s soup available in 1962. This iconic work launched the Pop Art movement and challenged traditional notions of what constitutes fine art.', 'Andy Warhol', '1962', 'Pop Art', '32 canvases, each 50.8 cm × 40.6 cm', '1995-07-18', 'Estate of Andy Warhol through Leo Castelli Gallery', TRUE, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=500&fit=crop'),

('Water Lilies (Series)', 'One of Monet\'s late water lily paintings from his garden at Giverny. This work exemplifies the artist\'s exploration of light, color, and atmosphere, painted when he was nearly blind but at the height of his artistic vision.', 'Claude Monet', '1919', 'Impressionism', '200 cm × 300 cm', '1970-04-22', 'Private collection, acquired through bequest', TRUE, 'https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=600&h=400&fit=crop'),

('Greek Red-Figure Amphora', 'An exceptional example of ancient Greek pottery featuring scenes from Homer\'s Odyssey. The red-figure technique, invented in Athens around 530 BCE, allowed for greater detail and naturalism in depicting human figures.', 'Attributed to the Achilles Painter', '5th Century BCE', 'Ancient Greek', '45 cm height', '1965-12-10', 'Private collection, Switzerland; acquired through purchase', FALSE, 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=600&fit=crop'),

('Abstract Composition No. 7', 'A vibrant example of Kandinsky\'s mature abstract style, featuring dynamic forms and bold colors that express pure emotion and spirituality. This work demonstrates the artist\'s theory of the spiritual in art.', 'Wassily Kandinsky', '1913', 'Abstract Art', '130 cm × 130 cm', '1985-09-14', 'Acquired from Galerie Maeght, Paris', FALSE, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop'),

('Moonrise, Hernandez', 'One of Ansel Adams\' most famous photographs, capturing the moon rising over a small New Mexico village. This image demonstrates Adams\' mastery of the Zone System and his ability to capture the sublime in nature.', 'Ansel Adams', '1941', 'Photography', '40.6 cm × 50.8 cm', '1978-11-05', 'Gift of the Ansel Adams Publishing Rights Trust', TRUE, 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=500&h=400&fit=crop'),

('Egyptian Sarcophagus of Ankh-ef-en-Sekhmet', 'A beautifully preserved sarcophagus from the Late Period of ancient Egypt, featuring intricate hieroglyphic inscriptions and colorful paintings depicting the journey to the afterlife. The mummy and burial goods are displayed alongside.', 'Unknown Egyptian Artisan', '664-332 BCE', 'Ancient Egyptian', '200 cm × 70 cm × 60 cm', '1955-03-28', 'Egyptian government cultural exchange program', FALSE, 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=600&fit=crop'),

('Digital Infinity', 'A groundbreaking digital art installation that responds to viewer movement and creates infinite variations of color and form. This piece represents the cutting edge of interactive digital art and artificial intelligence in creative expression.', 'Alex Chen', '2023', 'Digital Art', 'Variable dimensions (projection)', '2023-12-01', 'Commissioned work for the museum', TRUE, 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=600&h=400&fit=crop'),

('Untitled (Rothko Chapel Study)', 'A color field painting in Rothko\'s signature style, featuring deep purples and blacks that invite contemplation and spiritual reflection. This work demonstrates the artist\'s belief in art\'s power to evoke profound emotional responses.', 'Mark Rothko', '1964', 'Abstract Expressionism', '228.6 cm × 177.8 cm', '1990-06-15', 'Estate of Mark Rothko through Pace Gallery', FALSE, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=600&fit=crop');

-- Insert virtual tours with videos
INSERT INTO virtual_tours (title, description, duration, image, video_url, highlights, featured) VALUES 
('Complete Museum Tour', 'Experience our entire museum with this comprehensive virtual tour featuring all major galleries and exhibitions.', '45 minutes', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Main Gallery,Modern Art Wing,Renaissance Collection,Sculpture Garden,Photography Gallery', TRUE),

('Modern Masterpieces Gallery', 'Explore our collection of 20th-century art with detailed commentary on each masterpiece.', '20 minutes', 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&h=600&fit=crop', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Abstract Expressionism,Pop Art,Minimalism,Contemporary Sculptures', FALSE),

('Renaissance Collection', 'Journey through the Renaissance period with our curated collection of paintings and sculptures.', '25 minutes', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Italian Masters,Religious Art,Portrait Gallery,Sculpture Hall', FALSE),

('Sculpture Garden', 'Take a peaceful walk through our outdoor sculpture garden featuring contemporary works.', '15 minutes', 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=800&h=600&fit=crop', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Contemporary Sculptures,Garden Landscapes,Interactive Installations', FALSE);

-- Insert sample newsletter subscriptions
INSERT INTO subscriptions (email, name) VALUES 
('visitor1@example.com', 'John Doe'),
('visitor2@example.com', 'Jane Smith'),
('artlover@example.com', 'Art Enthusiast'),
('student@university.edu', 'Sarah Johnson'),
('collector@artworld.com', 'Michael Chen');
